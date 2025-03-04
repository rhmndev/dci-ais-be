<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('reminders');

        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('username');
            $table->morphs('remindable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('reminder_datetime');
            $table->boolean('starred')->default(false);
            $table->string('category')->nullable();
            $table->dateTime('expires_at');
            $table->string('reminder_frequency');
            $table->json('frequency_settings')->nullable();
            $table->string('reminder_method')->default('email');
            $table->string('whatsapp_number')->nullable();
            $table->json('repeat_options')->nullable();
            $table->json('remind_me_at_options')->nullable();
            $table->json('emails')->nullable();
            $table->json('files')->nullable();
            $table->boolean('is_reminded')->default(false);
            $table->dateTime('ends_at')->nullable();
            $table->integer('max_occurrences')->nullable();
            $table->boolean('notify_until_expired')->default(false);
            $table->integer('notify_interval')->nullable();
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
        Schema::dropIfExists('reminders');
    }
}
