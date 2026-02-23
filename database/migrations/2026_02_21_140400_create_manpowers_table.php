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
        Schema::create('manpowers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('department_id')
                ->constrained('departments')
                ->onDelete('restrict');

            $table->foreignId('position_id')
                ->constrained('positions')
                ->onDelete('restrict');

            $table->string('report_to');
            $table->date('date_required');

            $table->enum('position_type', [
                'NEW POSITION',
                'REPLACEMENT'
            ]);

            $table->string('replacement_for')->nullable()->default('NONE');

            $table->text('job_description');
            $table->text('justification');

            $table->enum('status_type', [
                'REGULAR (Apointment)',
                'PROBATIONARY',
                'PROJECT BASED',
                'OJT/TRAINEE',
                'CONTRACTUAL (5 MONTHS ONLY)',
                'SEASONAL (2 MONTHS ONLY)',
                'ON-CALL'
            ]);

            $table->enum('payment_type', [
                'MONTHLY PAID EMPLOYEE',
                'DAILY PAID EMPLOYEE',
                'ALLOWANCE'
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manpowers');
    }
};
