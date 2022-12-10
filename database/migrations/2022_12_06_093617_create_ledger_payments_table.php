<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLedgerPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_payments', function (Blueprint $table) {
            $table->id();
            $table->string('method')->default('cash');
            $table->string('status')->default('pending');
            $table->decimal('amount')->default(0.00);
            $table->string('bank_name')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('cheque_number')->nullable();
            $table->integer('shop_id');
            $table->integer('ledger_id');
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
      Schema::dropIfExists('ledger_payments');
    }
}
