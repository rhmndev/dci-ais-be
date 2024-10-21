<?php

namespace App\Exports;

use App\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class SupplierExport implements FromCollection
{
    protected $suppliers;

    public function __construct($suppliers)
    {
        $this->suppliers = $suppliers;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->suppliers->map(function ($supplier) {
            return [
                'Code' => $supplier->code,
                'Name' => $supplier->name,
                'Address' => $supplier->address,
                'Phone' => $supplier->phone,
                'Email' => $supplier->email,
                'Contact' => $supplier->contact,
                'Currency' => $supplier->currency,
                // Add other fields as needed
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Address',
            'Phone',
            'Email',
            'Contact',
            'Currency',
            // Add other headings as needed
        ];
    }
}
