<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelDocumentLabelTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_document_label_temps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('po_number')->nullable();
            $table->string('po_item_id')->nullable();
            $table->string('item_number')->unique();
            $table->string('lot_production_number');
            $table->string('inspector_name');
            $table->date('inspection_date');
            $table->integer('qty');
            $table->string('qr_path');
            $table->string('td_no')->nullable();
            $table->boolean('is_scanned')->default(false);
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
        Schema::dropIfExists('travel_document_label_temps');
    }
}
