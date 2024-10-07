<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Inspection extends Model
{
    use SoftDeletes;

    protected $table = 'inspection';

    protected $fillable = [
        'code',
        'report_date',
        'line_number',
        'lot_number',
        'customer_id',
        'customer_name',
        'part_component_id',
        'part_component_number',
        'check',
        'qty_ok',
        'inspection_by',
        'qrcode_path',
        'changed_by',
        'deleted_by',
    ];

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = Inspection::query();

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

        $query = Inspection::query();
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

    public static function getCustomerDataById($id)
    {
        return Customer::firstOrFail($id);
    }

    public static function getNameCustomerById($id)
    {
        $data = Inspection::getCustomerDataById($id);

        return ($data->name != '') ? $data->name : "";
    }

    public static function getPartComponentDataById($id)
    {
        return PartComponent::find($id);
    }

    public static function GenerateQR()
    {
        return "path/to/qr";
    }
}
