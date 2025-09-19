<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LoanAccount extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'account_no',
        'loan_amount',
        'total_payable',
        'total_paid',
        'grace_amount',
        'interest_rate',
        'number_of_installments',
        'installment_amount',
        'disbursement_date',
        'status',
        'installment_frequency', 'next_due_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'disbursement_date' => 'date',
        'loan_amount' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'installment_amount' => 'decimal:2',
    ];

    /**
     * Get the member (borrower) that owns the loan account.
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * Get the guarantor associated with the loan account.
     * একটি ঋণের শুধুমাত্র একজন জামিনদার থাকবে।
     */
    public function guarantor()
    {
        return $this->hasOne(Guarantor::class);
    }

    /**
     * Get the installments for the loan account.
     * একটি ঋণের একাধিক কিস্তি থাকবে।
     */
    public function installments()
    {
        return $this->hasMany(LoanInstallment::class);
    }

    /**
     * Register the media collections for the model.
     * ঋণের বিপরীতে জমাকৃত ডকুমেন্টগুলো এখানে সংরক্ষিত হবে।
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('loan_documents'); // একাধিক ফাইল রাখার অনুমতি
    }
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function getLoanDueAmountAttribute()
    {
        return $this->total_payable - $this->total_paid - $this->grace_amount;
    }

}
