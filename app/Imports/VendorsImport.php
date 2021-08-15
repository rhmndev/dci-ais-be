<?php

namespace App\Imports;

use App\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class VendorsImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        return new Delivery([
            'code'  => $row[0],
            'name' => $row[1],
            'address' => $row[2],
            'phone' => $row[3],
            'email' => $row[4],
            'contact' => $row[5],
        ]);
    }
    
    public function headingRow(): int
    {
        return 1;
    }
}
