<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'member_id',
        'account_no',
        'date',
        'user_id',
        'savings_account_id',
        'loan_account_id',
        'deposit',
        'savings_balance',
        'loan_installment',
        'loan_balance',
        'grace_amount',
        'notes',
        'withdraw',
    ];

    protected $casts = [
        'date' => 'date',
        'deposit' => 'decimal:2',
        'savings_balance' => 'decimal:2',
        'loan_installment' => 'decimal:2',
        'loan_balance' => 'decimal:2',
        'grace_amount' => 'decimal:2',
        'withdraw' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class);
    }
    
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
