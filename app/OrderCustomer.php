<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\TrackingBox;

class OrderCustomer extends Model
{
    protected $table = 'order_customer';

    protected $fillable = [
        'customer',
        'plant',
        'dn_no',
        'part_no',
        'part_name',
        'job_no',
        'del_date',
        'del_time',
        'cycle',
        'qty',
        'qty_kbn',
        'last_upd',
        'user_id'
    ];

    public function TrackingBoxes()
    {
        return $this->hasMany(TrackingBox::class, 'dn_number', 'dn_no');
    }

    public function compareDeliveryNotes()
    {
        return $this->hasMany(CompareDeliveryNote::class, 'dn_no', 'dn_no');
    }

    public function getTrackingBoxes()
    {
        return TrackingBox::where('dn_number', $this->dn_no)->get();
    }

    public function getTrackingBoxesByKanban()
    {
        $kanbanNumbers = $this->compareDeliveryNotes->pluck('kbn_no');
        return TrackingBox::whereIn('kanban', $kanbanNumbers)->get();
    }

    public function getParts()
    {
        return self::where('dn_no', $this->dn_no)->get();
    }
}
