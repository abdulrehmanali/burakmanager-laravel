<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('ledgers', function (Blueprint $table) {
        $table->id();
        $table->integer('shop_id');
        $table->string('type');
        $table->string('payment_method');
        $table->string('payment_status');
        $table->decimal('amount_received');
        $table->decimal('total');
        $table->integer('customer_id');
        $table->text('note')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('transaction_id')->nullable();
        $table->string('cheque_number')->nullable();
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
        Schema::dropIfExists('ledgers');
    }
}
