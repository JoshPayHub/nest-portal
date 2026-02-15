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
        Schema::create('change_off_label', function (Blueprint $table) {
            $table->id();

            $table->foreignId('change_off_id')
                ->constrained('change_offs')
                ->onDelete('restrict');

            $table->foreignId('off_id')
                ->constrained('offs')
                ->onDelete('restrict');

            $table->date('original_date');
            $table->date('new_date');


            $table->foreignId('original_day_id')
                ->constrained('offs')
                ->onDelete('restrict');

            $table->foreignId('new_day_id')
                ->constrained('offs')
                ->onDelete('restrict');

            $table->time('original_time');
            $table->time('new_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_off_label');
    }
};
