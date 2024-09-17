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
        'number_of_employees',
        'price_per_employee',
        'total_price'
    ];

    /**
     * Automatically calculate the total price when creating or updating a record.
     *
     * @param  array  $attributes
     * @param  bool   $exists
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->total_price) && isset($this->number_of_employees, $this->price_per_employee)) {
            $this->attributes['total_price'] = $this->calculateTotalPrice();
        }
    }

    /**
     * Calculate the total price for the employee hiring.
     *
     * @return float
     */
    public function calculateTotalPrice()
    {
        return $this->number_of_employees * $this->price_per_employee;
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
