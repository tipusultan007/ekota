<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Member extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'account_no',
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
        'nominee_name',
        'nominee_relation',
        'nominee_nid',
        'nominee_phone',
        'nominee_address',
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

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public static function generateNextMemberAccountNumber()
    {
        $lastMember = self::orderByRaw('CAST(account_no AS UNSIGNED) DESC')
            ->first();

        $newNumber = 101; // Starting from 101 or any base you prefer
        if ($lastMember && is_numeric($lastMember->account_no)) {
            $newNumber = (int) $lastMember->account_no + 1;
        }

        return (string) $newNumber;
    }
}
