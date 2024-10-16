<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseOrderResource;
use App\PurchaseOrder;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\PurchaseOrderCreated;
use App\PurchaseOrderActivities;
use App\PurchaseOrderItem;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);
        $purchaseOrder = PurchaseOrder::where(function ($where) use ($request) {

            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%' . $request->keyword . '%');
                    } else {
                        $where->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            }
        })
            ->when(!empty($request->sort), function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order == 'ascend' ? 'asc' : 'desc');
            })
            ->take((int)$request->perpage)
            ->skip((int)$skip)
            ->get();

        $total = PurchaseOrder::where(function ($where) use ($request) {

            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%' . $request->keyword . '%');
                    } else {
                        $where->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            }
        })->count();

        return response()->json([
            'type' => 'success',
            'data' => $purchaseOrder,
            'total' => $total
        ], 200);
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
            Mail::to($supplier->email)->send(new PurchaseOrderCreated($purchaseOrder));

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
        $PurchaseOrder = PurchaseOrder::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' => $PurchaseOrder
        ], 200);
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

    public function markAsSeen($po_number)
    {
        try {
            $purchaseOrderActivity = PurchaseOrderActivities::where('po_number', $po_number)->first();
            if ($purchaseOrderActivity) {
                // $purchaseOrderActivity->po_id = $purchaseOrderActivity->purchaseOrder->_id;
                $purchaseOrderActivity->seen += 1;
                $purchaseOrderActivity->save();
            } else {
                // Create a new record if it doesn't exist
                PurchaseOrderActivities::create([
                    'po_id' => $purchaseOrderActivity,
                    'po_number' => $po_number,
                    'seen' => 1
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
                // Create a new record if it doesn't exist

                PurchaseOrderActivities::create([
                    'po_id' => $purchaseOrderActivity,
                    'po_number' => $po_number,
                    'downloaded' => 1
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

    public function listNeedSigned(Request $request)
    {
        $request->validate([
            'type'
        ]);

        try {
            $PurchaseOrder = "";

            switch ($request->type) {
                case "knowed":
                    $PurchaseOrder = PurchaseOrder::whereNull('purchase_knowed_by')
                        ->orWhere(function ($query) {
                            $query->where('purchase_knowed_by', '!=', null)
                                ->where('purchase_knowed_by', '=', '');
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
                                ->where('purchase_agreement_by', '=', '');
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
    public function signedAsApproved($id)
    {
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);

            $PurchaseOrder->purchase_agreement_by = auth()->user()->npk;
            // $PurchaseOrder->approved_at = 
            $PurchaseOrder->status = "approved";
            $PurchaseOrder->save();

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
}
