<?php

namespace App\Imports;

use App\StockSlock;
use Maatwebsite\Excel\Concerns\ToModel;

class StockSlockImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new StockSlock([
            'slock_code' => $row['slock_code'],
            'rack_code' => $row['rack_code'],
            'material_code' => $row['material_code'],
            'val_stock_value' => $row['val_stock_value'],
            'valuated_stock' => $row['valuated_stock'],
            'uom' => $row['uom'],
        ]);
    }
}
