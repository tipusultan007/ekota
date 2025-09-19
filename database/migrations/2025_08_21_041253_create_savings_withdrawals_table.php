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
        Schema::create('savings_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_account_id')->constrained('savings_accounts')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('processed_by_user_id')->constrained('users')->onDelete('cascade'); // কোন ব্যবহারকারী এটি প্রসেস করেছেন

            $table->decimal('withdrawal_amount', 15, 2); // উত্তোলনের পরিমাণ
            $table->decimal('profit_amount', 15, 2)->default(0.00); // যোগ করা মুনাফার পরিমাণ
            $table->decimal('total_amount', 15, 2); // মোট (উত্তোলন + মুনাফা)

            $table->date('withdrawal_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_withdrawals');
    }
};
