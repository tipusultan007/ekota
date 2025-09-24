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
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_payment_account')->default(false)->after('name');
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense'])->after('is_payment_account');
            $table->string('code')->nullable()->unique()->after('id'); // অ্যাকাউন্টিং কোড (e.g., 1010 for Cash)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['is_payment_account', 'type', 'code']);
        });
    }
};
