<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class GoodReceivingDetail extends Model
{
    //
    protected $fillable = [
        'reference',
        'PO_Number',
        'item_po'
    ];

    public function getDetails($SJ_Number, $vendor)
    {

        $query = GoodReceivingDetail::query();

        $query = $query->where('SJ_Number', $SJ_Number);

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }
        
        $data = $query->get();

        return $data;
    }
}
