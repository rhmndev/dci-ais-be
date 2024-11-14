<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocumentLabelTemp extends Model
{
    protected $fillable = [
        'po_number',
        'po_item_id',
        'po_item_code',
        'item_number',
        'lot_production_number',
        'inspector_name',
        'inspection_date',
        'qty',
        'pack',
        'qr_path',
        'td_no',
        'is_scanned',
    ];

    protected $date = [
        'inspection_date',
        'created_at',
        'updated_at'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id', '_id');
    }
}
