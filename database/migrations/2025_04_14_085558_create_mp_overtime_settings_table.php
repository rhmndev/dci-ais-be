<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpOvertimeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_overtime_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('start_time_open_overtime')->nullable();
            $table->string('end_time_open_overtime')->nullable();
            $table->boolean('enable_whatsapp')->default(false);
            $table->string('whatsapp_numbers')->nullable();
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
        Schema::dropIfExists('mp_overtime_settings');
    }
}
