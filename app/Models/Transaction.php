<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'transactionable_id',
        'transactionable_type',
        'savings_account_id',
        'loan_account_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the account that the transaction belongs to.
     * প্রতিটি লেনদেন একটি নির্দিষ্ট আর্থিক অ্যাকাউন্টের (Account) সাথে যুক্ত।
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the parent transactionable model.
     * এই পলিমরফিক রিলেশনশিপটি লেনদেনের উৎসকে (যেমন: Salary, BalanceTransfer, SavingsCollection) নির্দেশ করে।
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function transactionable()
    {
        return $this->morphTo();
    }
    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class);
    }
    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }
}
