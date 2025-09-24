<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create a compound double-entry journal transaction.
     *
     * @param string $date
     * @param string $description
     * @param array $entries An array of journal entries, e.g., [['account_id' => 1, 'debit' => 100], ['account_id' => 2, 'credit' => 100]]
     * @param \Illuminate\Database\Eloquent\Model $transactionable
     * @return Transaction
     * @throws \Exception
     */
    public function createTransaction(string $date, string $description, array $entries, $transactionable): Transaction
    {
        // নিশ্চিত করুন যে ডেবিট এবং ক্রেডিটের যোগফল সমান
        $totalDebits = collect($entries)->sum('debit');
        $totalCredits = collect($entries)->sum('credit');

        if (round($totalDebits, 2) !== round($totalCredits, 2)) {
            throw new \Exception('Debit and Credit totals do not match.');
        }
        if ($totalDebits <= 0) {
            throw new \Exception('Transaction amount must be greater than zero.');
        }

        // ... রিলেশনশিপ আছে কিনা তা যাচাই করার কোড ...
        $relationName = $this->getTransactionRelationName($transactionable);
        
        return DB::transaction(function () use ($date, $description, $entries, $transactionable, $relationName) {
            
            // ১. মূল Transaction রেকর্ড তৈরি করুন
            $transaction = $transactionable->{$relationName}()->create([
                'date' => $date,
                'description' => $description,
            ]);

            // ২. প্রতিটি এন্ট্রির জন্য Journal Entry এবং ব্যালেন্স আপডেট করুন
            foreach ($entries as $entry) {
                $account = Account::findOrFail($entry['account_id']);
                $debitAmount = $entry['debit'] ?? null;
                $creditAmount = $entry['credit'] ?? null;

                $transaction->journalEntries()->create([
                    'account_id' => $account->id,
                    'debit' => $debitAmount,
                    'credit' => $creditAmount,
                ]);
            }
            
            return $transaction;
        });
    }


    /**
     * Determine the correct transaction relationship name for a given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     * @throws \Exception
     */
    private function getTransactionRelationName($model): string
    {
        if (method_exists($model, 'transactions')) {
            return 'transactions'; // morphMany-এর জন্য
        }

        if (method_exists($model, 'transaction')) {
            return 'transaction'; // morphOne-এর জন্য
        }

        // যদি কোনো রিলেশনশিপ না পাওয়া যায়, তাহলে একটি এরর থ্রো করুন
        throw new \Exception('The provided model [' . get_class($model) . '] does not have a "transaction" or "transactions" relationship defined.');
    }
}