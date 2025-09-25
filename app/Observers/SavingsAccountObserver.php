<?php
namespace App\Observers;
use App\Models\SavingsAccount;
use Illuminate\Support\Facades\DB;

class SavingsAccountObserver
{
    public function deleting(SavingsAccount $savingsAccount): void
    {
        try {
            DB::transaction(function () use ($savingsAccount) {

                foreach ($savingsAccount->collections()->get() as $collection) {
                    $collection->delete();
                }

                foreach ($savingsAccount->withdrawals()->get() as $withdrawal) {
                    $withdrawal->delete();
                }

                $savingsAccount->clearMediaCollection('nominee_photo');

            });
        } catch (\Exception $e) {
            \Log::error("Error deleting savings account (ID: {$savingsAccount->id}): " . $e->getMessage());
            throw $e;
        }
    }
}
