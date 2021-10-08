<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodReceivingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('good_receivings');
        Schema::create('good_receivings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('GR_Number');
            $table->string('PO_Number');
            $table->string('SJ_Number');

            $table->string('create_date');
            $table->string('delivery_date');
            $table->string('release_date');
            
            $table->string('PO_Status');
            $table->string('vendor_id');
            $table->string('vendor_nm');
            $table->string('warehouse_id');
            $table->string('warehouse_nm');
            $table->string('description');
            $table->string('headerText');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('good_receivings');
    }
}
