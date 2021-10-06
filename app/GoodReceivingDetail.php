<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class GoodReceivingDetail extends Model
{
    //
    protected $fillable = [
        'GR_Number',
        'index'
    ];

    public function getDetails($GR_Number, $vendor)
    {

        $query = GoodReceivingDetail::query();

        $query = $query->where('GR_Number', $GR_Number);

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }
        
        $data = $query->get();

        return $data;
    }
}
