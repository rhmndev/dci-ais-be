<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PurchaseOrder;
use App\TravelDocument;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;

class TravelDocumentController extends Controller
{
    public function byPO(Request $request)
    {
        $request->validate([
            'po_number' => 'required|string',
        ]);

        $po_number = $request->po_number;
        try {
            $purchaseOrder = PurchaseOrder::where('po_number', $po_number)->first();

            if (!$purchaseOrder) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Purchase order not found.',
                    'data' => NULL,
                ], 404);
            }

            $travelDocuments = TravelDocument::where('po_number', $po_number)->get();

            return response()->json([
                'type' => 'success',
                'message' => 'Travel documents fetched successfully.',
                'data' => $travelDocuments
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
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($poId);

            // Validation (e.g., ensure PO exists and has 'approved' status)

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

            // foreach ($purchaseOrder->items as $poItem) {
            //     $travelDocument->items()->create([
            //         'po_item_id' => $poItem->_id,
            //     ]);
            // }

            return response()->json([
                'type' => 'success',
                'message' => 'Travel document created successfully',
                'data' => $travelDocument
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error creating travel document', 'data' => $th->getMessage()], 500);
        }
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
