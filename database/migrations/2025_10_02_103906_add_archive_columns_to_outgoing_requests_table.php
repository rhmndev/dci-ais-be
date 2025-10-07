<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchiveColumnsToOutgoingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('outgoing_requests', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->string('archived_by')->nullable();
            $table->string('archived_reason')->nullable();
            $table->string('good_receipt_ref')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outgoing_requests', function (Blueprint $table) {
        $table->dropColumn(['is_archived', 'archived_at', 'archived_by', 'archived_reason', 'good_receipt_ref']);
    });
    }
}
