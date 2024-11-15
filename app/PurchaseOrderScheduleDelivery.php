<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;


class PurchaseOrderScheduleDelivery extends Model
{
    protected $fillable = [
        'po_number',
        'filename',
        'description',
        'file_path',
        'show_to_supplier',
        'is_send_email_to_supplier',
        'status_schedule',
        'supplier_confirmed',
        'supplier_revision_notes',
        'supplier_revised_file_path',
        'supplier_confirmed_at',
        'created_by',
        'updated_by',
    ];

    public function getAllData($keyword, $columns, $sort, $order, $po_number)
    {
        $query = PurchaseOrderScheduleDelivery::query();

        if (!empty($keyword)) {

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {

                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        if ($po_number !== null && $po_number !== '') {
            $query->where('po_number', $po_number);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order, $po_number)
    {

        $query = PurchaseOrderScheduleDelivery::query();
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

        if ($po_number !== null && $po_number !== '') {
            $query->where('po_number', $po_number);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }

    public function po()
    {
        return $this->hasOne(PurchaseOrder::class, 'po_number', 'po_number');
    }
}
