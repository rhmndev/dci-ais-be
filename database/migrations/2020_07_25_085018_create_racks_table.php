<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('racks');

        Schema::create('racks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number');
            $table->string('name');
            $table->string('warehouse')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('value_lamp')->nullable();
            $table->string('value_button')->nullable();
            $table->string('sequence')->nullable();
            $table->boolean('flag')->default(0);
            $table->string('created_by')->nullable();
            $table->string('changed_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('racks');
    }
}
