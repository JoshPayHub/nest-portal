<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_type_id')->after('id')->constrained('user_types')->onDelete('restrict');
            $table->foreignId('department_id')->after('user_type_id')->constrained('departments')->onDelete('restrict');
            $table->foreignId('position_id')->after('department_id')->constrained('positions')->onDelete('restrict');
            $table->foreignId('status_id')->after('position_id')->constrained('statuses')->onDelete('restrict');

            $table->string('phone', 20)->unique()->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->enum('gender', ['Male', 'Female', 'Other', 'Prefer not to say'])->nullable()->after('phone_verified_at');
            $table->string('address')->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['status_id']);
            $table->dropColumn([
                'user_type_id',
                'department_id',
                'position_id',
                'status_id',
                'phone',
                'phone_verified_at',
                'gender',
                'address',
            ]);
        });
    }
};
