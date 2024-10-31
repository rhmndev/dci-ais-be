<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocumentItem extends Model
{
    protected $fillable = [
        'travel_document_id',
        'po_item_id',
        'qty',
        'qr_path',
    ];

    public function travelDocument()
    {
        return $this->belongsTo(TravelDocument::class, 'travel_document_id', '_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id', '_id');
    }
}
