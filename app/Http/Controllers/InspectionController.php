<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Inspection;
use App\Material;
use App\Qr;
use App\SupplierPart;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Zxing\QrReader;
use Carbon\Carbon;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';

        try {
            $Inspection = new Inspection;
            $data = array();

            $resultAlls = $Inspection->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Inspection->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'total' => count($resultAlls)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required',
            'line_number' => 'required',
            'lot_supplier' => 'required',
            'lot_number' => 'required',
            'customer_id' => 'required',
            'part_component_id' => 'required',
            'part_component_number' => 'required',
            'check' => 'required',
            'qty_ok' => 'required',
        ]);

        try {
            $Inspection = new Inspection;
            $Inspection->code = "INSPECTION-" . date('YmdHis') . "-" . $request->lot_number . "-" . Inspection::GetTotalRow();
            // $Inspection->report_date = $request->report_date;
            // $Inspection->line_number = $request->line_number;
            // $Inspection->lot_number = $request->lot_number;
            $Inspection->report_date = new \MongoDB\BSON\UTCDateTime(strtotime($request->report_date) * 1000);
            $Inspection->line_number = $request->line_number;
            $Inspection->lot_number = $request->lot_number;

            $CustomerData = Customer::findOrFail($request->customer_id);
            $cust_name = isset($CustomerData->name) ? $CustomerData->name : $request->customer_id;

            $Inspection->customer_id = $request->customer_id;
            $Inspection->customer_name = $cust_name;

            $MaterialData = Material::findOrFail($request->part_component_id);
            $material_name = isset($MaterialData->description) ? $MaterialData->description : $request->part_component_id;
            $Inspection->lot_supplier = $request->lot_supplier;

            $SupplierData = SupplierPart::where('part_id', $request->part_component_id)->where('part_number', $request->part_component_number)->first();

            $Inspection->supplier_name = isset($SupplierData->supplier) ? $SupplierData->supplier->name : $SupplierData->supplier_id;
            $Inspection->supplier_id = isset($SupplierData->supplier) ? $SupplierData->supplier->id : $SupplierData->supplier_id;
            $Inspection->part_component_id = $request->part_component_id;
            $Inspection->part_component_name = $material_name;
            $Inspection->part_component_number = $request->part_component_number;
            $Inspection->check = $request->check;
            $Inspection->qty_ok = $request->qty_ok;
            $Inspection->inspection_by = auth()->user()->npk;
            $Inspection->save();

            $inspectionId = $Inspection->id;

            try {
                $qrCodeUrl = $this->qrcode($inspectionId);
            } catch (\Exception $e) {
                // Log the error message
                // Log::error('QR Code generation failed: ' . $e->getMessage());

                // Return response indicating that the QR code generation failed
                return response()->json([
                    'type' => 'success',
                    'message' => 'Inspection data saved but QR code failed to generated',
                    'data' => 'Inspection saved!'
                ], 500);
            }
            return response()->json([
                'type' => 'success',
                'message' => 'Inspection data saved and QR has generated.',
                'data' => [
                    'inspection_id' => $inspectionId,
                    'QR_PATH' => $qrCodeUrl,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function qrcode($dataId)
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            );

            $writer = new Writer($renderer);

            $qrCodeId = str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);

            $Inspection = Inspection::findOrFail($dataId);

            $data = "{$Inspection->supplier_name}|{$Inspection->part_component_name}|{$Inspection->part_component_number}|{$Inspection->lot_supplier}|{$Inspection->qty_ok}|{$Inspection->lot_number}|{$qrCodeId}";
            // Format QR : SUPPLIER_NAME|PART_NAME|PART_NUMBER|LOT_SUPPLIER|Qty|LOT_DCI|UUID
            // $data = "SUPPLIER_NAME|PART_NAME|PART_NUMBER|LOT_SUPPLIER|Qty|LOT_DCI|{$qrCodeId}";
            $qrImage = $writer->writeString($data);
            $fileName =  time() . '.png';
            Storage::disk('public')->put('qrcode/' . $fileName, $qrImage);

            $QrCode = new Qr;
            $QrCode->uuid = $qrCodeId;
            $QrCode->path = 'qrcode/' . $fileName;
            $QrCode->type = 'inspection';
            $QrCode->created_by = auth()->user()->npk;
            $QrCode->save();

            $Inspection->qr_uuid = $qrCodeId;
            $Inspection->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // function qr decoder
    public function qrDecode(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->getPathname();

                $qrReader = new QrReader('https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg', QrReader::SOURCE_TYPE_FILE);
                $text = $qrReader->text();
                return response()->json([
                    'type' => 'success',
                    'message' => 'QR code decoded successfully.',
                    'data' => $imagePath,
                ], 200);

                if ($text) {
                    return response()->json([
                        'type' => 'success',
                        'message' => 'QR code decoded successfully.',
                        'data' => $text,
                    ], 200);
                } else {
                    return response()->json([
                        'type' => 'failed',
                        'message' => 'QR code not detected in the image.',
                        'data' => null,
                    ], 400);
                }
            } else {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'No image file provided.',
                    'data' => null,
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error decoding QR code: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getInspectionSummary()
    {
        try {
            $totalInspections = Inspection::count();

            $today = Carbon::today();
            $totalInspectionsToday = Inspection::where('report_date', '>=', $today)
                ->where('report_date', '<', $today->copy()->addDay())
                ->count();

            $lastInspection = Inspection::orderBy('updated_at', 'desc')->first();
            $lastInspectionCode = $lastInspection ? $lastInspection->code : null;
            $lastUpdateDate = $lastInspection ? $lastInspection->updated_at->toDateTimeString() : null;

            return response()->json([
                'type' => 'success',
                'data' => [
                    'total_inspections' => $totalInspections,
                    'total_inspections_today' => $totalInspectionsToday,
                    'last_inspection_code' => $lastInspectionCode,
                    'last_update_date' => $lastUpdateDate,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 500);
        }
    }

    public function getLastLOTNumber()
    {
        try {
            $lastInspection = Inspection::orderBy('lot_number', 'desc')->first();
            $lastLotNumber = $lastInspection ? $lastInspection->lot_number : null;

            return response()->json([
                'type' => 'success',
                'data' => [
                    'last_lot_number' => $lastLotNumber,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 500);
        }
    }
}
