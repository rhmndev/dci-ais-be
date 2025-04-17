<?php

namespace App\Imports;

use App\DeliveryOrder;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DeliveryOrderImport implements ToCollection, SkipsOnFailure, WithBatchInserts
{
    use SkipsFailures;

    public function collection(Collection $rows)
    {
        $header = $rows->first(); // optional
        $dataRows = $rows->skip(1);

        foreach ($dataRows as $row) {
            $docDate = $this->toDate($row[2]);
            $acGiDate = $this->toDate($row[18]);
            $podDate = $this->toDate($row[20]);
            $poDate = $this->toDate($row[21]);

            DeliveryOrder::create([
                'sold_to_pt' => $row[0],
                'name_1' => $row[1],
                'doc_date' => $docDate,
                'purchase_order_no' => $row[3],
                'external_delivery_id' => $row[4],
                'delivery' => $row[5],
                'customer_material_number' => $row[6],
                'description' => $row[7],
                'delivery_quantity' => (float) $row[8],
                'net_price' => (float) $row[9],
                'total' => (float) $row[10],
                'pod_status' => $row[11],
                'gm' => $row[12],
                'bs' => $row[13],
                'plant' => $row[14],
                'currency' => $row[15],
                'su' => $row[16],
                'material' => $row[17],
                'shpt' => $row[18],
                'ac_gi_date' => $acGiDate,
                'time' => $row[19],
                'pod_date' => $podDate,
                'po_date' => $poDate,
                'ref_doc' => $row[22],
                'sorg' => $row[23],
                'curr' => $row[24],
                'dlvt' => $row[25],
                'ship_to' => $row[26],
                'name_1_ship_to' => $row[27],
                'dchl' => $row[28],
                'item' => $row[29],
                'sloc' => $row[30],
                'mat_frt_gp' => $row[31],
                'gi_indicator' => $row[32],
                'quantity_dn' => (float) $row[33],
                'su_dn' => $row[34],
                'status_dn' => $row[35],
                'dn_customer' => $row[36],
                'zsogdo_cgrn' => $row[37],
                'nomor_kendaraan' => $row[38],
                'pod' => $row[39],
                'sales_text' => $row[40],
            ]);
        }
    }

    public function batchSize(): int
    {
        return 100; // agar lebih ringan saat insert massal
    }

    public function withoutTransaction()
    {
        return true; // mencegah Laravel Excel melakukan DB transaction
    }

    private function toDate($value)
    {
        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($value)
                ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))
                : Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
