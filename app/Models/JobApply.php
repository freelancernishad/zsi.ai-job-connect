<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApply extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'service',
        'location',
        'employment_type',
        'hourly_rate_min',
        'hourly_rate_max',
        'note',
    ];



        /**
     * Get the user that owns the job application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
