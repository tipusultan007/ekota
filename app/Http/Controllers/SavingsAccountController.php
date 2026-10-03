<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Area;
use App\Models\Member;
use App\Models\SavingsAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SavingsAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SavingsAccount::with('member.area', 'member');

        // ভূমিকা অনুযায়ী বেস কোয়েরি ফিল্টার
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereHas('member', function ($q) use ($areaIds) {
                $q->whereIn('area_id', $areaIds);
            });
        }

        // রিকোয়েস্ট থেকে আসা ফিল্টারগুলো প্রয়োগ করুন
        if ($request->filled('area_id') && $user->hasRole('Admin')) {
            $query->whereHas('member', fn ($q) => $q->where('area_id', $request->area_id));
        }
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('opening_date', [$request->start_date, $request->end_date]);
        }

        // Statistics calculation (Optimized)
        $statistics = (clone $query)->toBase()->selectRaw('SUM(current_balance) as total_balance')->first();

        $statsSub = \App\Models\SavingsCollection::whereIn('savings_account_id', (clone $query)->select('id'))
            ->selectRaw('SUM(amount) as total_deposit, SUM(withdraw_amount) as total_withdrawal, SUM(interest_amount) as total_interest')
            ->first();

        $statistics->total_deposit = $statsSub->total_deposit ?? 0;
        $statistics->total_withdrawal = $statsSub->total_withdrawal ?? 0;
        $statistics->total_interest = $statsSub->total_interest ?? 0;

        $savingsAccounts = $query->orderByRaw('CAST(account_no AS UNSIGNED) ASC')->paginate(25);
        $totalSavingsAccounts = $savingsAccounts->total();

        // ফিল্টারের ড্রপডাউনের জন্য ডেটা
        $members = Member::orderBy('name')->get(['id', 'name', 'account_no']);
        if ($user->hasRole('Admin')) {
            $areas = Area::orderBy('name')->get(['id', 'name']);
        } else {
            $areas = $user->areas()->orderBy('name')->get(['areas.id', 'areas.name']);
        }

        return view('savings_accounts.index', compact('savingsAccounts', 'totalSavingsAccounts', 'members', 'areas', 'statistics'));
    }

    public function create(Member $member)
    {
        $this->authorizeAccess($member);

        return view('savings_accounts.create', compact('member'));
    }

    public function newSavings()
    {
        $members = Member::select('name', 'mobile_no', 'id')->get();

        return view('savings_accounts.new_savings', compact('members'));
    }

    public function newSavingsStore(Request $request)
    {
        $fullAccountNo = null;
        if ($request->auto_generate_account_no == '0' && $request->filled('account_no_suffix')) {
            // ৫ ডিজিটের জন্য 0 দিয়ে পূরণ করুন
            $suffix = str_pad($request->account_no_suffix, 5, '0', STR_PAD_LEFT);
            $fullAccountNo = 'SAV-'.$suffix;

            // রিকোয়েস্টে নতুন মানটি যোগ করুন যাতে ভ্যালিডেটর এটি ব্যবহার করতে পারে
            $request->merge(['account_no' => $fullAccountNo]);
        }

        $request->validate([
            'member_id' => 'required',
            'account_no' => [
                Rule::requiredIf($request->auto_generate_account_no == '0'),
                'nullable',
                'string',
                'max:255',
                Rule::unique('savings_accounts'),
            ],
            'scheme_type' => 'required|string',
            'interest_rate' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
            'nominee_name' => 'required|string|max:255',
            'nominee_relation' => 'required|string|max:255',
            'collection_frequency' => 'required|string|in:daily,weekly,monthly',
            'nominee_nid' => 'nullable|string|max:50',
            'nominee_phone' => 'nullable|string|max:20',
            'nominee_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // ভ্যালিডেশন
        ]);

        $data = $request->except(['nominee_photo', 'auto_generate_account_no']);

        // Use member's account number
        $memberForAccount = Member::findOrFail($request->member_id);
        $data['account_no'] = $memberForAccount->account_no;

        $openingDate = Carbon::parse($request->opening_date);
        if ($request->collection_frequency == 'daily') {
            $data['next_due_date'] = $openingDate->addDay();
        } elseif ($request->collection_frequency == 'weekly') {
            $data['next_due_date'] = $openingDate->addWeek();
        } elseif ($request->collection_frequency == 'monthly') {
            $data['next_due_date'] = $openingDate->addMonth();
        }

        $savingsAccount = SavingsAccount::create($data);

        // Spatie Media Library ব্যবহার করে ছবি আপলোড
        if ($request->hasFile('nominee_photo')) {
            $savingsAccount
                ->addMediaFromRequest('nominee_photo')
                ->toMediaCollection('nominee_photo');
        }

        return redirect()->route('savings_accounts.show', $savingsAccount->id)->with('success', 'Savings account created with nominee details.');
    }

    public function store(Request $request, Member $member)
    {
        $this->authorizeAccess($member);
        $fullAccountNo = null;
        if ($request->auto_generate_account_no == '0' && $request->filled('account_no_suffix')) {
            // ৫ ডিজিটের জন্য 0 দিয়ে পূরণ করুন
            $suffix = str_pad($request->account_no_suffix, 5, '0', STR_PAD_LEFT);
            $fullAccountNo = 'SAV-'.$suffix;

            // রিকোয়েস্টে নতুন মানটি যোগ করুন যাতে ভ্যালিডেটর এটি ব্যবহার করতে পারে
            $request->merge(['account_no' => $fullAccountNo]);
        }
        $request->validate([
            'scheme_type' => 'required|string',
            'account_no' => [
                Rule::requiredIf($request->auto_generate_account_no == '0'),
                'nullable',
                'string',
                'max:255',
                Rule::unique('savings_accounts'),
            ],
            'interest_rate' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
            'nominee_name' => 'required|string|max:255',
            'nominee_relation' => 'required|string|max:255',
            'collection_frequency' => 'required|string|in:daily,weekly,monthly',
            'nominee_nid' => 'nullable|string|max:50',
            'nominee_phone' => 'nullable|string|max:20',
            'nominee_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // ভ্যালিডেশন
        ]);
        $data = $request->except(['nominee_photo', 'auto_generate_account_no']);
        $data['member_id'] = $member->id;

        // Use member's account number
        $data['account_no'] = $member->account_no;

        $openingDate = Carbon::parse($request->opening_date);
        if ($request->collection_frequency == 'daily') {
            $data['next_due_date'] = $openingDate->addDay();
        } elseif ($request->collection_frequency == 'weekly') {
            $data['next_due_date'] = $openingDate->addWeek();
        } elseif ($request->collection_frequency == 'monthly') {
            $data['next_due_date'] = $openingDate->addMonth();
        }

        // dd($data);
        $savingsAccount = SavingsAccount::create($data);

        // Spatie Media Library ব্যবহার করে ছবি আপলোড
        if ($request->hasFile('nominee_photo')) {
            $savingsAccount
                ->addMediaFromRequest('nominee_photo')
                ->toMediaCollection('nominee_photo');
        }

        return redirect()->route('members.show', $member->id)->with('success', 'Savings account created with nominee details.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SavingsAccount $savingsAccount)
    {
        // নিরাপত্তা যাচাই
        $this->authorizeAccess($savingsAccount->member);

        $collections = $savingsAccount->collections()
            ->orderBy('collection_date', 'desc')
            ->paginate(20);

        $accounts = Account::active()->payment()->get();

        return view('savings_accounts.show', compact('savingsAccount', 'collections', 'accounts'));
    }

    private function authorizeAccess(Member $member)
    {
        $user = Auth::user();
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            if (! in_array($member->area_id, $areaIds)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }
    }

    public function edit(SavingsAccount $savingsAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিনরা অ্যাকাউন্ট সম্পাদনা করতে পারবেন
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION. You do not have permission to edit accounts.');
        }

        // if ($savingsAccount->status !== 'active') {
        //     return redirect()->route('savings_accounts.show', $savingsAccount->id)
        //         ->with('error', 'This account cannot be edited because it is already ' . $savingsAccount->status . '.');
        // }

        return view('savings_accounts.edit', compact('savingsAccount'));
    }

    public function update(Request $request, SavingsAccount $savingsAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $request->validate([
            'scheme_type' => 'required|string|max:255',
            'interest_rate' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
            'status' => 'required|string|in:active,closed,matured',

            // Nominee validation
            'nominee_name' => 'required|string|max:255',
            'nominee_relation' => 'required|string|max:255',
            'nominee_phone' => 'nullable|string|max:20',
            'nominee_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $savingsAccount) {
                $data = $request->except('nominee_photo');
                if ($savingsAccount->collection_frequency !== $request->collection_frequency || $savingsAccount->opening_date->format('Y-m-d') !== $request->opening_date) {
                    $baseDate = Carbon::parse($request->opening_date);

                    // সর্বশেষ কালেকশনের তারিখকে বেস ডেট হিসেবে ধরা যেতে পারে, যদি থাকে
                    $lastCollection = $savingsAccount->collections()->latest('collection_date')->first();
                    if ($lastCollection) {
                        $baseDate = Carbon::parse($lastCollection->collection_date);
                    }

                    if ($request->collection_frequency == 'daily') {
                        $data['next_due_date'] = $baseDate->addDay();
                    } elseif ($request->collection_frequency == 'weekly') {
                        $data['next_due_date'] = $baseDate->addWeek();
                    } elseif ($request->collection_frequency == 'monthly') {
                        $data['next_due_date'] = $baseDate->addMonth();
                    }
                }

                // ধাপ ১: সঞ্চয় অ্যাকাউন্টের মূল তথ্য আপডেট করুন
                $savingsAccount->update($data);

                // ধাপ ২: যদি নতুন নমিনির ছবি আপলোড করা হয়, তাহলে সেটি আপডেট করুন
                if ($request->hasFile('nominee_photo')) {
                    // পুরানো ছবি ডিলিট করে দিন
                    $savingsAccount->clearMediaCollection('nominee_photo');
                    // নতুন ছবি যোগ করুন
                    $savingsAccount->addMediaFromRequest('nominee_photo')->toMediaCollection('nominee_photo');
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating the account: '.$e->getMessage())->withInput();
        }

        return redirect()->route('savings_accounts.show', $savingsAccount->id)
            ->with('success', 'Savings account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * একটি সঞ্চয় অ্যাকাউন্ট এবং এর সাথে সম্পর্কিত সকল ডেটা ডিলিট করে।
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SavingsAccount $savingsAccount)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            // এই একটি মাত্র লাইনই SavingsAccountObserver-কে এবং তার ভেতরের চেইনকে ট্রিগার করবে
            $savingsAccount->delete();
        } catch (\Exception $e) {
            return redirect()->route('savings_accounts.index')
                ->with('error', 'Failed to delete the savings account: '.$e->getMessage());
        }

        return redirect()->route('savings_accounts.index')
            ->with('success', 'Savings account and all its data have been permanently deleted.');
    }

    public function generateNextSavingsAccountNumber()
    {
        $prefix = 'SAV-';

        // ডাটাবেস থেকে সর্বশেষ অ্যাকাউন্ট নম্বর খুঁজুন যা 'SAV-' দিয়ে শুরু হয়
        // এবং শুধুমাত্র সাংখ্যিক অংশটিকে عددی হিসেবে সাজান
        $lastAccount = SavingsAccount::where('account_no', 'like', $prefix.'%')
            ->select('account_no')
            ->get()
            ->sortByDesc(function ($account) use ($prefix) {
                return (int) str_replace($prefix, '', $account->account_no);
            })
            ->first();

        $newNumber = 1;
        if ($lastAccount) {
            // উপসর্গ ('SAV-') বাদ দিয়ে শুধুমাত্র নম্বর অংশটি নিন
            $lastNumber = (int) str_replace($prefix, '', $lastAccount->account_no);
            $newNumber = $lastNumber + 1;
        }

        // ৫ ডিজিটের জন্য 0 দিয়ে পূরণ করুন (e.g., 00001, 00123)
        return $prefix.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
