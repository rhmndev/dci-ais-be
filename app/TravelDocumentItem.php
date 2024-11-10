<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocumentItem extends Model
{
    protected $fillable = [
        'travel_document_id',
        'po_item_id',
        'lot_production_number',
        'qty',
        'qr_tdi_no',
        'qr_path',
        'verified_by',
        'is_scanned',
        'scanned_at',
        'scanned_by',
        'notes',
    ];

    protected $dates = [
        'scanned_at',
    ];

    public function travelDocument()
    {
        return $this->belongsTo(TravelDocument::class, 'travel_document_id', '_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id', '_id');
    }

    public function packingItems()
    {
        return $this->hasMany(TravelDocumentPackingItems::class, 'travel_document_item_id', '_id');
    }

    public function tempLabelItem()
    {
        return $this->hasOne(TravelDocumentLabelTemp::class, 'qr_tdi_no', 'item_number');
    }
}
