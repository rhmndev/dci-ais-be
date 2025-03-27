<?php

namespace App\Exports;

use App\Part;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PartsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
            'No',
            'Code',
            'Name',
            'Description',
            'Category Code',
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
        // adding numbering
        static $number = 1;
        return [
            $number++,
            $part->code,
            $part->name,
            $part->description,
            $part->category_code,
            $part->uom,
            $part->min_stock,
            $part->can_partially_out ? 'Yes' : 'No',
            $part->must_select_out_target ? 'Yes' : 'No',
            $part->stock,
        ];
    }

    /**
     * Apply styles to the table
     *
     * @param Sheet $sheet
     * @return array
     */
    public function styles(Sheet $sheet)
    {
        return [
            // Style for the first row (headings)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 10, // Larger font size for headings
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THICK,
                    ],
                ],
            ],

            // Style for data rows
            'A2:J' => [
                'font' => [
                    'size' => 9, // Smaller font size for data rows
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'], // Black border color
                    ],
                ],
            ],

            // Style for column widths
            'A' => ['width' => 5], // No column width for numbering
            'B' => ['width' => 20], // Code column
            'C' => ['width' => 30], // Name column
            'D' => ['width' => 40], // Description column
            'E' => ['width' => 15], // Category Code column
            'F' => ['width' => 10], // UOM column
            'G' => ['width' => 12], // Min Stock column
            'H' => ['width' => 15], // Can Partially Out column
            'I' => ['width' => 20], // Must Select Out Target column
            'J' => ['width' => 10], // Stock column
        ];
    }
    /**
     * Set the title of the sheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Parts Data'; // You can set the sheet name here
    }
}
