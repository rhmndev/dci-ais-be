<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('inspections');
        Schema::create('inspections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('row')->nullable();
            $table->string('column')->nullable();
            $table->string('index')->nullable();
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
        Schema::dropIfExists('inspections');
    }
}
