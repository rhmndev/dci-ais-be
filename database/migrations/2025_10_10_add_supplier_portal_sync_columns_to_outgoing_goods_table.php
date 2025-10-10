<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierPortalSyncColumnsToOutgoingGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outgoing_goods', function (Blueprint $table) {
            $table->string('supplier_portal_sync_status')->nullable()->after('archived_reason');
            $table->timestamp('supplier_portal_sync_date')->nullable()->after('supplier_portal_sync_status');
            $table->text('supplier_portal_sync_error')->nullable()->after('supplier_portal_sync_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outgoing_goods', function (Blueprint $table) {
            $table->dropColumn(['supplier_portal_sync_status', 'supplier_portal_sync_date', 'supplier_portal_sync_error']);
        });
    }
}