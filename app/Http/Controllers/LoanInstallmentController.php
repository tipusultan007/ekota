<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\LoanInstallment;
use App\Models\LoanAccount;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanInstallmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = LoanInstallment::with('member', 'loanAccount', 'collector');

        if ($user->hasRole('Field Worker')) {
            $query->where('collector_id', $user->id);
        }

        $installments = $query->latest()->paginate(20);
        return view('loan_installments.index', compact('installments'));
    }

    public function create()
    {
        $user = Auth::user();
        $membersQuery = Member::where('status', 'active')->whereHas('loanAccounts', function ($q) {
            $q->where('status', 'running');
        });

        if ($user->hasRole('Field Worker')) {
            $membersQuery->where('area_id', $user->area_id);
        }

        $members = $membersQuery->with('loanAccounts')->get();
        $installmentsQuery = LoanInstallment::with('member', 'loanAccount', 'collector');
        if ($user->hasRole('Field Worker')) {
            $installmentsQuery->where('collector_id', $user->id);
        }
        $accounts = Account::where('is_active', true)->get();
        // শুধুমাত্র আজকের এবং সাম্প্রতিক লেনদেনগুলো দেখানো যেতে পারে
        $recentInstallments = $installmentsQuery->latest()->where('payment_date', today())->paginate(15);

        return view('loan_installments.create', compact('members', 'recentInstallments','accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_account_id' => 'required|exists:loan_accounts,id',
            'paid_amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id', // টাকা কোন অ্যাকাউন্টে জমা হচ্ছে
        ]);

        $loanAccount = LoanAccount::findOrFail($request->loan_account_id);
        $paymentAccount = Account::findOrFail($request->account_id); // ক্যাশ/ব্যাংক অ্যাকাউন্ট
        $paidAmount = $request->paid_amount;

        $graceAmount = (float)($request->grace_amount ?? 0);
        $totalReduction = $paidAmount + $graceAmount;
        $dueAmount = $loanAccount->total_payable - $loanAccount->total_paid;

        if ($totalReduction > $dueAmount) {
            return back()->with('error', 'Paid amount + Grace amount cannot be greater than the remaining due.');
        }

        // নিরাপত্তা ও ব্যালেন্স যাচাই
        $this->authorizeAccess($loanAccount->member);
        if (($loanAccount->total_paid + $paidAmount) > $loanAccount->total_payable) {
            return back()->with('error', 'Paid amount cannot be greater than the remaining due.');
        }

        try {
            DB::transaction(function () use ($request, $loanAccount, $paymentAccount, $paidAmount,$graceAmount) {

                // ধাপ ১: কিস্তির রেকর্ড তৈরি করুন
                $lastInstallment = $loanAccount->installments()->latest()->first();
                $installment = LoanInstallment::create([
                    'loan_account_id' => $loanAccount->id,
                    'member_id' => $loanAccount->member_id,
                    'collector_id' => Auth::id(),
                    'installment_no' => $lastInstallment ? $lastInstallment->installment_no + 1 : 1,
                    'paid_amount' => $paidAmount,
                    'grace_amount' => $graceAmount,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes,
                ]);

                // ধাপ ২: অ্যাকাউন্টিং ইন্টিগ্রেশন
                // ক) transactions টেবিলে একটি ক্রেডিট (জমা) লেনদেন তৈরি করুন
                $paymentAccount->transactions()->create([
                    'type' => 'credit',
                    'loan_account_id' => $loanAccount->id,
                    'amount' => $paidAmount,
                    'description' => 'Loan installment from ' . $loanAccount->member->name . ' (Loan A/C: ' . $loanAccount->account_no . ')',
                    'transaction_date' => $request->payment_date,
                    'transactionable_id' => $installment->id,
                    'transactionable_type' => LoanInstallment::class,
                ]);
                // খ) ক্যাশ/ব্যাংক অ্যাকাউন্টে ব্যালেন্স যোগ করুন
                $paymentAccount->increment('balance', $paidAmount);


                // ধাপ ৩: ঋণের মূল অ্যাকাউন্ট আপডেট করুন
                // ক) পরিশোধিত অর্থ যোগ করুন
                $loanAccount->increment('total_paid', $paidAmount);

                if ($graceAmount > 0 && ($loanAccount->total_paid + $graceAmount) >= $loanAccount->total_payable) {
                    $loanAccount->increment('grace_amount', $graceAmount);
                    $loanAccount->status = 'paid';
                }

                // খ) স্ট্যাটাস আপডেট করুন
                if ($loanAccount->total_paid >= $loanAccount->total_payable) {
                    $loanAccount->status = 'paid';
                }

                // গ) পরবর্তী কিস্তির তারিখ আপডেট করুন
                $loanAccount->next_due_date = DateHelper::calculateNextDueDate(
                    $loanAccount->disbursement_date,
                    $loanAccount->installment_frequency,
                    $loanAccount->next_due_date
                );
                $loanAccount->save();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Installment collected successfully.');
    }

    private function authorizeAccess(Member $member)
    {
        $user = Auth::user();

        // অ্যাডমিনদের জন্য কোনো সীমাবদ্ধতা নেই, তাই তারা এই চেক থেকে বেরিয়ে যাবে
        if ($user->hasRole('Admin')) {
            return;
        }

        // শুধুমাত্র মাঠকর্মীদের জন্য এই চেকটি প্রয়োগ হবে
        if ($user->hasRole('Field Worker')) {
            // ধাপ ১: মাঠকর্মীর সকল নির্ধারিত এলাকার আইডিগুলো একটি অ্যারেতে নিন
            $allowedAreaIds = $user->areas()->pluck('areas.id')->toArray();

            // ধাপ ২: সদস্যের এলাকাটি কি মাঠকর্মীর নির্ধারিত এলাকাগুলোর মধ্যে আছে?
            // in_array() ফাংশনটি একটি মান একটি অ্যারের মধ্যে আছে কিনা তা যাচাই করে
            if (!in_array($member->area_id, $allowedAreaIds)) {
                // যদি না থাকে, তাহলে একটি 403 Forbidden এরর দেখান
                abort(403, 'UNAUTHORIZED ACTION. You do not have permission to access members from this area.');
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoanInstallment $loanInstallment)
    {
        $this->authorizeAdmin();
        $accounts = Account::where('is_active', true)->get();
        return view('loan_installments.edit', compact('loanInstallment','accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanInstallment $loanInstallment)
    {
        $this->authorizeAdmin();
        $request->validate([
            'paid_amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
        ]);

        DB::transaction(function () use ($request, $loanInstallment) {
            $oldAmount = $loanInstallment->paid_amount;
            $newAmount = $request->paid_amount;

            $loanAccount = $loanInstallment->loanAccount;
            $transaction = $loanInstallment->transactions;

            if (!$transaction) {
                throw new \Exception('Associated transaction not found.');
            }

            $oldPaymentAccount = $transaction->account;
            $newPaymentAccount = Account::find($request->account_id);

            $loanAccount->total_paid = ($loanAccount->total_paid - $oldAmount) + $newAmount;

            // ২. স্ট্যাটাস অ্যাডজাস্ট করুন
            $loanAccount->status = ($loanAccount->total_paid >= $loanAccount->total_payable) ? 'paid' : 'running';
            $loanAccount->save();

            // ৩. লেনদেন রেকর্ড আপডেট করুন
            $transaction->update(['account_id' => $newPaymentAccount->id, 'amount' => $newAmount, 'transaction_date' => $request->payment_date]);

            // ৪. কিস্তির রেকর্ড আপডেট করুন
            $loanInstallment->update($request->except('account_id'));
        });

        return redirect()->route('loan-installments.index')->with('success', 'Installment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * একটি ঋণের কিস্তির রেকর্ড ডিলিট করে এবং মূল অ্যাকাউন্টের ব্যালেন্স অ্যাডজাস্ট করে।
     *
     * @param  \App\Models\LoanInstallment  $loanInstallment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(LoanInstallment $loanInstallment)
    {
        $this->authorizeAdmin();
        DB::transaction(function () use ($loanInstallment) {
            $loanAccount = $loanInstallment->loanAccount;
            $transaction = $loanInstallment->transactions;


            // ২. ঋণের মূল অ্যাকাউন্ট থেকে পরিশোধিত অর্থ বিয়োগ করুন
            $loanAccount->decrement('total_paid', $loanInstallment->paid_amount);
            $loanAccount->status = 'running';
            $loanAccount->save();

            // ৩. কিস্তির রেকর্ড ডিলিট করুন
            $loanInstallment->delete();
        });
        return redirect()->route('loan-installments.index')->with('success', 'Installment deleted and balances restored.');
    }

    /**
     * Helper method to authorize admin access.
     * এই মেথডটি যাচাই করে যে লগইন করা ব্যবহারকারীর ভূমিকা 'Admin' কিনা।
     * যদি না হয়, তাহলে এটি একটি 403 Forbidden error দেখায়।
     */
    private function authorizeAdmin()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION. ONLY ADMINS CAN PERFORM THIS TASK.');
        }
    }
}
