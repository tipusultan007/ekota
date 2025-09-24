<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // স্কোপের জন্য Builder ইম্পোর্ট করুন
use Illuminate\Database\Eloquent\Casts\Attribute;

class Account extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'details',
        'is_active',
        'is_payment_account',
        'is_system_account',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_payment_account' => 'boolean', 
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function transfersFrom()
    {
        return $this->hasMany(BalanceTransfer::class, 'from_account_id');
    }
    public function transfersTo()
    {
        return $this->hasMany(BalanceTransfer::class, 'to_account_id');
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include payment accounts.
     * এটি আপনাকে Account::payment()->get() এর মতো সুন্দর কোয়েরি লেখার সুযোগ দেবে।
     */
    public function scopePayment(Builder $query): void
    {
        $query->where('is_payment_account', true);
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    /**
     * Get the dynamically calculated balance for the account.
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: function () {
                
                $sums = $this->journalEntries()
                    ->selectRaw('SUM(debit) as total_debits, SUM(credit) as total_credits')
                    ->first();

               
                $totalDebits = $sums->total_debits ?? 0;
                $totalCredits = $sums->total_credits ?? 0;

                
                if (in_array($this->type, ['Asset', 'Expense'])) {
                    return $totalDebits - $totalCredits;
                }
                
                
                if (in_array($this->type, ['Liability', 'Equity', 'Income'])) {
                    return $totalCredits - $totalDebits;
                }

            
                return 0;
            },
        );
    }

    //======================================================================
    // METHODS
    //======================================================================

    
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
    public function openingBalanceTransaction()
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }
}
