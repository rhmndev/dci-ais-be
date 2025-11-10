<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('outgoing_no');
            $table->string('po_number')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_code')->nullable();
            $table->string('customer_name')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('driver_name')->nullable();
            $table->json('items')->nullable();
            $table->string('current_status')->default('Pending');
            $table->string('qr_code')->nullable();

            // Archive fields
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->string('archived_by')->nullable();
            $table->text('archived_reason')->nullable();
            $table->string('good_receipt_ref')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes dengan nama custom untuk menghindari konflik
            $table->index(['outgoing_no'], 'idx_outgoing_requests_outgoing_no');
            $table->index(['qr_code'], 'idx_outgoing_requests_qr_code');
            $table->index(['is_archived'], 'idx_outgoing_requests_is_archived');
            $table->index(['current_status'], 'idx_outgoing_requests_current_status');
            $table->index(['supplier_code'], 'idx_outgoing_requests_supplier_code');
            $table->index(['delivery_date'], 'idx_outgoing_requests_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_requests');
    }
}
