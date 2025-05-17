<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    //
    protected $fillable = ['id_vendor','code','purch_org','nm_vendor','name','allias','street','district','postal_code','city','country','region','phone_1','vat_reg','order_curr','pay_term','sales_person','phone_2','vend_email','email','status_vendor'];
    
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

    public function checkVendor($code)
    {
        $query = Vendor::query();

        $query = $query->where('code', $code)->get();

        return $query;
    }

}
