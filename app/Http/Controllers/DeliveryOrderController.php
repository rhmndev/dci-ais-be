<?php

namespace App\Http\Controllers;

use App\DeliveryOrder;
use App\Imports\DeliveryOrderImport;
use App\Exports\DeliveryOrderExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DeliveryOrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $filterableFields = [
                'sold_to_pt',
                'name_1',
                'doc_date',
                'purchase_order_no',
                'external_delivery_id',
                'delivery',
                'customer_material_number',
                'description',
                'delivery_quantity',
                'net_price',
                'total',
                'pod_status',
                'gm',
                'bs',
                'plant',
                'currency',
                'su',
                'material',
                'shpt',
                'ac_gi_date',
                'time',
                'pod_date',
                'po_date',
                'ref_doc',
                'sorg',
                'curr',
                'dlvt',
                'ship_to',
                'name_1_ship_to',
                'dchl',
                'item',
                'sloc',
                'mat_frt_gp',
                'gi_indicator',
                'quantity_dn',
                'su_dn',
                'status_dn',
                'dn_customer',
                'zsogdo_cgrn',
                'nomor_kendaraan',
                'pod',
                'sales_text',
            ];

            $lists = DeliveryOrder::query();

            foreach ($filterableFields as $field) {
                $value = $request->get($field);

                if (!is_null($value) && $value !== '') {
                    $lists->where($field, 'like', '%' . $value . '%');
                }
            }

            $data = $lists->latest()->paginate(20);

            return response()->json([
                'data' => $data,
                'message' => 'Delivery orders retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve delivery orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sold_to_pt' => 'nullable|string',
            'name_1' => 'nullable|string',
            'doc_date' => 'nullable|date',
            'purchase_order_no' => 'nullable|string',
            'external_delivery_id' => 'nullable|string',
            'delivery' => 'nullable|string',
            'customer_material_number' => 'nullable|string',
            'description' => 'nullable|string',
            'delivery_quantity' => 'nullable|numeric',
            'net_price' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'pod_status' => 'nullable|string',
            'gm' => 'nullable|string',
            'bs' => 'nullable|string',
            'plant' => 'nullable|string',
            'currency' => 'nullable|string',
            'su' => 'nullable|string',
            'material' => 'nullable|string',
            'shpt' => 'nullable|string',
            'ac_gi_date' => 'nullable|date',
            'time' => 'nullable|string', // or 'nullable|date_format:H:i:s' if strict
            'pod_date' => 'nullable|date',
            'po_date' => 'nullable|date',
            'ref_doc' => 'nullable|string',
            'sorg' => 'nullable|string',
            'curr' => 'nullable|string',
            'dlvt' => 'nullable|string',
            'ship_to' => 'nullable|string',
            'name_1_ship_to' => 'nullable|string',
            'dchl' => 'nullable|string',
            'item' => 'nullable|string',
            'sloc' => 'nullable|string',
            'mat_frt_gp' => 'nullable|string',
            'gi_indicator' => 'nullable|string',
            'quantity_dn' => 'nullable|numeric',
            'su_dn' => 'nullable|string',
            'status_dn' => 'nullable|string',
            'dn_customer' => 'nullable|string',
            'zsogdo_cgrn' => 'nullable|string',
            'nomor_kendaraan' => 'nullable|string',
            'pod' => 'nullable|string',
            'sales_text' => 'nullable|string',
        ]);

        $deliveryOrder = DeliveryOrder::create($data);
        return response()->json(['message' => 'Created', 'data' => $deliveryOrder], 201);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {

            $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
            $data = Excel::toArray([], $request->file('file'));

            $rows = $data[0]; // Ambil sheet pertama
            $header = $rows[0]; // Baris pertama sebagai header
            $dataRows = array_slice($rows, 1); // Sisanya data

            foreach ($dataRows as $row) {
                $docDate = $this->toDate($row[2] ?? null);
                $acGiDate = $this->toDate($row[18] ?? null);
                $podDate = $this->toDate($row[20] ?? null);
                $poDate = $this->toDate($row[21] ?? null);

                DeliveryOrder::create([
                    'sold_to_pt' => $row[0] ?? null,
                    'name_1' => $row[1] ?? null,
                    'doc_date' => $docDate,
                    'purchase_order_no' => $row[3] ?? null,
                    'external_delivery_id' => $row[4] ?? null,
                    'delivery' => $row[5] ?? null,
                    'customer_material_number' => $row[6] ?? null,
                    'description' => $row[7] ?? null,
                    'delivery_quantity' => isset($row[8]) ? (float) $row[8] : null,
                    'net_price' => isset($row[9]) ? (float) $row[9] : null,
                    'total' => isset($row[10]) ? (float) $row[10] : null,
                    'pod_status' => $row[11] ?? null,
                    'gm' => $row[12] ?? null,
                    'bs' => $row[13] ?? null,
                    'plant' => $row[14] ?? null,
                    'currency' => $row[15] ?? null,
                    'su' => $row[16] ?? null,
                    'material' => $row[17] ?? null,
                    'shpt' => $row[18] ?? null,
                    'ac_gi_date' => $acGiDate,
                    'time' => $row[19] ?? null,
                    'pod_date' => $podDate,
                    'po_date' => $poDate,
                    'ref_doc' => $row[22] ?? null,
                    'sorg' => $row[23] ?? null,
                    'curr' => $row[24] ?? null,
                    'dlvt' => $row[25] ?? null,
                    'ship_to' => $row[26] ?? null,
                    'name_1_ship_to' => $row[27] ?? null,
                    'dchl' => $row[28] ?? null,
                    'item' => $row[29] ?? null,
                    'sloc' => $row[30] ?? null,
                    'mat_frt_gp' => $row[31] ?? null,
                    'gi_indicator' => $row[32] ?? null,
                    'quantity_dn' => isset($row[33]) ? (float) $row[33] : null,
                    'su_dn' => $row[34] ?? null,
                    'status_dn' => $row[35] ?? null,
                    'dn_customer' => $row[36] ?? null,
                    'zsogdo_cgrn' => $row[37] ?? null,
                    'nomor_kendaraan' => $row[38] ?? null,
                    'pod' => $row[39] ?? null,
                    'sales_text' => $row[40] ?? null,
                ]);
            }

            return response()->json(['message' => 'File imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to import file', 'error' => $e->getMessage()], 500);
        }
    }

    private function toDate($value)
    {
        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new DeliveryOrderExport, 'delivery_orders.xlsx');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to export delivery orders.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deliveryOrder = DeliveryOrder::findOrFail($id);
            $deliveryOrder->delete();

            return response()->json(['message' => 'Delivery order deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete delivery order.', 'error' => $e->getMessage()], 500);
        }
    }
}
