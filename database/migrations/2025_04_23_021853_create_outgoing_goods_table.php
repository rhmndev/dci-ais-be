<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_goods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number')->unique(); // Unique identifier for the outgoing good
            $table->string('date'); // Date of the outgoing good
            $table->string('priority'); // Priority of the outgoing good
            $table->string('part_code'); // Code of the part being sent out
            $table->string('part_name'); // Name of the part being sent out
            $table->string('date_out'); // Date when the part was sent out
            $table->string('outgoing_location'); // Location where the part is sent out
            $table->string('rel_state'); // State of the outgoing good (e.g., 'pending', 'completed')
            $table->string('status'); // Status of the outgoing good (e.g., 'active', 'inactive')
            $table->string('qr_code'); // QR code for the outgoing good
            $table->string('handle_for'); // Person or entity handling the outgoing good
            $table->string('handle_by'); // Person or entity who handled the outgoing good
            $table->string('created_by'); // User who created the outgoing good record
            $table->string('updated_by'); // User who last updated the outgoing good
            $table->text('notes')->nullable(); // Additional notes or comments
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
        Schema::dropIfExists('outgoing_goods');
    }
}
