<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePaymentsFieldsFromLedgers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasColumn('ledgers', 'payment_method')) {
        Schema::table('ledgers', function (Blueprint $table) {
          $table->dropColumn('payment_method');
          $table->dropColumn('payment_status');
          $table->dropColumn('amount_received');
          $table->dropColumn('bank_name');
          $table->dropColumn('transaction_id');
          $table->dropColumn('cheque_number');
        });
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('ledgers', function (Blueprint $table) {
        $table->string('payment_method');
        $table->string('payment_status');
        $table->decimal('amount_received');
        $table->string('bank_name')->nullable();
        $table->string('transaction_id')->nullable();
        $table->string('cheque_number')->nullable();
      });
    }
}
