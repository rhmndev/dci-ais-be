<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('purchase_orders');
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plant_number');
            $table->string('po_number')->unique();
            $table->string('user');
            $table->string('user_npk');
            $table->date('order_date');
            $table->string('delivery_email');
            $table->date('delivery_date');
            $table->string('delivery_address');
            $table->string('supplier_id');
            $table->string('supplier_code');
            $table->string('s_locks_code');
            $table->string('p_gr_code');
            $table->decimal('total_item_quantity');
            $table->decimal('total_amount');
            $table->string('purchase_currency_type');
            $table->string('purchase_checked_by');
            $table->date('checked_at');
            $table->boolean('is_checked')->default(false);
            $table->string('purchase_knowed_by');
            $table->date('knowed_at');
            $table->boolean('is_knowed')->default(false);
            $table->string('purchase_agreement_by');
            $table->date('approved_at');
            $table->boolean('is_approved')->default(false);
            $table->decimal('subtotal');
            $table->string('tax')->nullable();
            $table->string('tax_type')->nullable();
            $table->string('status')->default('pending');
            $table->string('po_status');
            $table->boolean('is_send_email_to_supplier')->default(false);
            $table->longText('notes')->nullable();
            $table->longText('notes_from_checker')->nullable();
            $table->longText('notes_from_knower')->nullable();
            $table->longText('notes_from_approver')->nullable();
            $table->string('qr_uuid')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
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
        Schema::dropIfExists('purchase_orders');
    }
}
