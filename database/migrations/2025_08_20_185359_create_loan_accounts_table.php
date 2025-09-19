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
        Schema::create('loan_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('account_no')->unique();
            $table->decimal('loan_amount', 15, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('total_paid', 15, 2)->default(0.00);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('number_of_installments');
            $table->decimal('installment_amount', 15, 2);
            $table->date('disbursement_date');
            $table->enum('status', ['running', 'paid', 'defaulted'])->default('running');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_accounts');
    }
};
