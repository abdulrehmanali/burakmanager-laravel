<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseFromAndDeliveryAtInProductBatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('products_batches', function (Blueprint $table) {
        $table->string('delivery_at')->nullable();
        $table->string('purchase_from_id')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('products_batches', function (Blueprint $table) {
        //
      });
    }
}
