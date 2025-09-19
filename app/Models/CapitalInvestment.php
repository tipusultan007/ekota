<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapitalInvestment extends Model
{

    protected $fillable = ['user_id', 'account_id', 'amount', 'investment_date', 'description'];
    protected $casts = ['investment_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function account() { return $this->belongsTo(Account::class); }

    // পলিমরফিক রিলেশন (অ্যাকাউন্টিং-এর জন্য)
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
