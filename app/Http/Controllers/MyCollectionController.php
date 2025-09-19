<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SavingsCollection;
use App\Models\LoanInstallment;
use Yajra\DataTables\Facades\DataTables;

class MyCollectionController extends Controller
{
    /**
     * Display the collections page shell.
     * ডেটা এখন AJAX এর মাধ্যমে লোড হবে।
     */
    public function index()
    {
        return view('my_collections.index');
    }

    /**
     * Process datatables ajax request for savings collections.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavingsData()
    {
        $query = SavingsCollection::with('member', 'savingsAccount')
            ->where('collector_id', Auth::id())
            ->select('savings_collections.*');

        return DataTables::of($query)
            ->addColumn('name', function ($collection) {
                $data = $collection->member ? $collection->member->name : '';
                $data .= '<br>'.$collection->savingsAccount->account_no;
                return $data;
            })
            ->editColumn('collection_date', function ($collection) {
                return \Carbon\Carbon::parse($collection->collection_date)->format('d M, Y');
            })
            ->editColumn('amount', function ($collection) {
                return number_format($collection->amount, 2);
            })
            ->rawColumns(['name'])
            ->make(true);
    }

    /**
     * Process datatables ajax request for loan installments.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoanData()
    {
        $query = LoanInstallment::with('member', 'loanAccount')
            ->where('collector_id', Auth::id())
            ->select('loan_installments.*');

        return DataTables::of($query)
            ->addColumn('name', function ($installment) {
                $data = $installment->member ? $installment->member->name : '';
                $data .= '<br>'.$installment->loanAccount->account_no;
                return $data;
            })
            ->editColumn('payment_date', function ($installment) {
                return \Carbon\Carbon::parse($installment->payment_date)->format('d M, Y');
            })
            ->editColumn('paid_amount', function ($installment) {
                return number_format($installment->paid_amount, 2);
            })
            ->rawColumns(['name'])
            ->make(true);
    }
}
