<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_employees', function (Blueprint $table) {
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

            $table->foreignId('payroll_cut_off_id')
                ->constrained('payroll_cut_offs')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_employees');
    }
};
