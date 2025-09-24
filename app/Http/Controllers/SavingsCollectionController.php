<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\SavingsCollection;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingsCollectionController extends Controller
{
    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

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

        return view('savings_collections.create', compact('members', 'recentCollections', 'accounts'));
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
                // $collection->transactions()->create([
                //     'account_id' => $depositAccount->id,
                //     'savings_account_id' => $savingsAccount->id,
                //     'type' => 'credit',
                //     'amount' => $amount,
                //     'description' => 'Savings deposit from ' . $savingsAccount->member->name . ' (A/C: ' . $savingsAccount->account_no . ')',
                //     'transaction_date' => $request->collection_date,
                // ]);

                $this->accountingService->createTransaction(
                    $request->date,
                    'Savings deposit from ' . $savingsAccount->member->name,
                    [ // entries array
                        ['account_id' => $depositAccount->id, 'debit' => $amount],
                        ['account_id' => Account::where('code', '2010')->first()->id, 'credit' => $amount],
                    ],
                    $collection
                );

        
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
        // ধাপ ১: নিরাপত্তা যাচাই
        $this->authorizeAdmin();

        // ধাপ ২: ফর্মের ড্রপডাউনের জন্য সকল সক্রিয় আর্থিক অ্যাকাউন্ট (ক্যাশ/ব্যাংক) আনুন
        // scopeActive() এবং scopePayment() ব্যবহার করে কোডটি আরও পরিষ্কার করা যায়
        $accounts = Account::active()->payment()->orderBy('name')->get();

        // ধাপ ৩: এই কালেকশনের সাথে যুক্ত মূল Transaction রেকর্ডটি খুঁজুন
        $transaction = $savingsCollection->transactions()->first();

        // ধাপ ৪: Transaction থেকে ডেবিট হওয়া অ্যাকাউন্টটি (Cash/Bank) খুঁজুন
        // যাতে ড্রপডাউনে এটি প্রি-সিলেক্টেড থাকে
        $currentDepositAccount = null;
        if ($transaction) {
            $debitEntry = $transaction->journalEntries()->whereNotNull('debit')->first();
            if ($debitEntry) {
                $currentDepositAccount = $debitEntry->account;
            }
        }

        return view('savings_collections.edit', compact(
            'savingsCollection',
            'accounts',
            'currentDepositAccount'
        ));
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

                // --- ধাপ ১: পুরানো লেনদেন খুঁজুন এবং রিভার্স করুন ---
                $oldTransaction = $savingsCollection->transactions()->first(); // যেহেতু একটিই থাকার কথা
                if (!$oldTransaction) {
                    // যদি কোনো কারণে লেনদেন না থাকে, তাহলে শুধু নতুন একটি তৈরি করুন (ফলব্যাক)
                    // অথবা একটি এরর থ্রো করুন
                    throw new \Exception('Associated transaction not found for this collection. Cannot update.');
                }

                // জার্নাল এন্ট্রিগুলো থেকে পুরানো অ্যাকাউন্ট এবং পরিমাণ নিন
                $oldDebitEntry = $oldTransaction->journalEntries()->whereNotNull('debit')->first();
                $oldCreditEntry = $oldTransaction->journalEntries()->whereNotNull('credit')->first();
                $oldDebitAccount = Account::find($oldDebitEntry->account_id);
                $oldCreditAccount = Account::find($oldCreditEntry->account_id);
                $oldAmount = $oldDebitEntry->debit;

                // পুরানো ব্যালেন্স পুনরুদ্ধার করুন
                $oldDebitAccount->handleCredit($oldAmount); // ডেবিটের উল্টো ক্রেডিট
                $oldCreditAccount->handleDebit($oldAmount);  // ক্রেডিটের উল্টো ডেবিট

                // সদস্যের সঞ্চয় অ্যাকাউন্ট থেকে পুরানো পরিমাণ বিয়োগ করুন
                $savingsCollection->savingsAccount->decrement('current_balance', $oldAmount);

                // পুরানো Transaction এবং Journal Entry ডিলিট করে দিন
                $oldTransaction->journalEntries()->delete();
                $oldTransaction->delete();


                // --- ধাপ ২: নতুন তথ্য দিয়ে কালেকশন রেকর্ড আপডেট করুন ---
                $savingsCollection->update([
                    'amount' => $request->amount,
                    'collection_date' => $request->collection_date,
                    'notes' => $request->notes,
                ]);


                // --- ধাপ ৩: নতুন তথ্য দিয়ে নতুন করে অ্যাকাউন্টিং এন্ট্রি দিন ---
                $newAmount = $request->amount;
                $newDepositAccountId = $request->account_id;
                $savingsPayableAccount = Account::where('code', '2010')->firstOrFail();

                // নতুন AccountingService ব্যবহার করে যৌগিক জাবেদা তৈরি করুন
                $this->accountingService->createTransaction(
                    $request->collection_date,
                    'Savings deposit from ' . $savingsCollection->member->name . ' (Updated)',
                    [
                        // Debit Entry:
                        ['account_id' => $newDepositAccountId, 'debit' => $newAmount],

                        // Credit Entry:
                        ['account_id' => $savingsPayableAccount->id, 'credit' => $newAmount],
                    ],
                    $savingsCollection
                );

                // সদস্যের সঞ্চয় অ্যাকাউন্টে নতুন পরিমাণ যোগ করুন
                $savingsCollection->savingsAccount->increment('current_balance', $newAmount);
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

                // ধাপ ১: সংশ্লিষ্ট Transaction এবং এর Journal Entry-গুলো খুঁজুন
                $transaction = $savingsCollection->transactions()->first(); // morphMany রিলেশন
                if (!$transaction) {
                    // যদি কোনো লেনদেন না থাকে, শুধু কালেকশনটি ডিলিট করুন (যদিও এটি ঘটার কথা নয়)
                    $savingsCollection->delete();
                    return;
                }

                $debitEntry = $transaction->journalEntries()->whereNotNull('debit')->first();
                $creditEntry = $transaction->journalEntries()->whereNotNull('credit')->first();

                if (!$debitEntry || !$creditEntry) {
                    throw new \Exception('Incomplete journal entries found for this transaction. Cannot safely delete.');
                }

                // ধাপ ২: সংশ্লিষ্ট অ্যাকাউন্টগুলোর ব্যালেন্স পুনরুদ্ধার করুন
                $debitAccount = Account::find($debitEntry->account_id);
                $creditAccount = Account::find($creditEntry->account_id);
                $amount = $savingsCollection->amount;

                // ক) ডেবিট অ্যাকাউন্টের ব্যালেন্স রিভার্স করুন (ক্রেডিট করে)
                $debitAccount->handleCredit($amount);

                // খ) ক্রেডিট অ্যাকাউন্টের ব্যালেন্স রিভার্স করুন (ডেবিট করে)
                $creditAccount->handleDebit($amount);

                // গ) সদস্যের ব্যক্তিগত সঞ্চয় লেজার (current_balance) থেকে টাকা বিয়োগ করুন
                $savingsCollection->savingsAccount->decrement('current_balance', $amount);

                // ধাপ ৩: অ্যাকাউন্টিং রেকর্ডগুলো ডিলিট করুন
                // (Transaction ডিলিট হলে এর সাথে যুক্ত JournalEntry-গুলোও ডিলিট হয়ে যাবে, যদি রিলেশন সঠিকভাবে সেট করা থাকে)
                $transaction->journalEntries()->delete();
                $transaction->delete();

                // ধাপ ৪: সবশেষে, মূল SavingsCollection রেকর্ডটি ডিলিট করুন
                $savingsCollection->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('savings-collections.index')->with('error', 'An error occurred while deleting the collection: ' . $e->getMessage());
        }

        return redirect()->route('savings-collections.index')->with('success', 'Collection deleted and all associated balances have been restored.');
    }

    private function authorizeAdmin()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }
    }
}
