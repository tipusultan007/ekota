<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\Collection;
use App\Models\LoanInstallment;
use App\Models\Member;
use App\Models\SavingsCollection;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Http\Request;
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

        $members = $membersQuery->orderBy('name')->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name.' - '.($member->account_no),
            ];
        });
        $accounts = Account::active()->payment()->orderBy('id')->get();

        $collectors = User::where('status', 'active');
        if (! $user->hasRole('Admin')) {
            $collectors->where('id', $user->id);
        }
        $collectors = $collectors->orderBy('name')->get();

        return view('collections.create', compact('members', 'accounts', 'collectors'));
    }

    public function getTodayCollections()
    {
        $user = Auth::user();

        $savingsQuery = SavingsCollection::with(['member', 'collector', 'savingsAccount'])->whereDate('collection_date', today());
        $loansQuery = LoanInstallment::with(['member', 'collector', 'loanAccount'])->whereDate('payment_date', today());

        // Strict filtering: Everyone except Admin sees only their own collections
        if (! $user->hasRole('Admin')) {
            $savingsQuery->where('collector_id', $user->id);
            $loansQuery->where('collector_id', $user->id);
        }

        $savings = $savingsQuery->latest()->get()->map(function ($item) {
            $item->type = 'savings';
            $item->date = $item->collection_date;

            return $item;
        });

        $loans = $loansQuery->latest()->get()->map(function ($item) {
            $item->type = 'loan';
            $item->date = $item->payment_date;

            return $item;
        });

        $savingsHtml = '';
        if ($savings->isNotEmpty()) {
            foreach ($savings as $item) {
                $savingsHtml .= view('collections.partials._savings_row', compact('item'))->render();
            }
        } else {
            $colspan = Auth::user()->hasRole('Admin') ? 5 : 4;
            $savingsHtml = '<tr><td colspan="'.$colspan.'" class="text-center py-4 text-muted">No savings collections recorded today.</td></tr>';
        }

        $loansHtml = '';
        if ($loans->isNotEmpty()) {
            foreach ($loans as $item) {
                $loansHtml .= view('collections.partials._loan_row', compact('item'))->render();
            }
        } else {
            $colspan = Auth::user()->hasRole('Admin') ? 7 : 6;
            $loansHtml = '<tr><td colspan="'.$colspan.'" class="text-center py-4 text-muted">No loan installments recorded today.</td></tr>';
        }

        return response()->json([
            'savings_html' => $savingsHtml,
            'loans_html' => $loansHtml,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'nullable|numeric|min:0',
            'loan_installment' => 'nullable|numeric|min:0',
            'grace_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'collector_id' => 'nullable|exists:users,id',
        ]);

        if (! $request->filled('amount') && ! $request->filled('loan_installment')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'You must enter an amount for savings or loan installment.'], 422);
            }

            return back()->with('error', 'You must enter an amount for savings or loan installment.')->withInput();
        }

        $member = Member::findOrFail($request->member_id);
        $depositAccount = Account::findOrFail($request->account_id);

        // Security Check
        $this->authorizeAccess($member);

        $collectorId = Auth::id();
        if (Auth::user()->hasRole('Admin') && $request->filled('collector_id')) {
            $collectorId = $request->collector_id;
        }

        // Find associated accounts
        $savingsAccount = $member->savingsAccounts()->where('status', 'active')->first();

        // Prioritize loan_account_id if provided in the request
        if ($request->filled('loan_account_id')) {
            $loanAccount = $member->loanAccounts()->find($request->loan_account_id);
        } else {
            $loanAccount = $member->loanAccounts()->where('status', 'running')->latest()->first();
        }

        if ($request->filled('loan_installment') && $request->loan_installment > 0 && ! $loanAccount) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No active loan account found for this member.'], 422);
            }

            return back()->with('error', 'No active loan account found for this member.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $member, $depositAccount, $savingsAccount, $loanAccount, $collectorId) {
                // --- Process Savings Deposit ---
                if ($savingsAccount && $request->filled('amount') && $request->amount > 0) {
                    $savingsAmount = $request->amount;

                    $savingsCol = SavingsCollection::create([
                        'savings_account_id' => $savingsAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => $collectorId,
                        'amount' => $savingsAmount,
                        'collection_date' => $request->date,
                        'notes' => $request->notes,
                    ]);

                    $this->accountingService->createTransaction(
                        $request->date, 'Savings deposit from '.$member->name,
                        [
                            ['account_id' => $depositAccount->id, 'debit' => $savingsAmount],
                            ['account_id' => Account::where('code', '2010')->first()->id, 'credit' => $savingsAmount],
                        ],
                        $savingsCol
                    );

                    $savingsAccount->increment('current_balance', $savingsAmount);

                    $savingsAccount->next_due_date = DateHelper::calculateNextDueDate(
                        $savingsAccount->opening_date,
                        $savingsAccount->collection_frequency,
                        $savingsAccount->next_due_date
                    );
                    $savingsAccount->save();
                }

                // --- Process Loan Installment ---
                if ($loanAccount && $request->filled('loan_installment') && $request->loan_installment > 0) {
                    $paidAmount = $request->loan_installment;
                    $graceAmount = $request->grace_amount ?? 0;
                    $dueAmount = $loanAccount->total_payable - $loanAccount->total_paid - $loanAccount->grace_amount;

                    if (($paidAmount + $graceAmount) > $dueAmount) {
                        throw new \Exception('Paid amount + Grace cannot be greater than the remaining due for loan account '.$loanAccount->account_no);
                    }

                    $installment = LoanInstallment::create([
                        'loan_account_id' => $loanAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => $collectorId,
                        'installment_no' => ($loanAccount->installments()->count() + 1),
                        'paid_amount' => $paidAmount,
                        'grace_amount' => $graceAmount,
                        'payment_date' => $request->date,
                        'notes' => $request->notes,
                    ]);

                    $principalPart = ($paidAmount + $graceAmount) * ($loanAccount->loan_amount / $loanAccount->total_payable);
                    $interestPart = ($paidAmount + $graceAmount) - $principalPart;

                    $entries = [];
                    if ($paidAmount > 0) {
                        $entries[] = ['account_id' => $depositAccount->id, 'debit' => $paidAmount];
                    }
                    if ($graceAmount > 0) {
                        $loanGraceAccount = Account::where('code', '5030')->firstOrFail();
                        $entries[] = ['account_id' => $loanGraceAccount->id, 'debit' => $graceAmount];
                    }
                    if ($principalPart > 0) {
                        $entries[] = ['account_id' => Account::where('code', '1110')->first()->id, 'credit' => $principalPart];
                    }
                    if ($interestPart > 0) {
                        $entries[] = ['account_id' => Account::where('code', '4010')->first()->id, 'credit' => $interestPart];
                    }

                    if (! empty($entries)) {
                        $this->accountingService->createTransaction(
                            $request->date,
                            'Loan installment from '.$member->name,
                            $entries,
                            $installment
                        );
                    }

                    $loanAccount->increment('total_paid', $paidAmount);
                    $loanAccount->increment('grace_amount', $graceAmount);

                    if ($loanAccount->total_paid + $loanAccount->grace_amount >= $loanAccount->total_payable) {
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

            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage())->withInput();
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
            if (! in_array($member->area_id, $allowedAreaIds)) {
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
            'account_no' => $member->account_no,
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

    public function edit(Collection $collection)
    {
        return view('collections.edit', compact('collection'));
    }

    public function update(Request $request, Collection $collection)
    {
        $request->validate([
            'date' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'loan_installment' => 'nullable|numeric|min:0',
            'grace_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::transaction(function () use ($request, $collection) {
                // 1. Revert Old Effects
                $member = $collection->member;
                $savingsAccount = $member->savingsAccounts()->where('status', 'active')->first();
                $loanAccount = $member->loanAccounts()->where('status', 'running')->first();

                // Revert Withdrawal (if legacy)
                if ($collection->withdraw > 0 && $savingsAccount) {
                    $savingsAccount->increment('current_balance', $collection->withdraw);
                }

                // Revert Deposit
                if ($collection->deposit > 0 && $savingsAccount) {
                    $savingsAccount->decrement('current_balance', $collection->deposit);
                }

                // Revert Loan
                if ($collection->loan_installment > 0 && $loanAccount) {
                    $loanAccount->decrement('total_paid', $collection->loan_installment);
                    if ($collection->grace_amount > 0) {
                        $loanAccount->decrement('grace_amount', $collection->grace_amount);
                    }
                    if ($loanAccount->status == 'paid') {
                        $loanAccount->status = 'running';
                    }
                    $loanAccount->save();
                }

                // Delete Old Accounting Transactions linked to this Collection
                foreach ($collection->transactions as $transaction) {
                    $transaction->delete();
                }

                // 2. Instead of updating Collection, we CREATE separate records (Transitioning)
                $depositAccount = Account::findOrFail($request->account_id);

                // Create Savings Collection if deposit > 0
                if ($request->amount > 0 && $savingsAccount) {
                    $savingsAmount = $request->amount;
                    $sCol = SavingsCollection::create([
                        'savings_account_id' => $savingsAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => $collection->user_id, // Keep original collector
                        'amount' => $savingsAmount,
                        'collection_date' => $request->date,
                        'notes' => $request->notes,
                    ]);

                    $this->accountingService->createTransaction(
                        $request->date, 'Savings deposit from '.$member->name.' (Migrated from legacy)',
                        [
                            ['account_id' => $depositAccount->id, 'debit' => $savingsAmount],
                            ['account_id' => Account::where('code', '2010')->first()->id, 'credit' => $savingsAmount],
                        ],
                        $sCol
                    );
                    $savingsAccount->increment('current_balance', $savingsAmount);
                    $savingsAccount->save();
                }

                // Create Loan Installment if installment > 0
                if ($request->loan_installment > 0 && $loanAccount) {
                    $paidAmount = $request->loan_installment;
                    $graceAmount = $request->grace_amount ?? 0;

                    $installment = LoanInstallment::create([
                        'loan_account_id' => $loanAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => $collection->user_id,
                        'installment_no' => ($loanAccount->installments()->count() + 1),
                        'paid_amount' => $paidAmount,
                        'grace_amount' => $graceAmount,
                        'payment_date' => $request->date,
                        'notes' => $request->notes,
                    ]);

                    $principalPart = ($paidAmount + $graceAmount) * ($loanAccount->loan_amount / $loanAccount->total_payable);
                    $interestPart = ($paidAmount + $graceAmount) - $principalPart;

                    $this->accountingService->createTransaction(
                        $request->date, 'Loan installment from '.$member->name.' (Migrated from legacy)',
                        [
                            ['account_id' => $depositAccount->id, 'debit' => $paidAmount],
                            ['account_id' => Account::where('code', '1110')->first()->id, 'credit' => $principalPart],
                            ['account_id' => Account::where('code', '4010')->first()->id, 'credit' => $interestPart],
                        ],
                        $installment
                    );

                    $loanAccount->increment('total_paid', $paidAmount);
                    $loanAccount->increment('grace_amount', $graceAmount);
                    if ($loanAccount->total_paid + $loanAccount->grace_amount >= $loanAccount->total_payable) {
                        $loanAccount->status = 'paid';
                    }
                    $loanAccount->save();
                }

                // 3. Delete the legacy Collection record
                $collection->delete();

            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Update failed: '.$e->getMessage());
        }

        return redirect()->route('members.show', $collection->member_id)->with('success', 'Collection updated and migrated to separate models successfully.');
    }

    public function destroy(Collection $collection)
    {
        try {
            DB::transaction(function () use ($collection) {
                $member = $collection->member;
                $savingsAccount = $member->savingsAccounts()->where('status', 'active')->first();
                $loanAccount = $member->loanAccounts()->where('status', 'running')->first();

                // Revert Withdrawal
                if ($collection->withdraw > 0 && $savingsAccount) {
                    $savingsAccount->increment('current_balance', $collection->withdraw);
                }

                // Revert Deposit
                if ($collection->deposit > 0 && $savingsAccount) {
                    $savingsAccount->decrement('current_balance', $collection->deposit);
                }

                // Revert Loan
                if ($collection->loan_installment > 0 && $loanAccount) {
                    $loanAccount->decrement('total_paid', $collection->loan_installment);
                    if ($collection->grace_amount > 0) {
                        $loanAccount->decrement('grace_amount', $collection->grace_amount);
                    }
                    if ($loanAccount->status == 'paid') {
                        $loanAccount->status = 'running';
                    }
                    $loanAccount->save();
                }

                // Delete Transactions
                // Check if the relation creates separate Transaction models or just links them.
                // Assuming we need to delete the specific accounting transactions linked to this collection.
                // $collection->transactions is a morphMany to Transaction.
                foreach ($collection->transactions as $transaction) {
                    $transaction->delete();
                }

                $collection->delete();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Delete failed: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Collection deleted successfully.');
    }
}
