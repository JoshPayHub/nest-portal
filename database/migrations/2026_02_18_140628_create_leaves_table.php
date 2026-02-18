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
        Schema::create('leaves', function (Blueprint $table) {
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

            $table->string('type_leave');
            $table->string('with_pay')->nullable();
            $table->string('without_pay')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedInteger('total_days');
            $table->text('reason');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
