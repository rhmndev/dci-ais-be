<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class GoodReceiptPortal extends Model
{
    protected $connection = 'mongodb_portal';
    protected $collection = 'good_receipts';

    protected $fillable = [
        'receiptNumber',
        'gr_number', // Alias untuk web
        'outgoingNumber',
        'outgoing_no', // Alias untuk web
        'poNumber',
        'po_number', // Alias untuk web
        'supplierName',
        'supplier_name', // Alias untuk web
        'supplierCode',
        'supplier_code', // Alias untuk web
        'userName',
        'phone',
        'fax',
        'address',
        'receiptDate',
        'gr_date', // Format untuk web
        'receivedBy',
        'archived_by', // Alias untuk web
        'status',
        'archived_status', // Status untuk web
        'archived_at', // Tanggal archive
        'archived_reason', // Alasan archive
        'sapStatus',
        'sapLastAttempt',
        'sapErrorMessage',
        'items',
        'createdDate',
        'updatedDate',
        'created_at',
        'updated_at',
        'notes',
        'sync_source',
        'source',
        // Portal Supplier sync fields
        'supplier_portal_sync_status',
        'supplier_portal_sync_at',
        'supplier_portal_sync_error'
    ];

    protected $casts = [
        'receiptDate' => 'datetime',
        'sapLastAttempt' => 'datetime',
        'supplier_portal_sync_at' => 'datetime',
        'createdDate' => 'datetime',
        'updatedDate' => 'datetime',
        'items' => 'array'
    ];

    protected $dates = [
        'receiptDate',
        'sapLastAttempt',
        'supplier_portal_sync_at',
        'createdDate',
        'updatedDate'
    ];
}
