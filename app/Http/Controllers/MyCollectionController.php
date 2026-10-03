<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SavingsCollection;
use App\Models\LoanInstallment;
use Carbon\Carbon;

class MyCollectionController extends Controller
{
    /**
     * Display the collections page with a monthly attendance-sheet style view.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', Carbon::today()->format('Y-m'));
        $date = Carbon::parse($month . '-01');
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $daysInMonth = $date->daysInMonth;

        $userId = Auth::id();

        // --- Savings Collections Matrix ---
        $savingsRaw = SavingsCollection::with('member', 'savingsAccount')
            ->where('collector_id', $userId)
            ->whereBetween('collection_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $savingsMatrix = $savingsRaw->groupBy('savings_account_id')->map(function ($items) {
            $first = $items->first();
            return [
                'member_name' => $first->member->name,
                'account_no' => $first->savingsAccount->account_no,
                'daily_amounts' => $items->groupBy(fn($i) => $i->collection_date->day)->map->sum('amount'),
                'total' => $items->sum('amount')
            ];
        })->sortBy('account_no');

        // --- Loan Installments Matrix ---
        $loansRaw = LoanInstallment::with('member', 'loanAccount')
            ->where('collector_id', $userId)
            ->whereBetween('payment_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $loansMatrix = $loansRaw->groupBy('loan_account_id')->map(function ($items) {
            $first = $items->first();
            return [
                'member_name' => $first->member->name,
                'account_no' => $first->loanAccount->account_no,
                'daily_amounts' => $items->groupBy(fn($i) => $i->payment_date->day)->map->sum('paid_amount'),
                'total' => $items->sum('paid_amount')
            ];
        })->sortBy('account_no');

        return view('my_collections.index', compact('savingsMatrix', 'loansMatrix', 'month', 'daysInMonth'));
    }
}
