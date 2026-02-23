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
        Schema::create('business_notifications', function (Blueprint $table) {
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

            $table->string('purposes');
            $table->text('reason');
            $table->string('location');

            $table->date('exact_date');
            $table->time('business_time');
            $table->time('returned_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_notifications');
    }
};
