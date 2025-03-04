<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderCustomerAhm extends Model
{
    protected $connection = 'mysql';

    protected $table = 'order_customer_ahm';

    protected $fillable = [
        'customer',
        'plant',
        'dn_no',
        'part_no',
        'part_name',
        'del_date',
        'supp_id',
        'qty',
        'po',
        'last_upd',
        'user_id'
    ];
    public function compareDeliveryNotes()
    {
        return $this->hasMany(CompareDeliveryNoteAHM::class, 'dn_no', 'dn_no');
    }

    public function tracking_boxes()
    {
        return $this->hasMany(TrackingBox::class, 'dn_number', 'dn_no');
    }

    public function getTrackingBoxes()
    {
        return TrackingBox::where('dn_number', $this->dn_no)->get();
    }

    public function getParts()
    {
        return self::where('dn_no', $this->dn_no)->get();
    }
}
