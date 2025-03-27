<?php

namespace App\Exports;

use App\Machine;
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

class MachineExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Get the collection of machines to export
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Machine::all(); // Fetch all machines data
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
            'Machine Code',
            'Machine Name',
            'Machine Description',
        ];
    }

    /**
     * Map the data to export
     *
     * @param mixed $machine
     * @return array
     */
    public function map($machine): array
    {
        // adding numbering
        static $number = 1;
        return [
            $number++,
            $machine->code,
            $machine->name,
            $machine->description,
            $machine->category_code,
        ];
    }

    // Other methods like __construct and collection...

    /**
     * Apply styles to the table
     *
     * @param Worksheet $sheet
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

            // Style for data rows (from row 2 onward)
            'A2:D' . $lastRow => [
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
            'A' => ['width' => 2], // Adjust width for column A
            'B' => ['width' => 20], // Adjust width for column B (Machine Code)
            'C' => ['width' => 30], // Adjust width for column C (Machine Name)
            'D' => ['width' => 40], // Adjust width for column D (Machine Description)
        ];
    }

    /**
     * Set the title of the sheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Machine Data'; // You can set the sheet name here
    }
}
