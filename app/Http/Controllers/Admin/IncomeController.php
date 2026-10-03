<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TransactionReversalTrait;
use App\Models\Account;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Services\AccountingService;
use Auth;
use DB;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    use TransactionReversalTrait;

    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index(Request $request)
    {
        $query = Income::with('category', 'user', 'account');

        if ($request->filled('income_category_id')) {
            $query->where('income_category_id', $request->income_category_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('income_date', [$request->start_date, $request->end_date]);
        }

        $incomes = $query->latest()->paginate(15);
        $accounts = Account::active()->payment()->get();
        $categories = IncomeCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.incomes.index', compact('incomes', 'categories', 'accounts'));
    }

    public function create()
    {
        $categories = IncomeCategory::where('is_active', true)->get();
        $accounts = Account::active()->payment()->get();

        return view('admin.incomes.create', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'income_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $data = $request->except(['receipt']);
                $data['user_id'] = Auth::id();

                $income = Income::create($data);

                if ($request->hasFile('receipt')) {
                    $income->addMediaFromRequest('receipt')->toMediaCollection('income_receipts');
                }

                $incomeCategory = IncomeCategory::find($request->income_category_id);
                $receivingAccount = Account::findOrFail($request->account_id);

                // Get or Create General Income GL Account
                $incomeGLAccount = Account::firstOrCreate(
                    ['name' => 'বিবিধ আয় (General Income)', 'type' => 'Income'],
                    ['code' => 'INC-GEN', 'is_system_account' => true, 'is_active' => true]
                );

                $this->accountingService->createTransaction(
                    $request->income_date,
                    'Income: '.$incomeCategory->name.($request->description ? ' - '.$request->description : ''),
                    [
                        ['account_id' => $receivingAccount->id, 'debit' => $request->amount], // Debit Cash/Bank (Asset increases)
                        ['account_id' => $incomeGLAccount->id, 'credit' => $request->amount], // Credit Income (Income increases)
                    ],
                    $income
                );
            });
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return redirect()->route('admin.incomes.index')->with('error', 'An error occurred: '.$e->getMessage());
        }

        return redirect()->route('admin.incomes.index')->with('success', __('Income recorded successfully.'));
    }

    public function edit(Income $income)
    {
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $categories = IncomeCategory::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::active()->payment()->orderBy('name')->get();

        return view('admin.incomes.edit', compact('income', 'categories', 'accounts'));
    }

    public function update(Request $request, Income $income)
    {
        $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'income_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $income) {
                // Reverse old transaction
                $oldTransaction = $income->transactions()->first();
                if ($oldTransaction) {
                    $this->reverseTransaction($oldTransaction);
                }

                $income->update($request->except(['receipt']));
                if ($request->hasFile('receipt')) {
                    $income->clearMediaCollection('income_receipts');
                    $income->addMediaFromRequest('receipt')->toMediaCollection('income_receipts');
                }

                $incomeCategory = IncomeCategory::find($request->income_category_id);
                $receivingAccount = Account::findOrFail($request->account_id);
                $incomeGLAccount = Account::where('type', 'Income')->where('name', 'বিবিধ আয় (General Income)')->first()
                    ?? Account::where('type', 'Income')->first();

                $this->accountingService->createTransaction(
                    $request->income_date,
                    'Income: '.$incomeCategory->name.' (Updated)',
                    [
                        ['account_id' => $receivingAccount->id, 'debit' => $request->amount],
                        ['account_id' => $incomeGLAccount->id, 'credit' => $request->amount],
                    ],
                    $income
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: '.$e->getMessage())->withInput();
        }

        return redirect()->route('admin.incomes.index')->with('success', __('Income updated successfully.'));
    }

    public function destroy(Income $income)
    {
        if (! Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($income) {
                $transaction = $income->transactions()->first();
                if ($transaction) {
                    $this->reverseTransaction($transaction);
                }
                $income->clearMediaCollection('income_receipts');
                $income->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.incomes.index')->with('error', 'An error occurred while deleting the income: '.$e->getMessage());
        }

        return redirect()->route('admin.incomes.index')->with('success', __('Income deleted successfully.'));
    }

    // reverseTransaction is now provided by TransactionReversalTrait
}
