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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('nid_no')->unique()->nullable()->after('address');
            $table->date('joining_date')->nullable()->after('nid_no');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('joining_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'nid_no', 'joining_date', 'status']);
        });
    }
};
