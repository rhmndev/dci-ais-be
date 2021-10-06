<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodReceivingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('good_receiving_details');
        Schema::create('good_receiving_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('GR_Number');
            $table->string('PO_Number');
            $table->string('material_id');
            $table->string('material_nm');
            $table->string('PR_Number');
            $table->string('receiving_qty');
            $table->string('receiving_unit');
            $table->string('order_qty');
            $table->string('order_unit');
            $table->string('residual_qty');
            $table->string('residual_unit');
            $table->string('stock');
            $table->string('description');
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
        Schema::dropIfExists('good_receiving_details');
    }
}
