<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\SavingsCollection;
use App\Models\Member;
use App\Models\SavingsAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingsCollectionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SavingsCollection::with('member', 'savingsAccount', 'collector')->orderBy('collection_date', 'desc');
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('collection_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('collection_date', '<=', $request->end_date);
        }
        if ($user->hasRole('Field Worker')) {
            $query->where('collector_id', $user->id);
        }

        $collections = $query->latest()->paginate(20);
        return view('savings_collections.index', compact('collections'));
    }

    public function create()
    {
        $user = Auth::user();

        // Member মডেলের উপর একটি বেস কোয়েরি তৈরি করুন
        $membersQuery = Member::where('status', 'active')
            ->whereHas('savingsAccounts', function ($q) {
                $q->where('status', 'active');
            }); // শুধুমাত্র যে সকল সদস্যের সক্রিয় সঞ্চয় অ্যাকাউন্ট আছে

        // যদি ব্যবহারকারী মাঠকর্মী হন, তাহলে তার নির্ধারিত এলাকার সদস্যদের ফিল্টার করুন
        if ($user->hasRole('Field Worker')) {
            // ধাপ ১: ব্যবহারকারীর সকল নির্ধারিত এলাকার আইডিগুলো একটি অ্যারেতে নিন
            $areaIds = $user->areas()->pluck('areas.id')->toArray();

            // ধাপ ২: whereIn ব্যবহার করে শুধুমাত্র সেই সদস্যদের আনুন যারা এই এলাকাগুলোর মধ্যে আছে
            $membersQuery->whereIn('area_id', $areaIds);
        }

        // চূড়ান্ত কোয়েরিটি এক্সিকিউট করুন এবং সম্পর্কগুলো eager load করুন
        // orderBy দিয়ে নাম অনুযায়ী সাজিয়ে নিন যাতে ড্রপডাউনে খুঁজে পেতে সুবিধা হয়
        $members = $membersQuery->with(['savingsAccounts' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('name', 'asc')->get();

        $collectionsQuery = SavingsCollection::with('member', 'savingsAccount', 'collector');
        if ($user->hasRole('Field Worker')) {
            $collectionsQuery->where('collector_id', $user->id);
        }
        $recentCollections = $collectionsQuery->latest()->take(10)->get();

        $accounts = Account::where('is_active', true)->get();

        return view('savings_collections.create', compact('members', 'recentCollections','accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'savings_account_id' => 'required|exists:savings_accounts,id',
            'amount' => 'required|numeric|min:1',
            'collection_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id', // টাকা কোন অ্যাকাউন্টে জমা হচ্ছে
        ]);

        $savingsAccount = SavingsAccount::findOrFail($request->savings_account_id);
        $depositAccount = Account::findOrFail($request->account_id); // ক্যাশ/ব্যাংক অ্যাকাউন্ট
        $amount = $request->amount;

        // নিরাপত্তা যাচাই
        $this->authorizeAccess($savingsAccount->member);

        try {
            DB::transaction(function () use ($request, $savingsAccount, $depositAccount, $amount) {

                // ধাপ ১: savings_collections টেবিলে মূল কালেকশনের রেকর্ড তৈরি করুন
                $collection = SavingsCollection::create([
                    'savings_account_id' => $savingsAccount->id,
                    'member_id' => $savingsAccount->member_id,
                    'collector_id' => Auth::id(),
                    'amount' => $amount,
                    'collection_date' => $request->collection_date,
                    'notes' => $request->notes,
                ]);

                // ধাপ ২: অ্যাকাউন্টিং ইন্টিগ্রেশন
                // ক) transactions টেবিলে একটি ক্রেডিট (জমা) লেনদেন তৈরি করুন
                // পলিমরফিক রিলেশন ব্যবহার করে
                $collection->transactions()->create([
                    'account_id' => $depositAccount->id,
                    'savings_account_id' => $savingsAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => 'Savings deposit from ' . $savingsAccount->member->name . ' (A/C: ' . $savingsAccount->account_no . ')',
                    'transaction_date' => $request->collection_date,
                ]);
                // খ) ক্যাশ/ব্যাংক অ্যাকাউন্টে ব্যালেন্স যোগ করুন
                $depositAccount->increment('balance', $amount);

                // ধাপ ৩: সদস্যের সঞ্চয় অ্যাকাউন্টের ব্যালেন্স এবং পরবর্তী কিস্তির তারিখ আপডেট করুন
                // ক) সদস্যের অ্যাকাউন্টে ব্যালেন্স যোগ করুন
                $savingsAccount->increment('current_balance', $amount);

                // খ) পরবর্তী কিস্তির তারিখ আপডেট করুন
                // আজকের তারিখ বা পেমেন্টের তারিখকে ভিত্তি হিসেবে ধরা যেতে পারে
                $savingsAccount->next_due_date = DateHelper::calculateNextDueDate(
                    $savingsAccount->opening_date,
                    $savingsAccount->collection_frequency,
                    $savingsAccount->next_due_date
                );
                $savingsAccount->save();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Savings collected successfully and account balances updated.');
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


    public function edit(SavingsCollection $savingsCollection)
    {
        $this->authorizeAdmin();

        $accounts = Account::where('is_active', true)->get();
        // কালেকশনের সাথে সম্পর্কিত লেনদেনটি খুঁজুন
        $transaction = $savingsCollection->transactions()->where('type', 'credit')->first();


        return view('savings_collections.edit', compact('savingsCollection','accounts','transaction'));
    }

    public function update(Request $request, SavingsCollection $savingsCollection)
    {
        $this->authorizeAdmin();

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'collection_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $savingsCollection) {
                $transaction = $savingsCollection->transactions()->where('type', 'credit')->first();
                if (!$transaction) {
                    throw new \Exception('Associated transaction not found for this collection.');
                }

                $oldAmount = $savingsCollection->amount;
                $newAmount = $request->amount;

                $oldDepositAccount = $transaction->account;
                $newDepositAccount = Account::find($request->account_id);

                // --- ধাপ ১: সকল ব্যালেন্স অ্যাডজাস্ট করুন ---
                // ক) সদস্যের সঞ্চয় অ্যাকাউন্ট ব্যালেন্স অ্যাডজাস্ট
                $savingsAccount = $savingsCollection->savingsAccount;
                $savingsAccount->current_balance = ($savingsAccount->current_balance - $oldAmount) + $newAmount;
                $savingsAccount->save();

                // খ) সমিতির আর্থিক অ্যাকাউন্ট (ক্যাশ/ব্যাংক) ব্যালেন্স অ্যাডজাস্ট
                $oldDepositAccount->decrement('balance', $oldAmount);
                $newDepositAccount->increment('balance', $newAmount);

                // --- ধাপ ২: রেকর্ডগুলো আপডেট করুন ---
                // ক) কালেকশন রেকর্ড
                $savingsCollection->update($request->except('account_id'));

                // খ) লেনদেন রেকর্ড
                $transaction->update([
                    'account_id' => $newDepositAccount->id,
                    'amount' => $newAmount,
                    'transaction_date' => $request->collection_date,
                    'description' => 'Savings deposit from ' . $savingsAccount->member->name . ' (A/C: ' . $savingsAccount->account_no . ') (Updated)',
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('savings-collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(SavingsCollection $savingsCollection)
    {
        $this->authorizeAdmin();

        try {
            DB::transaction(function () use ($savingsCollection) {
                $transaction = $savingsCollection->transactions()->where('type', 'credit')->first();

                if ($transaction) {
                    // ধাপ ১: ক্যাশ/ব্যাংক অ্যাকাউন্ট থেকে টাকা ফেরত নিন
                    $transaction->account->decrement('balance', $savingsCollection->amount);
                    $transaction->delete();
                }

                // ধাপ ২: সদস্যের সঞ্চয় অ্যাকাউন্ট থেকে টাকা বিয়োগ করুন
                $savingsCollection->savingsAccount->decrement('current_balance', $savingsCollection->amount);

                // ধাপ ৩: কালেকশন রেকর্ডটি ডিলিট করুন
                $savingsCollection->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('savings-collections.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }

        return redirect()->route('savings-collections.index')->with('success', 'Collection deleted and balances restored successfully.');
    }

    private function authorizeAdmin()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }
    }
}
