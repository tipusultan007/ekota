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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mobile_no')->unique();
            $table->string('email')->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('nid_no')->nullable()->unique();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->date('joining_date');
            $table->enum('gender', ['male', 'female','other'])->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('occupation')->nullable();
            $table->string('work_place')->nullable();
            $table->string('spouse_name')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
