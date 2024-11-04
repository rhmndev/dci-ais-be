<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderScheduleDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_schedule_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('po_number');
            $table->string('filename');
            $table->string('description')->nullable();
            $table->longText('file_path');
            $table->boolean('show_to_supplier')->default(false);
            $table->boolean('is_send_email_to_supplier')->default(false);
            $table->string('created_by');
            $table->string('updated_by');
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
        Schema::dropIfExists('purchase_order_schedule_deliveries');
    }
}
