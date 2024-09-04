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
        Schema::table('employment_histories', function (Blueprint $table) {
            // Drop the existing columns
            $table->dropColumn(['start_date', 'end_date']);
        });

        Schema::table('employment_histories', function (Blueprint $table) {
            // Re-add the columns in the new order
            $table->string('start_date')->nullable()->after('position');
            $table->string('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_histories', function (Blueprint $table) {
            // Drop the columns in the new order
            $table->dropColumn(['start_date', 'end_date']);
        });

        Schema::table('employment_histories', function (Blueprint $table) {
            // Re-add the columns in the original order
            $table->date('start_date')->nullable()->after('company');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }
};