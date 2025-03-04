<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocumentPackingItems extends Model
{
    protected $fillable = [
        'travel_document_item_id',
        'td_no',
        'item_number',
        'qty',
        'qr_path',
        'is_scanned',
        'scanned_at',
        'scanned_by',
        'notes',
        'reason_not_scanned',
        'original_td_no',
        'td_history'
    ];
    protected $casts = [
        'td_history' => 'array',
    ];

    protected $date = [
        'scanned_at'
    ];

    public function travelDocumentItem()
    {
        return $this->belongsTo(TravelDocumentItem::class, '_id', 'travel_document_item_id');
    }
}
