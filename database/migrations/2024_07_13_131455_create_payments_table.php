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
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->desc();
            $table->string('union')->nullable();
            $table->string('trxId')->nullable();
            $table->unsignedBigInteger('userid')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('applicant_mobile')->nullable();
            $table->string('status')->nullable();
            $table->date('date')->nullable();
            $table->string('month')->nullable();
            $table->year('year')->nullable();
            $table->string('paymentUrl')->nullable();
            $table->text('ipnResponse')->nullable();
            $table->string('method')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
