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
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->integer('employee_needed')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropColumn('employee_needed');
        });
    }
};
