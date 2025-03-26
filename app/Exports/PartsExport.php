<?php

namespace App\Exports;

use App\Part;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PartsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Get the collection of parts to export
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Part::all(); // Fetch all parts data
    }

    /**
     * Define the headings for the exported file
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Description',
            'Category Code',
            'Category Name',
            'UOM',
            'Min Stock',
            'Can Parsially Out',
            'Must Select Out Target',
            'Stock',
        ];
    }

    /**
     * Map the data to export
     *
     * @param mixed $part
     * @return array
     */
    public function map($part): array
    {
        return [
            $part->code,
            $part->name,
            $part->description,
            $part->category_code,
            $part->category_name,
            $part->uom,
            $part->min_stock,
            $part->is_partially_out ? 'Y' : 'N',
            $part->is_out_target ? 'Y' : 'N',
            $part->partStock->stock ?? 0,
        ];
    }
}
