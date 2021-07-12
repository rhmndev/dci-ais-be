<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseReceivingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('warehouse_receivings');
        Schema::create('warehouse_receivings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('part_id');
            $table->integer('rack_id');
            $table->boolean('flag')->default(1);
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
        Schema::dropIfExists('warehouse_receivings');
    }
}
