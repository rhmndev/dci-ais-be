<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\PurchaseOrderActivities;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'user',
        'user_npk',
        'order_date',
        'delivery_email',
        'delivery_date',
        'delivery_address',
        'supplier_id',
        'supplier_code',
        'total_item_quantity',
        'total_amount',
        'purchase_checked_by',
        'checked_at',
        'is_checked',
        'purchase_knowed_by',
        'knowed_at',
        'is_knowed',
        'purchase_agreement_by',
        'approved_at',
        'is_approved',
        'tax',
        'tax_type',
        'status',
        'is_send_email_to_supplier',
        'notes',
        'created_by',
        'updated_by',
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

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }

    // Define the relationship to Supplier
    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'code', 'supplier_code');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }


    public function purchaseOrderActivity()
    {
        return $this->hasOne(PurchaseOrderActivities::class, 'po_number', 'po_number');
    }

    public function checkedUserBy()
    {
        return $this->hasOne(User::class, 'npk', 'purchase_checked_by');
    }

    public function knowedUserBy()
    {
        return $this->hasOne(User::class, 'npk', 'purchase_knowed_by');
    }

    public function approvedUserBy()
    {
        return $this->hasOne(User::class, 'npk', 'purchase_agreement_by');
    }
}
