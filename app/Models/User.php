<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Foreign Keys
        'user_type_id',
        'department_id',
        'position_id',
        'status_id',

        // Basic Info
        'employee_id',
        'profile_photo',
        'username',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'gender',
        'date_birth',
        'civil_status',
        'nationality',

        // Employment details
        'employment_status',
        'employment_type',
        'date_hired',
        'regularization_date',
        'immediate_supervisor',
        'work_location',
        'payroll_group',
        'leave_pay',

        // Contact Information
        'company_email',
        'company_email_verified_at',
        'personal_email',
        'personal_email_verified_at',
        'mobile_number',
        'mobile_verified_at',
        'telephone_number',
        'present_address',
        'permanent_address',
        'password',

        // Government information
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',

        // Emergency information
        'contact_person',
        'relationship',
        'contact_number',
        'address',

        // Documents
        'resume',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'company_email_verified_at' => 'datetime',
            'personal_email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'date_birth' => 'date:Y-m-d',
            'date_hired' => 'date:Y-m-d',
            'regularization_date' => 'date:Y-m-d',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function salaryEmployee()
    {
        return $this->hasOne(SalaryEmployee::class, 'user_id');
    }
    public function otps()
    {
        return $this->hasMany(UserOtp::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
