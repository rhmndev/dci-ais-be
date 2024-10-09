<?php

namespace App\Exports;

use App\Material;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MaterialsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $keyword = $this->request->keyword ?? '';
        $sortColumn = $this->request->sort ?? 'code';
        $sortOrder = $this->request->order === 'descend' ? 'desc' : 'asc';

        return Material::where('code', 'like', "%$keyword%")
            ->orWhere('description', 'like', "%$keyword%")
            ->orWhere('type', 'like', "%$keyword%")
            ->orWhere('unit', 'like', "%$keyword%")
            ->orderBy($sortColumn, $sortOrder)
            ->select('code', 'description', 'type', 'unit') // Select columns for export
            ->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Description',
            'Type',
            'Unit',
        ];
    }
}
