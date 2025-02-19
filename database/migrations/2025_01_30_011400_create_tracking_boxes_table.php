<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_boxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number_box');
            $table->string('barcode');
            $table->string('dn_number');
            $table->string('destination_code');
            $table->string('destination_aliases');
            $table->enum('status', ['IN', 'OUT', 'LOST', 'DAMAGE', 'RETURN', 'RECEIVED', 'DELIVERED', 'OTHER']);
            $table->dateTime('date_time');
            $table->string('scanned_by');
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
        Schema::dropIfExists('tracking_boxes');
    }
}
