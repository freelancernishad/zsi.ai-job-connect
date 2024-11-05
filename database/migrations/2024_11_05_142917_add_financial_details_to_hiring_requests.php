<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialDetailsToHiringRequests extends Migration
{
    public function up()
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->integer('total_hours')->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'total_hours', 'paid_amount', 'due_amount', 'total_amount']);
        });
    }
}
