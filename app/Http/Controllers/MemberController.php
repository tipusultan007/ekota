<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Http\Controllers\Traits\TransactionReversalTrait;
use App\Models\Account;
use App\Models\Area;
use App\Models\Guarantor;
use App\Models\Member;
use App\Models\SavingsCollection;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    use TransactionReversalTrait;

    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Member::with('area');

        // ভূমিকা অনুযায়ী বেস কোয়েরি ফিল্টার
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereIn('area_id', $areaIds);
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('account_no')) {
            $query->where('account_no', 'like', '%'.$request->account_no.'%');
        }
        if ($request->filled('mobile_no')) {
            $query->where('mobile_no', 'like', '%'.$request->mobile_no.'%');
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // ---------------------------------------------

        $members = $query->orderByRaw('CAST(account_no AS UNSIGNED) ASC')->paginate(25);
        $totalMembers = $members->total();

        // ফিল্টারের ড্রপডাউনের জন্য ডেটা
        if ($user->hasRole('Admin')) {
            $areas = \App\Models\Area::orderBy('name')->get(['id', 'name']);
        } else {
            $areas = $user->areas()->orderBy('name')->get(['areas.id', 'areas.name']);
        }

        return view('members.index', compact('members', 'totalMembers', 'areas'));
    }

    public function create()
    {
        $areas = Area::where('is_active', true)->get();

        return view('members.create', compact('areas'));
    }

    public function store(Request $request)
    {
        // ধাপ ১: নতুন ফিল্ডসহ পূর্ণাঙ্গ ভ্যালিডেশন
        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mobile_no' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', Rule::unique('members')],
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'area_id' => 'nullable|exists:areas,id',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'nid_no' => ['nullable', 'string', 'max:20'],
            'gender' => 'nullable|string|in:male,female,other',
            'marital_status' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'occupation' => 'nullable|string',
            'work_place' => 'nullable|string',
            'spouse_name' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nominee_name' => 'nullable|string|max:255',
            'nominee_relation' => 'nullable|string|max:255',
            'nominee_nid' => 'nullable|string|max:20',
            'nominee_phone' => 'nullable|string|max:20',
            'nominee_address' => 'nullable|string',
        ]);

        $user = Auth::user();

        // নিরাপত্তা যাচাই: মাঠকর্মী কি তার নির্ধারিত এলাকার বাইরে সদস্য যোগ করছেন?
        if ($user->hasRole('Field Worker')) {
            $allowedAreaIds = $user->areas()->pluck('areas.id')->toArray();
            if ($request->filled('area_id') && ! in_array($request->area_id, $allowedAreaIds)) {
                return back()->with('error', 'You are not authorized to add a member to this area.')->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request) {
                // ধাপ ২: শুধুমাত্র fillable ফিল্ডগুলো দিয়ে সদস্য তৈরি করুন
                $memberData = $request->only([
                    'account_no', 'area_id', 'name', 'father_name', 'mother_name', 'mobile_no', 'email',
                    'date_of_birth', 'nid_no', 'present_address', 'permanent_address',
                    'joining_date', 'gender', 'marital_status', 'nationality', 'religion',
                    'blood_group', 'occupation', 'work_place', 'spouse_name', 'status',
                    'nominee_name', 'nominee_relation', 'nominee_nid', 'nominee_phone', 'nominee_address',
                ]);

                if (empty($memberData['joining_date'])) {
                    $memberData['joining_date'] = now();
                }
                if (empty($memberData['account_no'])) {
                    $memberData['account_no'] = Member::generateNextMemberAccountNumber();
                }
                $member = Member::create($memberData);

                // automatically create a savings account
                $member->savingsAccounts()->create([
                    'member_id' => $member->id,
                    'account_no' => $member->account_no,
                    'scheme_type' => 'General',
                    'interest_rate' => 0,
                    'opening_date' => $member->joining_date ?? now(),
                    'status' => 'active',
                ]);

                // ধাপ ৩: Spatie Media Library ব্যবহার করে ফাইল আপলোড
                if ($request->hasFile('photo')) {
                    $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
                }
                if ($request->hasFile('signature')) {
                    $member->addMediaFromRequest('signature')->toMediaCollection('member_signature');
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while creating the member: '.$e->getMessage())->withInput();
        }

        return redirect()->route('members.index')->with('success', 'Member created successfully.');
    }

    public function show(Member $member)
    {
        $this->authorizeAccess($member);
        // eager load the relationships
        $member->load('savingsAccounts', 'loanAccounts');

        $recentSavings = SavingsCollection::where('member_id', $member->id)
            ->with(['savingsAccount', 'collector'])
            ->latest('collection_date')
            ->paginate(10, ['*'], 'savings_page');

        $recentInstallments = \App\Models\LoanInstallment::where('member_id', $member->id)
            ->with(['loanAccount', 'collector'])
            ->latest('payment_date')
            ->paginate(10, ['*'], 'installments_page');

        $accounts = Account::active()->payment()->orderBy('id')->get();
        
        $collectors = collect();
        if (Auth::user()->hasRole('Admin')) {
            $collectors = \App\Models\User::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        } else {
            $collectors = collect([Auth::user()]);
        }

        return view('members.show', compact('member', 'recentSavings', 'recentInstallments', 'accounts', 'collectors'));
    }

    public function edit(Member $member)
    {
        // নিরাপত্তা যাচাই: মাঠকর্মী কি তার এলাকার বাইরের সদস্য এডিট করার চেষ্টা করছে?
        $this->authorizeAccess($member);

        $areas = Area::where('is_active', true)->get();

        return view('members.edit', compact('member', 'areas'));
    }

    public function update(Request $request, Member $member)
    {
        // নিরাপত্তা যাচাই: মাঠকর্মী কি তার এলাকার বাইরের সদস্য এডিট করার চেষ্টা করছে?
        $user = Auth::user();
        if ($user->hasRole('Field Worker')) {
            $allowedAreaIds = $user->areas()->pluck('areas.id')->toArray();
            if ($member->area_id && ! in_array($member->area_id, $allowedAreaIds)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }

        // ধাপ ১: নতুন ফিল্ডসহ পূর্ণাঙ্গ ভ্যালিডেশন
        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mobile_no' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', Rule::unique('members')->ignore($member->id)],
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'area_id' => 'required|exists:areas,id',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',

            'nid_no' => ['nullable', 'string', 'max:20'],
            'gender' => 'nullable|string|in:male,female,other',
            'marital_status' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'occupation' => 'nullable|string',
            'work_place' => 'nullable|string',
            'spouse_name' => 'nullable|string',

            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nominee_name' => 'nullable|string|max:255',
            'nominee_relation' => 'nullable|string|max:255',
            'nominee_nid' => 'nullable|string|max:20',
            'nominee_phone' => 'nullable|string|max:20',
            'nominee_address' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $member) {
                // ধাপ ২: শুধুমাত্র fillable ফিল্ডগুলো দিয়ে সদস্যের তথ্য আপডেট করুন
                $memberData = $request->only([
                    'area_id', 'name', 'father_name', 'mother_name', 'mobile_no', 'email',
                    'date_of_birth', 'nid_no', 'present_address', 'permanent_address',
                    'joining_date', 'gender', 'marital_status', 'nationality', 'religion',
                    'blood_group', 'occupation', 'work_place', 'spouse_name', 'status',
                    'nominee_name', 'nominee_relation', 'nominee_nid', 'nominee_phone', 'nominee_address',
                ]);
                $member->update($memberData);

                // ধাপ ৩: Spatie Media Library ব্যবহার করে ফাইল আপলোড/আপডেট
                if ($request->hasFile('photo')) {
                    // পুরানো ছবি ডিলিট করে নতুনটি যোগ করুন
                    $member->clearMediaCollection('member_photo');
                    $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
                }
                if ($request->hasFile('signature')) {
                    // পুরানো স্বাক্ষর ডিলিট করে নতুনটি যোগ করুন
                    $member->clearMediaCollection('member_signature');
                    $member->addMediaFromRequest('signature')->toMediaCollection('member_signature');
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the member: '.$e->getMessage())->withInput();
        }

        return redirect()->route('members.show', $member->id)->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        // নিরাপত্তা যাচাই
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($member) {
                $member->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('members.index')->with('error', 'Failed to delete member and all related data. Error: '.$e->getMessage());
        }

        return redirect()->route('members.index')->with('success', 'Member and all associated data have been permanently deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:members,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $members = Member::whereIn('id', $request->ids)->get();
                foreach ($members as $member) {
                    $member->delete();
                }
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete members. Error: '.$e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => __('messages.bulk_delete_success_message')]);
    }

    private function authorizeAccess(Member $member)
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return;
        }

        if ($user->hasRole('Field Worker')) {
            $allowedAreaIds = $user->areas()->pluck('areas.id')->toArray();
            if ($member->area_id && ! in_array($member->area_id, $allowedAreaIds)) {
                abort(403, 'UNAUTHORIZED ACTION. You do not have permission to access members from this area.');
            }
        }
    }

    public function createWithAccount()
    {
        $areas = Area::where('is_active', true)->get();
        $guarantors = Member::all();
        $accounts = Account::active()->payment()->get();
        $next_account_no = Member::generateNextMemberAccountNumber();

        return view('members.new_account', compact('areas', 'accounts', 'guarantors', 'next_account_no'));
    }

    /**
     * Store a new member and their initial savings account.
     * এই মেথডটি সমন্বিত ফর্ম থেকে ডেটা সেভ করবে।
     */
    public function storeWithAccount(Request $request)
    {
        // --- ধাপ ১: পূর্ণাঙ্গ ভ্যালিডেশন ---
        $request->validate([
            // Member validation
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'present_address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'account_no' => ['nullable', 'numeric', Rule::unique('members')],
            'area_id' => 'required|exists:areas,id',
            'photo' => 'nullable|image|max:2048',
            'nominee_name' => 'nullable|string|max:255',
            'nominee_relation' => 'nullable|string|max:255',
            'nominee_nid' => 'nullable|string|max:20',
            'nominee_phone' => 'nullable|string|max:20',
            'nominee_address' => 'nullable|string',

            'initial_deposit' => ['nullable', 'numeric', 'min:0'],

            // --- Loan Account validation (সংশোধিত) ---
            'issue_loan_account' => 'nullable|boolean',

            'loan_amount' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'numeric', 'min:1'],
            'account_id' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'exists:accounts,id'],
            'loan_interest_rate' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'numeric', 'min:0'],
            'number_of_installments' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'integer', 'min:1'],
            'disbursement_date' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'date'],
            'installment_frequency' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'string', 'in:daily,weekly,monthly'],
            // 'guarantor_type' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'in:member,outsider'],
            'guarantor_type' => 'nullable|in:member,outsider',
            'processing_fee' => 'nullable|numeric|min:0',

            // Guarantor conditional validation
            'member_guarantor_id' => [Rule::requiredIf(fn () => $request->boolean('issue_loan_account') && $request->guarantor_type == 'member'), 'nullable', 'exists:members,id'],
            'outsider_name' => [Rule::requiredIf(fn () => $request->boolean('issue_loan_account') && $request->guarantor_type == 'outsider'), 'nullable', 'string', 'max:255'],
            'outsider_phone' => ['nullable', 'string', 'max:20'],
            'outsider_address' => ['nullable', 'string'],

            'guarantor_nid' => 'nullable|image|max:2048',
            'guarantor_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = Auth::user();
        $createdMember = null;
        try {
            DB::transaction(function () use ($request, $user, &$createdMember) {

                // --- ধাপ ২: সদস্য তৈরি করুন ---
                $memberData = $request->only([
                    'account_no', 'name', 'father_name', 'mother_name', 'mobile_no', 'email',
                    'date_of_birth', 'nid_no', 'present_address', 'permanent_address',
                    'joining_date', 'gender', 'marital_status', 'nationality', 'religion',
                    'blood_group', 'occupation', 'work_place', 'spouse_name', 'status', 'area_id',
                    'nominee_name', 'nominee_relation', 'nominee_nid', 'nominee_phone', 'nominee_address',
                ]);
                if (empty($memberData['joining_date'])) {
                    $memberData['joining_date'] = now();
                }
                if (empty($memberData['account_no'])) {
                    $memberData['account_no'] = Member::generateNextMemberAccountNumber();
                }
                $member = Member::create($memberData);
                $createdMember = $member;

                // --- ধাপ ৩: অটোমেটিক সঞ্চয়ী হিসাব তৈরি করুন ---
                $savingsAccount = $member->savingsAccounts()->create([
                    'member_id' => $member->id,
                    'account_no' => $member->account_no,
                    'scheme_type' => 'General',
                    'interest_rate' => 0,
                    'opening_date' => $member->joining_date ?? now(),
                    'current_balance' => $request->initial_deposit ?? 0,
                    'status' => 'active',
                ]);

                // যদি প্রারম্ভিক জমা থাকে, তবে কালেকশন এবংaccounting ট্রানজেকশন রেকর্ড করা উচিত
                $initialDeposit = $request->initial_deposit ?? 0;
                if ($initialDeposit > 0) {
                    $openingDate = Carbon::parse($member->joining_date ?? now());
                    $collection = $savingsAccount->collections()->create([
                        'member_id' => $member->id,
                        'collector_id' => $user->id,
                        'amount' => $initialDeposit,
                        'collection_date' => $openingDate,
                        'notes' => 'Initial deposit during account opening',
                    ]);

                    $cashAccount = Account::where('code', '1010')->firstOrFail();
                    $savingsPayableAccount = Account::where('code', '2010')->firstOrFail();

                    $this->accountingService->createTransaction(
                        $openingDate,
                        'Initial savings deposit from '.$member->name,
                        [
                            ['account_id' => $cashAccount->id, 'debit' => $initialDeposit],
                            ['account_id' => $savingsPayableAccount->id, 'credit' => $initialDeposit],
                        ],
                        $collection
                    );

                    // Update next due date for the automatic account
                    $savingsAccount->next_due_date = DateHelper::calculateNextDueDate(
                        $savingsAccount->opening_date,
                        'General',
                        $savingsAccount->next_due_date
                    );
                    $savingsAccount->save();
                }

                if ($request->hasFile('photo')) {
                    $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
                }

                // --- ধাপ ৪: যদি ঋণ অ্যাকাউন্ট খোলার অপশন সিলেক্ট করা হয় ---
                if ($request->boolean('issue_loan_account')) {
                    $disbursementAccount = Account::findOrFail($request->account_id);
                    $loanAmount = $request->loan_amount;

                    // if ($disbursementAccount->balance < $loanAmount) {
                    //     throw new \Exception('Insufficient balance to disburse this loan.');
                    // }

                    $interest = ($loanAmount * $request->loan_interest_rate) / 100;
                    $totalPayable = $loanAmount + $interest;
                    $installmentAmount = $totalPayable / $request->number_of_installments;
                    $disbursementDate = Carbon::parse($request->disbursement_date);

                    $loanAccount = $member->loanAccounts()->create([
                        'account_no' => $member->account_no,
                        'loan_amount' => $loanAmount,
                        'interest_rate' => $request->loan_interest_rate,
                        'number_of_installments' => $request->number_of_installments,
                        'disbursement_date' => $disbursementDate,
                        'installment_frequency' => $request->installment_frequency,
                        'next_due_date' => \App\Helpers\DateHelper::calculateNextDueDate($disbursementDate, $request->installment_frequency),
                        'total_payable' => $totalPayable,
                        'installment_amount' => $installmentAmount,
                    ]);

                    $loansReceivableAccount = Account::where('code', '1110')->firstOrFail();
                    $processingFee = $request->processing_fee ?? 0;
                    $this->accountingService->createTransaction(
                        $request->disbursement_date,
                        'Loan disbursed to '.$member->name,
                        [
                            ['account_id' => $loansReceivableAccount->id, 'debit' => $loanAmount],
                            ['account_id' => $disbursementAccount->id, 'credit' => $loanAmount],
                        ],
                        $loanAccount
                    );

                    if ($processingFee > 0) {
                        $feeIncomeAccount = Account::where('code', '4020')->firstOrFail();
                        $this->accountingService->createTransaction(
                            $request->disbursement_date,
                            'Processing fee income from '.$member->name,
                            [
                                ['account_id' => $disbursementAccount->id, 'debit' => $processingFee],
                                ['account_id' => $feeIncomeAccount->id, 'credit' => $processingFee],
                            ],
                            $loanAccount
                        );
                    }

                    // জামিনদার এবং ডকুমেন্ট তৈরি
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
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during onboarding: '.$e->getMessage())->withInput();
        }

        return redirect()->route('members.index')->with('success', 'Member onboarded successfully.');
    }

    public function search(Request $request)
    {
        $term = $request->term;
        $user = Auth::user();
        $query = Member::query();

        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereIn('area_id', $areaIds);
        }

        $members = $query->where(function ($q) use ($term) {
            $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('account_no', $term);
        })
            ->limit(10)
            ->get(['id', 'name', 'account_no', 'mobile_no']);

        return response()->json($members->map(function ($member) {
            return [
                'id' => $member->id,
                'account_no' => $member->account_no,
                'name' => $member->name,
                'mobile_no' => $member->mobile_no,
                'photo_url' => $member->getFirstMediaUrl('member_photo') ?: 'https://placehold.co/100x100?text='.urlencode(substr($member->name, 0, 1)),
                'profile_url' => route('members.show', $member->id),
            ];
        }));
    }
    public function updateStatus(Request $request, Member $member)
    {
        $this->authorizeAccess($member);

        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $newStatus = $request->status;

        // If trying to deactivate or suspend, check for obligations
        if ($newStatus !== 'active') {
            // Check for due loans
            $hasDueLoan = $member->loanAccounts()
                ->whereRaw('total_payable - total_paid - grace_amount > 0')
                ->exists();

            if ($hasDueLoan) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.cannot_change_status_loan_dues'),
                ], 422);
            }

            // Check for savings balance
            $hasSavingsBalance = $member->savingsAccounts()
                ->where('current_balance', '>', 0)
                ->exists();

            if ($hasSavingsBalance) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.cannot_change_status_savings_balance'),
                ], 422);
            }
        }

        $member->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => __('messages.status_updated_successfully'),
            'new_status' => $newStatus,
            'status_label' => __('messages.' . $newStatus),
        ]);
    }
}
