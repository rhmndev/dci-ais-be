<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderSignersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('purchase_order_signers');
        Schema::create('purchase_order_signers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['knowed', 'checked', 'approved']);
            $table->string('user_id');
            $table->string('npk');
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
        Schema::dropIfExists('purchase_order_signers');
    }
}
