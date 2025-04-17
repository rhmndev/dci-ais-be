<?php

namespace App\Imports;

use App\OrderCustomer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrderCustomerImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        return new OrderCustomer([
            'customer'    => $row['customer'],
            'plant'       => $row['plant_code'],
            'dn_no'       => $row['order_no'],
            'part_no'     => $row['part_no'],
            'part_name'   => $row['part_name'],
            'job_no'      => $row['job_no'],
            'del_date'    => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['del_date']),
            'del_time'    => $row['del_time'],
            'cycle'       => $row['del_cycle'],
            'qty'         => $row['qty_kbn'],
            'qty_kbn'     => $row['order_kbn'],
            'last_upd'    => now(),
            'user_id'     => auth()->user()->npk,
        ]);
    }
}
