<?php

namespace App\Imports;

use App\WhsScheduleDelivery;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScheduleDeliveriesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new WhsScheduleDelivery([
            'customer_id' => $row['ship_to'] ?? null,
            'customer_name' => $row['customer'] ?? null,
            'customer_plant' => $row['plant'] ?? null,
            'part_type' => $row['part_type'] ?? null,
            'schedule_date' => isset($row['ac_gi_date']) ? \Carbon\Carbon::parse($row['ac_gi_date']) : null,
            'part_id' => $row['material'] ?? null,
            'part_number' => $row['customer_material_number'] ?? null,
            'part_name' => $row['description'] ?? null,
            'cycle' => $row['cycle'] ?? null,
            'qty' => $row['qty'] ?? null,
            'planning_time' => $row['planning_time'] ?? null,
            'on_time' => $row['on_time'] ?? null,
            'delay' => $row['delay'] ?? null,
            'status_prod' => $row['status_prod'] ?? null,
            'status_qc' => $row['status_qc'] ?? null,
            'status_spa' => $row['status_spa'] ?? null,
            'status_ok' => $row['status_ok'] ?? null,
            'status_ready_to_delivery' => $row['status_ready_to_delivery'] ?? null,
            'status_delivery' => $row['status_delivery'] ?? null,
            'po_no' => $row['purchase_order_no'] ?? null,
            'ext_delivery_id' => $row['external_delivery_id'] ?? null,
            'delivery' => $row['delivery'] ?? null,
            'customer_mat_no' => $row['material'] ?? null,
            'description' => $row['description'] ?? null,
            'del_qty' => $row['delivery_quantity'] ?? null,
            'net_price' => $row['net_price'] ?? null,
            'total' => $row['total'] ?? null,
            'pod_status' => $row['pod_status'] ?? null,
            'gm' => $row['gm'] ?? null,
            'bs' => $row['bs'] ?? null,
            'currency' => $row['curr'] ?? null,
            'su' => $row['su'] ?? null,
            'material' => $row['material'] ?? null,
            'shpt' => $row['shpt'] ?? null,
            'ac_gi_date' => isset($row['ac_gi_date']) ? \Carbon\Carbon::parse($row['ac_gi_date']) : null,
            'created_by' => $row['created_by'] ?? null,
        ]);
    }
}
