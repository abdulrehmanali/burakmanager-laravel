<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('email');
            $table->string('token')->nullable();
            $table->timestamp('expire_at');
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('can_create_entries_in_ledger')->default(false);
            $table->boolean('can_create_customers')->default(false);
            $table->boolean('can_create_products')->default(false);
            $table->boolean('can_edit_entries_in_ledger')->default(false);
            $table->boolean('can_edit_customers')->default(false);
            $table->boolean('can_edit_products')->default(false);
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
        Schema::dropIfExists('invitations');
    }
}
