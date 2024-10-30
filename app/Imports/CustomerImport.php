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
            'address' => $row[3],
            'phone' => $row[4],
            'email' => $row[5],
            'contact' => $row[6],
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
