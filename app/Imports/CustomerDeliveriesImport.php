<?php

namespace App\Imports;

use App\CustomerScheduleDeliveryList;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class CustomerDeliveriesImport implements ToModel, ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
    }

    public function model(array $row)
    {
        return new CustomerScheduleDeliveryList([
            'customer_id' => $row['customer_id'] ?? null,
            'customer_name' => $row['customer_name'] ?? null,
            'customer_plant' => $row['customer_plant'] ?? null,
        ]);
    }
}
