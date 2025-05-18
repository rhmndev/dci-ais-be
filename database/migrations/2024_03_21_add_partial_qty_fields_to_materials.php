<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartialQtyFieldsToMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->float('standard_qty')->nullable();
            $table->float('min_partial_qty')->nullable();
            $table->float('max_partial_qty')->nullable();
            $table->string('uom')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('standard_qty');
            $table->dropColumn('min_partial_qty');
            $table->dropColumn('max_partial_qty');
            $table->dropColumn('uom');
        });
    }
} 