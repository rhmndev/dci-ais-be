<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockSlocTakeOutTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_sloc_take_out_temps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('job_seq');  
            $table->string('material_code');
            $table->string('sloc_code');
            $table->string('rack_code');
            $table->string('uom');
            $table->integer('qty');
            $table->string('uom_take_out');
            $table->integer('qty_take_out');
            $table->string('user_id');
            $table->string('status');
            $table->string('note');
            $table->boolean('is_success');
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
        Schema::dropIfExists('stock_sloc_take_out_temps');
    }
}
