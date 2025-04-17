<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCustomerAhmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_customer_ahm', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer')->nullable();
            $table->string('plant')->nullable();
            $table->string('dn_no')->nullable();
            $table->string('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->date('del_date')->nullable();
            $table->string('supp_id')->nullable();
            $table->integer('qty')->nullable();
            $table->string('po')->nullable();
            $table->timestamp('last_upd')->nullable();
            $table->string('user_id')->nullable();
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
        Schema::dropIfExists('order_customer_ahm');
    }
}
