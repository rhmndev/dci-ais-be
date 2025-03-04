<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusPartComponentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('status_part_component');
        Schema::create('status_part_component', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['rejected', 'repairs', 'no_type'])->default('no_type');
            $table->string('group_type');
            $table->string('name');
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
        Schema::dropIfExists('status_part_component');
    }
}
