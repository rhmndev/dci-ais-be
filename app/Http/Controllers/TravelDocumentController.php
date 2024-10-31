<?php

namespace App\Http\Controllers;

use App\Http\Resources\TravelDocumentResource;
use Illuminate\Http\Request;
use App\PurchaseOrder;
use App\TravelDocument;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class TravelDocumentController extends Controller
{
    public function show(Request $request, $id)
    {
        $TravelDocument = TravelDocument::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $TravelDocument
        ]);
    }
    public function byPO(Request $request)
    {
        $request->validate([
            'po_id' => 'required|string',
        ]);

        $po_id = $request->po_id;
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($po_id);

            if (!$purchaseOrder) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Purchase order not found.',
                    'data' => NULL,
                ], 404);
            }
            $po_number = $purchaseOrder->po_number;
            $travelDocuments = TravelDocument::with('purchaseOrder')->with(['items' => function ($query) {
                $query->with('poItem');
            }])->where('po_number', $po_number)->get();

            return response()->json([
                'type' => 'success',
                'message' => 'Travel documents fetched successfully.',
                'data' => TravelDocumentResource::collection($travelDocuments),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function create(Request $request, $poId)
    {
        $request->validate([
            'items' => 'required|array', // 'items' must be an array
            'items.*.po_item_id' => 'required|string', // Each item must have a 'po_item_id'
            'items.*.qty' => 'required'
        ]);
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($poId);

            // adding check duplicate po_item_id selected inside travel document item
            $items = $request->has('items') ? $request->items : [];

            if (count($items) == 0) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Items are required',
                    'data' => NULL,
                ], 400);
            }

            $travelDocument = new TravelDocument([
                'no' => $this->generateTravelDocumentNumber(), // Implement this function
                'po_number' => $purchaseOrder->po_number,
                'po_date' => $purchaseOrder->order_date,
                'supplier_code' => $purchaseOrder->supplier_code,
                'shipping_address' => $request->shipping_address,
                'driver_name' => $request->driver_name,
                'vehicle_number' => $request->vehicle_number,
                'notes' => $request->notes,
                'status' => 'created',
            ]);

            $travelDocument->save();

            $travelDocument->qr_path = $this->generateAndStoreQRCode($travelDocument->no);
            $travelDocument->save();

            foreach ($items as $item) {
                $poItem = $purchaseOrder->items->where('_id', $item['po_item_id'])->first();
                if ($poItem) {
                    $travelDocument->items()->create([
                        'po_item_id' => $item['po_item_id'],
                        'qty' => $item['qty'],
                    ]);

                    // generate qr for each travel document item
                    $qrCode = QrCode::create($item['po_item_id']);
                    $qrCode->setSize(150);
                    $writer = new PngWriter();
                    $qrCodeData = $writer->write($qrCode);
                    $fileName = 'qrcodes/travel_document_item_' . $item['po_item_id'] . '_' . uniqid() . '.png';
                    Storage::disk('public')->put($fileName, $qrCodeData->getString());
                    $travelDocument->items()->updateOrCreate(
                        ['po_item_id' => $item['po_item_id']],
                        ['qr_path' => $fileName]
                    );
                }
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Travel document created successfully',
                'data' => $travelDocument
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error creating travel document', 'data' => $th->getMessage()], 500);
        }
    }

    private function generateAndStoreQRCode($travelDocumentNumber)
    {
        // Generate the QR code
        $qrCode = QrCode::create($travelDocumentNumber);
        $qrCode->setSize(300);

        // Create the writer
        $writer = new PngWriter();
        $qrCodeData = $writer->write($qrCode);

        // Generate a unique filename for the QR code
        $fileName = 'qrcodes/travel_document_' . $travelDocumentNumber . '_' . uniqid() . '.png';

        // Store the QR code image in the storage folder
        Storage::disk('public')->put($fileName, $qrCodeData->getString());

        // Return the path to the stored QR code
        return $fileName;
    }

    public function download($id)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($id);

        $pdf = PDF::loadView('travel_documents.pdf', ['travelDocument' => $travelDocument])
            ->setPaper('a4', 'landscape'); // Set landscape orientation

        return $pdf->stream('Surat-Jalan-' . $travelDocument->no . '.pdf');
    }

    private function generateTravelDocumentNumber()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->format('m');
        $day = $currentDate->format('d');

        $lastTravelDocument = TravelDocument::orderBy('created_at', 'desc')->first();

        if ($lastTravelDocument) {
            $lastNumber = (int)substr($lastTravelDocument->no, -3);
            $nextNumber = $lastNumber + 1;
            return 'SJ' . $year . $month . $day . sprintf('%03d', $nextNumber);
        } else {
            return 'SJ' . $year . $month . $day . '001';
        }
    }

    public function downloadToPdf($travelDocumentId)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($travelDocumentId);
        // return response()->json(['message' => 'Error creating travel document', 'data' => $travelDocument], 500);

        $pdf = PDF::loadView('travel_documents.pdf', ['travelDocument' => $travelDocument])
            ->setPaper('a4', 'landscape'); // Set landscape orientation

        return $pdf->download('Surat-Jalan-.pdf');
    }
}
