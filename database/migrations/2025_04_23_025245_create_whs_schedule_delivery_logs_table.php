<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhsScheduleDeliveryLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whs_schedule_delivery_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('whs_schedule_delivery_id');
            $table->string('action'); // Action performed (e.g., 'created', 'updated', 'deleted')
            $table->json('changes'); // JSON field to store changes made
            $table->string('performed_by')->nullable(); // User who performed the action
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
        Schema::dropIfExists('whs_schedule_delivery_logs');
    }
}
