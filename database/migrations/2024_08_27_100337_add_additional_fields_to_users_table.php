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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('role_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone_number')->nullable()->after('last_name');
            $table->string('address')->nullable()->after('phone_number');
            $table->date('date_of_birth')->nullable()->after('address');
            $table->string('profile_picture')->nullable()->after('date_of_birth');
            $table->string('preferred_job_title')->nullable()->after('profile_picture');
            $table->text('description')->nullable()->after('preferred_job_title');
            $table->integer('years_of_experience_in_the_industry')->nullable()->after('description');
            $table->string('preferred_work_state')->nullable()->after('years_of_experience_in_the_industry');
            $table->string('preferred_work_zipcode')->nullable()->after('preferred_work_state');
            $table->text('your_experience')->nullable()->after('preferred_work_zipcode');
            $table->boolean('familiar_with_safety_protocols')->default(false)->after('your_experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
