<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldSupplierDataToInspectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inspection', function (Blueprint $table) {
            $table->string('supplier_id')->nullable()->after('report_date');
            $table->string('supplier_name')->nullable()->after('supplier_id');
            $table->string('lot_supplier')->nullable()->after('supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspection', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
            $table->dropColumn('supplier_name');
            $table->dropColumn('lot_supplier');
        });
    }
}
