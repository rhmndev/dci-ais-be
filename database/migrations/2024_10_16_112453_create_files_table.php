<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('files');
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('created_by');
            $table->string('name');
            $table->string('path');
            $table->string('size');
            $table->string('type');
            $table->string('ext');
            $table->date('expires_at')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->boolean('send_notification')->default(true);
            $table->boolean('send_notification_only_me')->default(true);
            $table->longText('send_notification_to')->nullable();
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
        Schema::dropIfExists('files');
    }
}
