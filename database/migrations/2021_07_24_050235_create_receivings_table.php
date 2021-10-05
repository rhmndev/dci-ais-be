<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('receivings');
        Schema::create('receivings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('PO_Number');
            $table->string('create_date');
            $table->string('delivery_date');
            $table->string('release_date');
            $table->string('vendor');
            $table->string('PO_Status');

            $table->string('reference')->nullable();
            $table->string('HeaderText')->nullable();
            
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
        Schema::dropIfExists('receivings');
    }
}
