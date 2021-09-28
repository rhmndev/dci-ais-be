<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('receiving_materials');
        Schema::create('receiving_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('PO_Number');
            $table->string('create_date');
            $table->string('delivery_date');
            $table->string('release_date');
            $table->string('material_id');
            $table->string('material_name');
            $table->string('item_po');
            $table->string('index_po');
            $table->string('qty');
            $table->string('unit');
            $table->string('price');
            $table->string('currency');
            $table->string('vendor');
            $table->string('ppn');
            $table->string('del_note')->nullable();
            $table->string('del_date')->nullable();
            $table->string('del_qty')->nullable();
            $table->string('prod_date')->nullable();
            $table->string('prod_lot')->nullable();
            $table->string('material')->nullable();
            $table->string('o_name')->nullable();
            $table->string('o_code')->nullable();

            $table->string('reference')->nullable();
            $table->string('receive_qty')->nullable();
            $table->string('gudang_id')->nullable();
            $table->string('gudang_nm')->nullable();
            $table->string('batch')->nullable();

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
        Schema::dropIfExists('receiving_materials');
    }
}
