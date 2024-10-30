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
        Schema::table('job_applies', function (Blueprint $table) {
            $table->enum('job_by', ['INDIVIDUAL', 'COMPANY'])->nullable()->after('company_name');
            $table->string('position')->nullable()->after('job_by');
            $table->integer('total_positions')->nullable()->after('position');
            $table->string('website')->nullable()->after('total_positions');
            $table->string('company_logo')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_applies', function (Blueprint $table) {
            $table->dropColumn(['job_by', 'position', 'total_positions', 'website', 'company_logo']);
        });
    }
};
