<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $categories = IncomeCategory::latest()
            ->withSum('incomes', 'amount')
            ->paginate(15);
        return view('admin.income_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.income_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:income_categories,name']);
        IncomeCategory::create($request->all());
        return redirect()->route('admin.income-categories.index')->with('success', __('Income category created successfully.'));
    }

    public function edit(IncomeCategory $incomeCategory)
    {
        return view('admin.income_categories.edit', compact('incomeCategory'));
    }

    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name,' . $incomeCategory->id,
            'is_active' => 'required|boolean',
        ]);
        $incomeCategory->update($request->all());
        return redirect()->route('admin.income-categories.index')->with('success', __('Income category updated successfully.'));
    }

    public function destroy(IncomeCategory $incomeCategory)
    {
        if ($incomeCategory->incomes()->count() > 0) {
            return redirect()->route('admin.income-categories.index')->with('error', __('Cannot delete category with associated incomes.'));
        }
        $incomeCategory->delete();
        return redirect()->route('admin.income-categories.index')->with('success', __('Income category deleted successfully.'));
    }
}
