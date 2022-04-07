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
        Schema::create('repayment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repayment_id')->references('id')->on('repayments')->onDelete('restrict');
            $table->unsignedBigInteger('transaction_id')->references('id')->on('transactions')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayment_transactions');
    }
};
