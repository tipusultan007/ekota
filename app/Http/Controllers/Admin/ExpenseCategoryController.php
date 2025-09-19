<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::latest()
            ->withSum('expenses', 'amount') // 'expenses' রিলেশনশিপের 'amount' কলামের যোগফল আনুন
            ->paginate(15);
        return view('admin.expense_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.expense_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:expense_categories,name']);
        ExpenseCategory::create($request->all());
        return redirect()->route('admin.expense-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('admin.expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'is_active' => 'required|boolean',
        ]);
        $expenseCategory->update($request->all());
        return redirect()->route('admin.expense-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        // ক্যাটাগরির সাথে যুক্ত খরচ থাকলে ডিলিট করা যাবে না
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->route('admin.expense-categories.index')->with('error', 'Cannot delete category with associated expenses.');
        }
        $expenseCategory->delete();
        return redirect()->route('admin.expense-categories.index')->with('success', 'Category deleted successfully.');
    }
}
