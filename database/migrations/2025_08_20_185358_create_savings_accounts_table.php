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
        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('account_no')->unique();
            $table->string('scheme_type'); // e.g., 'DPS', 'FDR', 'General Savings'
            $table->decimal('interest_rate', 5, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->date('opening_date');
            $table->enum('status', ['active', 'closed', 'matured'])->default('active');

            // Nominee Information
            $table->string('nominee_name');
            $table->string('nominee_relation');
            $table->string('nominee_nid')->nullable();
            $table->string('nominee_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_accounts');
    }
};
