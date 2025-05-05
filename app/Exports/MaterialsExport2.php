<?php

namespace App\Exports;

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
use App\Material;
class MaterialsExport2 implements  FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $materials;

    public function __construct($materials)
    {
        $this->materials = $materials;
    }

    public function collection()
    {
        return $this->materials;
    }


    public function headings(): array
    {
        return [
            'Code',
            'Description',
            'Type',
            'Unit',
            'MinQty',
            'MaxQty',
        ];
    }

    public function map($material): array
    {
        return [
            $material->code,
            $material->description,
            $material->type,
            $material->unit,
            $material->minQty,
            $material->maxQty,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A1:G1')->getFill()->getStartColor()->setARGB('FF0000');
    }


    public function title(): string
    {
        return 'Materials';
    }
  

    
}
