<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('subscriptions', function (Blueprint $table) {
          $table->id();
          $table->string('name');
          $table->decimal('price_pkr');
          $table->decimal('price_usd');
          $table->decimal('price_eur');
          $table->boolean('public')->default(false);
          $table->integer('user_id')->nullable();
          $table->integer('duration_days');
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
        Schema::dropIfExists('subscriptions');
    }
}
