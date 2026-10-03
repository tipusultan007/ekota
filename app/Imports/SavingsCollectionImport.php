<?php

namespace App\Imports;

use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\Member;
use App\Models\SavingsCollection;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SavingsCollectionImport implements ToCollection, WithHeadingRow
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $accountNo = $row['account_no'] ?? null;
            $date = isset($row['date']) ? Carbon::parse($row['date']) : now();
            $deposit = $row['deposit'] ?? 0;

            if (empty($accountNo) || $deposit <= 0) {
                continue;
            }

            $member = Member::where('account_no', $accountNo)->first();
            if (! $member) {
                continue;
            }

            $savingsAccount = $member->savingsAccounts()->where('scheme_type', 'General')->where('status', 'active')->first();
            if (! $savingsAccount) {
                continue;
            }

            DB::transaction(function () use ($member, $savingsAccount, $date, $deposit) {
                // 1. Create Collection Record
                $collection = SavingsCollection::create([
                    'savings_account_id' => $savingsAccount->id,
                    'member_id' => $member->id,
                    'collector_id' => Auth::id() ?? 1, // Default to system user if not logged in (CLI)
                    'amount' => $deposit,
                    'collection_date' => $date,
                    'notes' => 'Bulk Import',
                ]);

                // 2. Accounting Transaction
                $cashAccount = Account::where('code', '1010')->first();
                $savingsPayableAccount = Account::where('code', '2010')->first();

                if ($cashAccount && $savingsPayableAccount) {
                    $this->accountingService->createTransaction(
                        $date->format('Y-m-d'),
                        'Savings deposit from '.$member->name.' (Import)',
                        [
                            ['account_id' => $cashAccount->id, 'debit' => $deposit],
                            ['account_id' => $savingsPayableAccount->id, 'credit' => $deposit],
                        ],
                        $collection
                    );
                }

                // 3. Update Balance
                $savingsAccount->increment('current_balance', $deposit);

                // 4. Update Next Due Date
                $savingsAccount->next_due_date = DateHelper::calculateNextDueDate(
                    $savingsAccount->opening_date,
                    $savingsAccount->collection_frequency,
                    $savingsAccount->next_due_date
                );
                $savingsAccount->save();
            });
        }
    }
}
