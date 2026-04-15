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
        Schema::create('salary_payroll', function (Blueprint $table) {
            $table->id();

            // employee record
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('department_id')
                ->constrained('departments')
                ->onDelete('restrict');

            $table->foreignId('position_id')
                ->constrained('positions')
                ->onDelete('restrict');

            $table->foreignId('attendance_employee_id')
                ->constrained('attendance_employees')
                ->onDelete('cascade');

            $table->foreignId('status_id')
                ->default(4)
                ->constrained('statuses')
                ->onDelete('restrict');

            // this is earning category
            $table->decimal('regular_pay', 10, 2)->default(0.00);
            $table->decimal('absence_with_pay', 10, 2)->default(0.00);
            $table->decimal('regular_ot', 10, 2)->default(0.00);
            $table->decimal('rdot', 10, 2)->default(0.00);
            $table->decimal('regular_holiday_ot', 10, 2)->default(0.00);
            $table->decimal('special_holiday_ot', 10, 2)->default(0.00);
            $table->decimal('rd_regular_holiday_ot', 10, 2)->default(0.00);
            $table->decimal('rd_special_holiday_ot', 10, 2)->default(0.00);
            $table->decimal('night_differential', 10, 2)->default(0.00);
            $table->decimal('regular_holiday', 10, 2)->default(0.00);
            $table->decimal('special_holiday', 10, 2)->default(0.00);
            $table->decimal('rd_regular_holiday', 10, 2)->default(0.00);
            $table->decimal('rd_special_holiday', 10, 2)->default(0.00);
            $table->decimal('adjustment', 10, 2)->default(0.00);
            $table->decimal('allowance', 10, 2)->default(0.00);

            // this is deduction
            $table->decimal('sss', 10, 2)->default(0.00);
            $table->decimal('pag_ibig', 10, 2)->default(0.00);
            $table->decimal('philhealth', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('salary_loan', 10, 2)->default(0.00);
            $table->decimal('cash_advance', 10, 2)->default(0.00);
            $table->decimal('undertime', 10, 2)->default(0.00);
            $table->decimal('absence_without_pay', 10, 2)->default(0.00);
            $table->decimal('flu_vaccine', 10, 2)->default(0.00);
            $table->decimal('food', 10, 2)->default(0.00);

            // total break down
            $table->decimal('total_earning', 10, 2)->default(0.00);
            $table->decimal('total_deduction', 10, 2)->default(0.00);
            $table->decimal('total_home_pay', 10, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payroll');
    }
};
