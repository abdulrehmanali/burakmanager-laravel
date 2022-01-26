<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LedgerProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('ledger_products', function (Blueprint $table) {
        $table->id();
        $table->integer('ledger_id');
        $table->integer('product_id');
        $table->integer('batch_id');
        $table->integer('quantity');
        $table->decimal('rate');
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
        //
    }
}
