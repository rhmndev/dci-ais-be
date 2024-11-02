<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelDocumentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_document_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('travel_document_id');
            $table->string('po_item_id');
            $table->string('lot_production_number');
            $table->string('qty');
            $table->string('qr_tdi_no');
            $table->string('qr_path');
            $table->string('verified_by');
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
        Schema::dropIfExists('travel_document_items');
    }
}
