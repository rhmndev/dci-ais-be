<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\PurchaseOrder;

class PurchaseOrderActivities extends Model
{
    protected $fillable = [
        'po_id',
        'po_number',
        'seen',
        'last_seen_at',
        'downloaded',
        'last_downloaded_at',
    ];

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = PurchaseOrderActivities::query();

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

        $query = PurchaseOrderActivities::query();
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

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'po_number', 'po_number');
    }
}
