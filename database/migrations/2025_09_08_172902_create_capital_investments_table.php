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
        Schema::create('capital_investments', function (Blueprint $table) {
             $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // কোন অ্যাডমিন/বিনিয়োগকারী বিনিয়োগ করেছেন
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade'); // কোন অ্যাকাউন্টে (ক্যাশ/ব্যাংক) টাকা জমা হয়েছে
            $table->decimal('amount', 15, 2);
            $table->date('investment_date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capital_investments');
    }
};
