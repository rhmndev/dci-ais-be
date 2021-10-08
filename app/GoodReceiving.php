<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class GoodReceiving extends Model
{
    //
    protected $fillable = [
        'SJ_Number',
        'PO_Number',
    ];

    public function getAllData($keyword, $columns, $sort, $order, $vendor)
    {

        $query = GoodReceiving::query();
        
        if(!empty($keyword)){

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%'.$keyword.'%');

                } else {

                    $query = $query->orWhere($column, 'like', '%'.$keyword.'%');

                }

            }
        }

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order, $vendor)
    {

        $query = GoodReceiving::query();
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

        if ($vendor != '') {
            $query = $query->where('vendor', $vendor);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }
}
