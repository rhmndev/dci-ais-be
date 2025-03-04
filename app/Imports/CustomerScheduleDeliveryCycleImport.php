<?php

namespace App\Imports;

use App\CustomerScheduleDeliveryCycle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerScheduleDeliveryCycleImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return new CustomerScheduleDeliveryCycle([
            'customer_id' => $row['customer_id'],
            'customer_name' => $row['customer_name'],
            'customer_plant' => $row['customer_plant'],
            'cycle' => $row['cycle'],
        ]);
    }
}
