<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseOutgoingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('warehouse_outgoings');
        Schema::create('warehouse_outgoings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('part_id');
            $table->integer('rack_id');
            $table->string('created_by')->default('Admin');
            $table->string('changed_by')->default('Admin');
            $table->string('deleted_by')->nullable();
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
        Schema::dropIfExists('warehouse_outgoings');
    }
}
