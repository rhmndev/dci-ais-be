<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoTrackingEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_tracking_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('po_id');
            $table->string('event'); // e.g., 'created', 'approved', 'scheduled', 'shipped', 'delivered', 'rejected', etc.
            $table->timestamp('occurred_at')->nullable(); // Timestamp of when the event happened
            $table->text('notes')->nullable(); // Optional notes for the event
            $table->timestamps();

            $table->foreign('po_id')->references('id')->on('purchase_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('po_tracking_events');
    }
}
