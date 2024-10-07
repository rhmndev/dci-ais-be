<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('inspection');
        Schema::create('inspection', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('report_date');
            $table->string('line_number');
            $table->string('lot_number');
            $table->string('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('part_component_id')->nullable();
            $table->string('part_component_number')->nullable();
            $table->string('check');
            $table->integer('qty_ok');
            $table->string('inspection_by')->nullable();
            $table->string('qrcode_path')->nullable();
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
        Schema::dropIfExists('inspection');
    }
}
