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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Foreign Key Relations
            $table->foreignId('user_type_id')->nullable()->constrained('user_types')->onDelete('restrict');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('restrict');
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('restrict');
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('restrict');

            // Basic employee info
            $table->string('employee_id')->unique()->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other', 'Prefer not to say'])->nullable();
            $table->date('date_birth')->nullable();
            $table->enum('civil_status', ['Married', 'Single', 'Other'])->nullable();
            $table->string('nationality')->nullable();

            // Employment details
            $table->enum('employment_status', ['Regular', 'Probationary', 'Contractual', 'Casual'])->nullable();
            $table->enum('employment_type', ['Full-Time', 'Part-Time'])->nullable();
            $table->date('date_hired')->nullable();
            $table->date('regularization_date')->nullable();
            $table->string('immediate_supervisor')->nullable();
            $table->string('work_location')->nullable();
            $table->string('payroll_group')->nullable();

            // Contact Information
            $table->string('company_email')->unique();
            $table->timestamp('company_email_verified_at')->nullable();
            $table->string('personal_email')->nullable()->unique();
            $table->timestamp('personal_email_verified_at')->nullable();
            $table->string('mobile_number', 20)->unique()->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('telephone_number', 20)->unique()->nullable();
            $table->string('present_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('password');
            $table->integer('leave_pay')->default(0);

            // Government information
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();

            // Emergency information
            $table->string('contact_person')->nullable();
            $table->string('relationship')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('address')->nullable();

            // Documents
            $table->string('resume')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Session Management
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
