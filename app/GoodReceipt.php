<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoodReceipt extends Model
{
    protected $fillable = [
        'receiptNumber',
        'outgoingNumber', 
        'poNumber',
        'supplierName',
        'supplierCode',
        'userName',
        'phone',
        'fax',
        'address',
        'receiptDate',
        'receivedBy',
        'status',
        'sapStatus',
        'sapLastAttempt',
        'sapErrorMessage',
        'items',
        'createdDate',
        'updatedDate',
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