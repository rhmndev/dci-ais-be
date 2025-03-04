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
            $table->string('uuid')->unique();
            $table->string('user_id');
            $table->string('user_npk');
            $table->string('original_name');
            $table->string('name');
            $table->string('disk');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('file_category')->nullable();
            $table->string('type');
            $table->string('extension');
            $table->string('created_by');
            $table->date('expires_at')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->boolean('send_notification')->default(true);
            $table->boolean('send_notification_only_me')->default(true);
            $table->json('send_notification_to')->nullable();
            $table->dateTime('reminder_datetime')->nullable();
            $table->string('reminder_method')->default('email');
            $table->boolean('notify_expiry')->default(false);
            $table->string('notification_method')->default('email');
            $table->string('whatsapp_number')->nullable();
            $table->dateTime('remind_at')->nullable();
            $table->boolean('remind_me_later')->default(false);
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
