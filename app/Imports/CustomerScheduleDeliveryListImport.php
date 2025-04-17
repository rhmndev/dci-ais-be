<?php

namespace App\Imports;

use App\CustomerScheduleDeliveryList;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerScheduleDeliveryListImport  implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        return new CustomerScheduleDeliveryList([
            'customer_id' => $row['customer_id'],
            'customer_name' => $row['customer_name'],
            'customer_plant' => $row['customer_plant'],
            'dn_no' => $row['dn_no'],
            'part_no' => $row['part_no'],
            'part_name' => $row['part_name'],
            'job_no' => $row['job_no'],
            'del_date' => $row['del_date'],
            'del_time' => $row['del_time'],
            'cycle' => $row['cycle'],
            'qty' => $row['qty'],
            'qty_kbn' => $row['qty_kbn'],
        ]);
    }
}
