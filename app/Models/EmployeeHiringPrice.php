<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeHiringPrice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'min_number_of_employees',
        'max_number_of_employees',
        'price_per_employee',
        'total_price'
    ];

    /**
     * Automatically calculate the total price when creating or updating a record.
     *
     * @return void
     */
    public function calculateTotalPrice()
    {
        $range = $this->max_number_of_employees - $this->min_number_of_employees + 1;
        return $range * $this->price_per_employee;
    }

    /**
     * Set the total price attribute automatically whenever the model is saved.
     *
     * @param  float  $value
     * @return void
     */
    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = $this->calculateTotalPrice();
    }
}
