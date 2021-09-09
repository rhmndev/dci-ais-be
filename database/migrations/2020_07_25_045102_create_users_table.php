<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('full_name');
            $table->string('department');
            $table->string('phone_number');
            $table->string('npk');
            $table->string('email');
            $table->string('password');
            $table->integer('type')->default(1);
            $table->string('role_id')->nullable();
            $table->string('role_name')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('photo')->nullable();
            $table->string('reset_token')->nullable();
            $table->string('api_token')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('users');
    }
}
