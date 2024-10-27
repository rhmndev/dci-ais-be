<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_analytics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('month_year');
            $table->integer('total_orders')->default(0);
            $table->integer('total_pending')->default(0);
            $table->integer('total_approved')->default(0);
            $table->integer('total_unapproved')->default(0);
            $table->integer('total_delivered')->default(0);
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
        Schema::dropIfExists('purchase_order_analytics');
    }
}
