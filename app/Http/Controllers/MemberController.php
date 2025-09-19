<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Guarantor;
use App\Models\Member;
use App\Models\Area;
use App\Models\SavingsAccount;
use App\Models\SavingsCollection;
use App\Models\SavingsWithdrawal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Member::with('area');

        // ভূমিকা অনুযায়ী বেস কোয়েরি ফিল্টার
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereIn('area_id', $areaIds);
        }

        // --- রিকোয়েস্ট থেকে আসা ফিল্টারগুলো প্রয়োগ করুন ---
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('mobile_no')) {
            $query->where('mobile_no', 'like', '%' . $request->mobile_no . '%');
        }
        if ($request->filled('area_id') && $user->hasRole('Admin')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // ---------------------------------------------

        $members = $query->latest()->paginate(25);

        // ফিল্টারের ড্রপডাউনের জন্য ডেটা
        $areas = \App\Models\Area::orderBy('name')->get(['id', 'name']);

        return view('members.index', compact('members', 'areas'));
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
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'mobile_no' => ['required', 'string', 'max:20', Rule::unique('members')],
            'email' => ['nullable', 'email', Rule::unique('members')],
            'date_of_birth' => 'required|date',
            'joining_date' => 'required|date',
            'area_id' => 'required|exists:areas,id',
            'present_address' => 'required|string',
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
        ]);

        $user = Auth::user();

        // নিরাপত্তা যাচাই: মাঠকর্মী কি তার নির্ধারিত এলাকার বাইরে সদস্য যোগ করছেন?
        if ($user->hasRole('Field Worker')) {
            $allowedAreaIds = $user->areas()->pluck('areas.id')->toArray();
            if (!in_array($request->area_id, $allowedAreaIds)) {
                return back()->with('error', 'You are not authorized to add a member to this area.')->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request) {
                // ধাপ ২: শুধুমাত্র fillable ফিল্ডগুলো দিয়ে সদস্য তৈরি করুন
                $memberData = $request->only([
                    'area_id', 'name', 'father_name', 'mother_name', 'mobile_no', 'email',
                    'date_of_birth', 'nid_no', 'present_address', 'permanent_address',
                    'joining_date', 'gender', 'marital_status', 'nationality', 'religion',
                    'blood_group', 'occupation', 'work_place', 'spouse_name', 'status',
                ]);
                $member = Member::create($memberData);

                // ধাপ ৩: Spatie Media Library ব্যবহার করে ফাইল আপলোড
                if ($request->hasFile('photo')) {
                    $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
                }
                if ($request->hasFile('signature')) {
                    $member->addMediaFromRequest('signature')->toMediaCollection('member_signature');
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while creating the member: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('members.index')->with('success', 'Member created successfully.');
    }
// MemberController.php
    public function show(Member $member)
    {
        $this->authorizeAccess($member);
        // eager load the relationships
        $member->load('savingsAccounts', 'loanAccounts');
        return view('members.show', compact('member'));
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
            if (!in_array($member->area_id, $allowedAreaIds)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }

        // ধাপ ১: নতুন ফিল্ডসহ পূর্ণাঙ্গ ভ্যালিডেশন
        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'mobile_no' => ['required', 'string', 'max:20', Rule::unique('members')->ignore($member->id)],
            'email' => ['nullable', 'email', Rule::unique('members')->ignore($member->id)],
            'date_of_birth' => 'required|date',
            'joining_date' => 'required|date',
            'area_id' => 'required|exists:areas,id',
            'present_address' => 'required|string',
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
        ]);

        try {
            DB::transaction(function () use ($request, $member) {
                // ধাপ ২: শুধুমাত্র fillable ফিল্ডগুলো দিয়ে সদস্যের তথ্য আপডেট করুন
                $memberData = $request->only([
                    'area_id', 'name', 'father_name', 'mother_name', 'mobile_no', 'email',
                    'date_of_birth', 'nid_no', 'present_address', 'permanent_address',
                    'joining_date', 'gender', 'marital_status', 'nationality', 'religion',
                    'blood_group', 'occupation', 'work_place', 'spouse_name', 'status',
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
            return redirect()->back()->with('error', 'An error occurred while updating the member: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('members.show', $member->id)->with('success', 'Member updated successfully.');
    }

    /*public function destroy(Member $member)
    {
        // শুধুমাত্র অ্যাডমিন সদস্য ডিলিট করতে পারবে
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $savingsAccounts = $member->savingsAccounts();
        $loans = $member->loanAccounts();
        if ($savingsAccounts->count() > 0) {
            foreach ($savingsAccounts as $account) {
                $withdrawals = $account->withdrawals();
                if ($withdrawals->count() > 0) {
                    foreach ($withdrawals as $withdrawal) {
                        $withdrawal->profitExpense->delete();
                        $withdrawal->delete();
                    }
                }
                Transaction::where('savings_account_id', $account->id)->delete();
                $account->clearMediaCollection('nominee_photo');
                $account->delete();
            }
        }
        if ($loans->count() > 0) {
            foreach ($loans as $loan) {
                Transaction::where('loan_account_id', $loan->id)->delete();
                $guarantor = $loan->guarantor;
                if ($guarantor) {
                    $guarantor->clearMediaCollection('guarantor_documents');
                }
                $loan->clearMediaCollection('loan_documents');
                $loan->delete();
            }
        }

        $member->clearMediaCollection('member_photo');
        $member->clearMediaCollection('member_signature');


        // ছবি ডিলিট করার কোড এখানে যোগ করা যেতে পারে
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Member deleted successfully.');
    }*/

    public function destroy(Member $member)
    {
        // নিরাপত্তা যাচাই
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($member) {
                // এই একটি মাত্র লাইনই সকল অবজারভার চেইনকে ট্রিগার করবে
                $member->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('members.index')->with('error', 'Failed to delete member and all related data. Error: ' . $e->getMessage());
        }

        return redirect()->route('members.index')->with('success', 'Member and all associated data have been permanently deleted.');
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


    public function createWithAccount()
    {
        $areas = Area::where('is_active', true)->get();
        $guarantors = Member::all();
        $accounts = Account::where('is_active', true)->get();
        return view('members.new_account', compact('areas','accounts','guarantors'));
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
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20|unique:members,mobile_no',
            'present_address' => 'required|string',
            'date_of_birth' => 'required|date',
            'joining_date' => 'required|date',
            'area_id' => 'required|exists:areas,id',
            'photo' => 'nullable|image|max:2048',

            // --- Savings Account validation (সংশোধিত) ---
            'open_savings_account' => 'nullable|boolean',

            'scheme_type' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'string'],
            'interest_rate' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'numeric', 'min:0'],
            'opening_date' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'date'],
            'installment_amount' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'numeric', 'min:0'],
            'initial_deposit' => ['nullable', 'numeric', 'min:0'],
            'nominee_name' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'string', 'max:255'],
            'nominee_phone' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'string', 'max:20'],
            'nominee_relation' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'string', 'max:255'],
            'nominee_address' => [Rule::requiredIf($request->boolean('open_savings_account')), 'nullable', 'string'],

            // --- Loan Account validation (সংশোধিত) ---
            'issue_loan_account' => 'nullable|boolean',

            'loan_amount' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'numeric', 'min:1'],
            'account_id' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'exists:accounts,id'],
            'loan_interest_rate' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'numeric', 'min:0'],
            'number_of_installments' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'integer', 'min:1'],
            'disbursement_date' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'date'],
            'installment_frequency' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'string', 'in:daily,weekly,monthly'],
            'guarantor_type' => [Rule::requiredIf($request->boolean('issue_loan_account')), 'nullable', 'in:member,outsider'],

            // Guarantor conditional validation
            'member_guarantor_id' => [Rule::requiredIf(fn() => $request->boolean('issue_loan_account') && $request->guarantor_type == 'member'), 'nullable', 'exists:members,id'],
            'outsider_name' => [Rule::requiredIf(fn() => $request->boolean('issue_loan_account') && $request->guarantor_type == 'outsider'), 'nullable', 'string', 'max:255'],
            'outsider_phone' => ['nullable', 'string', 'max:20'],
            'outsider_address' => ['nullable', 'string'],

            'guarantor_nid' => 'nullable|image|max:2048',
            'guarantor_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        //dd($request->all());

        $user = Auth::user();

        try {
            DB::transaction(function () use ($request, $user) {

                // --- ধাপ ২: সদস্য তৈরি করুন ---
                $memberData = $request->only(['name', 'father_or_husband_name', 'mother_name', 'mobile_no', 'address', 'date_of_birth', 'joining_date', 'area_id']);
                $member = Member::create($memberData);

                if ($request->hasFile('photo')) {
                    $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
                }

                // --- ধাপ ৩: যদি সঞ্চয় অ্যাকাউন্ট খোলার অপশন সিলেক্ট করা হয় ---
                if ($request->boolean('open_savings_account')) {
                    $openingDate = Carbon::parse($request->opening_date);
                    $initialDeposit = $request->initial_deposit ?? 0;

                    $savingsAccount = $member->savingsAccounts()->create([
                        'account_no' => 'SAV-' . $member->id . '-' . time(),
                        'scheme_type' => $request->scheme_type,
                        'collection_frequency' => $request->scheme_type, // Assuming scheme_type and frequency are same initially
                        'interest_rate' => $request->interest_rate,
                        'opening_date' => $openingDate,
                        'next_due_date' => \App\Helpers\DateHelper::calculateNextDueDate($openingDate, $request->scheme_type),
                        'current_balance' => $initialDeposit,
                        'nominee_name' => $request->nominee_name,
                        'nominee_phone' => $request->nominee_phone,
                        'nominee_relation' => $request->nominee_relation,
                        'nominee_address' => $request->nominee_address,
                    ]);

                    if ($initialDeposit > 0) {
                        $collection = $savingsAccount->collections()->create([
                            'member_id' => $member->id,
                            'collector_id' => $user->id,
                            'amount' => $initialDeposit,
                            'collection_date' => $openingDate,
                            'notes' => 'Initial deposit.',
                        ]);

                        $collection->transactions()->create([
                            'account_id' => 1,
                            'savings_account_id' => $savingsAccount->id,
                            'type' => 'credit',
                            'amount' => $initialDeposit,
                            'description' => 'Initial deposit from ' . $savingsAccount->member->name . ' (A/C: ' . $savingsAccount->account_no . ')',
                            'transaction_date' => $openingDate,
                        ]);

                    }
                }

                // --- ধাপ ৪: যদি ঋণ অ্যাকাউন্ট খোলার অপশন সিলেক্ট করা হয় ---
                if ($request->boolean('issue_loan_account')) {
                    $disbursementAccount = Account::findOrFail($request->account_id);
                    $loanAmount = $request->loan_amount;

                    if ($disbursementAccount->balance < $loanAmount) {
                        throw new \Exception('Insufficient balance to disburse this loan.');
                    }

                    $interest = ($loanAmount * $request->loan_interest_rate) / 100;
                    $totalPayable = $loanAmount + $interest;
                    $installmentAmount = $totalPayable / $request->number_of_installments;
                    $disbursementDate = Carbon::parse($request->disbursement_date);

                    $loanAccount = $member->loanAccounts()->create([
                        'account_no' => 'LOAN-' . $member->id . '-' . time(),
                        'loan_amount' => $loanAmount,
                        'interest_rate' => $request->loan_interest_rate,
                        'number_of_installments' => $request->number_of_installments,
                        'disbursement_date' => $disbursementDate,
                        'installment_frequency' => $request->installment_frequency,
                        'next_due_date' => \App\Helpers\DateHelper::calculateNextDueDate($disbursementDate, $request->installment_frequency),
                        'total_payable' => $totalPayable,
                        'installment_amount' => $installmentAmount,
                    ]);

                    $nextDueDate = clone $disbursementDate;
                    if ($request->installment_frequency == 'daily') {
                        $nextDueDate->addDay();
                    } elseif ($request->installment_frequency == 'weekly') {
                        $nextDueDate->addWeek();
                    } elseif ($request->installment_frequency == 'monthly') {
                        $nextDueDate->addMonth();
                    }

                    // অ্যাকাউন্টিং লেনদেন
                    $disbursementAccount->decrement('balance', $loanAmount);
                    $loanAccount->transactions()->create([
                        'account_id' => $disbursementAccount->id,
                        'type' => 'debit',
                        'amount' => $loanAmount,
                        'description' => 'Loan disbursed to member ' . $member->name . ' (A/C: ' . $loanAccount->account_no . ')',
                        'transaction_date' => $disbursementDate,
                    ]);

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
            return back()->with('error', 'An error occurred during onboarding: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('members.index')->with('success', 'Member onboarded successfully.');
    }
}
