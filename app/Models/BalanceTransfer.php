<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransfer extends Model
{
    use HasFactory;

    protected $fillable = ['from_account_id', 'to_account_id', 'amount', 'transfer_date', 'notes', 'processed_by_user_id'];
    protected $casts = ['transfer_date' => 'date'];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function transactions()
    {
        return $this->morphMany(\App\Models\Transaction::class, 'transactionable');
    }
}
