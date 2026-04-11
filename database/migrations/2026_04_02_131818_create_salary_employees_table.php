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
        Schema::create('salary_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            // Changed to decimal for accurate math (10 digits total, 2 after decimal)
            $table->decimal('salary_amount', 10, 2)->default(0.00);

            // Added De Minimis (Tax-exempt benefits)
            $table->decimal('de_minimis', 10, 2)->default(0.00);

            // Optional: Helper column for the total agreed package
            $table->decimal('gross_salary', 10, 2)->virtualAs('salary_amount + de_minimis');

            $table->enum('type', ['monthly', 'daily'])->default('monthly');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_employees');
    }
};
