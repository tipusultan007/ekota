<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->enum('collection_frequency', ['daily', 'weekly', 'monthly'])->default('daily')->after('scheme_type');
            $table->date('next_due_date')->nullable()->after('opening_date');
        });
    }
    public function down(): void {
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropColumn(['collection_frequency', 'next_due_date']);
        });
    }
};
