<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingGoodTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_good_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code_template')->unique(); // Unique code for the outgoing good template
            $table->string('template_name'); // Name of the outgoing good template
            $table->string('outgoing_location'); // Location where the outgoing good is sent
            $table->string('take_material_from_location'); // Location from where the material is taken
            $table->string('component_code'); // Code of the component being sent out
            $table->string('component_name'); // Name of the component being sent out
            $table->string('handle_by'); // Person or entity who handled the outgoing good
            $table->string('handle_for'); // Person or entity handling the outgoing good
            $table->string('handle_for_type'); // Type of person or entity handling the outgoing good
            $table->string('handle_for_id'); // ID of the person or entity handling the outgoing good
            $table->string('assigned_to'); // Person or entity assigned to handle the outgoing good
            $table->string('priority'); // Priority of the outgoing good
            $table->string('rel_state'); // State of the outgoing good (e.g., 'pending', 'completed')
            $table->string('status'); // Status of the outgoing good (e.g., 'active', 'inactive')
            $table->string('created_by'); // User who created the outgoing good template
            $table->string('updated_by'); // User who last updated the outgoing good template
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
        Schema::dropIfExists('outgoing_good_templates');
    }
}
