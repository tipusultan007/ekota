<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsCollection;
use App\Services\AccountingService;
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
        $query = SavingsCollection::with('member.area', 'savingsAccount', 'collector');

        // --- ভূমিকা অনুযায়ী বেস কোয়েরি ফিল্টার ---
        if ($user->hasRole('Field Worker')) {
            // মাঠকর্মী শুধুমাত্র তার নিজের কালেকশন দেখতে পাবে
            $query->where('collector_id', $user->id);
        }

        // --- রিকোয়েস্ট থেকে আসা ফিল্টারগুলো প্রয়োগ করুন ---

        // তারিখ অনুযায়ী ফিল্টার
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('collection_date', [$request->start_date, $request->end_date]);
        }

        // সদস্য অনুযায়ী ফিল্টার
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        // কালেক্টর/মাঠকর্মী অনুযায়ী ফিল্টার (শুধুমাত্র অ্যাডমিনের জন্য)
        if ($request->filled('collector_id') && $user->hasRole('Admin')) {
            $query->where('collector_id', $request->collector_id);
        }

        // এলাকা অনুযায়ী ফিল্টার
        if ($request->filled('area_id')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            });
        }
        // ----------------------------------------------------

        $collections = $query->latest('id')->paginate(25);

        // --- ফিল্টারের ড্রপডাউনের জন্য ডেটা প্রস্তুত করুন ---
        $members = \App\Models\Member::orderBy('name')->get(['id', 'name', 'account_no']);
        $areas = \App\Models\Area::orderBy('name')->get(['id', 'name']);

        // কালেক্টরের তালিকা শুধুমাত্র অ্যাডমিনদের জন্য
        $collectors = collect();
        if ($user->hasRole('Admin')) {
            $collectors = \App\Models\User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Admin', 'Field Worker']))
                ->orderBy('name')->get(['id', 'name']);
        }

        return view('savings_collections.index', compact('collections', 'members', 'areas', 'collectors'));
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
            'amount' => 'nullable|numeric|min:0',
            'withdraw_amount' => 'nullable|numeric|min:0',
            'interest_amount' => 'nullable|numeric|min:0',
            'collection_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'collector_id' => 'nullable|exists:users,id',
        ]);

        $savingsAccount = SavingsAccount::findOrFail($request->savings_account_id);
        $depositAccount = Account::findOrFail($request->account_id);
        $amount = $request->amount ?? 0;
        $withdrawAmount = $request->withdraw_amount ?? 0;
        $interestAmount = $request->interest_amount ?? 0;

        if ($amount == 0 && $withdrawAmount == 0 && $interestAmount == 0) {
            return redirect()->back()->with('error', 'Please enter at least one amount (Deposit, Withdraw, or Interest).')->withInput();
        }

        // নিরাপত্তা যাচাই
        $this->authorizeAccess($savingsAccount->member);

        $collectorId = Auth::user()->hasRole('Admin') ? ($request->collector_id ?? Auth::id()) : Auth::id();

        try {
            DB::transaction(function () use ($request, $savingsAccount, $depositAccount, $amount, $withdrawAmount, $interestAmount, $collectorId) {

                $collection = SavingsCollection::create([
                    'savings_account_id' => $savingsAccount->id,
                    'member_id' => $savingsAccount->member_id,
                    'collector_id' => $collectorId,
                    'amount' => $amount,
                    'withdraw_amount' => $withdrawAmount,
                    'interest_amount' => $interestAmount,
                    'collection_date' => $request->collection_date,
                    'notes' => $request->notes,
                ]);

                $entries = [];
                $savingsCode = '2010';
                $interestExpenseCode = '5030';

                // 1. Deposit Transaction
                if ($amount > 0) {
                    $entries[] = ['account_id' => $depositAccount->id, 'debit' => $amount];
                    $entries[] = ['account_id' => Account::where('code', $savingsCode)->first()->id, 'credit' => $amount];
                }

                // 2. Withdrawal Transaction
                if ($withdrawAmount > 0) {
                    $entries[] = ['account_id' => Account::where('code', $savingsCode)->first()->id, 'debit' => $withdrawAmount];
                    $entries[] = ['account_id' => $depositAccount->id, 'credit' => $withdrawAmount];
                }

                // 3. Interest Transaction
                if ($interestAmount > 0) {
                    $entries[] = ['account_id' => Account::where('code', $interestExpenseCode)->first()->id, 'debit' => $interestAmount];
                    $entries[] = ['account_id' => Account::where('code', $savingsCode)->first()->id, 'credit' => $interestAmount];
                }

                if (! empty($entries)) {
                    $this->accountingService->createTransaction(
                        $request->collection_date,
                        'Savings transaction for '.$savingsAccount->member->name,
                        $entries,
                        $collection
                    );
                }

                // Update balance
                $netChange = $amount - $withdrawAmount + $interestAmount;
                $savingsAccount->increment('current_balance', $netChange);

                // Update next due date only if deposit was made
                if ($amount > 0) {
                    $savingsAccount->next_due_date = DateHelper::calculateNextDueDate(
                        $savingsAccount->opening_date,
                        $savingsAccount->collection_frequency,
                        $savingsAccount->next_due_date
                    );
                }
                $savingsAccount->save();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Savings record saved successfully.');
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

        // ধাপ ৫: কালেক্টরদের তালিকা আনুন (কালেক্টর পরিবর্তন করার সুবিধার জন্য)
        $collectors = \App\Models\User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Admin', 'Field Worker']))
            ->orderBy('name')->get(['id', 'name']);

        return view('savings_collections.edit', compact(
            'savingsCollection',
            'accounts',
            'currentDepositAccount',
            'collectors'
        ));
    }

    public function update(Request $request, SavingsCollection $savingsCollection)
    {
        $this->authorizeAdmin();

        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'withdraw_amount' => 'nullable|numeric|min:0',
            'interest_amount' => 'nullable|numeric|min:0',
            'collection_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'collector_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $savingsCollection) {

                $oldNetChange = $savingsCollection->amount - $savingsCollection->withdraw_amount + $savingsCollection->interest_amount;
                $savingsCollection->savingsAccount->decrement('current_balance', $oldNetChange);

                // --- ধাপ ১: পুরানো লেনদেন খুঁজুন এবং রিভার্স করুন ---
                $oldTransaction = $savingsCollection->transactions()->first();
                if ($oldTransaction) {
                    $oldTransaction->journalEntries()->delete();
                    $oldTransaction->delete();
                }

                // --- ধাপ ২: নতুন তথ্য দিয়ে কালেকশন রেকর্ড আপডেট করুন ---
                $savingsCollection->update([
                    'amount' => $request->amount ?? 0,
                    'withdraw_amount' => $request->withdraw_amount ?? 0,
                    'interest_amount' => $request->interest_amount ?? 0,
                    'collection_date' => $request->collection_date,
                    'collector_id' => $request->collector_id,
                    'notes' => $request->notes,
                ]);

                // --- ধাপ ৩: নতুন তথ্য দিয়ে নতুন করে অ্যাকাউন্টিং এন্ট্রি দিন ---
                $amount = $request->amount ?? 0;
                $withdrawAmount = $request->withdraw_amount ?? 0;
                $interestAmount = $request->interest_amount ?? 0;
                $depositAccountId = $request->account_id;
                $savingsCode = '2010';
                $interestExpenseCode = '5030';
                $depositAccount = Account::findOrFail($depositAccountId);

                $entries = [];
                // 1. Deposit Transaction
                if ($amount > 0) {
                    $entries[] = ['account_id' => $depositAccount->id, 'debit' => $amount];
                    $entries[] = ['account_id' => Account::where('code', $savingsCode)->first()->id, 'credit' => $amount];
                }

                // 2. Withdrawal Transaction
                if ($withdrawAmount > 0) {
                    $entries[] = ['account_id' => Account::where('code', $savingsCode)->first()->id, 'debit' => $withdrawAmount];
                    $entries[] = ['account_id' => $depositAccount->id, 'credit' => $withdrawAmount];
                }

                // 3. Interest Transaction
                if ($interestAmount > 0) {
                    $entries[] = ['account_id' => Account::where('code', $interestExpenseCode)->first()->id, 'debit' => $interestAmount];
                    $entries[] = ['account_id' => Account::where('code', $savingsCode)->first()->id, 'credit' => $interestAmount];
                }

                if (! empty($entries)) {
                    $this->accountingService->createTransaction(
                        $request->collection_date,
                        'Savings transaction for '.$savingsCollection->member->name.' (Updated)',
                        $entries,
                        $savingsCollection
                    );
                }

                // সদস্যের সঞ্চয় অ্যাকাউন্টে নতুন পরিমাণ যোগ করুন
                $newNetChange = $amount - $withdrawAmount + $interestAmount;
                $savingsCollection->savingsAccount->increment('current_balance', $newNetChange);
                $savingsCollection->savingsAccount->save();
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: '.$e->getMessage())->withInput();
        }

        return redirect()->route('savings-collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(SavingsCollection $savingsCollection)
    {
        $this->authorizeAdmin();

        try {
            DB::transaction(function () use ($savingsCollection) {
                $netChange = $savingsCollection->amount - $savingsCollection->withdraw_amount + $savingsCollection->interest_amount;
                $savingsCollection->savingsAccount->decrement('current_balance', $netChange);
                $savingsCollection->savingsAccount->save();

                $transaction = $savingsCollection->transactions()->first();
                if ($transaction) {
                    $transaction->journalEntries()->delete();
                    $transaction->delete();
                }

                $savingsCollection->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('savings-collections.index')->with('error', 'An error occurred while deleting the collection: '.$e->getMessage());
        }

        return redirect()->route('savings-collections.index')->with('success', 'Collection deleted and all associated balances have been restored.');
    }

    private function authorizeAdmin()
    {
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }
    }
}
