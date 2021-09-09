<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class ReceivingMaterial extends Model
{
    //
    protected $fillable = [
        'PO_Number',
        'material_id'
    ];

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = ReceivingMaterial::query();
        
        if(!empty($keyword)){

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%'.$keyword.'%');

                } else {

                    $query = $query->orWhere($column, 'like', '%'.$keyword.'%');

                }

            }
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order)
    {

        $query = ReceivingMaterial::query();
        $skip = $perpage * ($page - 1);
        
        if(!empty($keyword)){

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%'.$keyword.'%');

                } else {

                    $query = $query->orWhere($column, 'like', '%'.$keyword.'%');

                }

            }
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;

    }

    public function getPODetails($PONumber, $perpage)
    {

        $query = ReceivingMaterial::query();
        $skip = $perpage * 0;

        $query = $query->where('PO_Number', $PONumber);
        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }
}
