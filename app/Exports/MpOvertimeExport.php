<?php

namespace App\Exports;

use App\MpOvertime;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MpOvertimeExport implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.mp_overtimes', [
            'overtimes' => $this->data
        ]);
    }
}
