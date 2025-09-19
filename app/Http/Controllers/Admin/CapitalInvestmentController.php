<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CapitalInvestment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapitalInvestmentController extends Controller
{
     /**
     * বিনিয়োগের ফর্ম এবং সাম্প্রতিক বিনিয়োগের তালিকা দেখাবে।
     */
    public function index()
    {
        // বিনিয়োগকারী হিসেবে শুধুমাত্র অ্যাডমিনদের তালিকা
        $investors = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->get();
        $accounts = Account::where('is_active', true)->get();
        $investments = CapitalInvestment::with('user', 'account')->latest()->paginate(15);

        return view('admin.capital_investments.index', compact('investors', 'accounts', 'investments'));
    }

    /**
     * নতুন মূলধন বিনিয়োগ রেকর্ড করবে।
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // কোন অ্যাডমিন বিনিয়োগ করছেন
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'investment_date' => 'required|date',
        ]);

        $investor = User::findOrFail($request->user_id);
        $depositAccount = Account::findOrFail($request->account_id);
        $amount = $request->amount;

        try {
            DB::transaction(function () use ($request, $investor, $depositAccount, $amount) {
                // ১. মূল বিনিয়োগের রেকর্ড তৈরি করুন
                $investment = CapitalInvestment::create([
                    'user_id' => $investor->id,
                    'account_id' => $depositAccount->id,
                    'amount' => $amount,
                    'investment_date' => $request->investment_date,
                    'description' => $request->description,
                ]);

                // ২. অ্যাকাউন্টিং: transactions টেবিলে একটি ক্রেডিট লেনদেন তৈরি করুন
                $investment->transactions()->create([
                    'account_id' => $depositAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => 'Capital investment from ' . $investor->name,
                    'transaction_date' => $request->investment_date,
                ]);

                // ৩. ক্যাশ/ব্যাংক অ্যাকাউন্টে ব্যালেন্স যোগ করুন
                $depositAccount->increment('balance', $amount);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.capital_investments.index')->with('success', 'Capital investment recorded successfully.');
    }
}
