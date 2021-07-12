<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('parts');
        
        Schema::create('parts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('part_number_internal');
            $table->string('part_number_external');
            $table->string('part_name');
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
        Schema::dropIfExists('parts');
    }
}
