<?php

namespace App\Http\Controllers;

use App\Inspection;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Zxing\QrReader;

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
        // $request->validate([
        //     'report_date' => 'required',
        //     'line_number' => 'required',
        //     'lot_number' => 'required',
        //     'check' => 'required',
        //     'qty_ok' => 'required',
        // ]);

        try {
            // $Inspection = Inspection::firstOrNew(['code' => $request->code]);
            $Inspection = new Inspection;
            $Inspection->code = isset($request->code) ? $request->code : "DEVELOPMENT-AAAA";
            // $Inspection->report_date = $request->report_date;
            // $Inspection->line_number = $request->line_number;
            // $Inspection->lot_number = $request->lot_number;
            $Inspection->report_date = "10/10/2024";
            $Inspection->line_number = "J119";
            $Inspection->lot_number = "20241010001";

            $Inspection->customer_id = auth()->user()->id;
            // $Inspection->customer_id = $request->customer_id;
            // $Inspection->customer_name = Inspection::getNameCustomerById($request->customer_id);
            $Inspection->customer_name = "Tarjo";

            $Inspection->part_component_id = "A12313";
            $Inspection->part_component_number = "X11111";
            $Inspection->check = $request->check;
            $Inspection->qty_ok = $request->qty_ok;
            // $Inspection->inspection_by = isset($request->inspection_by) ? $request->inspection_by : auth()->user()->name;
            $Inspection->inspection_by = "developer";
            // $Inspection->qrcode_path = Inspection::GenerateQR();
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
                    'data' => [
                        'inspection_id' => $inspectionId,
                        'QR_PATH' => 'QR Code generation error: ' . $e->getMessage(),
                    ],
                ], 500);
            }
            return response()->json([
                'type' => 'success',
                'message' => '',
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

    public function qrcode($id)
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeId = Str::uuid()->toString();
        // Format QR : SUPPLIER_NAME|PART_NAME|PART_NUMBER|LOT_SUPPLIER|Qty|LOT_DCI|UUID
        $data = "SUPPLIER_NAME|PART_NAME|PART_NUMBER|LOT_SUPPLIER|Qty|LOT_DCI|{$qrCodeId}";
        $qrImage = $writer->writeString($data);
        $fileName = 'qrcode_' . time() . '.png';
        Storage::disk('public')->put('qrcode/' . $fileName, $qrImage);
        // return response()->json(['file_path' => 'storage/qrcode/' . $fileName]);
        return response()->json([
            'file_path' => 'storage/qrcode/' . $fileName, // Return the path to the stored QR code
            'base64' => 'data:image/png;base64,' . base64_encode($qrImage) // Return base64-encoded image
        ]);
    }
}
