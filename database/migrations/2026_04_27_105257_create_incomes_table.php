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
        Schema::create('incomes', function (Blueprint $schema) {
            $schema->id();
            $schema->foreignId('income_category_id')->constrained()->onDelete('restrict');
            $schema->foreignId('account_id')->constrained()->onDelete('restrict');
            $schema->decimal('amount', 15, 2);
            $schema->date('income_date');
            $schema->text('description')->nullable();
            $schema->foreignId('user_id')->constrained()->onDelete('restrict');
            $schema->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
