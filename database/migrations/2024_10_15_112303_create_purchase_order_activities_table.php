<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('purchase_order_activities');
        Schema::create('purchase_order_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('po_id');
            $table->string('po_number');
            $table->integer('seen')->default(0);
            $table->date('last_seen_at')->nullable();
            $table->integer('downloaded')->default(0);
            $table->date('last_downloaded_at')->nullable();
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
        Schema::dropIfExists('purchase_order_activities');
    }
}
