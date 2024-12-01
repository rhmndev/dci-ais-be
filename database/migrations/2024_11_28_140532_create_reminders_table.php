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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');  // User who created the reminder
            $table->string('username');
            $table->morphs('remindable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('reminder_datetime');
            $table->dateTime('expires_at')->nullable(); // Add expiration date
            $table->enum('reminder_frequency', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])->default('daily');
            $table->json('frequency_settings')->nullable(); // Store specific settings for each frequency
            $table->enum('reminder_method', ['email', 'whatsapp', 'both'])->default('email'); // How to remind
            $table->string('whatsapp_number')->nullable();
            $table->json('emails')->nullable();
            $table->json('files')->nullable();
            $table->boolean('is_reminded')->default(false);
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
