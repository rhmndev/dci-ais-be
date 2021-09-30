<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class ReceivingMaterial extends Model
{
    //
    protected $fillable = [
        'PO_Number',
        'material_id',
        'item_po'
    ];

    public function getAllData($PONumber, $search, $columns, $sort, $order, $vendor)
    {

        $query = ReceivingMaterial::query();
        
        if(!empty($search)){

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%'.$search.'%');

                } else {

                    $query = $query->orWhere($column, 'like', '%'.$search.'%');

                }

            }
        }

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }

        $query = $query->where('PO_Number', $PONumber);

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($PONumber, $search, $columns, $perpage, $page, $sort, $order, $vendor)
    {

        $query = ReceivingMaterial::query();
        $skip = $perpage * ($page - 1);
        
        if(!empty($search)){

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%'.$search.'%');

                } else {

                    $query = $query->orWhere($column, 'like', '%'.$search.'%');

                }

            }
        }

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }

        $query = $query->where('PO_Number', $PONumber);

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;

    }

    public function scanData($PONumber, $materialId, $itemNo, $vendor)
    {
        $query = ReceivingMaterial::query();

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }

        $query = $query->where('PO_Number', $PONumber);
        $query = $query->where('material_id', $materialId);
        $query = $query->where('item_po', $itemNo);
        $data = $query->first();

        return $data; 

    }

    public function getPODetails($PONumber, $perpage, $vendor)
    {

        $query = ReceivingMaterial::query();
        $skip = $perpage * 0;

        $query = $query->where('PO_Number', $PONumber);

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }
        
        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }
}
