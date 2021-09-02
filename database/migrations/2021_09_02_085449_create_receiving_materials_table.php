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
        Schema::create('receiving_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('PO_Number');
            $table->string('create_date');
            $table->string('send_date');
            $table->string('material_id');
            $table->string('material_name');
            $table->string('qty');
            $table->string('unit');
            $table->string('price');
            $table->string('currency');
            $table->string('vendor');
            $table->string('ppn');
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
