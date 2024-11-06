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
    ];

    protected $date = [
        'scanned_at'
    ];
}
