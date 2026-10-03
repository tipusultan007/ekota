<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Income extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'income_category_id',
        'account_id',
        'user_id',
        'amount',
        'income_date',
        'description',
    ];

    protected $casts = [
        'income_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('income_receipts');
    }
}
