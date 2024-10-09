<?php

namespace App\Http\Controllers;

use App\Inspection;
use App\Qr;
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
        $request->validate([
            'report_date' => 'required',
            'line_number' => 'required',
            'lot_number' => 'required',
            'customer_id' => 'required',
            'check' => 'required',
            'qty_ok' => 'required',
        ]);

        try {
            // $Inspection = Inspection::firstOrNew(['code' => $request->code]);
            $Inspection = new Inspection;
            $Inspection->code = isset($request->code) ? $request->code : "DEVELOPMENT-AAAA";
            // $Inspection->report_date = $request->report_date;
            // $Inspection->line_number = $request->line_number;
            // $Inspection->lot_number = $request->lot_number;
            $Inspection->report_date = new \MongoDB\BSON\UTCDateTime(strtotime($request->report_date) * 1000);
            $Inspection->line_number = $request->line_number;
            $Inspection->lot_number = $request->lot_number;

            $Inspection->customer_id = $request->customer_id;
            // $Inspection->customer_id = $request->customer_id;
            // $Inspection->customer_name = Inspection::getNameCustomerById($request->customer_id);
            $Inspection->customer_name = "developing";

            $Inspection->part_component_id = $request->part_component_id;
            $Inspection->part_component_number = "X11111";
            $Inspection->check = $request->check;
            $Inspection->qty_ok = $request->qty_ok;
            // $Inspection->inspection_by = isset($request->inspection_by) ? $request->inspection_by : auth()->user()->name;
            $Inspection->inspection_by = isset($request->inspection_by) ? $request->inspection_by : auth()->user()->id;
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

            $qrCodeId = Str::uuid()->toString();

            $Inspection = Inspection::findOrFail($dataId);

            $data = "SUPPLIER_NAME|CABLE COMP A THROTTLE K1A|{$Inspection->part_component_number}|LOT_SUPPLIER|{$Inspection->qty_ok}|{$Inspection->lot_number}|{$qrCodeId}";

            // Format QR : SUPPLIER_NAME|PART_NAME|PART_NUMBER|LOT_SUPPLIER|Qty|LOT_DCI|UUID
            // $data = "SUPPLIER_NAME|PART_NAME|PART_NUMBER|LOT_SUPPLIER|Qty|LOT_DCI|{$qrCodeId}";
            $qrImage = $writer->writeString($data);
            $fileName =  time() . '.png';
            Storage::disk('public')->put('qrcode/' . $fileName, $qrImage);

            $QrCode = new Qr;
            $QrCode->uuid = $qrCodeId;
            $QrCode->path = 'qrcode/' . $fileName;
            $QrCode->type = 'inspection';
            $QrCode->created_by = auth()->user()->id;
            $QrCode->save();

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
}
