<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_account_id',
        'member_id',
        'collector_id',
        'installment_no',
        'paid_amount',
        'grace_amount',
        'payment_date',
        'receipt_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    /**
     * Get the loan account that this installment belongs to.
     */
    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class);
    }

    /**
     * Get the member associated with this installment.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the collector (user) who collected this installment.
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function transactions()
{
    return $this->morphMany(Transaction::class, 'transactionable');
}
}
