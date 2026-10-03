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
        Schema::table('members', function (Blueprint $table) {
            $table->date('joining_date')->nullable()->change();
            $table->foreignId('area_id')->nullable()->change();
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->string('nominee_name')->nullable()->change();
            $table->string('nominee_relation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->date('joining_date')->nullable(false)->change();
            $table->foreignId('area_id')->nullable(false)->change();
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->string('nominee_name')->nullable(false)->change();
            $table->string('nominee_relation')->nullable(false)->change();
        });
    }
};
