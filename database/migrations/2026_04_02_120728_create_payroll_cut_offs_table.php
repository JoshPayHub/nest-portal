<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_cut_offs', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['first_cutoff', 'second_cutoff'])->nullable();
            $table->date('from_cutoff_date');
            $table->date('to_cutoff_date');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_cut_offs');
    }
};
