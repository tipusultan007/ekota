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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('account_no')->nullable();
            $table->date('date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('savings_account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('loan_account_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('deposit', 15, 2)->default(0);
            $table->decimal('loan_installment', 15, 2)->default(0);
            $table->decimal('grace_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
