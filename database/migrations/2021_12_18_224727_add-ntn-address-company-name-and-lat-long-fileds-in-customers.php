<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNtnAddressCompanyNameAndLatLongFiledsInCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('customers', function (Blueprint $table) {
        $table->longText('address')->nullable();
        $table->string('ntn')->nullable();
        $table->string('company_name')->nullable();
        $table->string('lat')->nullable();
        $table->string('long')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn('address');
        $table->dropColumn('ntn');
        $table->dropColumn('company_name');
        $table->dropColumn('lat');
        $table->dropColumn('long');
      });
    }
}
