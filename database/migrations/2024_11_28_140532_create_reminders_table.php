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
            $table->morphs('remindable');       // Polymorphic relationship (can be any model)
            $table->string('title');            // Title or brief description of the reminder
            $table->text('description')->nullable(); // More detailed description (optional)
            $table->dateTime('reminder_datetime'); // Date and time of the reminder
            $table->enum('reminder_method', ['email', 'whatsapp', 'both'])->default('email'); // How to remind
            $table->string('whatsapp_number')->nullable(); // WhatsApp number (if applicable)
            $table->json('files')->nullable();         // Array of attached file UUIDs (optional)
            $table->boolean('is_reminded')->default(false);  // Has the reminder been sent?
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
