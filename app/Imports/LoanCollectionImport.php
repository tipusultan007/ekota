<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\LoanAccount;
use App\Models\LoanInstallment;
use App\Models\Account;
use App\Services\AccountingService;
use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LoanCollectionImport implements ToCollection, WithHeadingRow
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
            $loanPaid = $row['loan_paid'] ?? 0;

            if (empty($accountNo) || $loanPaid <= 0) continue;

            $member = Member::where('account_no', $accountNo)->first();
            if (!$member) continue;

            $loanAccount = $member->loanAccounts()->where('status', 'running')->first();
            if (!$loanAccount) continue;

            DB::transaction(function () use ($member, $loanAccount, $date, $loanPaid) {
                // 1. Create Installment Record
                $installment = $loanAccount->installments()->create([
                    'member_id' => $member->id,
                    'collector_id' => Auth::id() ?? 1,
                    'installment_no' => ($loanAccount->installments()->count() + 1),
                    'paid_amount' => $loanPaid,
                    'payment_date' => $date,
                    'notes' => 'Bulk Import',
                ]);

                // 2. Accounting Split Logic (Principal / Interest)
                $dueAmount = $loanAccount->total_payable - $loanAccount->total_paid - $loanAccount->grace_amount;
                if ($dueAmount <= 0) return;

                $principalRatio = $loanAccount->loan_amount / $loanAccount->total_payable;
                $principalToClear = min($loanPaid * $principalRatio, $loanAccount->loan_amount - ($loanAccount->total_paid * $principalRatio)); // Approximation
                $interestToClear = $loanPaid - $principalToClear;

                $entries = [
                    ['account_id' => Account::where('code', '1010')->first()->id, 'debit' => $loanPaid],
                ];

                if ($principalToClear > 0) {
                    $entries[] = ['account_id' => Account::where('code', '1110')->first()->id, 'credit' => $principalToClear];
                }
                if ($interestToClear > 0) {
                    $entries[] = ['account_id' => Account::where('code', '4010')->first()->id, 'credit' => $interestToClear];
                }

                $this->accountingService->createTransaction(
                    $date->format('Y-m-d'),
                    'Loan payment from ' . $member->name . ' (Import)',
                    $entries,
                    $installment
                );

                // 3. Update Loan Account
                $loanAccount->increment('total_paid', $loanPaid);
                
                if ($loanAccount->total_paid >= $loanAccount->total_payable) {
                    $loanAccount->status = 'paid';
                }

                if ($loanAccount->status !== 'paid') {
                    $loanAccount->next_due_date = DateHelper::calculateNextDueDate(
                        $loanAccount->disbursement_date,
                        $loanAccount->installment_frequency,
                        $loanAccount->next_due_date
                    );
                }
                $loanAccount->save();
            });
        }
    }
}
