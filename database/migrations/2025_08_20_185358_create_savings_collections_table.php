<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('savings_collections', function (Blueprint $table) {
            // টেবিলের প্রাইমারি কী (Auto-incrementing Big Integer)
            $table->id();

            // কোন সঞ্চয় অ্যাকাউন্টে টাকা জমা হচ্ছে তার জন্য Foreign Key
            // savings_accounts টেবিলের সাথে লিঙ্ক করা।
            // যদি মূল অ্যাকাউন্ট ডিলিট হয়, তাহলে এর সাথে সম্পর্কিত সকল কালেকশনও ডিলিট হয়ে যাবে।
            $table->foreignId('savings_account_id')->constrained('savings_accounts')->onDelete('cascade');

            // কোন সদস্য টাকা জমা দিচ্ছেন তার জন্য Foreign Key
            // members টেবিলের সাথে লিঙ্ক করা।
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');

            // কোন ফিল্ড ওয়ার্কার (ব্যবহারকারী) টাকা সংগ্রহ করছেন তার জন্য Foreign Key
            // users টেবিলের সাথে লিঙ্ক করা।
            $table->foreignId('collector_id')->constrained('users')->onDelete('cascade');

            // জমাকৃত টাকার পরিমাণ। decimal টাইপ আর্থিক হিসাবের জন্য নির্ভুল।
            // মোট ১৫ ডিজিট এবং দশমিকের পর ২ ডিজিট পর্যন্ত রাখতে পারবে।
            $table->decimal('amount', 15, 2);

            // টাকা সংগ্রহের তারিখ।
            $table->date('collection_date');

            // রশিদ নম্বর (যদি থাকে)। এটি ঐচ্ছিক, তাই nullable।
            $table->string('receipt_no')->nullable();

            // লেনদেন সম্পর্কে কোনো মন্তব্য বা নোট (ঐচ্ছিক)।
            $table->text('notes')->nullable();

            // created_at এবং updated_at কলাম দুটি স্বয়ংক্রিয়ভাবে তৈরি করবে।
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_collections');
    }
};
