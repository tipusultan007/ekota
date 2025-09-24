<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_system_account')->default(false)->after('is_payment_account');
        });
    }
    public function down(): void {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_system_account');
        });
    }
};
