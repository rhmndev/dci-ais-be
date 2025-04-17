<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sold_to_pt')->nullable();
            $table->string('name_1')->nullable();
            $table->date('doc_date')->nullable();
            $table->string('purchase_order_no')->nullable();
            $table->string('external_delivery_id')->nullable();
            $table->string('delivery')->nullable();
            $table->string('customer_material_number')->nullable();
            $table->string('description')->nullable();
            $table->decimal('delivery_quantity', 12, 2)->nullable();
            $table->decimal('net_price', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->string('pod_status')->nullable();
            $table->string('gm')->nullable();
            $table->string('bs')->nullable();
            $table->string('plant')->nullable();
            $table->string('currency')->nullable();
            $table->string('su')->nullable();
            $table->string('material')->nullable();
            $table->string('shpt')->nullable();
            $table->date('ac_gi_date')->nullable();
            $table->time('time')->nullable();
            $table->date('pod_date')->nullable();
            $table->date('po_date')->nullable();
            $table->string('ref_doc')->nullable();
            $table->string('sorg')->nullable();
            $table->string('curr')->nullable();
            $table->string('dlvt')->nullable();
            $table->string('ship_to')->nullable();
            $table->string('name_1_ship_to')->nullable();
            $table->string('dchl')->nullable();
            $table->string('item')->nullable();
            $table->string('sloc')->nullable();
            $table->string('mat_frt_gp')->nullable();
            $table->string('gi_indicator')->nullable();
            $table->decimal('quantity_dn', 12, 2)->nullable();
            $table->string('su_dn')->nullable();
            $table->string('status_dn')->nullable();
            $table->string('dn_customer')->nullable();
            $table->string('zsogdo_cgrn')->nullable();
            $table->string('nomor_kendaraan')->nullable();
            $table->string('pod')->nullable();
            $table->text('sales_text')->nullable();
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
        Schema::dropIfExists('delivery_orders');
    }
}
