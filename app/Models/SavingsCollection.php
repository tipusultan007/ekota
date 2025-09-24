<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_account_id',
        'member_id',
        'collector_id',
        'amount',
        'collection_date',
        'receipt_no',
        'notes',
    ];

    protected $casts = [
        'collection_date' => 'date',
    ];

    /**
     * Get the savings account that this collection belongs to.
     */
    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    /**
     * Get the member associated with this collection.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the collector (user) who made this collection.
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
