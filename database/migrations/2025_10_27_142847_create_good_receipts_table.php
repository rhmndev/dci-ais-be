<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('receiptNumber')->unique();
            $table->string('outgoingNumber')->index();
            $table->string('poNumber')->nullable();
            $table->string('supplierName')->nullable();
            $table->string('supplierCode')->nullable()->index();
            $table->string('userName')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->text('address')->nullable();
            $table->dateTime('receiptDate')->nullable();
            $table->string('receivedBy')->nullable();
            $table->string('status')->default('Pending')->index();
            $table->string('sapStatus')->default('Not Posted')->index();
            $table->dateTime('sapLastAttempt')->nullable();
            $table->text('sapErrorMessage')->nullable();
            $table->json('items')->nullable();
            $table->dateTime('createdDate')->nullable();
            $table->dateTime('updatedDate')->nullable();
            
            // Portal Supplier sync fields
            $table->string('supplier_portal_sync_status')->default('pending')->index();
            $table->dateTime('supplier_portal_sync_at')->nullable();
            $table->text('supplier_portal_sync_error')->nullable();
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['receiptDate', 'status']);
            $table->index(['supplierCode', 'receiptDate']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_receipts');
    }
}
