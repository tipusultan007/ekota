<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\SavingsCollection;
use Illuminate\Http\Request;
use App\Models\SavingsAccount;
use App\Models\SavingsWithdrawal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SavingsWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = SavingsWithdrawal::with('member', 'savingsAccount', 'processedBy');

        // ভূমিকা অনুযায়ী ফিল্টার
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereHas('member', function ($q) use ($areaIds) {
                $q->whereIn('area_id', $areaIds);
            });
        }

        // তারিখ অনুযায়ী ফিল্টার
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('withdrawal_date', [$request->start_date, $request->end_date]);
        }

        $withdrawals = $query->latest()->paginate(20);

        return view('savings_withdrawals.index', compact('withdrawals'));
    }

    public function store(Request $request, SavingsAccount $savingsAccount)
    {
        $this->authorize('isAdmin');

        $request->validate([
            'profit_amount' => 'nullable|numeric|min:0',
            'withdrawal_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        $currentBalance = (float) $savingsAccount->current_balance;
        $profitAmount = (float) ($request->profit_amount ?? 0);
        $totalPaidToMember = $currentBalance + $profitAmount;

        $paymentAccount = Account::findOrFail($request->account_id);

        try {
            DB::transaction(function () use ($request, $savingsAccount, $paymentAccount, $currentBalance, $profitAmount, $totalPaidToMember) {

                // ধাপ ১: উত্তোলনের মূল রেকর্ড তৈরি করুন
                $withdrawalRecord = SavingsWithdrawal::create([
                    'savings_account_id' => $savingsAccount->id,
                    'member_id' => $savingsAccount->member_id,
                    'processed_by_user_id' => Auth::id(),
                    'withdrawal_amount' => $currentBalance,
                    'profit_amount' => $profitAmount,
                    'total_amount' => $totalPaidToMember,
                    'withdrawal_date' => $request->withdrawal_date,
                    'notes' => $request->notes,
                ]);

                // ধাপ ২: যদি মুনাফা দেওয়া হয়, তাহলে সেটিকে একটি খরচ হিসেবে রেকর্ড করুন
                if ($profitAmount > 0) {
                    $profitExpenseCategory = ExpenseCategory::firstOrCreate(['name' => 'Profit Paid to Members']);

                    $withdrawalRecord->profitExpense()->create([
                        'expense_category_id' => $profitExpenseCategory->id,
                        'user_id' => Auth::id(),
                        'amount' => $profitAmount,
                        'expense_date' => $request->withdrawal_date,
                        'description' => 'Profit paid to member ' . $savingsAccount->member->name .
                            ' (A/C: ' . $savingsAccount->account_no . ')',
                    ]);
                }

                // ধাপ ৩: সঞ্চয় অ্যাকাউন্টের ব্যালেন্স শূন্য এবং স্ট্যাটাস 'closed' করুন
                $savingsAccount->update([
                    'current_balance' => 0,
                    'status' => 'closed'
                ]);

                // ======== ধাপ ৪: অ্যাকাউন্টিং ইন্টিগ্রেশন ========
                // ক) transactions টেবিলে একটি ডেবিট (খরচ) লেনদেন তৈরি করুন
                $paymentAccount->transactions()->create([
                    'type' => 'debit',
                    'savings_account_id' => $savingsAccount->id,
                    'amount' => $totalPaidToMember, // সদস্যকে মোট যে টাকা দেওয়া হচ্ছে
                    'description' => 'Savings withdrawal for member ' . $savingsAccount->member->name . ' (A/C: ' . $savingsAccount->account_no . ')',
                    'transaction_date' => $request->withdrawal_date,
                    'transactionable_id' => $withdrawalRecord->id, // পলিমরফিক রিলেশন
                    'transactionable_type' => SavingsWithdrawal::class,
                ]);

                // খ) পেমেন্ট অ্যাকাউন্ট (ক্যাশ/ব্যাংক) থেকে ব্যালেন্স বিয়োগ করুন
                $paymentAccount->decrement('balance', $totalPaidToMember);
                // ===========================================

            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred during withdrawal: ' . $e->getMessage());
        }

        return redirect()->route('members.show', $savingsAccount->member_id)
            ->with('success', 'Final withdrawal of ' . number_format($totalPaidToMember) . ' BDT processed. Account has been closed.');
    }

    // একটি সহজ অথোরাইজেশন মেথড
    protected function authorize($ability)
    {
        if ($ability === 'isAdmin' && !Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }
    }

    public function destroy(SavingsWithdrawal $savingsWithdrawal)
    {
        $this->authorize('isAdmin'); // নিরাপত্তা যাচাই

        try {
            DB::transaction(function () use ($savingsWithdrawal) {
                $savingsAccount = $savingsWithdrawal->savingsAccount;
                $paymentTransaction = $savingsWithdrawal->transactions()->where('type', 'debit')->first();

                // ধাপ ১: পেমেন্ট অ্যাকাউন্টের ব্যালেন্স পুনরুদ্ধার করুন
                if ($paymentTransaction) {
                    $paymentAccount = $paymentTransaction->account;
                    $paymentAccount->increment('balance', $savingsWithdrawal->total_amount);
                }

                // ধাপ ২: সঞ্চয় অ্যাকাউন্টের ব্যালেন্স পুনরুদ্ধার করুন
                // (যেহেতু স্ট্যাটাস 'closed' করা হয়েছিল, তাই ব্যালেন্স ছিল 0)
                $savingsAccount->increment('balance', $savingsWithdrawal->withdrawal_amount);
                $savingsAccount->update(['status' => 'active']); // অ্যাকাউন্টটি আবার সক্রিয় করুন

                // ধাপ ৩: সংশ্লিষ্ট মুনাফার খরচটি (profit expense) ডিলিট করুন
                // --- পরিবর্তন এখানে ---
                if ($savingsWithdrawal->profitExpense) {
                    $savingsWithdrawal->profitExpense->delete();
                }

                // ধাপ ৪: সংশ্লিষ্ট সকল লেনদেন (transactions) ডিলিট করুন
                $savingsWithdrawal->transactions()->delete();

                // ধাপ ৫: সবশেষে, উত্তোলনের মূল রেকর্ডটি ডিলিট করুন
                $savingsWithdrawal->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while reversing the withdrawal: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Withdrawal reversed successfully. All balances have been restored.');
    }
}
