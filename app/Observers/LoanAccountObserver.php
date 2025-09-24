<?php

namespace App\Observers;

use App\Models\LoanAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanAccountObserver
{
    
    public function deleting(LoanAccount $loanAccount): void
    {
        try {
            DB::transaction(function () use ($loanAccount) {
        
                $loanAccount->installments()->delete();
                
                if ($loanAccount->guarantor) {
                    $loanAccount->guarantor->delete();
                }

                $loanAccount->clearMediaCollection('loan_documents');

            });
        } catch (\Exception $e) {
           
            Log::error("Error deleting loan account (ID: {$loanAccount->id}): " . $e->getMessage());
           
            throw $e;
        }
    }
}
