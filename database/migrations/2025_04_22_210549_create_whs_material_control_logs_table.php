<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhsMaterialControlLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whs_material_control_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('whs_material_control_id');
            $table->string('action'); // e.g., 'created', 'updated', 'deleted', 'stock_out'
            $table->json('changes')->nullable(); // JSON field to store changes made
            $table->string('performed_by'); // User who performed the action
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
        Schema::dropIfExists('whs_material_control_logs');
    }
}
