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
        Schema::create('guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_account_id')->constrained('loan_accounts')->onDelete('cascade');

            // যদি গ্যারান্টার একজন বিদ্যমান সদস্য হন
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');

            // যদি গ্যারান্টার বাইরের কেউ হন
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantors');
    }
};
