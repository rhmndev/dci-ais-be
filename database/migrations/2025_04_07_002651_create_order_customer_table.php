<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_customer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer', 300)->nullable();
            $table->string('plant', 50)->nullable();
            $table->string('dn_no', 150)->nullable();
            $table->string('part_no', 300)->nullable();
            $table->string('part_name', 765)->nullable();
            $table->string('job_no', 150)->nullable();
            $table->date('del_date')->nullable();
            $table->time('del_time')->nullable();
            $table->char('cycle', 15)->nullable();
            $table->integer('qty')->nullable();
            $table->integer('qty_kbn')->nullable();
            $table->dateTime('last_upd')->nullable();
            $table->string('user_id', 60)->nullable();
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
        Schema::dropIfExists('order_customer');
    }
}
