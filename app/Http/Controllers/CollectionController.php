<?php
namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\LoanAccount;
use App\Models\LoanInstallment;
use App\Models\SavingsAccount;
use App\Models\SavingsCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }
    /**
     * সমন্বিত কালেকশন ফর্মটি দেখানোর জন্য।
     */
    public function create()
    {
        $user = Auth::user();
        $membersQuery = Member::where('status', 'active');

        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $membersQuery->whereIn('area_id', $areaIds);
        }

        $members = $membersQuery->orderBy('name')->get();
        $accounts = Account::active()->payment()->orderBy('name')->get();


        return view('collections.create', compact('members', 'accounts'));
    }

      public function getTodaySavings()
    {
        $user = Auth::user();
        $query = SavingsCollection::with('member')->whereDate('collection_date', today());
        if ($user->hasRole('Field Worker')) {
            $query->where('collector_id', $user->id);
        }
        $collections = $query->latest()->get();

        // ডেটার উপর লুপ চালিয়ে প্রতিটি আইটেমের জন্য Blade ভিউ রেন্ডার করুন
        $html = '';
        if ($collections->isNotEmpty()) {
            foreach ($collections as $item) {
                $html .= view('collections.partials._savings_row', compact('item'))->render();
            }
        } else {
            $colspan = $user->hasRole('Admin') ? 4 : 3;
            $html = '<tr><td colspan="' . $colspan . '" class="text-center">No savings collected today.</td></tr>';
        }

        return response()->json(['html' => $html]);
    }

    public function getTodayLoans()
    {
        $user = Auth::user();
        $query = LoanInstallment::with('member')->whereDate('payment_date', today());
        if ($user->hasRole('Field Worker')) {
            $query->where('collector_id', $user->id);
        }
        $installments = $query->latest()->get();

        $html = '';
        if ($installments->isNotEmpty()) {
            foreach ($installments as $item) {
                $html .= view('collections.partials._loan_row', compact('item'))->render();
            }
        } else {
            $colspan = $user->hasRole('Admin') ? 4 : 3;
            $html = '<tr><td colspan="' . $colspan . '" class="text-center">No loan installments collected today.</td></tr>';
        }

        return response()->json(['html' => $html]);
    }

    public function store(Request $request)
    {


        $request->validate([
            'member_id' => 'required|exists:members,id',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',

            // প্রতিটি ফিল্ড এখন nullable, কিন্তু required_with ব্যবহার করে শর্ত যোগ করা হয়েছে
            'savings_account_id' => 'nullable|exists:savings_accounts,id|required_with:amount',
            'amount' => 'nullable|numeric|min:1|required_with:savings_account_id',

            'loan_account_id' => 'nullable|exists:loan_accounts,id|required_with:loan_installment',
            'loan_installment' => 'nullable|numeric|min:1|required_with:loan_account_id',
            'notes' => 'nullable|string',
        ]);

        if (!$request->filled('amount') && !$request->filled('loan_installment')) {
             if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'You must enter an amount for either savings or loan installment.'], 422);
        }
            return back()
                ->with('error', 'You must enter an amount for either savings or loan installment.')
                ->withInput();
        }

        $member = Member::findOrFail($request->member_id);
        $depositAccount = Account::findOrFail($request->account_id);

        // নিরাপত্তা যাচাই
        $this->authorizeAccess($member);

        try {
            // সকল অপারেশনের জন্য একটি মাত্র ট্রানজেকশন
            DB::transaction(function () use ($request, $member, $depositAccount) {

                // --- সঞ্চয় আদায় প্রক্রিয়া ---
                if ($request->filled('savings_account_id') && $request->filled('amount')) {
                    $savingsAccount = SavingsAccount::findOrFail($request->savings_account_id);
                    $savingsAmount = $request->amount;

                    $collection = SavingsCollection::create([
                        'savings_account_id' => $savingsAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => Auth::id(),
                        'amount' => $savingsAmount,
                        'collection_date' => $request->date,
                        'notes' => $request->notes,
                    ]);

                    // $collection->transactions()->create([
                    //     'account_id' => $depositAccount->id,
                    //     'type' => 'credit',
                    //     'amount' => $savingsAmount,
                    //     'description' => 'Savings deposit for ' . $member->name,
                    //     'transaction_date' => $request->date,
                    // ]);

                    $this->accountingService->createTransaction(
                        $request->date, 'Savings deposit from ' . $member->name,
                        [ // entries array
                            ['account_id' => $depositAccount->id, 'debit' => $savingsAmount],
                            ['account_id' => Account::where('code', '2010')->first()->id, 'credit' => $savingsAmount],
                        ],
                        $collection
                    );

                  //  $depositAccount->increment('balance', $savingsAmount);
                    $savingsAccount->increment('current_balance', $savingsAmount);

                    $savingsAccount->next_due_date = DateHelper::calculateNextDueDate(
                        $savingsAccount->opening_date,
                        $savingsAccount->collection_frequency,
                        $savingsAccount->next_due_date
                    );
                    $savingsAccount->save();
                }

                // --- ঋণ কিস্তি আদায় প্রক্রিয়া ---
                if ($request->filled('loan_account_id') && $request->filled('loan_installment')) {
                    $loanAccount = LoanAccount::findOrFail($request->loan_account_id);
                    $paidAmount = $request->loan_installment;

                    if (($loanAccount->total_paid + $paidAmount) > $loanAccount->total_payable) {
                        // ট্রানজেকশন ব্যর্থ করার জন্য একটি Exception থ্রো করুন
                        throw new \Exception('Paid amount cannot be greater than the remaining due for loan account ' . $loanAccount->account_no);
                    }

                    $lastInstallment = $loanAccount->installments()->latest()->first();
                    $installment = LoanInstallment::create([
                        'loan_account_id' => $loanAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => Auth::id(),
                        'installment_no' => $lastInstallment ? $lastInstallment->installment_no + 1 : 1,
                        'paid_amount' => $paidAmount,
                        'payment_date' => $request->date,
                        'notes' => $request->notes,
                    ]);

                    // $installment->transactions()->create([
                    //     'account_id' => $depositAccount->id,
                    //     'type' => 'credit',
                    //     'amount' => $paidAmount,
                    //     'description' => 'Loan installment from ' . $member->name,
                    //     'transaction_date' => $request->date,
                    // ]);

                    $principalPart = $paidAmount * ($loanAccount->loan_amount / $loanAccount->total_payable);
                    $interestPart = $paidAmount - $principalPart;

                    $this->accountingService->createTransaction(
                        $request->date,
                        'Loan installment received from ' . $member->name,
                        [
                            // Debit Entry:
                            ['account_id' => $depositAccount->id, 'debit' => $paidAmount],

                            // Credit Entries:
                            ['account_id' => Account::where('code', '1110')->first()->id, 'credit' => $principalPart],
                            ['account_id' => Account::where('code', '4010')->first()->id, 'credit' => $interestPart],
                        ],
                        $installment
                    );


                    //$depositAccount->increment('balance', $paidAmount);
                    $loanAccount->increment('total_paid', $paidAmount);

                    if ($loanAccount->total_paid >= $loanAccount->total_payable) {
                        $loanAccount->status = 'paid';
                    }

                    $loanAccount->next_due_date = DateHelper::calculateNextDueDate(
                        $loanAccount->disbursement_date,
                        $loanAccount->installment_frequency,
                        $loanAccount->next_due_date
                    );

                    $loanAccount->save();
                }

            });
        } catch (\Exception $e) {
             if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
        if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Collection(s) recorded successfully.',
                ]);
            }
        return redirect()->back()->with('success', 'Collection(s) recorded successfully.');
    }


    private function authorizeAccess(Member $member)
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return;
        }

        if ($user->hasRole('Field Worker')) {
            $allowedAreaIds = $user->areas()->pluck('areas.id')->toArray();
            if (!in_array($member->area_id, $allowedAreaIds)) {
                abort(403, 'UNAUTHORIZED ACTION. You do not have permission to access members from this area.');
            }
        }
    }
    /**
     * API: একজন নির্দিষ্ট সদস্যের সকল সক্রিয় সঞ্চয় ও ঋণ অ্যাকাউন্ট প্রদান করবে।
     */
    public function getMemberAccounts(Member $member)
    {
        $memberDetails = [
            'name' => $member->name,
            'phone' => $member->mobile_no,
            'address' => $member->address,
            'photo_url' => $member->getFirstMediaUrl('member_photo', 'thumb') ?: 'https://placehold.co/80x80',
        ];

        $savingsAccounts = $member->savingsAccounts()->where('status', 'active')->get();
        $loanAccounts = $member->loanAccounts()->where('status', 'running')->get();

        // রেসপন্সে সদস্যের তথ্য যোগ করে দিন
        return response()->json([
            'member' => $memberDetails,
            'savings' => $savingsAccounts,
            'loans' => $loanAccounts,
        ]);
    }
}
