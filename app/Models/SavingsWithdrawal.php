<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsWithdrawal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'savings_account_id',
        'member_id',
        'processed_by_user_id',
        'withdrawal_amount',
        'profit_amount',
        'total_amount',
        'withdrawal_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'withdrawal_date' => 'date',
    ];

    // --- রিলেশনশিপ (Relationships) ---

    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function profitExpense()
    {
        return $this->morphOne(Expense::class, 'expensable');
    }
}
