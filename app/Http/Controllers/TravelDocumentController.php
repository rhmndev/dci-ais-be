<?php

namespace App\Http\Controllers;

use App\Http\Resources\TravelDocumentResource;
use Illuminate\Http\Request;
use App\PurchaseOrder;
use App\TravelDocument;
use App\TravelDocumentItem;
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
        $TravelDocument = TravelDocument::with('items', 'supplier', 'scannedUserBy')->findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  new TravelDocumentResource($TravelDocument)
        ]);
    }
    public function showItem($id)
    {
        $TravelDocumentItem = TravelDocumentItem::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $TravelDocumentItem
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
    public function createDraftTravelDocument(Request $request, $poId)
    {
        $purchaseOrder = PurchaseOrder::firstOrCreate($poId);
    }

    public function generateItemLabels(Request $request, $poId)
    {
        $request->validate([
            'order_delivery_date' => 'required',
            'items' => 'required|array', // 'items' must be an array
            'shipping_address' => 'required|string',
            'items.*.po_item_id' => 'required|string', // Each item must have a 'po_item_id'
            'items.*.qty' => 'required',
            'items.*.lot_production_number' => 'required',
            'items.*.inspector_name' => 'required|string',
            'items.*.inspector_date' => 'required',
            'driver_name' => 'required|string',
            'vehicle_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($poId);
        $qrCodeData = [];

        foreach ($items as $item) {
            $poItem = $purchaseOrder->items->where('_id', $item['po_item_id'])->first();

            for ($i = 0; $i < $numLabels; $i++) {
                $itemNumber =  $item['po_item_id'] . '-' . $i; // Unique identifier for the label
                $labelQty = min($packQty, $remainingQty);

                // Generate QR code and store data temporarily (e.g., in session)
                $qrCodePath = $this->generateAndStoreQRCodeForItemLabel($itemNumber);
                $qrCodeData[] = [
                    'item_number' => $itemNumber,
                    'qty' => $labelQty,
                    'qr_path' => $qrCodePath,
                ];

                // ... (update $remainingQty) ...
            }
        }
    }

    public function create(Request $request, $poId)
    {
        $request->validate([
            'order_delivery_date' => 'required',
            'items' => 'required|array', // 'items' must be an array
            'shipping_address' => 'required|string',
            'items.*.po_item_id' => 'required|string', // Each item must have a 'po_item_id'
            'items.*.qty' => 'required',
            'items.*.lot_production_number' => 'required',
            'items.*.inspector_name' => 'required|string',
            'items.*.inspector_date' => 'required',
            'driver_name' => 'required|string',
            'vehicle_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $purchaseOrder = PurchaseOrder::firstOrCreate($poId);

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
                'no' => $this->generateTravelDocumentNumber(),
                'po_number' => $purchaseOrder->po_number,
                'order_delivery_date' => $request->order_delivery_date,
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
                    $travelDocumentItem = $travelDocument->items()->create([
                        'po_item_id' => $item['po_item_id'],
                        'qty' => $item['qty'],
                        'lot_production_number' => $item['lot_production_number'],
                        'inspector_name' => $item['inspector_name'],
                        'inspector_date' => $item['inspector_date'],
                        'notes' => $request->notes,
                    ]);

                    $packQty = $poItem->material->default_packing_qty ?: 100; // Default to 100 if not set
                    $numLabels = ceil($item['qty'] / $packQty);

                    $remainingQty = $item['qty'];

                    for ($i = 0; $i < $numLabels; $i++) {
                        $itemNumber = $travelDocumentItem->no . "-" . $item['po_item_id'] . '-' . $i;
                        $labelQty = min($packQty, $remainingQty);

                        $travelDocumentPackingItem = $travelDocumentItem->packingItems()->create([
                            'td_no' => $itemNumber,
                            'item_number' => $itemNumber,
                            'qty' => $labelQty,
                            'qr_path' => $this->generateAndStoreQRCodeForItemLabel($itemNumber),
                        ]);

                        $remainingQty -= $labelQty;
                    }
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

    private function generateAndStoreQRCodeForItemLabel($itemNumber)
    {
        // Generate the QR code
        $qrCode = QrCode::create($itemNumber);
        $qrCode->setSize(150);

        // Create the writer
        $writer = new PngWriter();
        $qrCodeData = $writer->write($qrCode);

        // Generate a unique filename for the QR code
        $fileName = 'qrcodes/travel_document_item_label_' . $itemNumber . '_' . uniqid() . '.png';

        // Store the QR code image in the storage folder
        Storage::disk('public')->put($fileName, $qrCodeData->getString());

        // Return the path to the stored QR code
        return $fileName;
    }

    public function getDeliveryOrders($no)
    {
        try {
            $travelDocument = TravelDocument::with('purchaseOrder', 'items.poItem.material')->where('no', $no)->first();

            if (!$travelDocument) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Travel document not found.',
                    'data' => NULL,
                ], 404);
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Travel document fetched successfully.',
                'data' => new TravelDocumentResource($travelDocument),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
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

    public function downloadItemsLabel(Request $request, $id)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($id);

        $pdf = PDF::loadView('travel_documents.item-pdf', ['travelDocument' => $travelDocument, 'is_all' => true])
            ->setPaper('a4', 'landscape');
        return $pdf->download('Label-Surat-Jalan-' . $travelDocument->no . '.pdf');
    }
    public function PrintItemsLabel(Request $request, $id)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($id);

        $pdf = PDF::loadView('travel_documents.item-pdf', ['travelDocument' => $travelDocument, 'is_all' => true])
            ->setPaper('a4', 'landscape');
        $pdfContent = $pdf->output();
        return response()->json(['pdf_data' => base64_encode($pdfContent)]);
    }

    public function downloadLabel($itemId)
    {
        $item = TravelDocumentItem::with('travelDocument.supplier', 'poItem.material')->findOrFail($itemId);

        $pdf = PDF::loadView('travel_documents.item-pdf', ['item' => $item, 'is_all' => false])->setPaper('a4', 'landscape');;
        return $pdf->download('Label-Item-' . $item->po_item_id . '.pdf');
    }

    public function printLabel($itemId)
    {
        $item = TravelDocumentItem::with('travelDocument.supplier', 'poItem.material')->findOrFail($itemId);

        $pdf = PDF::loadView('travel_documents.item-pdf', ['item' => $item, 'is_all' => false])->setPaper('a4', 'landscape');;
        $pdfContent = $pdf->output();
        return response()->json(['pdf_data' => base64_encode($pdfContent)]);
        // return $pdf->stream('Label-Item-' . $item->po_item_id . '.pdf', array("Attachment" => false));
    }

    public function downloadToPdf($travelDocumentId)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($travelDocumentId);
        // return response()->json(['message' => 'Error creating travel document', 'data' => $travelDocument], 500);

        $pdf = PDF::loadView('travel_documents.pdf', ['travelDocument' => $travelDocument])
            ->setPaper('a4', 'landscape'); // Set landscape orientation

        return $pdf->download('Surat-Jalan-.pdf');
    }

    public function printTravelDocument($travelDocumentId)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($travelDocumentId);
        // return response()->json(['message' => 'Error creating travel document', 'data' => $travelDocument], 500);

        $pdf = PDF::loadView('travel_documents.pdf', ['travelDocument' => $travelDocument])
            ->setPaper('a4', 'landscape'); // Set landscape orientation

        $pdfContent = $pdf->output();
        return response()->json(['pdf_data' => base64_encode($pdfContent)]);
    }

    public function viewToPdf($travelDocumentId)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($travelDocumentId);
        // return response()->json(['message' => 'Error creating travel document', 'data' => $travelDocument], 500);

        $pdf = PDF::loadView('travel_documents.pdf', ['travelDocument' => $travelDocument])
            ->setPaper('a4', 'landscape'); // Set landscape orientation

        return $pdf->stream('Surat-Jalan-.pdf');
    }

    public function confirmScan(Request $request, $TdId)
    {
        try {
            $travelDocument = TravelDocument::findOrFail($TdId);

            if ($travelDocument->is_scanned) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Travel Document already scanned.',
                    'data' => NULL,
                ], 400);
            }

            $travelDocumentItems = $travelDocument->items;

            foreach ($travelDocumentItems as $item) {
                $item->is_scanned = true;
                $item->scanned_at = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                $item->scanned_by = auth()->user() ? auth()->user()->npk : '';
                $item->save();
            }

            $travelDocument->status = "completed";
            $travelDocument->is_scanned = true;
            $travelDocument->scanned_at = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));

            $travelDocument->scanned_by = auth()->user() ? auth()->user()->npk : '';;
            $travelDocument->notes = $request->has('notes') ? $request->notes : '';
            $travelDocument->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Travel Document scanned successfully.',
                'data' => $travelDocument,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function confirmScanItem(Request $request, $TdiId)
    {
        try {
            $travelDocumentItem = TravelDocumentItem::findOrFail($TdiId);

            if ($travelDocumentItem->is_scanned) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Item already scanned.',
                    'data' => NULL,
                ], 400);
            }

            $travelDocumentItem->is_scanned = true;
            $travelDocumentItem->scanned_at = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
            $travelDocumentItem->scanned_by = $request->scanned_by;
            $travelDocumentItem->notes = $request->has('notes') ? $request->notes : '';
            $travelDocumentItem->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Item scanned successfully.',
                'data' => $travelDocumentItem,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }
}
