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
        Schema::table('employee_hiring_prices', function (Blueprint $table) {
                        // Add the new columns after `id`
                        $table->integer('min_number_of_employees')->after('id');
                        $table->integer('max_number_of_employees')->after('min_number_of_employees');

                        // Remove the old `number_of_employees` column
                        $table->dropColumn('number_of_employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_hiring_prices', function (Blueprint $table) {
                       // Rollback: remove the newly added columns
                       $table->dropColumn('min_number_of_employees');
                       $table->dropColumn('max_number_of_employees');

                       // Re-add the old `number_of_employees` column
                       $table->integer('number_of_employees')->after('id');
        });
    }
};
