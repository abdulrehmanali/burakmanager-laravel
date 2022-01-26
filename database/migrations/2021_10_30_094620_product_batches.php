<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductBatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('products_batches', function (Blueprint $table) {
        $table->id();
        $table->integer('product_id');
        $table->timestamp('purchased_at');
        $table->decimal('purchasing_price');
        $table->decimal('selling_price');
        $table->integer('quantity');
        $table->string('measurement_unit');
        $table->timestamp('expire_at')->nullable();
        $table->string('status'); 
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
