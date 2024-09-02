<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // First, drop the existing column
            $table->dropColumn('years_of_experience_in_the_industry');
        });

        Schema::table('users', function (Blueprint $table) {
            // Then, add the column back with the new data type
            $table->string('years_of_experience_in_the_industry', 255)->nullable(); // You can adjust the length and nullability as needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the string column
            $table->dropColumn('years_of_experience_in_the_industry');
        });

        Schema::table('users', function (Blueprint $table) {
            // Add back the integer column (as it was originally)
            $table->integer('years_of_experience_in_the_industry')->nullable(); // Adjust nullability if needed
        });
    }
};
