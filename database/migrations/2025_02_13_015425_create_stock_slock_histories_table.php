<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockSlockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_slock_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slock_code');
            $table->string('rack_code');
            $table->string('material_code');
            $table->decimal('val_stock_value', 15, 2);
            $table->decimal('valuated_stock', 15, 3);
            $table->string('uom');
            $table->timestamp('date_time');
            $table->string('scanned_by');
            $table->dateTime('scanned_at');
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
        Schema::dropIfExists('stock_slock_histories');
    }
}
