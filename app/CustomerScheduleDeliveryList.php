<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class CustomerScheduleDeliveryList extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_plant',
        'customer_alias',
        'customer_image',
        'part_no',
        'part_name',
        'part_type',
        'show',
    ];

    public function getCycles()
    {
        return CustomerScheduleDeliveryCycle::where('customer_id', $this->customer_id)
            ->where('customer_plant', $this->customer_plant)
            ->where('customer_name', $this->customer_name)
            ->get();
    }

    public function getListParts($type = null)
    {
        $query = self::where('customer_plant', $this->customer_plant)
            ->select('part_no', 'part_name', 'part_type');

        if (!is_null($type)) {
            $query->where('part_type', $type);
        }

        return $query->get();
    }

    public function getPickUpTimes()
    {
        return CustomerScheduleDeliveryPickupTime::where('customer_id', $this->customer_id)
            ->where('customer_name', $this->customer_name)
            ->where('customer_plant', $this->customer_plant)
            ->get();
    }

    public function getScheduleParts()
    {
        return WhsScheduleDelivery::where('customer_id', $this->customer_id)
            // where('part_number', $this->part_no)
            ->where('customer_name', $this->customer_name)
            ->where('customer_plant', $this->customer_plant)
            // ->where('part_name', $this->part_name)
            ->get();
    }
}
