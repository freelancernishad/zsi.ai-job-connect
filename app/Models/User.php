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
        'employer_status',
        'activation_payment_made',
        'activation_payment_cancel',
        'email_verified_at',
        'employer_step',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
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

    public function resume()
    {
        return $this->hasOne(Resume::class)->latest('id');
    }

    public function activateUser()
    {
        $this->update(['step' => 3, 'status' => 'active']);
    }

    public function userLookingServices()
    {
        return $this->hasMany(UserLookingService::class);
    }

    public function servicesLookingFor()
    {
        return $this->belongsToMany(Service::class, 'user_looking_services');
    }

    public function lookingServices()
    {
        return $this->hasMany(UserLookingService::class);
    }




        // Define the relationship for HiringAssignments
        public function hiringAssignments()
        {
            return $this->hasMany(HiringAssignment::class, 'assigned_employee_id');
        }

        // Define the relationship for employees in HiringAssignments
        public function assignedHiringAssignments()
        {
            return $this->hasMany(HiringAssignment::class, 'assigned_employee_id');
        }




        // Relationship with Service for preferred job title
        public function preferredJobTitleService()
        {
            return $this->belongsTo(Service::class, 'preferred_job_title', 'id');
        }

        public function getPreferredJobTitleAttribute()
        {
            $service = Service::find($this->attributes['preferred_job_title']);
            return $service ? $service->name : null;

        }





        public function scopeFilter($query, $filters)
        {
            // Filter by user model attributes
            foreach ($this->fillable as $column) {
                // Skip the preferred_job_title column
                if ($column === 'preferred_job_title') {
                    continue;
                }

                // Apply the filter if the column exists in the filters array and is not null
                if (isset($filters[$column]) && $filters[$column] !== null) {
                    // Use 'LIKE' for partial matches, or adjust based on your needs
                    $query->where($column, 'LIKE', '%' . $filters[$column] . '%');
                }
            }




            // Filter by organization name
            if (isset($filters['organization_name']) && $filters['organization_name'] !== null) {
                $query->whereHas('organization', function($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['organization_name'] . '%');
                });
            }

            // Filter by role name
            if (isset($filters['role_name']) && $filters['role_name'] !== null) {
                $query->whereHas('roles', function($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['role_name'] . '%');
                });
            }

            // Filter by language
            if (isset($filters['language_name']) && $filters['language_name'] !== null) {
                $query->whereHas('languages', function($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['language_name'] . '%');
                });
            }

            // Filter by certifications
            if (isset($filters['certification_name']) && $filters['certification_name'] !== null) {
                $query->whereHas('certifications', function($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['certification_name'] . '%');
                });
            }

            // Filter by skills
            if (isset($filters['skill_name']) && $filters['skill_name'] !== null) {
                $query->whereHas('skills', function($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['skill_name'] . '%');
                });
            }

            // Filter by education institution name
            if (isset($filters['education_institution']) && $filters['education_institution'] !== null) {
                $query->whereHas('education', function($q) use ($filters) {
                    $q->where('institution', 'LIKE', '%' . $filters['education_institution'] . '%');
                });
            }

            // Filter by employment history
            if (isset($filters['employment_company']) && $filters['employment_company'] !== null) {
                $query->whereHas('employmentHistory', function($q) use ($filters) {
                    $q->where('company_name', 'LIKE', '%' . $filters['employment_company'] . '%');
                });
            }


                // Handle preferred_job_title filter
            if (isset($filters['preferred_job_title'])) {
                // Get the service name from the request
                $serviceName = $filters['preferred_job_title'];

                // Get service ID from the service name
                $serviceId = Service::where('name', $serviceName)->pluck('id')->first();

                if ($serviceId) {
                    // Filter users by service ID
                    $query->where('preferred_job_title', $serviceId);
                } else {
                    // If no service found, return empty result
                    $query->whereRaw('1 = 0'); // No results
                }
            }



            return $query;
        }


            // Browsing history for the user
            public function browsingHistory()
            {
                return $this->hasMany(BrowsingHistory::class, 'user_id');
            }

            // Users who have been viewed by this user
            public function viewedUsers()
            {
                return $this->hasMany(BrowsingHistory::class, 'viewed_user_id');
            }


            public function receivedLikes()
            {
                return $this->hasMany(Like::class, 'liked_user_id');
            }

            public function givenLikes()
            {
                return $this->hasMany(Like::class, 'user_id');
            }

            public function isLikedByUser(int $userId): bool
            {
                return $this->receivedLikes()->where('user_id', $userId)->exists();
            }

            public function thumbnail()
            {
                return $this->hasOne(Thumbnail::class);
            }


     // Relationship with HiringSelections
     public function hiringSelections()
     {
         return $this->hasMany(HiringSelection::class, 'employee_id'); // Adjust foreign key if needed
     }

     // Relationship with HiringRequests (if applicable)
     public function hiringRequests()
     {
         return $this->hasMany(HiringRequest::class, 'employer_id');
     }

     public function pendingHiring()
     {
         return HiringRequest::where('employer_id', $this->id)
             ->where('status', 'pending')
             ->with('selectedEmployees.employee') // Load the associated employees
             ->get();
     }


     public function hiredEmployees()
     {
        return HiringRequest::where('employer_id', $this->id)
        ->where('status', 'Assigned')
        ->with('hiringAssignments.employee') // Load the associated employees
        ->get();
     }

     public function got_hired()
    {
        return HiringAssignment::where('status', 'Assigned') // Ensure the assignment status is 'Assigned'
            ->where('assigned_employee_id', $this->id) // Adjust this to your actual job_id field
            ->with('hiringRequest.employer') // Load the associated employees
            ->get();
    }


}
