<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Guarantor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'loan_account_id', // কোন ঋণের বিপরীতে জামিনদার
        'member_id',       // জামিনদার যদি সদস্য হন, তার আইডি
        'name',            // জামিনদার যদি বাইরের হন, তার নাম
        'phone',           // বাইরের জামিনদারের ফোন নম্বর
        'address',         // বাইরের জামিনদারের ঠিকানা
    ];

    /**
     * Get the loan account that this guarantor is for.
     */
    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class);
    }

    /**
     * Get the member details if the guarantor is an existing member.
     * এই সম্পর্কটি ঐচ্ছিক, কারণ জামিনদার বাইরেরও হতে পারেন।
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Register the media collections for the model.
     * জামিনদারের ডকুমেন্টগুলো এখানে সংরক্ষিত হবে।
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('guarantor_nid')
            ->singleFile(); // NID সাধারণত একটিই হয়

        $this
            ->addMediaCollection('guarantor_documents'); // অন্যান্য ডকুমেন্ট একাধিক হতে পারে
    }
}
