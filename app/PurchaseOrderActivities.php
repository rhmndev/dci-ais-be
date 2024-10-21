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

        $query = PurchaseOrder::query();

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
        $query = PurchaseOrder::query();
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
        $data = $query->with(['purchaseOrderActivity' => function ($q) {
            $q->select('po_number', 'seen', 'last_seen_at', 'downloaded', 'last_downloaded_at');
        }])->take((int)$perpage)->skip((int)$skip)->get();

        $transformedData = $data->map(function ($item) {
            return $item->purchaseOrderActivity ?? (object)[
                '_id' => $item->_id,
                'po_number' => $item->po_number,
                'seen' => 0,
                'last_seen_at' => null,
                'downloaded' => 0,
                'last_downloaded_at' => null
            ];
        });

        return $transformedData;
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'po_number', 'po_number');
    }
}
