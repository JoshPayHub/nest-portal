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
        Schema::create('sss_contributions', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_salary', 10, 2);
            $table->decimal('max_salary', 10, 2);
            $table->decimal('msc', 10, 2);
            $table->decimal('ee_share', 10, 2);
            $table->decimal('er_share', 10, 2);
            $table->decimal('wisp_ee', 10, 2)->default(0);
            $table->decimal('wisp_er', 10, 2)->default(0);
            $table->decimal('ec_er', 10, 2)->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sss_contributions');
    }
};
