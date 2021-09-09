<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Vendor extends Model
{
    //
    protected $fillable = ['code'];

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = Vendor::query();
        
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

        $query = Vendor::query();
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

    public function getList($keyword)
    {
        $query = Vendor::query();

        if ($keyword != ''){
            $query = $query->where('name', 'like', '%'.$keyword.'%');
        }

        $data = $query->take(10)->get();

        return $data;
    }

}
