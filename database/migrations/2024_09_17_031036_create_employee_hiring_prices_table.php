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
        Schema::create('employee_hiring_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('number_of_employees'); // Number of employees
            $table->decimal('price_per_employee', 8, 2); // Price per employee
            $table->decimal('total_price', 10, 2)->nullable(); // Total price (calculated)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_hiring_prices');
    }
};
