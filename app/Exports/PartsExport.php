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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $parts;
    public function __construct($parts)
    {
        $this->parts = $parts;
    }

    public function collection()
    {
        return $this->parts;
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
            'UOM',
            'Brand Name',
            'Min Stock',
            'Max Stock',
            'Rack',
            'Can Parsially Out',
            // 'Must Select Out Target',
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
        $stock = $part->PartStock ? $part->PartStock->stock : 0;
        return [
            $number++,
            $part->code,
            $part->name,
            $part->description,
            $part->uom,
            $part->min_stock,
            $part->max_stock,
            $part->rack,
            $part->brand_name,
            $part->can_partially_out ? 'Y' : 'N',
            // $part->must_select_out_target ? 'Yes' : 'No',
            $stock,
        ];
    }

    /**
     * Apply styles to the table
     *
     * @param Worksheet $sheet  // Updated parameter type to Worksheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
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
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D3D3D3'],  // Light Gray background color
                ],
            ],

            // Style for data rows
            'A2:K' . $lastRow => [
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],

            // Style for column widths
            'A' => ['width' => 2],  // Column A: No column width for numbering
            'B' => ['width' => 20], // Column B: Code column
            'C' => ['width' => 40], // Column C: Name column
            'D' => ['width' => 40], // Column D: Description column
            'E' => ['width' => 15], // Column E: Category Code column
            'F' => ['width' => 10], // Column F: UOM column
            'G' => ['width' => 12], // Column G: Min Stock column
            'H' => ['width' => 15], // Column H: Can Partially Out column
            'I' => ['width' => 20], // Column I: Must Select Out Target column
            'J' => ['width' => 10], // Column J: Stock column
            'K' => ['width' => 20], // Column K: Brand Name column
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
