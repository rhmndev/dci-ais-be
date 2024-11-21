<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\PurchaseOrderActivities;
use Carbon\Carbon;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'plant_number',
        'pr_number',
        'po_number',
        'user',
        'user_npk',
        'delivery_email',
        'delivery_address',
        'supplier_id',
        'supplier_code',
        's_locks_code',
        'p_gr_code',
        'total_item_quantity',
        'total_amount',
        'purchase_currency_type',
        'purchase_checked_by',
        'is_checked',
        'purchase_knowed_by',
        'is_knowed',
        'purchase_agreement_by',
        'is_approved',
        'tax',
        'tax_type',
        'status',
        'po_status',
        'is_send_email_to_supplier',
        'notes',
        'notes_from_checker',
        'notes_from_knower',
        'notes_from_approver',
        'order_date',
        'delivery_date',
        'checked_at',
        'knowed_at',
        'approved_at',
        'expired_at',
        'qr_uuid',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'order_date',
        'delivery_date',
        'checked_at',
        'knowed_at',
        'approved_at',
        'expired_at',
    ];

    public function getAllData($keyword, $columns, $sort, $order, $status, $startDate = null, $endDate = null)
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

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order, $status, $startDate = null, $endDate = null)
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

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }

    public function getDataByStatus($status = null)
    {
        $query = PurchaseOrder::query();

        // If a specific status is provided, filter by it
        if ($status) {
            $query->where('status', $status);
        }

        // Otherwise, get data for all three statuses
        else {
            $query->whereIn('status', ['approved', 'waiting for checked', 'waiting for knowed', 'waiting for approval',  'pending', 'unapproved']);
        }

        $data = $query->get();

        return $data;
    }

    public function getBySupplierCodeData($keyword, $columns, $sort, $order, $status, $supplier_code)
    {
        $query = PurchaseOrder::where('supplier_code', $supplier_code);

        if (!empty($keyword)) {
            foreach ($columns as $index => $column) {
                if ($index == 0) {
                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {
                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        return $query->get();
    }

    public function getBySupplierCodeDataPagination($keyword, $columns, $perpage, $page, $sort, $order, $status, $supplier_code)
    {
        $query = PurchaseOrder::query();
        $query = $query->where('supplier_code', $supplier_code);
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

        if ($status != '') {
            $query->where('status', $status);
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

    public function slock()
    {
        return $this->hasOne(SLock::class, 'code', 's_locks_code');
    }

    public function travelDocument()
    {
        return $this->hasMany(TravelDocument::class, 'po_number', 'po_number');
    }

    public function pgr()
    {
        return $this->hasOne(PGR::class, 'code', 'p_gr_code');
    }

    public function qrCode()
    {
        return $this->hasOne(Qr::class, 'uuid', 'qr_uuid');
    }

    public function scheduleDeliveries()
    {
        return $this->hasMany(PurchaseOrderScheduleDelivery::class, 'po_number', 'po_number');
    }
}
