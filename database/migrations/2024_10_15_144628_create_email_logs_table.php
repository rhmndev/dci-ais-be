<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('recipient'); // The email recipient
            $table->string('subject'); // The subject of the email
            $table->text('message'); // The content of the email (optional)
            $table->enum('status', ['sent', 'failed'])->default('sent'); // Status of the email
            $table->string('error_message')->nullable(); // Error message in case of failure
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
        Schema::dropIfExists('email_logs');
    }
}
