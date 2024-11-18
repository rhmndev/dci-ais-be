<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelDocumentLabelPackageItemTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_document_label_package_item_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('package_id')->nullable();
            $table->string('package_number');
            $table->string('item_number_id');
            $table->string('item_number');
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
        Schema::dropIfExists('travel_document_label_package_item_temp');
    }
}
