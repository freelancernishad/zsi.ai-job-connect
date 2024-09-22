<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiringRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'job_title',
        'job_description',
        'expected_start_date',
        'salary_offer',
        'status',
        'employee_needed',
    ];

    // Relationship with Employer (User)
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    // Define the relationship to HiringSelections
    public function selectedEmployees()
    {
        return $this->hasMany(HiringSelection::class, 'hiring_request_id');
    }

    // Define the relationship to HiringAssignments
    public function hiringAssignments()
    {
        return $this->hasMany(HiringAssignment::class, 'hiring_request_id');
    }
}
