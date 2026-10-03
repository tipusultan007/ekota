<?php

namespace App\Http\Controllers;

use App\Imports\MembersImport;
use App\Imports\SavingsCollectionImport;
use App\Imports\LoanCollectionImport;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportController extends Controller
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function showImportForm()
    {
        return view('members.import');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:2048']);
        try {
            $import = new MembersImport($this->accountingService);
            Excel::import($import, $request->file('file'));
            
            if (count($import->skippedLoans) > 0) {
                return redirect()->route('members.index')
                    ->with('success', 'Members and loans imported successfully!')
                    ->with('skippedLoans', $import->skippedLoans);
            }

            return redirect()->route('members.index')->with('success', 'Members and loans imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importSavings(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:2048']);
        try {
            Excel::import(new SavingsCollectionImport($this->accountingService), $request->file('file'));
            return redirect()->route('savings-collections.index')->with('success', 'Savings collections imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Savings import failed: ' . $e->getMessage());
        }
    }

    public function importLoans(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:2048']);
        try {
            Excel::import(new LoanCollectionImport($this->accountingService), $request->file('file'));
            return redirect()->route('loan-installments.index')->with('success', 'Loan installments imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Loan import failed: ' . $e->getMessage());
        }
    }
}
