<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRackMaterialPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rack_material_positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code_rack');
            $table->string('code_material');
            $table->string('stock');
            $table->dateTime('in_at')->nullable();
            $table->dateTime('out_at')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('rack_material_positions');
    }
}
