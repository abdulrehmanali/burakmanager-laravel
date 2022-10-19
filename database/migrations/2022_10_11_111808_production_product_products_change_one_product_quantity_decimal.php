<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductionProductProductsChangeOneProductQuantityDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('production_product_products', function (Blueprint $table) {
        $table->decimal('one_product_quantity', 16, 12)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('production_product_products', function (Blueprint $table) {
        $table->decimal('one_product_quantity')->change();
      });
    }
}
