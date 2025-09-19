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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']); // Credit = আয়/জমা, Debit = ব্যয়/খরচ
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('transaction_date');

            // পলিমরফিক রিলেশন: কোন লেনদেন কিসের সাথে সম্পর্কিত (ঐচ্ছিক কিন্তু শক্তিশালী)
            $table->nullableMorphs('transactionable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
