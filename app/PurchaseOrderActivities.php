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
        $query = $query->with('supplier');

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
        $query = $query->with('supplier');

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
        $data = $query->with(['purchaseOrderActivity'])->take((int)$perpage)->skip((int)$skip)->get();

        $transformedData = $data->map(function ($item) {
            return [
                'po_number' => $item->po_number,
                'supplier_name' => $item->supplier->name ?? $item->supplier_code,
                'po_date' => $item->order_date,
                'total_amount' => $item->total_amount,
                'seen' => $item->purchaseOrderActivity ? $item->purchaseOrderActivity->seen : 0,
                'last_seen_at' => $item->purchaseOrderActivity ? $item->purchaseOrderActivity->last_seen_at : null,
                'downloaded' => $item->purchaseOrderActivity ? $item->purchaseOrderActivity->downloaded : 0,
                'last_downloaded_at' => $item->purchaseOrderActivity ? $item->purchaseOrderActivity->last_downloaded_at : null,
            ];
        });

        return $transformedData;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }
}
