<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhsScheduleDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whs_schedule_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_id');
            $table->string('customer_name');
            $table->string('customer_plant');
            $table->string('part_type');
            $table->dateTime('schedule_date');
            $table->string('part_id');
            $table->string('part_number');
            $table->string('part_name');
            $table->string('cycle');
            $table->string('qty');
            $table->dateTime('planning_time');
            $table->dateTime('on_time'); //like actual time
            $table->dateTime('delay');
            $table->dateTime('status_prod');
            $table->dateTime('status_qc');
            $table->dateTime('status_spa');
            $table->dateTime('status_ok');
            $table->dateTime('status_ready_to_delivery');
            $table->dateTime('status_delivery');
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
        Schema::dropIfExists('whs_schedule_deliveries');
    }
}
