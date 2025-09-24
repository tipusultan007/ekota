<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
     protected $fillable = [
        'transaction_id',
        'account_id',
        'debit',
        'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Each journal entry belongs to a single transaction.
     * প্রতিটি জার্নাল এন্ট্রি একটি মূল লেনদেনের অংশ।
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Each journal entry affects one account from the Chart of Accounts.
     * প্রতিটি জার্নাল এন্ট্রি একটি নির্দিষ্ট হিসাবের খাতকে প্রভাবিত করে।
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
