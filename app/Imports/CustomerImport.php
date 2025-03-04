<?php

namespace App\Imports;

use App\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class CustomerImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * @param Collection $collection
     */

    public function model(array $row)
    {
        return new Customer([
            'code' => $row[0],
            'name' => $row[1],
            'codename' => $row[2],
            'plant' => $row[3],
            'address' => $row[4],
            'phone' => $row[5],
            'email' => $row[6],
            'contact' => $row[7],
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
