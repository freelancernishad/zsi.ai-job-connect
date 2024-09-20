<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPaymenturlInPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remove the existing paymentUrl column
            $table->dropColumn('paymentUrl');

            // Add the paymentUrl column as TEXT after the year column
            $table->longText('paymentUrl')->after('year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Rollback: remove the text paymentUrl and add it back as string (if necessary)
            $table->dropColumn('paymentUrl');
            $table->string('paymentUrl')->nullable();
        });
    }
}
