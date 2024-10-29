<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_document', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('no');
            $table->string('po_number');
            $table->date('po_date');
            $table->date('po_date_receive');
            $table->string('supplier_id')->nullable();
            $table->string('supplier_code');
            $table->string('shipping_address');
            $table->string('driver_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('status');
            $table->longText('qr_path');
            $table->string('created_by');
            $table->string('updated_by');
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
        Schema::dropIfExists('travel_document');
    }
}
