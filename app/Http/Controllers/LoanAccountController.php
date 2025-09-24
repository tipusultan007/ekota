<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\TransactionReversalTrait;
use App\Models\Account;
use App\Models\Area;
use App\Models\Guarantor;
use App\Models\LoanAccount;
use App\Models\LoanInstallment;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LoanAccountController extends Controller
{
    use TransactionReversalTrait;
    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LoanAccount::with('member.area');

        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereHas('member', fn($q) => $q->whereIn('area_id', $areaIds));
        }

        if ($request->filled('area_id') && $user->hasRole('Admin')) {
            $query->whereHas('member', fn($q) => $q->where('area_id', $request->area_id));
        }
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('disbursement_date', [$request->start_date, $request->end_date]);
        }

        $loanAccounts = $query->latest()->paginate(25);
        $members = Member::orderBy('name')->get(['id', 'name']);
        $areas = Area::orderBy('name')->get(['id', 'name']);

        return view('loan_accounts.index', compact('loanAccounts', 'members', 'areas'));
    }

    public function create(Member $member)
    {
        // নিরাপত্তা যাচাই
        $this->authorizeAccess($member);

        // গ্যারান্টার হিসেবে অন্য সদস্যদের তালিকা
        $guarantors = Member::where('id', '!=', $member->id)->where('status', 'active')->get();
        $accounts = Account::active()->payment()->orderBy('name')->get();

        return view('loan_accounts.create', compact('member', 'guarantors', 'accounts'));
    }

    public function newLoanAccount()
    {
        $members = Member::select('id', 'name', 'mobile_no')->get();
        $guarantors = Member::select('id', 'name', 'mobile_no')->get();
        $accounts = Account::active()->payment()->get();
        return view('loan_accounts.new', compact('members', 'accounts', 'guarantors'));
    }

    public function storeLoanAccount(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'loan_amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1',
            'disbursement_date' => 'required|date',
            'guarantor_type' => 'required|in:member,outsider',
            'installment_frequency' => 'required|string|in:daily,weekly,monthly',

            // শর্তসাপেক্ষ ভ্যালিডেশন
            'member_guarantor_id' => [
                Rule::requiredIf($request->guarantor_type == 'member'),
                'nullable',
                'exists:members,id'
            ],

            // বাইরের জামিনদারের জন্য শর্ত
            'outsider_name' => [
                Rule::requiredIf($request->guarantor_type == 'outsider'),
                'nullable',
                'string',
                'max:255'
            ],
            'outsider_phone' => [
                'nullable',
                'string',
                'max:20'
            ],
            'outsider_address' => [
                'nullable',
                'string'
            ],
            'guarantor_nid' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'guarantor_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            'document_names.*' => 'nullable|string|max:255',
            'loan_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $disbursementAccount = Account::findOrFail($request->account_id);
        $loanAmount = $request->loan_amount;
        $member = Member::find($request->member_id);


        DB::beginTransaction();

        try {
            // ধাপ ১: ঋণ অ্যাকাউন্ট তৈরি করুন
            $interest = ($loanAmount * $request->interest_rate) / 100;
            $total_payable = $loanAmount + $interest;
            $installment_amount = $total_payable / $request->number_of_installments;
            $disbursementDate = Carbon::parse($request->disbursement_date);

            $nextDueDate = \App\Helpers\DateHelper::calculateNextDueDate($disbursementDate, $request->installment_frequency);

            $loanAccount = LoanAccount::create([
                'member_id' => $member->id,
                'account_no' => 'LOAN-' . $member->id . '-' . time(),
                'loan_amount' => $loanAmount,
                'interest_rate' => $request->interest_rate,
                'number_of_installments' => $request->number_of_installments,
                'disbursement_date' => $disbursementDate,
                'total_payable' => $total_payable,
                'installment_amount' => $installment_amount,
                'installment_frequency' => $request->installment_frequency,
                'next_due_date' => $nextDueDate,
            ]);

            // ধাপ ২: অ্যাকাউন্টিং ইন্টিগ্রেশন
            $loanAccount->transactions()->create([
                'account_id' => $disbursementAccount->id,
                'type' => 'debit',
                'amount' => $loanAmount,
                'description' => 'Loan disbursed to member ' . $member->name . ' (A/C: ' . $loanAccount->account_no . ')',
                'transaction_date' => $disbursementDate,
            ]);
            $disbursementAccount->decrement('balance', $loanAmount);

            $guarantorData = ['loan_account_id' => $loanAccount->id];
            if ($request->guarantor_type === 'member') {
                $guarantorData['member_id'] = $request->member_guarantor_id;
            } else {
                $guarantorData['name'] = $request->outsider_name;
                $guarantorData['phone'] = $request->outsider_phone;
                $guarantorData['address'] = $request->outsider_address;
            }
            $guarantor = \App\Models\Guarantor::create($guarantorData);

            if ($request->hasFile('guarantor_nid')) {
                $guarantor->addMediaFromRequest('guarantor_nid')->toMediaCollection('guarantor_nid');
            }
            if ($request->hasFile('guarantor_documents')) {
                foreach ($request->file('guarantor_documents') as $file) {
                    $guarantor->addMedia($file)->toMediaCollection('guarantor_documents');
                }
            }
            if ($request->hasFile('loan_documents')) {
                foreach ($request->file('loan_documents') as $key => $file) {
                    if (isset($request->document_names[$key])) {
                        $loanAccount->addMedia($file)
                            ->withCustomProperties(['document_name' => $request->document_names[$key]])
                            ->toMediaCollection('loan_documents');
                    }
                }
            }

            // সকল কাজ সফল হলে, পরিবর্তনগুলো ডাটাবেসে স্থায়ীভাবে সেভ করুন
            DB::commit();

            // এখন সফলভাবে রিডাইরেক্ট করুন
            return redirect()->route('loan-accounts.show', $loanAccount->id)
                ->with('success', 'Loan disbursed and recorded successfully.');
        } catch (\Exception $e) {
            // যদি কোনো সমস্যা হয়, সকল পরিবর্তন বাতিল করুন
            DB::rollBack();

            return redirect()->back()->with('error', 'Something went wrong! ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Member $member)
    {
        $this->authorizeAccess($member);

        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'loan_amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1',
            'disbursement_date' => 'required|date',
            'guarantor_type' => 'required|in:member,outsider',
            'installment_frequency' => 'required|string|in:daily,weekly,monthly',
            'processing_fee' => 'nullable|numeric|min:0',

            // শর্তসাপেক্ষ ভ্যালিডেশন
            'member_guarantor_id' => 'required_if:guarantor_type,member|exists:members,id',
            'outsider_name' => 'required_if:guarantor_type,outsider|string|max:255',
            'outsider_phone' => 'required_if:guarantor_type,outsider|string|max:20',
            'outsider_address' => 'required_if:guarantor_type,outsider|string',
            'guarantor_nid' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'guarantor_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            'document_names.*' => 'nullable|string|max:255',
            'loan_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $disbursementAccount = Account::findOrFail($request->account_id);
        $loanAmount = $request->loan_amount;
        $processingFee = (float)($request->processing_fee ?? 0);

        try {
            DB::transaction(function () use ($request, $member, $disbursementAccount, $loanAmount, $processingFee) {
                // ধাপ ১: ঋণ অ্যাকাউন্ট তৈরি করুন
                $interest = ($loanAmount * $request->interest_rate) / 100;
                $total_payable = $loanAmount + $interest;
                $installment_amount = $total_payable / $request->number_of_installments;
                $disbursementAccount = Account::findOrFail($request->account_id);


                $disbursementDate = Carbon::parse($request->disbursement_date);

                $nextDueDate = \App\Helpers\DateHelper::calculateNextDueDate($disbursementDate, $request->installment_frequency);

                $loanAccount = LoanAccount::create([
                    'member_id' => $member->id,
                    'account_no' => 'LOAN-' . $member->id . '-' . time(),
                    'loan_amount' => $loanAmount,
                    'interest_rate' => $request->interest_rate,
                    'number_of_installments' => $request->number_of_installments,
                    'disbursement_date' => $disbursementDate,
                    'total_payable' => $total_payable,
                    'installment_amount' => $installment_amount,
                    'installment_frequency' => $request->installment_frequency,
                    'next_due_date' => $nextDueDate,
                    'processing_fee' => $processingFee,
                ]);

                $loansReceivableAccount = Account::where('code', '1110')->firstOrFail();
                $this->accountingService->createTransaction(
                    $request->disbursement_date,
                    'Loan disbursed to ' . $member->name,
                    [
                        // Debit Entry:
                        ['account_id' => $loansReceivableAccount->id, 'debit' => $loanAmount],
                        // Credit Entry:
                        ['account_id' => $disbursementAccount->id, 'credit' => $loanAmount],
                    ],
                    $loanAccount // এই লেনদেনটি LoanAccount মডেলের সাথে যুক্ত
                );

                if ($processingFee > 0) {
                    $feeIncomeAccount = Account::where('code', '4020')->firstOrFail();
                    $this->accountingService->createTransaction(
                        $request->disbursement_date,
                        'Processing fee income from ' . $member->name,
                        [
                            // Debit Entry:
                            ['account_id' => $disbursementAccount->id, 'debit' => $processingFee],
                            // Credit Entry:
                            ['account_id' => $feeIncomeAccount->id, 'credit' => $processingFee],
                        ],
                        $loanAccount // এই লেনদেনটিও LoanAccount মডেলের সাথে যুক্ত
                    );
                }

                // খ) অ্যাকাউন্ট থেকে ব্যালেন্স বিয়োগ করুন
                //$disbursementAccount->decrement('balance', $loanAmount);


                // ধাপ ৩: গ্যারান্টার এবং ডকুমেন্ট পরিচালনা (অপরিবর্তিত)
                $guarantorData = ['loan_account_id' => $loanAccount->id];
                if ($request->guarantor_type === 'member') {
                    $guarantorData['member_id'] = $request->member_guarantor_id;
                } else {
                    $guarantorData['name'] = $request->outsider_name;
                    $guarantorData['phone'] = $request->outsider_phone;
                    $guarantorData['address'] = $request->outsider_address;
                }

                $guarantor = \App\Models\Guarantor::create($guarantorData);

                if ($request->hasFile('guarantor_nid')) {
                    $guarantor->addMediaFromRequest('guarantor_nid')->toMediaCollection('guarantor_nid');
                }
                if ($request->hasFile('guarantor_documents')) {
                    foreach ($request->file('guarantor_documents') as $file) {
                        $guarantor->addMedia($file)->toMediaCollection('guarantor_documents');
                    }
                }
                if ($request->hasFile('loan_documents')) {
                    foreach ($request->file('loan_documents') as $key => $file) {
                        if (isset($request->document_names[$key])) {
                            $loanAccount->addMedia($file)
                                ->withCustomProperties(['document_name' => $request->document_names[$key]])
                                ->toMediaCollection('loan_documents');
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong! ' . $e->getMessage())->withInput();
        }

        return redirect()->route('members.show', $member->id)->with('success', 'Loan disbursed and recorded successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show(LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই: ব্যবহারকারীর কি এই ঋণটি দেখার অনুমতি আছে?
        $user = Auth::user();
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            if (!in_array($loanAccount->member->area_id, $areaIds)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }

        // প্রয়োজনীয় সকল সম্পর্ক লোড করুন
        $loanAccount->load('member', 'guarantor.member', 'installments.collector');
        $accounts = Account::active()->payment()->orderBy('name')->get();


        return view('loan_accounts.show', compact('loanAccount', 'accounts'));
    }
    // একটি হেল্পার ফাংশন যা অ্যাক্সেস নিয়ন্ত্রণ করবে
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
     * Mark a loan as fully paid off by accepting the remaining due amount.
     */
    /*public function payOff(Request $request, LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        // যদি ঋণটি আগে থেকেই পরিশোধিত থাকে
        if ($loanAccount->status !== 'running') {
            return back()->with('error', 'This loan is not in a running state.');
        }

        $dueAmount = $loanAccount->total_payable - $loanAccount->total_paid;
        $graceAmount = (float)($request->grace_amount ?? 0);

        // ভ্যালিডেশন
        $request->validate([
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'grace_amount' => 'nullable|numeric|min:0|max:' . $dueAmount,
        ]);

        $finalPayment = $dueAmount - $graceAmount;

        if ($dueAmount <= 0) {
            return back()->with('error', 'No due amount remaining for this loan.');
        }

        DB::transaction(function () use ($request, $loanAccount, $dueAmount) {

            // ধাপ ১: বকেয়া টাকার সমপরিমাণ একটি চূড়ান্ত কিস্তি তৈরি করুন
            LoanInstallment::create([
                'loan_account_id' => $loanAccount->id,
                'member_id' => $loanAccount->member_id,
                'collector_id' => Auth::id(),
                'installment_no' => ($loanAccount->installments()->count() + 1),
                'paid_amount' => $dueAmount,
                'payment_date' => $request->payment_date,
                'notes' => 'Final pay-off installment. ' . $request->notes,
            ]);

            // ধাপ ২: ঋণের মূল অ্যাকাউন্টের স্ট্যাটাস এবং পরিশোধিত অর্থ আপডেট করুন
            $loanAccount->update([
                'total_paid' => $loanAccount->total_payable,
                'status' => 'paid',
            ]);

        });

        return redirect()->route('loan_accounts.show', $loanAccount->id)
            ->with('success', 'Loan has been successfully paid off.');
    }*/
    /**
     * Mark a loan as fully paid off, accepting the remaining due, applying grace,
     * and creating all necessary accounting transactions.
     */
    /*  public function payOff(Request $request, LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        if ($loanAccount->status !== 'running') {
            return back()->with('error', 'This loan is not in a running state.');
        }

        $dueAmount = $loanAccount->total_payable - $loanAccount->total_paid;

        $request->validate([
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id', // টাকা কোন অ্যাকাউন্টে জমা হচ্ছে
            'grace_amount' => 'nullable|numeric|min:0|max:' . $dueAmount,
            'notes' => 'nullable|string',
        ]);

        $graceAmount = (float)($request->grace_amount ?? 0);
        $finalPayment = $dueAmount - $graceAmount;
        $depositAccount = Account::find($request->account_id);

        if ($dueAmount <= 0) {
            return back()->with('error', 'No due amount remaining for this loan.');
        }

        try {
            DB::transaction(function () use ($request, $loanAccount, $depositAccount, $dueAmount, $graceAmount, $finalPayment) {

                // ধাপ ১: যদি কোনো চূড়ান্ত পেমেন্ট থাকে, তাহলে তার জন্য একটি কিস্তি এবং লেনদেন তৈরি করুন
                if ($finalPayment > 0) {
                    $installment = LoanInstallment::create([
                        'loan_account_id' => $loanAccount->id,
                        'member_id' => $loanAccount->member_id,
                        'collector_id' => Auth::id(),
                        'installment_no' => ($loanAccount->installments()->count() + 1),
                        'paid_amount' => $finalPayment,
                        'grace_amount' => $graceAmount ?? 0,
                        'payment_date' => $request->payment_date,
                        'notes' => 'Final pay-off installment. ' . $request->notes,
                    ]);

                    // অ্যাকাউন্টিং: ক্যাশ/ব্যাংক অ্যাকাউন্টে টাকা জমা (ক্রেডিট) করুন
                    $installment->transactions()->create([
                        'account_id' => $depositAccount->id,
                        'type' => 'credit',
                        'amount' => $finalPayment,
                        'description' => 'Final loan pay-off from ' . $loanAccount->member->name,
                        'transaction_date' => $request->payment_date,
                    ]);
                    $depositAccount->increment('balance', $finalPayment);
                }

                // ধাপ ২: যদি কোনো ছাড় দেওয়া হয়, তাহলে সেটিকে একটি খরচ হিসেবে রেকর্ড করুন
                if ($graceAmount > 0) {
                    // আমরা আর expenses টেবিলে কোনো এন্ট্রি দিচ্ছি না।
                    // এর পরিবর্তে, আমরা একটি "Loan Interest Income" অ্যাকাউন্ট এবং একটি "Loan Grace" অ্যাকাউন্টে
                    // বিপরীতমুখী লেনদেন তৈরি করতে পারি।
                    // সরলতার জন্য, আমরা শুধু বিবরণ দিয়ে একটি লেনদেন তৈরি করব।

                    // একটি "Loan Grace" বা "Income Adjustment" অ্যাকাউন্ট তৈরি করুন (Chart of Accounts-এ)
                    // ধরে নিচ্ছি এর আইডি 6
                    $graceAccount = Account::firstOrCreate(['name' => 'Loan Grace Adjustment']);

                    // ক) Loan Grace অ্যাকাউন্টে ডেবিট করুন (খরচ বাড়ানো)
                    $graceAccount->transactions()->create([
                        'type' => 'debit',
                        'amount' => $graceAmount,
                        'description' => 'Grace given for loan pay-off to ' . $loanAccount->member->name . ' (A/C: ' . $loanAccount->account_no . ')',
                        'transaction_date' => $request->payment_date,
                    ]);
                    // এই অ্যাকাউন্টের ব্যালেন্স সাধারণত ডেবিট দিকে বাড়বে।

                    // খ) অর্জিত সুদ (Interest Income) থেকে কমানোর জন্য একটি বিপরীতমুখী এন্ট্রি
                    // এটি আরও অ্যাডভান্সড অ্যাকাউন্টিং, আপাতত আমরা শুধু একটি খরচ হিসেবে দেখাচ্ছি
                    // কিন্তু নগদ টাকা কমাচ্ছি না।
                }

                // ধাপ ৩: ঋণের মূল অ্যাকাউন্টের স্ট্যাটাস এবং পরিশোধিত অর্থ আপডেট করুন
                $loanAccount->update([
                    'total_paid' => $loanAccount->total_paid + $finalPayment,
                    'grace_amount' => $loanAccount->grace_amount + $graceAmount,
                    'status' => 'paid',
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->route('loan_accounts.show', $loanAccount->id)->with('error', 'An error occurred: ' . $e->getMessage());
        }

        return redirect()->route('loan_accounts.show', $loanAccount->id)
            ->with('success', 'Loan has been successfully paid off.');
    } */

    public function payOff(Request $request, LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        if ($loanAccount->status !== 'running') {
            return back()->with('error', 'This loan is not in a running state.');
        }

        $dueAmount = $loanAccount->total_payable - $loanAccount->total_paid;

        $request->validate([
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'grace_amount' => 'nullable|numeric|min:0|max:' . $dueAmount,
            'notes' => 'nullable|string',
        ]);

        $graceAmount = (float)($request->grace_amount ?? 0);
        $finalPayment = $dueAmount - $graceAmount;

        if ($dueAmount <= 0) {
            return back()->with('error', 'No due amount remaining for this loan.');
        }

        try {
            DB::transaction(function () use ($request, $loanAccount, $graceAmount, $finalPayment, $dueAmount) {

              $depositAccount = Account::findOrFail($request->account_id);
            $installment = null;
            
            // --- ধাপ ১: একটি মাত্র কিস্তির রেকর্ড তৈরি করুন (যদি কোনো পেমেন্ট বা ছাড় থাকে) ---
            if ($finalPayment > 0 || $graceAmount > 0) {
                $installment = $loanAccount->installments()->create([
                    'member_id' => $loanAccount->member_id,
                    'collector_id' => Auth::id(),
                    'installment_no' => ($loanAccount->installments()->count() + 1),
                    'paid_amount' => $finalPayment,
                    'grace_amount' => $graceAmount, // কিস্তির সাথে grace amount রেকর্ড করুন
                    'payment_date' => $request->payment_date,
                    'notes' => 'Final pay-off installment. ' . $request->notes,
                ]);
            }
            
            // --- ধাপ ২: একটি মাত্র যৌগিক জাবেদা দাখিলা (Compound Journal Entry) তৈরি করুন ---
            
            // বকেয়ার আসল এবং সুদ আলাদা করুন
            $duePrincipalPart = $dueAmount * ($loanAccount->loan_amount / $loanAccount->total_payable);
            $dueInterestPart = $dueAmount - $duePrincipalPart;
            
            $loansReceivableAccount = Account::where('code', '1110')->firstOrFail();
            $interestIncomeAccount = Account::where('code', '4010')->firstOrFail();
            $loanGraceAccount = Account::where('code', '5030')->firstOrFail(); // Loan Grace Expense Account

            $entries = [];

            // ক) Debit Entry: ক্যাশ/ব্যাংক-এ মোট যে টাকা জমা হচ্ছে
            if ($finalPayment > 0) {
                $entries[] = ['account_id' => $depositAccount->id, 'debit' => $finalPayment];
            }
            // খ) Debit Entry: প্রদত্ত ছাড় একটি খরচ
            if ($graceAmount > 0) {
                $entries[] = ['account_id' => $loanGraceAccount->id, 'debit' => $graceAmount];
            }

            // গ) Credit Entry: ঋণের আসল (Receivable) কমানো হচ্ছে
            // যদি ছাড় দেওয়া হয়, তাহলে সুদ থেকে আগে বাদ যাবে
            $interestToClear = min($dueInterestPart, $finalPayment);
            $principalToClear = $finalPayment - $interestToClear;
            
            // যা কিছু বাকি থাকলো, তা grace বা আসল থেকে ক্লিয়ার হবে
            $remainingGrace = $graceAmount - ($dueInterestPart - $interestToClear);
            if ($remainingGrace > 0) {
                 $principalToClear += $remainingGrace;
            }

            $entries[] = ['account_id' => $loansReceivableAccount->id, 'credit' => $duePrincipalPart];
            
            // ঘ) Credit Entry: ঋণের সুদ (Income) কমানো হচ্ছে
            $entries[] = ['account_id' => $interestIncomeAccount->id, 'credit' => $dueInterestPart];

            // অ্যাকাউন্টিং সার্ভিস কল করুন (যদি কোনো লেনদেন থাকে)
            if (!empty($entries)) {
                $this->accountingService->createTransaction(
                    $request->payment_date,
                    'Loan Pay-off for ' . $loanAccount->member->name,
                    $entries,
                    $installment ?? $loanAccount // যদি কোনো পেমেন্ট না থাকে, তাহলে LoanAccount-এর সাথে যুক্ত করুন
                );
            }

                // --- ধাপ ৩: ঋণের মূল অ্যাকাউন্টের স্ট্যাটাস এবং পরিশোধিত অর্থ আপডেট করুন ---
                // $loanAccount->update([
                //     // total_paid এখন total_payable এর সমান হবে, কারণ হিসাবটি বন্ধ
                //     'total_paid' => $loanAccount->total_payable,
                //     'grace_amount' => $loanAccount->grace_amount + $graceAmount,
                //     'status' => 'paid',
                // ]);
                $loanAccount->increment('total_paid', $finalPayment);
                $loanAccount->increment('grace_amount', $graceAmount);
                $loanAccount->status = 'paid';
                $loanAccount->save();
            });
        } catch (\Exception $e) {
            return redirect()->route('loan_accounts.show', $loanAccount->id)->with('error', 'An error occurred: ' . $e->getMessage());
        }

        return redirect()->route('loan_accounts.show', $loanAccount->id)
            ->with('success', 'Loan has been successfully paid off.');
    }
    /**
     * Show the form for editing the specified resource.
     * ঋণ অ্যাকাউন্ট সম্পাদনা করার ফর্ম দেখাবে।
     */
    public function edit(LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }
        if ($loanAccount->status !== 'running') {
            return redirect()->route('loan_accounts.show', $loanAccount->id)
                ->with('error', 'This loan account cannot be edited because it is already ' . $loanAccount->status . '.');
        }
        $guarantors = Member::where('id', '!=', $loanAccount->member->id)->where('status', 'active')->get();
        $accounts = Account::active()->payment()->orderBy('name')->get();

        return view('loan_accounts.edit', compact('loanAccount', 'guarantors', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     * ঋণ অ্যাকাউন্টের তথ্য আপডেট করবে এবং পুনরায় গণনা চালাবে।
     */
    // app/Http/Controllers/LoanAccountController.php

    public function update(Request $request, LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'loan_amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1',
            'status' => 'required|string|in:running,paid,defaulted',
            'disbursement_date' => 'required|date',
            'installment_frequency' => 'required|string|in:daily,weekly,monthly',
            'processing_fee' => 'nullable|numeric|min:0',
            'member_guarantor_id' => [
                Rule::requiredIf($request->guarantor_type == 'member'),
                'nullable',
                'exists:members,id'
            ],

            // বাইরের জামিনদারের জন্য শর্ত
            'outsider_name' => [
                Rule::requiredIf($request->guarantor_type == 'outsider'),
                'nullable',
                'string',
                'max:255'
            ],
            'outsider_phone' => [
                'nullable',
                'string',

                'max:20'
            ],
            'outsider_address' => [
                'nullable',
                'string'
            ],
            'guarantor_nid' => 'nullable|image|max:2048',
            'guarantor_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'document_names.*' => 'nullable|string|max:255',
            'loan_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'existing_documents_to_delete' => 'nullable|array',
        ]);


        try {
            DB::transaction(function () use ($request, $loanAccount) {

                // ক) ঋণ বিতরণের পুরানো লেনদেন
                $oldDisbursementTransaction = $loanAccount->transactions()->where('description', 'like', 'Loan disbursed%')->first();
                if ($oldDisbursementTransaction) {
                    $this->reverseTransaction($oldDisbursementTransaction);
                }

                // খ) প্রক্রিয়াকরণ ফি-এর পুরানো লেনদেন
                $oldFeeTransaction = $loanAccount->transactions()->where('description', 'like', 'Processing fee%')->first();
                if ($oldFeeTransaction) {
                    $this->reverseTransaction($oldFeeTransaction);
                }

                // =============================================================
                // === ধাপ ২: নতুন তথ্য দিয়ে ঋণ অ্যাকাউন্ট আপডেট করুন ===
                // =============================================================
                $newLoanAmount = (float) $request->loan_amount;
                $newProcessingFee = (float) ($request->processing_fee ?? 0);
                $disbursementDate = Carbon::parse($request->disbursement_date);

                $interest = ($newLoanAmount * $request->interest_rate) / 100;
                $newTotalPayable = $newLoanAmount + $interest;
                $newInstallmentAmount = $newTotalPayable / $request->number_of_installments;

                $loanAccount->update([
                    'loan_amount' => $newLoanAmount,
                    'processing_fee' => $newProcessingFee,
                    'interest_rate' => $request->interest_rate,
                    'number_of_installments' => $request->number_of_installments,
                    'status' => $request->status,
                    'disbursement_date' => $disbursementDate,
                    'installment_frequency' => $request->installment_frequency,
                    'total_payable' => $newTotalPayable,
                    'installment_amount' => $newInstallmentAmount,
                    'next_due_date' => \App\Helpers\DateHelper::calculateNextDueDate($disbursementDate, $request->installment_frequency, $loanAccount),
                ]);

                // =============================================================
                // === ধাপ ৩: নতুন অ্যাকাউন্টিং লেনদেন তৈরি করুন (Repopost) ===
                // =============================================================
                $disbursementAccount = Account::findOrFail($request->account_id);

                // ক) নতুন ঋণ বিতরণের জন্য ডাবল-এন্ট্রি
                $loansReceivableAccount = Account::where('code', '1110')->firstOrFail();
                $this->accountingService->createTransaction(
                    $disbursementDate,
                    'Loan disbursed to ' . $loanAccount->member->name . ' (Updated)',
                    [['account_id' => $loansReceivableAccount->id, 'debit' => $newLoanAmount], ['account_id' => $disbursementAccount->id, 'credit' => $newLoanAmount]],
                    $loanAccount
                );

                // খ) নতুন প্রক্রিয়াকরণ ফি আয়ের জন্য ডাবল-এন্ট্রি
                if ($newProcessingFee > 0) {
                    $feeIncomeAccount = Account::where('code', '4020')->firstOrFail();
                    $this->accountingService->createTransaction(
                        $disbursementDate,
                        'Processing fee from ' . $loanAccount->member->name . ' (Updated)',
                        [['account_id' => $disbursementAccount->id, 'debit' => $newProcessingFee], ['account_id' => $feeIncomeAccount->id, 'credit' => $newProcessingFee]],
                        $loanAccount
                    );
                }

                // === ধাপ ৪: জামিনদারের তথ্য আপডেট ===
                if ($loanAccount->guarantor) {
                    $loanAccount->guarantor->delete(); // পুরানো জামিনদার ডিলিট
                }

                $guarantorData = ['loan_account_id' => $loanAccount->id];
                if ($request->guarantor_type === 'member') {
                    $guarantorData['member_id'] = $request->member_guarantor_id;
                } else {
                    $guarantorData['name'] = $request->outsider_name;
                    $guarantorData['phone'] = $request->outsider_phone;
                    $guarantorData['address'] = $request->outsider_address;
                }
                $guarantor = Guarantor::create($guarantorData);

                if ($request->hasFile('guarantor_nid')) {
                    $guarantor->addMediaFromRequest('guarantor_nid')->toMediaCollection('guarantor_nid');
                }
                if ($request->hasFile('guarantor_documents')) {
                    foreach ($request->file('guarantor_documents') as $file) {
                        $guarantor->addMedia($file)->toMediaCollection('guarantor_documents');
                    }
                }

                // === ধাপ ৫: ঋণের ডকুমেন্ট পরিচালনা ===
                if ($request->filled('existing_documents_to_delete')) {
                    foreach ($request->existing_documents_to_delete as $mediaId) {
                        $mediaItem = $loanAccount->getMedia('loan_documents')->find($mediaId);
                        if ($mediaItem) $mediaItem->delete();
                    }
                }
                if ($request->hasFile('loan_documents')) {
                    foreach ($request->file('loan_documents') as $key => $file) {
                        if (isset($request->document_names[$key])) {
                            $loanAccount->addMedia($file)->withCustomProperties(['document_name' => $request->document_names[$key]])->toMediaCollection('loan_documents');
                        }
                    }
                }
            });
        } catch (\Exception $e) {

            return back()->with('error', 'An error occurred during update: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('loan_accounts.show', $loanAccount->id)->with('success', 'Loan account updated successfully.');
    }

    // private function reverseTransaction(\App\Models\Transaction $transaction)
    // {
    //     foreach ($transaction->journalEntries as $entry) {
    //         $account = $entry->account;
    //         if ($entry->debit > 0) {
    //             // ডেবিটের উল্টো ক্রেডিট করে ব্যালেন্স পুনরুদ্ধার
    //             $account->handleCredit($entry->debit);
    //         }
    //         if ($entry->credit > 0) {
    //             // ক্রেডিটের উল্টো ডেবিট করে ব্যালেন্স পুনরুদ্ধার
    //             $account->handleDebit($entry->credit);
    //         }
    //     }
    //     // জার্নাল এন্ট্রি এবং মূল লেনদেন ডিলিট করুন
    //     $transaction->journalEntries()->delete();
    //     $transaction->delete();
    // }

    /**
     * Helper to calculate the next due date on update.
     */
    private function calculateNextDueDate(Carbon $baseDate, $frequency, LoanAccount $loanAccount)
    {
        $lastInstallment = $loanAccount->installments()->latest('payment_date')->first();
        $dateToUse = $lastInstallment ? Carbon::parse($lastInstallment->payment_date) : $baseDate;

        if ($frequency == 'daily') return $dateToUse->addDay();
        if ($frequency == 'weekly') return $dateToUse->addWeek();
        if ($frequency == 'monthly') return $dateToUse->addMonth();

        return $baseDate->addMonth(); // Default
    }

    /*    public function destroy(LoanAccount $loanAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }



        try {
            DB::transaction(function () use ($loanAccount) {

                // ধাপ ২: সংশ্লিষ্ট সকল কিছু ডিলিট করুন
                // ক) লেনদেন (পলিমরফিক রিলেশন)
                $loanAccount->transactions()->delete();
                // খ) জামিনদার (hasOne রিলেশন)
                $loanAccount->guarantor()->delete();
                // গ) কিস্তি (hasMany রিলেশন - যদিও আমরা আগেই চেক করেছি)
                $loanAccount->installments()->delete();
                // ঘ) ডকুমেন্ট
                $loanAccount->clearMediaCollection('loan_documents');

                // ধাপ ৩: সবশেষে, মূল ঋণ অ্যাকাউন্টটি ডিলিট করুন
                $loanAccount->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('loan_accounts.index')->with('error', 'An error occurred while deleting the loan account: ' . $e->getMessage());
        }

        return redirect()->route('loan_accounts.index')->with('success', 'Loan account deleted successfully and balance restored.');
    }*/

    public function destroy(LoanAccount $loanAccount)
    {
       
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($loanAccount) {

               
                $installments = $loanAccount->installments()->with('transactions.journalEntries.account')->get();

                foreach ($installments as $installment) {
                   
                    foreach ($installment->transactions as $transaction) {
                        $this->reverseTransaction($transaction);
                    }
                }

        
                $directTransactions = $loanAccount->transactions()->with('journalEntries.account')->get();

                foreach ($directTransactions as $transaction) {
                    $this->reverseTransaction($transaction);
                }

            
                $loanAccount->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('loan_accounts.index')
                ->with('error', 'Failed to delete the loan account: ' . $e->getMessage());
        }

        return redirect()->route('loan_accounts.index')->with('success', 'Loan account and all associated transactions have been deleted. Balances are restored.');
    }
}
