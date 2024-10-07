<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionPartComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('inspection_part_components');
        Schema::create('inspection_part_components', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('inspection_id');
            $table->string('part_component_status_id')->nullable();
            $table->string('part_component_status_name');
            $table->integer('qty');
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
        Schema::dropIfExists('inspection_part_components');
    }
}
