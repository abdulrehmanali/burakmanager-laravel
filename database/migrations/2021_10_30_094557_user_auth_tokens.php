<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserAuthTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('users_auth_tokens', function (Blueprint $table) {
        $table->id();
        $table->integer('user_id');
        $table->string('token')->unique();
        $table->timestamp('last_used_at')->nullable();
        $table->timestamp('expired_at')->nullable();
        $table->boolean('active')->default(false);
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
