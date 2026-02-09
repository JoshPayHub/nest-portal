<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accomplish_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accomplish_report_id')
                ->constrained('accomplish_reports')
                ->onDelete('cascade');

            // employee activity date
            $table->date('activity_date');

            // activity details
            $table->text('activity');

            // employee activity status
            $table->foreignId('status_id')
                ->constrained('statuses')
                ->onDelete('restrict')
                ->default(4); // pending

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accomplish_activities');
    }
};
