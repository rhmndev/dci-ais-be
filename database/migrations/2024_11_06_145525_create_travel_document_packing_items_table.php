<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelDocumentPackingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_document_packing_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('travel_document_item_id');
            $table->string('td_no');
            $table->string('item_number');
            $table->string('qty');
            $table->string('qr_path');
            $table->boolean('is_scanned')->default(0);
            $table->date('scanned_at')->nullable();
            $table->string('scanned_by')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('travel_document_packing_items');
    }
}
