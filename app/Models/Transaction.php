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
        'date',
        'description',
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
        'date' => 'date',
    ];


      /**
     * Get the parent transactionable model (e.g., SavingsCollection, LoanInstallment).
     * এই রিলেশনশিপটি লেনদেনের উৎসকে নির্দেশ করে।
     */
    public function transactionable()
    {
        return $this->morphTo();
    }

    /**
     * A transaction is composed of multiple journal entries.
     * প্রতিটি লেনদেনের দুটি বা তার বেশি জার্নাল এন্ট্রি থাকবে।
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
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
