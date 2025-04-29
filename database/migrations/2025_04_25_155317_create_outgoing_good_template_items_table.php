<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingGoodTemplateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_good_template_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code_template'); // Code of the outgoing good template
            $table->string('material_code'); // Code of the part being sent out
            $table->string('material_name'); // Name of the part being sent out
            $table->string('quantity_needed'); // Quantity of the part needed
            $table->string('uom_needed'); // Unit of measure for the quantity needed

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
        Schema::dropIfExists('outgoing_good_template_items');
    }
}
