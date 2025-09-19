<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Member extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'area_id',
        'name',
        'father_name',
        'spouse_name',
        'mother_name',
        'mobile_no',
        'email',
        'date_of_birth',
        'nid_no',
        'present_address',
        'permanent_address',
        'joining_date',
        'status',
        'gender',
        'marital_status',
        'blood_group',
        'occupation',
        'work_place',
        'religion',
        'nationality',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
    ];

    /**
     * Get the area that the member belongs to.
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the savings accounts for the member.
     */
    public function savingsAccounts()
    {
        return $this->hasMany(SavingsAccount::class);
    }

    /**
     * Get the loan accounts for the member.
     */
    public function loanAccounts()
    {
        return $this->hasMany(LoanAccount::class);
    }

    /**
     * Get all of the withdrawals for the member.
     */
    public function withdrawals()
    {
        return $this->hasMany(SavingsWithdrawal::class);
    }
}
