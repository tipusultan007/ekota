<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = ['user_id', 'processed_by_user_id', 'amount', 'salary_month', 'payment_date', 'notes','account_id'];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function expense()
    {
        return $this->morphOne(Expense::class, 'expensable');
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
