<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'mobile',
        'email',
        'password',
        'role',
        'role_id',
        'first_name',
        'last_name',
        'phone_number',
        'address',
        'date_of_birth',
        'profile_picture',
        'preferred_job_title',
        'description',
        'years_of_experience_in_the_industry',
        'preferred_work_state',
        'preferred_work_zipcode',
        'your_experience',
        'familiar_with_safety_protocols',
        'step',
        'resume',
        'email_verification_hash',
        'status',
        'activation_payment_made'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
    ];

    // Relationship with Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org');
    }

    // JWT required methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationship with Roles
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relationship with Permissions through Roles
    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            'role_permission', // Pivot table name
            'user_id',         // Foreign key on the pivot table related to the User model
            'role_id',         // Foreign key on the pivot table related to the Permission model
            'id',              // Local key on the User model
            'role_id'          // Local key on the pivot table related to the Permission model
        );
    }

    // Check if user has a specific role
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    // Check if user has a specific permission
    public function hasPermission($routeName)
    {
        $permissions = $this->roles()->with('permissions')->get()->pluck('permissions')->flatten();
        return $permissions->contains(function ($permission) use ($routeName) {
            return true;
        });
    }

    // Relationship with Languages
    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    // Relationship with Certifications
    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    // Relationship with Skills
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    // Relationship with Education
    public function education()
    {
        return $this->hasMany(Education::class);
    }

    // Relationship with Employment History
    public function employmentHistory()
    {
        return $this->hasMany(EmploymentHistory::class);
    }


    public function resumes()
    {
        return $this->hasMany(Resume::class);
    }


    public function activateUser()
    {
        $this->update(['step' => 3, 'status' => 'active']);
    }

}
