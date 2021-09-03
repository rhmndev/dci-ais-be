<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Receiving extends Model
{
    //
    public function getAllData($keyword, $columns, $sort, $order, $flag)
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

        $query = $query->where('flag', $flag);

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order, $flag)
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

        $query = $query->where('flag', $flag);

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;

    }
}
