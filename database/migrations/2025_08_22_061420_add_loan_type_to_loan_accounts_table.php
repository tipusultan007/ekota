<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->enum('installment_frequency', ['daily', 'weekly', 'monthly'])->default('monthly')->after('installment_amount');
            $table->date('next_due_date')->nullable()->after('disbursement_date');
        });
    }
    public function down(): void {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropColumn(['installment_frequency', 'next_due_date']);
        });
    }
};
