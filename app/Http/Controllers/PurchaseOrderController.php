<?php

namespace App\Http\Controllers;

use App\ForecastQty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Mail\PurchaseOrderCreated;
use App\Http\Resources\PurchaseOrderResource;
use App\PurchaseOrder;
use App\Supplier;
use App\PurchaseOrderActivities;
use App\PurchaseOrderItem;
use App\PurchaseOrderScheduleDelivery;
use App\Qr;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'status' => 'nullable|string',
            'order' => 'string',
        ]);

        $user = auth()->user();

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $status = ($request->status != null) ? $request->status : '';
        try {
            $PurchaseOrder = new PurchaseOrder;
            $data = array();
            $resultAlls = $PurchaseOrder->getAllData($keyword, $request->columns, $request->sort, $order, $status);
            $results = $PurchaseOrder->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $status);

            return response()->json([
                'type' => 'success',
                'data' => PurchaseOrderResource::collection($results),
                'dataAll' => $resultAlls,
                'total' => count($resultAlls),
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,

            ], 400);
        }
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
                $status = ($request->status != null) ? $request->status : 'approved';

                $PurchaseOrder = new PurchaseOrder;
                $data = array();
                $resultAlls = $PurchaseOrder->getBySupplierCodeData($keyword, $request->columns, $request->sort, $order, $status, $supplier_code);
                $results = $PurchaseOrder->getBySupplierCodeDataPagination($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $status, $supplier_code);

                return response()->json([
                    'type' => 'success',
                    'data' => PurchaseOrderResource::collection($results),
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

    public function showByCodeSupplier(Request $request, $supplier_code)
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
            $keyword = ($request->keyword != null) ? $request->keyword : '';
            $order = ($request->order != null) ? $request->order : 'ascend';
            $status = ($request->status != null) ? $request->status : '';

            $PurchaseOrder = new PurchaseOrder;
            $data = array();
            $resultAlls = $PurchaseOrder->getBySupplierCodeData($keyword, $request->columns, $request->sort, $order, $status, $supplier_code);
            $results = $PurchaseOrder->getBySupplierCodeDataPagination($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $status, $supplier_code);

            return response()->json([
                'type' => 'success',
                'data' => PurchaseOrderResource::collection($results),
                'dataAll' => $resultAlls,
                'total' => count($resultAlls),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: ' . $th->getMessage(),
            ], 400);
        }
    }

    // Create a new Purchase Order
    public function store(Request $request)
    {
        $request->validate([
            // 'po_number' => 'required|string|unique:purchase_order,po_number',
            'nomor_type' => 'required|bool',
            'order_date' => 'required|string|date',
            'supplier_id' => 'required|exists:supplier,_id',
            'items' => 'required|array',
            'items.*.material_id' => 'required|exists:materials,_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.unit_price_type' => 'required|string',
        ]);

        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Get the number of existing Purchase Orders
            $numberOfPOs = PurchaseOrder::count();
            $nextPONumber = 'PO-' . str_pad($numberOfPOs + 1, 5, '0', STR_PAD_LEFT); // Generate PO-00001, PO-00002, etc.

            // Fetch supplier
            $supplier = Supplier::find($request->supplier_id);

            // Create the Purchase Order
            $purchaseOrder = new PurchaseOrder([
                'po_number' => $nextPONumber,
                'supplier_id' => $supplier->_id,
                'order_date' => $request->orderDate,
                'delivery_date' => $request->deliveryDate,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'created_by' => auth()->user()->id,
            ]);

            $purchaseOrder->save();

            // save items of purchase order to purchase order items
            foreach ($request->items as $item) {
                $purchaseOrderItem = new PurchaseOrderItem([
                    'purchase_order_id' => $purchaseOrder->_id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                $purchaseOrderItem->save();
            }

            // Send an email notification
            // Mail::to($supplier->email)->send(new PurchaseOrderCreated($purchaseOrder));

            return response()->json(['message' => 'Purchase order created successfully', 'data' => $purchaseOrder], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: ' . $th->getMessage(),
            ], 400);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $this->markAsSeen($PurchaseOrder->po_number);

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => new PurchaseOrderResource($PurchaseOrder)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'success',
                'message' => 'Error: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function showPO(Request $request, $po_number)
    {
        try {
            $PurchaseOrder = PurchaseOrder::where('po_number', $po_number)->first();
            if ($PurchaseOrder->items != '') {
                foreach ($PurchaseOrder->items as $item) {
                    $item->material = isset($item->material) ? $item->material : '';
                }
            }

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => new PurchaseOrderResource($PurchaseOrder)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'success',
                'message' => 'Error: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function uploadScheduleDelivery(Request $request, $po_number)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,csv,xls',
                'description' => 'nullable|string',
                'show_to_supplier' => 'required|boolean',
                'is_send_email_to_supplier' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'type' => 'error',
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
            $fileNameSlug = Str::slug($fileNameWithoutExtension, '-');
            $fileName = $fileNameSlug . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('schedule_deliveries', $fileName, 'public');

            // Create and save the schedule delivery record
            $scheduleDelivery = new PurchaseOrderScheduleDelivery([
                'po_number' => $po_number,
                'filename' => $fileName,
                'description' => $request->description,
                'file_path' => $filePath,
                'show_to_supplier' => $request->show_to_supplier ?? 0,
                'is_send_email_to_supplier' => $request->is_send_email_to_supplier ?? 0,
                'created_by' => auth()->user()->npk,
                'updated_by' => auth()->user()->npk,
            ]);
            $scheduleDelivery->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Schedule delivery uploaded successfully',
                'data' => $scheduleDelivery,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error uploading schedule delivery: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function showScheduleDeliveries(Request $request, $po_number)
    {
        try {
            $PurchaseOrderScheduleDeliveries = PurchaseOrder::where('po_number', $po_number)->first()->scheduleDeliveries;

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $PurchaseOrderScheduleDeliveries
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'success',
                'message' => 'Error: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function markAsSeen($po_number)
    {
        try {
            $purchaseOrderActivity = PurchaseOrderActivities::where('po_number', $po_number)->first();
            if ($purchaseOrderActivity) {
                // $purchaseOrderActivity->po_id = $purchaseOrderActivity->purchaseOrder->_id;
                $purchaseOrderActivity->seen += 1;
                $purchaseOrderActivity->save();
            } else {
                $PurchaseOrder = PurchaseOrder::where('po_number', $po_number)->first();
                $po_id = $PurchaseOrder->_id;
                // Create a new record if it doesn't exist
                PurchaseOrderActivities::create([
                    'po_id' => $po_id,
                    'po_number' => $po_number,
                    'seen' => 1,
                    'last_seen_at' => new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3)),
                    'downloaded' => 0,
                    'last_downloaded_at' => ''
                ]);
            }

            return response()->json([
                'type' => 'success',
                'data' => 'PO No.: ' . $po_number . ' has been marked as seen.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function markAsDownloaded($po_number)
    {
        try {
            $purchaseOrderActivity = PurchaseOrderActivities::where('po_number', $po_number)->first();
            if ($purchaseOrderActivity) {
                // $purchaseOrderActivity->po_id = $purchaseOrderActivity->purchaseOrder->_id;
                $purchaseOrderActivity->downloaded += 1;
                // $purchaseOrderActivity->last_downloaded_at =
                $purchaseOrderActivity->save();
            } else {
                $PurchaseOrder = PurchaseOrder::where('po_number', $po_number)->first();
                $po_id = $PurchaseOrder->_id;
                // Create a new record if it doesn't exist
                PurchaseOrderActivities::create([
                    'po_id' => $purchaseOrderActivity,
                    'po_number' => $po_number,
                    'seen' => 0,
                    'last_seen_at' => null,
                    'downloaded' => 1,
                    'last_downloaded_at' => new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3))
                ]);
            }

            return response()->json([
                'type' => 'success',
                'data' => 'Success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function listNeedScheduleDeliveries()
    {
        try {
            $purchaseOrders = PurchaseOrder::with('scheduleDeliveries')->where('status', 'approved')
                ->whereDoesntHave('scheduleDeliveries')
                ->orderBy('approved_at', 'asc')
                ->get();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $purchaseOrders
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getScheduleDeliveriesByPoId(Request $request, $id)
    {
        try {
            $ScheduleDelivery = PurchaseOrder::with('scheduleDeliveries')->where('_id', $id)->first()->scheduleDeliveries;

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $ScheduleDelivery
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getListScheduleDelivered()
    {
        try {
            $purchaseOrders = PurchaseOrder::where('status', 'approved')
                ->whereHas('scheduleDeliveries', function ($query) {
                    $query->where('show_to_supplier', 1);
                })
                ->get();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $purchaseOrders
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getDashboardData()
    {
        return response()->json([
            'activities' => [
                ['time' => '20:33', 'description' => 'Ubah Desain Cetakan Pesanan Pembelian'],
            ],
            'upcomingActivities' => [],
            'salesTrend' => [
                ['date' => 'Sen', 'sales' => 0],
                ['date' => 'Sel', 'sales' => 0],
                ['date' => 'Rab', 'sales' => 0],
                ['date' => 'Kam', 'sales' => 0],
                ['date' => 'Jum', 'sales' => 0],
                ['date' => 'Sab', 'sales' => 0],
                ['date' => 'Min', 'sales' => 0],
            ],
            'cashFlow' => [
                ['date' => '6 Okt', 'cash' => 0],
                ['date' => '7 Okt', 'cash' => 0],
                ['date' => '8 Okt', 'cash' => 0],
                ['date' => '9 Okt', 'cash' => 0],
                ['date' => '10 Okt', 'cash' => 0],
                ['date' => '11 Okt', 'cash' => 0],
                ['date' => '12 Okt', 'cash' => 0],
            ],
            'companyExpenses' => ['total' => 0],
            'profitAndLoss' => [
                'revenue' => 0,
                'hpp' => 0,
                'expenses' => 0,
            ],
            'sales' => [
                'revenue' => 0,
                'unpaid' => 0,
            ],
            'purchases' => [
                'total' => 0,
                'unpaid' => 0,
            ],
        ]);
    }

    public function showToSupplier($po_number)
    {
        $res_po = Crypt::decryptString($po_number);
        try {
            $PurchaseOrder = PurchaseOrder::where('po_number', $res_po)->first();

            $this->markAsSeen($PurchaseOrder->po_number);

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $PurchaseOrder
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
    public function printLabelQRForSupplier($po_id)
    {
        try {
            $res_po = PurchaseOrder::findOrFail($po_id);

            $qrCode = QrCode::create($res_po->po_number);
            $qrCode->setSize(300); // Set QR code size

            // Create the writer to output as PNG
            $writer = new PngWriter();

            // Generate the QR code image data
            $qrCodeData = $writer->write($qrCode);

            // Pass the QR code image data to your view
            return view('purchase_orders.qr-label', [
                'po' => $res_po,
                'qrCode' => $qrCodeData->getDataUri(), // Get data URI for embedding in HTML
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error print QR Label: ' . $th->getMessage(),
                'data' => $res_po->po_number
            ], 500);
        }
    }
    public function downloadPDFForSupplier($po_id)
    {
        // $res_po = Crypt::decryptString($po_number);
        $res_po = PurchaseOrder::findOrFail($po_id);
        try {
            $this->downloadPDF($res_po->po_number);

            $this->markAsDownloaded($res_po->po_number);

            return response()->json([
                'type' => 'success',
                'message' => 'PDF downloaded successfully',
                'data' => ['po_number' => $res_po->po_number]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error downloading PDF: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function printQrLabel($poNumber)
    {
        $po = PurchaseOrder::where('po_number', $poNumber)->firstOrFail();

        // Create the QR code instance
        $qrCode = QrCode::create($po->po_number);
        $qrCode->setSize(300); // Set QR code size

        // Create the writer to output as PNG
        $writer = new PngWriter();

        // Generate the QR code image data
        $qrCodeData = $writer->write($qrCode);

        // Pass the QR code image data to your view
        return view('purchase_orders.qr-label', [
            'po' => $po,
            'qrCode' => $qrCodeData->getDataUri(), // Get data URI for embedding in HTML
        ]);
    }

    public function generateAndStoreQRCode($poNumber)
    {
        $filename = '';
        try {
            $po = PurchaseOrder::where('po_number', $poNumber)->firstOrFail();
            $qr = Qr::GenerateQR('purchase_order', $poNumber);
            $filename = $qr->path;
            $po->qr_uuid = $qr->uuid;
            $po->save();
        } catch (\Throwable $th) {
            //throw $th;

        }

        return $filename;
    }

    public function downloadPDF($po_number)
    {
        try {
            $purchaseOrder = PurchaseOrder::where('po_number', $po_number)->first();
            $forecastQties = ForecastQty::where('is_active', 1)->get();

            if (!$purchaseOrder) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Purchase order not found',
                    'data' => null
                ], 404);
            }

            // check status purchase order
            if (!isset($purchaseOrder->status)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Purchase order status not found',
                    'data' => null
                ], 404);
            }

            $pdf = PDF::loadView('purchase_orders.pdf2', ['forecastQties' => $forecastQties, 'purchaseOrder' => new PurchaseOrderResource($purchaseOrder)]);
            return $pdf->download('' . $purchaseOrder->po_number . '.pdf');
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function downloadMultiplePDF(Request $request)
    {
        $request->validate([
            'po_numbers' => 'required|array',
        ]);

        try {
            $zip = new \ZipArchive();
            $zipFileName = 'purchase_orders_' . date('YmdHis') . '.zip';
            $zipPath = storage_path('app/public/temp/' . $zipFileName);

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($request->po_numbers as $po_number) {
                    $purchaseOrder = PurchaseOrder::where('po_number', $po_number)->first();

                    if ($purchaseOrder) {
                        $pdf = PDF::loadView('purchase_orders.pdf2', ['purchaseOrder' => $purchaseOrder]);
                        $pdfFileName = $purchaseOrder->po_number . '.pdf';
                        $pdf->save(storage_path('app/public/temp/' . $pdfFileName));

                        $zip->addFile(storage_path('app/public/temp/' . $pdfFileName), $pdfFileName);
                    }
                }

                $zip->close();

                // Delete temporary PDF files
                foreach ($request->po_numbers as $po_number) {
                    $pdfFileName = $po_number . '.pdf';
                    if (file_exists(storage_path('app/public/temp/' . $pdfFileName))) {
                        unlink(storage_path('app/public/temp/' . $pdfFileName));
                    }
                }

                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            } else {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Error creating zip file',
                    'data' => null
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error: ' . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function listNeedSigned(Request $request, $approvalType)
    {
        try {
            $PurchaseOrder = "";

            switch ($approvalType) {
                case "knowed":
                    $PurchaseOrder = PurchaseOrder::whereNull('purchase_knowed_by')
                        ->orWhere(function ($query) {
                            $query->where('purchase_knowed_by', '!=', null)
                                ->where('purchase_knowed_by', '=', '')
                                ->where('is_checked', '=', 1);
                        })
                        ->get();
                    break;

                case "checked":
                    $PurchaseOrder = PurchaseOrder::whereNull('purchase_checked_by')
                        ->orWhere(function ($query) {
                            $query->where('purchase_checked_by', '!=', null)
                                ->where('purchase_checked_by', '=', '');
                        })
                        ->get();
                    break;

                case "approved":
                    $PurchaseOrder = PurchaseOrder::whereNull('purchase_agreement_by')
                        ->orWhere(function ($query) {
                            $query->where('purchase_agreement_by', '!=', null)
                                ->where('purchase_agreement_by', '=', '')
                                ->where('is_knowed', '=', 1)
                                ->where('is_checked', '=', 1);
                        })
                        ->get();
                    break;

                default:
                    return response()->json([
                        'type' => 'error',
                        'message' => '',
                        'data' => "Type not found!"
                    ]);
            }

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $PurchaseOrder
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }

    public function signedAsKnowed($id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_knowed_by = auth()->user()->npk;
            $PurchaseOrder->knowed_at = new \MongoDB\BSON\UTCDateTime();
            $PurchaseOrder->is_knowed = 1;
            // $PurchaseOrder->knowed_at = 
            $PurchaseOrder->save();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => "Purchase Order " . $PurchaseOrder->po_number . " was confirmed to knowed."
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
    public function signedAsChecked($id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_checked_by = auth()->user()->npk;
            $PurchaseOrder->checked_at = new \MongoDB\BSON\UTCDateTime();
            $PurchaseOrder->is_checked = 1;
            // $PurchaseOrder->checked_at = 
            $PurchaseOrder->save();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => "Purchase Order " . $PurchaseOrder->po_number . " was confirmed to checked."
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
    public function signedAsApproved(Request $request, $id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_agreement_by = auth()->user()->npk;
            $PurchaseOrder->approved_at = new \MongoDB\BSON\UTCDateTime();
            $PurchaseOrder->is_approved = 1;
            $PurchaseOrder->status = "approved";
            $PurchaseOrder->save();

            // Send an email notification
            EmailController::sendEmailPurchaseOrderConfirmation($request, $PurchaseOrder->po_number);

            $msg = $PurchaseOrder->po_number . ' sudah di approved dan PO sudah dikirim ke supplier.';
            $receipt_number = 'whatsapp:+6285156376462';
            WhatsAppController::sendWhatsAppMessage($request, $receipt_number, $msg);

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => "Purchase Order " . $PurchaseOrder->po_number . " was confirmed to approved."
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
    public function signedAsKnowedUnconfirmed($id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_knowed_by = auth()->user()->npk;
            $PurchaseOrder->knowed_at = new \MongoDB\BSON\UTCDateTime();
            $PurchaseOrder->is_knowed = 0;
            // $PurchaseOrder->knowed_at = 
            $PurchaseOrder->status = "unapproved";
            $PurchaseOrder->save();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => "Purchase Order " . $PurchaseOrder->po_number . " was set to unconfirmed to knowed."
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
    public function signedAsCheckedUnconfirmed($id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_checked_by = auth()->user()->npk;
            $PurchaseOrder->checked_at = new \MongoDB\BSON\UTCDateTime();
            $PurchaseOrder->is_checked = 0;
            // $PurchaseOrder->checked_at = 
            $PurchaseOrder->status = "unapproved";
            $PurchaseOrder->save();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => "Purchase Order " . $PurchaseOrder->po_number . " was unconfirmed to checked."
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
    public function signedAsApprovedUnconfirmed($id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_agreement_by = auth()->user()->npk;
            $PurchaseOrder->approved_at = new \MongoDB\BSON\UTCDateTime();
            $PurchaseOrder->is_approved = 0;
            $PurchaseOrder->status = "unapproved";
            $PurchaseOrder->save();

            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => "Purchase Order " . $PurchaseOrder->po_number . " was unconfirmed to approved."
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ]);
        }
    }
}
