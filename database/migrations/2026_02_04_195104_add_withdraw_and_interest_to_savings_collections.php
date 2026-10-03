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
        Schema::table('savings_collections', function (Blueprint $table) {
            $table->decimal('withdraw_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('interest_amount', 15, 2)->default(0)->after('withdraw_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_collections', function (Blueprint $table) {
            $table->dropColumn(['withdraw_amount', 'interest_amount']);
        });
    }
};
