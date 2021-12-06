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

    public function getDetails($reference, $PO_Number, $vendor)
    {

        $query = GoodReceivingDetail::query();

        $query = $query->where('reference', $reference);
        $query = $query->where('PO_Number', $PO_Number);

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }
        
        $data = $query->get();

        return $data;
    }
}
