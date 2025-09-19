<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SavingsAccount extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'member_id',
        'account_no',
        'scheme_type',
        'interest_rate',
        'installment',
        'current_balance',
        'opening_date',
        'status',
        'nominee_name',
        'nominee_relation',
        'nominee_nid',
        'nominee_phone',
        'nominee_photo',
        'collection_frequency', 'next_due_date'
    ];

    protected $casts = [
        'opening_date' => 'date',
    ];

    /**
     * Get the member that owns the savings account.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the collections for the savings account.
     */
    public function collections()
    {
        return $this->hasMany(SavingsCollection::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('nominee_photo')
            ->singleFile(); // একজন নমিনির জন্য একটিই ছবি থাকবে
    }

    public function withdrawals()
    {
        return $this->hasMany(SavingsWithdrawal::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
