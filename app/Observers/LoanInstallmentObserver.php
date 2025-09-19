<?php
namespace App\Observers;
use App\Models\LoanInstallment;
class LoanInstallmentObserver
{
    public function deleting(LoanInstallment $loanInstallment): void
    {
        \Illuminate\Support\Facades\Log::info("Deleting transactions for Loan Installment ID: " . $loanInstallment->id);

        $loanInstallment->transactions()->delete();
    }
}
