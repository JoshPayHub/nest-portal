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


            $table->foreignId('original_day_id')
                ->nullable()
                ->constrained('offs')
                ->onDelete('restrict');

            $table->foreignId('new_day_id')
                ->nullable()
                ->constrained('offs')
                ->onDelete('restrict');

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
