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
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();

            //to users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            // OTP + token
            $table->string('otp_code', 10);
            $table->string('token')->unique();

            // expiration + usage
            $table->dateTime('expires_at');
            $table->boolean('is_used')->default(false);

            // optional (security)
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamps();

            // indexes (important for performance)
            $table->index('user_id');
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
