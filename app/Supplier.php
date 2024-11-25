<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'emails',
        'contact',
        'currency',
        'created_by',
        'updated_by',
        'user_id',
    ];

    public function getEmailsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setEmailsAttribute($value)
    {
        $this->attributes['emails'] = json_encode($value);
    }

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = Supplier::query();

        if (!empty($keyword)) {

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {

                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order)
    {

        $query = Supplier::query();
        $skip = $perpage * ($page - 1);

        if (!empty($keyword)) {

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {

                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }

    // Define the relationship to PurchaseOrders
    // public function purchaseOrders()
    // {
    //     return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    // }
    public function purchaseOrders()
    {
        return $this->belongsTo(PurchaseOrder::class, 'supplier_code', 'code');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
