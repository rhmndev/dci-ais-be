<?php

namespace App\Imports;

use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class UsersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        return new Delivery([
            'username'  => $row[0],
            'full_name' => $row[1],
            'department' => $row[2],
            'phone_number' => $row[3],
            'npk' => $row[4],
            'email' => $row[5],
            'password' => $row[6],
        ]);
    }
    
    public function headingRow(): int
    {
        return 1;
    }
}
