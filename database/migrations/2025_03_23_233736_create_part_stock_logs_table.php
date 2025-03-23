<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part_stock_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('part_code');
            $table->integer('stock_change'); // Positive for increase, negative for decrease
            $table->integer('new_stock'); // The resulting stock after the change
            $table->string('action'); // 'increase' or 'decrease'
            $table->string('created_by');
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
        Schema::dropIfExists('part_stock_logs');
    }
}
