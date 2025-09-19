<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('salary', 15, 2)->nullable()->default(0.00)->after('status');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('salary');
        });
    }
};
