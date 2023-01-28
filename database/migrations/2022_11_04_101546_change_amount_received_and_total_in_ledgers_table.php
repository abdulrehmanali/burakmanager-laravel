<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAmountReceivedAndTotalInLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ledgers', function (Blueprint $table) {
          if (Schema::hasColumn('ledgers', 'amount_received')){
            $table->decimal('amount_received', 12, 2)->change();
          }
          $table->decimal('total', 12, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('ledgers', function (Blueprint $table) {
        //   $table->decimal('amount_received')->change();
        //   $table->decimal('total')->change();
        // });
    }
}
