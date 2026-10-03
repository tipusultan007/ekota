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
            $table->string('nominee_name')->nullable();
            $table->string('nominee_relation')->nullable();
            $table->string('nominee_nid')->nullable();
            $table->string('nominee_phone')->nullable();
            $table->text('nominee_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['nominee_name', 'nominee_relation', 'nominee_nid', 'nominee_phone', 'nominee_address']);
        });
    }
};
