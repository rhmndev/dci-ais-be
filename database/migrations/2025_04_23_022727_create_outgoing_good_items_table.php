<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingGoodItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_good_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('outgoing_good_number'); // Reference to the OutgoingGood model
            $table->string('material_code');     // Code of the part being sent out
            $table->string('material_name');     // Name of the part being sent out
            $table->float('quantity_needed');     // Quantity of the part needed
            $table->float('quantity_out');        // Quantity of the part sent out
            $table->string('uom_needed');        // Unit of Measure for the quantity needed
            $table->string('uom_out');          // Unit of Measure for the quantity sent out
            $table->string('created_by');       // User who created the outgoing good item record
            $table->string('updated_by');       // User who last updated the outgoing good item
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
        Schema::dropIfExists('outgoing_good_items');
    }
}
