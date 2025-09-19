<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     * এই ফিল্ডগুলোতে create() বা update() মেথডের মাধ্যমে সরাসরি ডেটা প্রবেশ করানো যাবে।
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'balance',
        'details',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     * ডেটাবেস থেকে আনার সময় এই ফিল্ডগুলোর ডেটা টাইপ পরিবর্তন করা হবে।
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2', // ব্যালেন্সকে সবসময় ২ দশমিক স্থান পর্যন্ত দেখাবে
        'is_active' => 'boolean',   // is_active কে true/false হিসেবে গণ্য করবে
    ];

    /**
     * Get all of the transactions for the Account.
     * একটি অ্যাকাউন্টের সাথে একাধিক লেনদেন (Transaction) থাকতে পারে।
     * এটি একটি one-to-many সম্পর্ক।
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all of the transfers FROM this account.
     * এই অ্যাকাউন্ট থেকে করা সকল ব্যালেন্স ট্রান্সফার।
     */
    public function transfersFrom()
    {
        return $this->hasMany(BalanceTransfer::class, 'from_account_id');
    }

    /**
     * Get all of the transfers TO this account.
     * এই অ্যাকাউন্টে আসা সকল ব্যালেন্স ট্রান্সফার।
     */
    public function transfersTo()
    {
        return $this->hasMany(BalanceTransfer::class, 'to_account_id');
    }

    protected function calculatedBalance(): Attribute
    {
        return Attribute::make(
            get: function () {
                // একটি মাত্র কোয়েরি দিয়ে ক্রেডিট এবং ডেবিট যোগফল আনুন
                $sums = $this->transactions()
                    ->select('type', DB::raw('SUM(amount) as total'))
                    ->groupBy('type')
                    ->pluck('total', 'type');

                // কালেকশন থেকে মানগুলো নিন
                $credits = $sums->get('credit', 0);
                $debits = $sums->get('debit', 0);

                return $credits - $debits;
            },
        );
    }
}
