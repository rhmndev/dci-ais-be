<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocumentLabelPackageTemp extends Model
{
    protected $fillable = [
        'po_number',
        'po_item_id',
        'package_number',
        'lot_production_number',
        'inspector_name',
        'inspection_date',
        'qty',
        'qr_path',
        'td_no',
        'is_scanned',
        'package_items'
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'is_scanned' => 'boolean',

    ];

    public function travelDocument()
    {
        return $this->belongsTo(TravelDocument::class, 'td_no', 'no');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id', '_id');
    }

    public function packageItems()
    {
        return $this->hasMany(TravelDocumentLabelPackageItemTemp::class, 'package_id', '_id');
    }
}
