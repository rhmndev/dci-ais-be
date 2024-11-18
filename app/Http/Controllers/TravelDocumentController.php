<?php

namespace App\Http\Controllers;

use App\Http\Resources\TravelDocumentResource;
use Illuminate\Http\Request;
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\TravelDocumentLabelTemp;
use App\TravelDocument;
use App\TravelDocumentItem;
use App\TravelDocumentLabelPackageTemp;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TravelDocumentController extends Controller
{
    public function show(Request $request, $id)
    {
        $TravelDocument = TravelDocument::with('items', 'items.tempLabelItem', 'supplier', 'scannedUserBy')->findOrFail($id);

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
            $travelDocuments = TravelDocument::with('purchaseOrder', 'items.poItem', 'items.tempLabelItem')->where('po_number', $po_number)->get();

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

    public function generateItemLabels(Request $request, $poId, $poItemId)
    {
        $request->validate([
            'lot_production_number' => 'required',
            'qty' => 'required',
            'qty_per_package' => 'required',
            'inspector_name' => 'required',
            'inspection_date' => 'required|date',
            'is_have_pack' => 'nullable|boolean',
            'pack' => 'nullable'
        ]);

        try {
            $poItem = PurchaseOrderItem::with('purchaseOrder', 'material')->findOrFail($poItemId);
            $yearMonth = Carbon::now()->format('ym');

            if ($poItem) {
                $poItemData = PurchaseOrderItem::findOrFail($poItemId);
                $printedLabelTemp = TravelDocumentLabelTemp::where('po_item_id', $poItemId)->sum('qty');
                $totalQty = $poItemData->quantity;
                $reqTotalQtyDelivery = $request->qty;
                $qtyPerPackage = $request->qty_per_package;
                $packQty = $request->pack ?? 1;

                $remainingQty = ($totalQty - $printedLabelTemp);
                if ($reqTotalQtyDelivery > $remainingQty || $remainingQty < 0) {
                    return response()->json([
                        'type' => 'failed',
                        'message' => 'Cannot generate more labels. The quantity exceeds the remaining PO item quantity.',
                        'data' => NULL
                    ], 400);
                }

                $totalPackages = ceil($reqTotalQtyDelivery / $qtyPerPackage);
                if ($packQty > $totalPackages) {
                    return response()->json([
                        'type' => 'failed',
                        'message' => 'Requested packs exceeds the number of possible packages based on total quantity and quantity per package.',
                        'data' => NULL
                    ], 400);
                }

                $totalItemLabels = ceil($reqTotalQtyDelivery / $qtyPerPackage);
                // $baseQtyPerItem = floor($reqTotalQtyDelivery / $totalItemLabels);
                // $extraItemsForItem = $reqTotalQtyDelivery % $totalItemLabels;
                $itemLabels = [];
                $remainingQtyForItems = $reqTotalQtyDelivery;

                $baseQtyPerBox = floor($reqTotalQtyDelivery / $packQty);
                $extraItems = $reqTotalQtyDelivery % $packQty;
                $data = [];
                $j = 0;
                // for ($j = 0; $j < $totalItemLabels; $j++) {
                while ($remainingQtyForItems > 0) {
                    $itemNumber = $this->generateUniqueItemNumber($yearMonth);
                    $qtyForThisItem = min($qtyPerPackage, $remainingQtyForItems);

                    $itemLabels[] = new TravelDocumentLabelTemp([
                        'po_number' => $poItem->purchaseOrder->po_number,
                        'po_item_id' => $poItem->_id,
                        'item_number' => $itemNumber,
                        'po_item_code' => $poItem->material->code ?? null,
                        'lot_production_number' => $request->lot_production_number,
                        'inspector_name' => $request->inspector_name,
                        'inspection_date' => $request->inspection_date,
                        'qty' => $qtyForThisItem,
                        'qr_path' => $this->generateAndStoreQRCodeForItemLabel($itemNumber),
                    ]);
                    // $data[] = $itemLabels[$j];

                    $remainingQtyForItems -= $qtyForThisItem;
                    $j++;
                }

                $packLabels = [];
                for ($i = 0; $i < $packQty; $i++) {
                    $packageNumber = $this->generateUniquePackageNumber($yearMonth);

                    // $lastLabel = TravelDocumentLabelTemp::where('created_at', '>=', Carbon::now()->startOfMonth())
                    //     ->where('created_at', '<=', Carbon::now()->endOfMonth())
                    //     ->orderBy('created_at', 'desc')
                    //     ->first();

                    // $lastLabelNumber = $lastLabel ? (int)substr($lastLabel->item_number, -6) : 0;
                    // $nextLabelNumber = $lastLabelNumber + 1;
                    // if ($nextLabelNumber > 1000000) {
                    //     return response()->json([
                    //         'type' => 'failed',
                    //         'message' => 'Cannot generate more labels. Label number limit reached.',
                    //         'data' => null
                    //     ], 400);
                    // }
                    // $itemNumber = $yearMonth . str_pad($nextLabelNumber, 6, '0', STR_PAD_LEFT);

                    $qtyForThisLabel = $baseQtyPerBox;
                    if ($extraItems > 0) {
                        $qtyForThisLabel++;
                        $extraItems--;
                    }
                    // if ($extraItems > 0 && $qtyForThisLabel + $extraItems <=  $baseQtyPerBox) {
                    //     $qtyForThisLabel += $extraItems;
                    //     $extraItems = 0;
                    // }
                    // if ($remainingQty < $baseQtyPerBox && $i == $packQty - 1 && $extraItems <= 0) {
                    //     $qtyForThisLabel = $remainingQty;
                    // }

                    // if ($qtyForThisLabel == 0) {
                    //     break;
                    // }
                    if ($qtyForThisLabel <= 0 || $remainingQty <= 0) {
                        break; // Stop if no more items or remainingQty = 0
                    }

                    // $travelDocumentLabelTemp = new TravelDocumentLabelTemp([
                    //     'po_number' => $poItem->purchaseOrder->po_number,
                    //     'po_item_id' => $poItem->_id,
                    //     'po_item_code' => $poItem->material->code ?? null,
                    //     'item_number' => $itemNumber,
                    //     'lot_production_number' => $request->lot_production_number,
                    //     'inspector_name' => $request->inspector_name,
                    //     'inspection_date' => $request->inspection_date,
                    //     'qty' => $qtyForThisLabel,
                    //     'pack' => $i + 1,
                    //     // 'qr_path' => $this->generateAndStoreQRCodeForItemLabel($itemNumber),
                    // ]);
                    $packLabels[] = new TravelDocumentLabelPackageTemp([
                        'po_number' => $poItem->purchaseOrder->po_number,
                        'po_item_id' => $poItem->_id,
                        'po_item_code' => $poItem->material->code ?? null,
                        'package_number' => $packageNumber,
                        'lot_production_number' => $request->lot_production_number,
                        'inspector_name' => $request->inspector_name,
                        'inspection_date' => $request->inspection_date,
                        'qty' => $qtyForThisLabel,
                        'qr_path' => $this->generateAndStoreQRCodeForPackageLabel($itemNumber),
                    ]);
                    // $travelDocumentLabelPackageTemp->save();
                    $remainingQty -= $qtyForThisLabel;
                }


                foreach ($itemLabels as $label) {
                    $label->save();
                }
                foreach ($packLabels as $packLabel) {
                    $packLabel->save();
                }

                return $this->tempPrintLabel($poItemId);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $th->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    private function generateUniqueItemNumber($yearMonth)
    {
        $lastLabel = TravelDocumentLabelTemp::where('created_at', '>=', Carbon::now()->startOfMonth())
            ->where('created_at', '<=', Carbon::now()->endOfMonth())
            ->orderBy('created_at', 'desc')
            ->first();

        $lastLabelNumber = $lastLabel ? (int)substr($lastLabel->item_number, -6) : 0;
        $nextLabelNumber = $lastLabelNumber + 1;
        if ($nextLabelNumber > 1000000) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Cannot generate more labels. Label number limit reached.',
                'data' => null
            ], 400);
        }
        $itemNumber = $yearMonth . str_pad($nextLabelNumber, 6, '0', STR_PAD_LEFT);

        return $itemNumber;
    }
    private function generateUniquePackageNumber($yearMonth)
    {
        $lastLabel = TravelDocumentLabelPackageTemp::where('created_at', '>=', Carbon::now()->startOfMonth())
            ->where('created_at', '<=', Carbon::now()->endOfMonth())
            ->orderBy('created_at', 'desc')
            ->first();

        $lastLabelNumber = $lastLabel ? (int)substr($lastLabel->item_number, -6) : 0;
        $nextLabelNumber = $lastLabelNumber + 1;
        if ($nextLabelNumber > 1000000) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Cannot generate more labels. Label package number limit reached.',
                'data' => null
            ], 400);
        }
        $packageNumber = "PKG" . $yearMonth . str_pad($nextLabelNumber, 6, '0', STR_PAD_LEFT);

        return $packageNumber;
    }

    public function getBySupplierLoggedUser(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'status' => 'nullable|string',
            'order' => 'string',
        ]);

        try {
            $user = auth()->user();
            if ($user->role_name == 'supplier' || $user->role_name == 'Supplier') {
                $supplier_code = $user->vendor_code;

                $keyword = ($request->keyword != null) ? $request->keyword : '';
                $order = ($request->order != null) ? $request->order : 'ascend';
                $status = ($request->status != null) ? $request->status : '';

                $TravelDocument = TravelDocument::where('supplier_code', $supplier_code)
                    ->when($keyword, function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('no', 'like', '%' . $keyword . '%')
                                ->orWhere('po_number', 'like', '%' . $keyword . '%');
                        });
                    })
                    ->when($status, function ($query) use ($status) {
                        $query->where('status', $status);
                    });
                $resultAlls = $TravelDocument->get($request->columns);
                $results = $TravelDocument->orderBy($request->sort, $order == 'descend' ? 'desc' : 'asc')
                    ->paginate($request->perpage);

                return response()->json([
                    'type' => 'success',
                    'data' => TravelDocumentResource::collection($results),
                    'dataAll' => $resultAlls,
                    'total' => count($resultAlls),
                ], 200);
            } else {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'User is not a supplier.',
                    'data' => $user,
                ], 403);
            }
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
            'order_delivery_date' => 'required',
            'items' => 'required|array',
            'shipping_address' => 'required|string',
            'made_by_user' => 'required|string',
            'driver_name' => 'nullable|string',
            'vehicle_number' => 'nullable|string',
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($poId);

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
                'made_by_user' => $request->made_by_user,
                'driver_name' => $request->driver_name ?? '',
                'vehicle_number' => $request->vehicle_number ?? '',
                'notes' => $request->notes,
                'status' => 'created',
            ]);

            $travelDocument->save();

            $travelDocument->qr_path = $this->generateAndStoreQRCode($travelDocument->no);
            $travelDocument->save();

            foreach ($items as $item) {
                $DataLabelsItem = TravelDocumentLabelTemp::where("po_item_id", $item)->get();

                foreach ($DataLabelsItem as $labelItem) {
                    try {
                        $travelDocumentItem = $travelDocument->items()->create([
                            'po_item_id' => $labelItem->po_item_id,
                            'qty' => $labelItem->qty,
                            'qr_tdi_no' => $labelItem->item_number,
                            'lot_production_number' => $labelItem->lot_production_number,
                            'inspector_name' => $labelItem->inspector_name,
                            'inspector_date' => $labelItem->inspection_date,
                            'qr_path' => $labelItem->qr_path
                        ]);

                        $labelItem->td_no = $travelDocument->no;
                        $labelItem->save();
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error("Error creating Travel Document Item: " . $e->getMessage());

                        return response()->json([
                            'type' => 'failed',
                            'message' => 'Error creating Travel Document Item: ' . $e->getMessage(),
                            'data' => null
                        ], 500);
                    }
                }
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Travel document created successfully',
                'data' => $travelDocument
            ], 201);
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error("Error creating Travel Document: " . $th->getMessage());

            // Rollback the transaction in case of any other exception
            DB::rollBack();
            return response()->json(['type' => 'failed', 'message' => 'Error creating travel document', 'data' => $th->getMessage()], 500);
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
    private function generateAndStoreQRCodeForPackageLabel($packageNumber)
    {
        // Generate the QR code
        $qrCode = QrCode::create($packageNumber);
        $qrCode->setSize(150);

        // Create the writer
        $writer = new PngWriter();
        $qrCodeData = $writer->write($qrCode);

        // Generate a unique filename for the QR code
        $fileName = 'qrcodes/travel_document_package_label_' . $packageNumber . '_' . uniqid() . '.png';

        // Store the QR code image in the storage folder
        Storage::disk('public')->put($fileName, $qrCodeData->getString());

        // Return the path to the stored QR code
        return $fileName;
    }

    public function getDeliveryOrders($no)
    {
        try {
            $travelDocument = TravelDocument::with('purchaseOrder', 'items.poItem.material', 'items.tempLabelItem')->where('no', $no)->first();

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

    public function getPrintedItemsLabel($po_number)
    {
        try {
            $tdiTemps = TravelDocumentLabelTemp::with('purchaseOrderItem', 'purchaseOrderItem.material')->where('po_number', $po_number)->groupBy('po_item_id')->get();
            $groupedData = [];
            foreach ($tdiTemps as $tdiTemp) {
                $dataTempQr = TravelDocumentLabelTemp::where('po_item_id', $tdiTemp->po_item_id)->get();
                $groupedData[] = [
                    "po_item_id" => $tdiTemp,
                    "description" => $tdiTemp->purchaseOrderItem->material->code . " - " . $tdiTemp->purchaseOrderItem->material->description,
                    "data" => $dataTempQr
                ];
            }

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $groupedData,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Not Found.',
                'data' => NULL,
            ], 400);
        }
    }
    public function getPrintedItemsLabelsForSupplier($poId)
    {
        try {
            $PoData = PurchaseOrder::findOrFail($poId);

            return $this->getPrintedItemsLabel($PoData->po_number);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Not Found.',
                'data' => NULL,
            ], 400);
        }
    }
    public function getPrintedLabels($poItemId)
    {
        try {
            $travelDocumentLabelTemp = TravelDocumentLabelTemp::where('po_item_id', $poItemId)->get();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $travelDocumentLabelTemp,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Not Found.',
                'data' => NULL,
            ], 400);
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

    public function downloadItemsLabel(Request $request, $id)
    {
        $travelDocument = TravelDocument::with('items')->findOrFail($id);

        $pdf = PDF::loadView('travel_documents.item-pdf', ['travelDocument' => $travelDocument, 'is_all' => true])
            ->setPaper('a4');
        return $pdf->download('Label-Surat-Jalan-' . $travelDocument->no . '.pdf');
    }
    public function PrintItemsLabel(Request $request, $id)
    {
        $travelDocument = TravelDocument::with('items', 'items.tempLabelItem')->findOrFail($id);

        // return response()->json(['travelDocument' => $travelDocument]);
        $pdf = PDF::loadView('travel_documents.item-pdf', ['travelDocument' => $travelDocument, 'is_all' => true])
            ->setPaper('a4');
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
        $items = TravelDocumentItem::with('travelDocument.supplier', 'tempLabelItem', 'poItem.material')->where('po_item_id', $itemId)->get();
        // return response()->json(['data' => $items]);

        $pdf = PDF::loadView('travel_documents.item-pdf', ['items' => $items, 'is_all' => false])->setPaper('a4');;
        $pdfContent = $pdf->output();
        return response()->json(['pdf_data' => base64_encode($pdfContent)]);
    }

    public function tempPrintLabel($itemId)
    {
        $itemLabels = TravelDocumentLabelTemp::with('purchaseOrder', 'purchaseOrder.supplier', 'purchaseOrderItem', 'purchaseOrderItem.material')->where('po_item_id', $itemId)->get();
        $pdf = PDF::loadView('travel_documents.item-labels', ['itemLabels' => $itemLabels, 'is_all' => true])->setPaper('a4');;
        $pdfContent = $pdf->output();
        return response()->json(['data' => base64_encode($pdfContent)]);
    }
    public function tempPrintLabelById($itemNumberId)
    {
        $itemLabel = TravelDocumentLabelTemp::with('purchaseOrder', 'purchaseOrder.supplier', 'purchaseOrderItem', 'purchaseOrderItem.material')->where('_id', $itemNumberId)->first();
        $pdf = PDF::loadView('travel_documents.item-labels', ['itemLabel' => $itemLabel, 'is_all' => false])->setPaper('a4');;
        $pdfContent = $pdf->output();
        return response()->json(['data' => base64_encode($pdfContent)]);
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
        $travelDocument = TravelDocument::with(['items' => function ($query) {
            $query->with('poItem.material'); // Eager load necessary relationships
        }])->findOrFail($travelDocumentId);
        $groupedItems = collect($travelDocument->items)
            ->groupBy('po_item_id')
            ->map(function ($group, $poItemId) {
                $firstItem = $group->first();
                return [
                    'po_item_id' => $poItemId,
                    'material' => $firstItem->poItem->material,
                    'poItem' => $firstItem->poItem,
                    'total_qty' => $group->sum('qty'),
                    'items' => $group,
                ];
            });

        $pdf = PDF::loadView('travel_documents.pdf', ['travelDocument' => $travelDocument, 'groupedItems' => $groupedItems])
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

            foreach ($request->scanned_items as $scannedItem) {
                $item = $travelDocumentItems->first(function ($item) use ($scannedItem) {
                    return $item->qr_tdi_no === $scannedItem['items']['qr_tdi_no'];
                });

                if ($item) {
                    $item->is_scanned = true;
                    $item->scanned_at = new UTCDateTime(Carbon::parse($scannedItem['scanTime'])->getPreciseTimestamp(3));
                    $item->scanned_by = auth()->user() ? auth()->user()->npk : '';
                    $item->save();

                    $itemsLabelTemp = TravelDocumentLabelTemp::where('item_number', $item->qr_tdi_no)->first();
                    $itemsLabelTemp->is_scanned = true;
                    $itemsLabelTemp->save();

                    $poItem = $item->poItem;
                    if ($poItem) {
                        $poItem->qty_delivered += $item->qty;

                        if ($poItem->qty_delivered >= $poItem->qty) {
                            $poItem->delivered_at = now();
                        } else if ($poItem->qty_delivered > 0 && $poItem->qty_delivered < $poItem->qty) {
                            $poItem->partially_delivered_at = now();
                        }

                        $poItem->save();
                    }
                }
            }

            $po = $travelDocument->purchaseOrder;
            if ($po && $po->items->every(function ($item) {
                return $item->qty_delivered >= $item->qty;
            })) {
                $po->po_status = 'closed';
                $po->save();
            }

            $travelDocument->status = "completed";
            $travelDocument->is_scanned = true;
            $travelDocument->scanned_at = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));

            $travelDocument->scanned_by = auth()->user() ? auth()->user()->npk : '';;
            $travelDocument->notes = $request->has('notes') ? $request->notes : '';
            $travelDocument->save();
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
