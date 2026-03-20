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
        Schema::create('announcement_policy_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_policy_id')->constrained('announcements_policies')->onDelete('restrict');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('restrict');
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_policy_filters');
    }
};
