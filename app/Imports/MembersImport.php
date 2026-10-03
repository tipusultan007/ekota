<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Area;
use App\Models\LoanAccount;
use App\Models\Member;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MembersImport implements ToCollection, WithHeadingRow
{
    protected AccountingService $accountingService;

    public array $skippedLoans = [];

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Basic data extraction
            $dateValue = $row['date'] ?? null;
            if (is_numeric($dateValue)) {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue));
            } elseif ($dateValue) {
                $date = Carbon::parse($dateValue);
            } else {
                $date = now();
            }
            $accountNo = $row['account_no'] ?? null;
            $name = $row['name'] ?? null;
            $mobileNo = isset($row['mobile_no']) ? '0'.ltrim((string) $row['mobile_no'], '0') : null;
            $areaName = $row['area'] ?? null;
            $loanAmount = $row['loan_amount'] ?? 0;

            if (empty($name) || empty($accountNo)) {
                continue;
            }

            DB::transaction(function () use ($date, $accountNo, $name, $mobileNo, $areaName, $loanAmount) {
                // 1. Find or create Area
                $area = Area::firstOrCreate(
                    ['name' => $areaName],
                    ['code' => strtoupper(substr($areaName, 0, 3)).rand(10, 99), 'is_active' => true]
                );

                // 2. Create Member
                $member = Member::updateOrCreate(
                    ['account_no' => $accountNo],
                    [
                        'name' => $name,
                        'mobile_no' => $mobileNo,
                        'area_id' => $area->id,
                        'joining_date' => $date,
                        'status' => 'active',
                    ]
                );

                // 3. Create Automatic Savings Account (if not exists)
                if (! $member->savingsAccounts()->where('scheme_type', 'General')->exists()) {
                    $member->savingsAccounts()->create([
                        'account_no' => $member->account_no,
                        'scheme_type' => 'General',
                        'interest_rate' => 0,
                        'opening_date' => $date,
                        'status' => 'active',
                    ]);
                }

                // 4. Create Loan Account if loan_amount > 0
                if ($loanAmount > 0) {
                    $existingLoan = LoanAccount::where('account_no', $accountNo)
                        ->whereDate('disbursement_date', $date->toDateString())
                        ->exists();

                    if ($existingLoan) {
                        $this->skippedLoans[] = [
                            'account_no' => $accountNo,
                            'name' => $name,
                            'loan_amount' => $loanAmount,
                            'date' => $date->format('Y-m-d'),
                        ];

                        return;
                    }

                    $interestRate = 15; // 15% as requested
                    $processingFeeRate = 2; // 2% as requested

                    $interest = ($loanAmount * $interestRate) / 100;
                    $totalPayable = $loanAmount + $interest;
                    $processingFee = ($loanAmount * $processingFeeRate) / 100;

                    // Default values for loan
                    $installmentFrequency = 'daily';
                    $numberOfInstallments = 100; // Assuming 100 as per common practice if not provided
                    $installmentAmount = $totalPayable / $numberOfInstallments;

                    $finalAccountNo = $accountNo;

                    $loanAccount = LoanAccount::create([
                        'member_id' => $member->id,
                        'account_no' => $finalAccountNo,
                        'loan_amount' => $loanAmount,
                        'interest_rate' => $interestRate,
                        'number_of_installments' => $numberOfInstallments,
                        'disbursement_date' => $date,
                        'installment_frequency' => $installmentFrequency,
                        'total_payable' => $totalPayable,
                        'installment_amount' => $installmentAmount,
                        'status' => 'running',
                        'next_due_date' => $date->copy()->addDay(),
                    ]);

                    // Accounting Transactions
                    $cashAccount = Account::where('code', '1010')->first();
                    $loansReceivableAccount = Account::where('code', '1110')->first();
                    $feeIncomeAccount = Account::where('code', '4020')->first();

                    if ($cashAccount && $loansReceivableAccount) {
                        // Loan Disbursement Transaction
                        $this->accountingService->createTransaction(
                            $date->format('Y-m-d'),
                            'Loan disbursed to '.$member->name.' (Import)',
                            [
                                ['account_id' => $loansReceivableAccount->id, 'debit' => $loanAmount],
                                ['account_id' => $cashAccount->id, 'credit' => $loanAmount],
                            ],
                            $loanAccount
                        );

                        // Processing Fee Transaction
                        if ($processingFee > 0 && $feeIncomeAccount) {
                            $this->accountingService->createTransaction(
                                $date->format('Y-m-d'),
                                'Processing fee income from '.$member->name.' (Import)',
                                [
                                    ['account_id' => $cashAccount->id, 'debit' => $processingFee],
                                    ['account_id' => $feeIncomeAccount->id, 'credit' => $processingFee],
                                ],
                                $loanAccount
                            );
                        }
                    }
                }
            });
        }
    }
}
