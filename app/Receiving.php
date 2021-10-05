<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Receiving extends Model
{
    //
    protected $fillable = ['PO_Number'];

    public function getAllData($keyword, $columns, $sort, $order, $flag, $vendor)
    {

        $query = Receiving::query();
        
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

        $query = $query->where('flag', $flag);

        if ( $flag == 0 ){

            $query = $query->where('PO_Status', 0);
            $query = $query->orWhere('PO_Status', 1);

        } elseif ( $flag == 1 ) {

            $query = $query->where('PO_Status', 0);
            $query = $query->orWhere('PO_Status', 1);

        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order, $flag, $vendor)
    {

        $query = Receiving::query();
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

        if ( $flag == 0 ){

            $query = $query->where('flag', 0);

            $query = $query->orWhere('flag', 1);

            $query = $query->whereBetween('PO_Status', [0, 1]);

        } elseif ( $flag == 1 ) {

            $query = $query->where('flag', 1);

            $query = $query->whereBetween('PO_Status', [0, 1]);

        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;

    }
}
