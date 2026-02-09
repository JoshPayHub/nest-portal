<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accomplish_reports', function (Blueprint $table) {
            $table->id();

            // employee
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            // snapshot
            $table->foreignId('department_id')
                ->constrained('departments')
                ->onDelete('restrict');

            $table->foreignId('position_id')
                ->constrained('positions')
                ->onDelete('restrict');

            // period
            $table->date('from_date');
            $table->date('to_date');

            // approval statuses (leader & HR)
            $table->foreignId('leader_status_id')
                ->constrained('statuses')
                ->onDelete('restrict')
                ->default(4);

            $table->foreignId('hr_status_id')
                ->constrained('statuses')
                ->onDelete('restrict')
                ->default(4);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accomplish_reports');
    }
};
