<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocument extends Model
{
    protected $table = 'travel_document';

    protected $fillable = [
        'no',
        'po_number',
        'supplier_code',
        'shipping_address',
        'order_delivery_date',
        'made_by_user',
        'driver_name',
        'vehicle_number',
        'notes',
        'status',
        'qr_path',
        'created_by',
        'updated_by',
        'is_scanned',
        'scanned_by',
        'scanned_at',
    ];

    protected $dates = [
        'po_date',
        'po_date_receive',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }

    public function items()
    {
        return $this->hasMany(TravelDocumentItem::class, 'travel_document_id', '_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'code');
    }

    private function generateTravelDocumentNumber()
    {
        $latestTravelDocument = TravelDocument::latest()->first();
        $latestNumber = $latestTravelDocument ? intval(substr($latestTravelDocument->no, 3)) : 0;
        return 'TD-' . str_pad($latestNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
