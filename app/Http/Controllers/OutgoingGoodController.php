<?php

namespace App\Http\Controllers;

use App\Material;
use App\OutgoingGood;
use App\OutgoingGoodItem;
use App\OutgoingGoodTemplate;
use App\OutgoingGoodTemplateItem;
use App\StockSlocTakeOutTemp;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class OutgoingGoodController extends Controller
{
    /**
     * Display a listing of outgoing goods
     */
    public function index(Request $request)
    {
        $query = OutgoingGood::with(['items', 'assignedTo']);

        // Filter by assignment status
        // if ($request->has('is_assigned')) {
        //     $query->where('is_assigned', $request->is_assigned);
        // }

        if($request->has('handle_for_id') && $request->handle_for_id !== '') {
            $query->where('handle_for_id', $request->handle_for_id);
        }

        if($request->has('keyword') && $request->keyword !== '') {
            $query->where('number', 'like', '%' . $request->keyword . '%');
        }

        // Filter by completion status
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if($request->has('part_number') && $request->part_number !== '') {
            $query->where('part_number', 'like', '%' . $request->part_number . '%');
        }
        if($request->has('part_name') && $request->part_name !== '') {
            $query->where('part_name', 'like', '%' . $request->part_name . '%');
        }

        if($request->has('multiple_status') && is_array($request->multiple_status)) {
            $query->whereNotIn('status', ['completed', 'cancelled','waiting_tp']);
        }

        $perPage = $request->input('per_page', 10); // default 10 items per page
        $page = $request->input('page', 1); // default to page 1

        $outgoingGoods = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $outgoingGoods->items(),
            'current_page' => $outgoingGoods->currentPage(),
            'last_page' => $outgoingGoods->lastPage(),
            'per_page' => $outgoingGoods->perPage(),
            'total' => $outgoingGoods->total(),
        ]);
    }

    /**
     * Store a newly created outgoing good
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'outgoing_location' => 'required|string',
            'handle_for' => 'required|string',
            'part_name' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_code' => 'required|string|exists:materials,code',
            'items.*.quantity_needed' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $session = null;
        try {
            // Start MongoDB transaction
            $session = DB::getMongoClient()->startSession();
         $session->startTransaction();

            // Generate a unique reference number with monthly sequence
            $currentMonth = date('Ym');
            $lastOutgoingGood = OutgoingGood::where('number', 'like', 'OG-' . $currentMonth . '-%')
                ->orderBy('number', 'desc')
                ->first();

            $sequence = 1;
            if ($lastOutgoingGood) {
                $lastSequence = (int) substr($lastOutgoingGood->number, -4);
                $sequence = $lastSequence + 1;
            }

            $refNumber = 'OG-' . $currentMonth . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $outgoingGood = new OutgoingGood();
            $outgoingGood->number = $refNumber;
            $outgoingGood->date = $request->date;
            $outgoingGood->time = $request->time;
            $outgoingGood->part_number = $request->part_number;
            $outgoingGood->part_name = $request->part_name;
            $outgoingGood->priority = $request->priority;
            $outgoingGood->shift = $request->shift ?? '1';
            $outgoingGood->outgoing_location = $request->outgoing_location;
            $outgoingGood->handle_for = $request->handle_for;
            $outgoingGood->handle_for_type = $request->handle_for_type ?? 'internal';
            $outgoingGood->handle_for_id = $request->handle_for_id ?? null;
            $outgoingGood->status = 'ready';
            $outgoingGood->is_assigned = ($request->handle_for) ? true : false;
            $outgoingGood->is_completed = false;
            $outgoingGood->created_by = auth()->user()->npk;
            $outgoingGood->notes = $request->notes;
            $outgoingGood->assigned_to = $request->handle_for_id;

            // === QR Code Generation ===
            $qrPath = 'whs/bkb/qr/' . $refNumber . '.png';
            $qrCode = QrCode::format('png')->size(300)->generate($refNumber);
            Storage::disk('public')->put($qrPath, $qrCode);
            $outgoingGood->qr_code_path = $qrPath;
            $outgoingGood->take_material_from_location = $request->take_material_from_location ?? null;

            $outgoingGood->save();

            $itemsData = [];

            $request->merge(['slock_code' => 'RAW01', 'tag' => 'ok']);
            unset($request['material_code']);

            // Save items
            foreach ($request->items as $item) {
                $stockSlock = new StockSlockController();
                $material = Material::where('code', $item['material_code'])->first();
                
                $outgoingItem = new OutgoingGoodItem();
                $outgoingItem->outgoing_good_id = $outgoingGood->id;
                $outgoingItem->outgoing_good_number = $refNumber;
                $outgoingItem->created_by = auth()->user()->npk;
                $outgoingItem->material_code = $item['material_code'];
                $outgoingItem->material_name = $material->description;
                $outgoingItem->quantity_needed = floatval($item['quantity_needed']);
                $outgoingItem->quantity_out = 0;
                $outgoingItem->uom_needed = $material->unit;
                $outgoingItem->uom_out = $material->unit;
                $outgoingItem->save();

                $request->merge(['material_code' => $item['material_code']]);
                $stockSlockData = $stockSlock->getStockMaterialAvailable($request);
                $stockSlockData = $stockSlockData->original['data'] ?? [];
                
                $filteredStockData = collect($stockSlockData)
                ->where('material_code', $item['material_code'])
                ->where('available_qty', '>', 0)
                ->sortBy(function($item) {
                    return $item['date_income'] . ' ' . $item['time_income'];
                })
                ->values(); 
                
                if ($filteredStockData->count() > 0) {
                    $stockNeeded = floatval($item['quantity_needed']);
                    $stockOut = 0;
                    $totalAvailable = $filteredStockData->sum('available_qty');
                    $stockAvailable = 0; 

                    // Check if we have enough total stock
                    if ($totalAvailable >= $stockNeeded) { 
                        foreach ($filteredStockData as $stock) {
                            if ($stockNeeded <= 0) {
                                break;
                            }

                            // Take the available stock 
                            if($material->is_partially_out == null || $material->is_partially_out == false) {
                                $stockOut += $stock['available_qty'];
                                $stockAvailable = $stock['available_qty'];
                                $stockNeeded -= $stock['available_qty']; 
                            } else {
                                $stockOut = $stock['available_qty'];
                                $stockAvailable = $stock['available_qty'];
                                $stockNeeded -= $stock['available_qty']; 
                            }

                            // Create temporary record for stock take out
                            $tempStock = new StockSlocTakeOutTemp();
                            $tempStock->job_seq = $stock['job_seq'];
                            $tempStock->material_code = $item['material_code'];
                            $tempStock->sloc_code = $stock['slock_code'];
                            $tempStock->rack_code = $stock['rack_code'];
                            $tempStock->uom = $stock['uom'];
                            $tempStock->qty = $stock['valuated_stock'];
                            $tempStock->uom_take_out = $material->unit;
                           
                            $tempStock->qty_take_out = $stockAvailable;
                            $tempStock->user_id = auth()->user()->npk;
                            $tempStock->status = 'ready';
                            $tempStock->note = 'Temporary hold for outgoing good: ' . $refNumber;
                            $tempStock->is_success = false;
                            $tempStock->save();

                            $itemsData[] = [
                                'rack_code' => $stock['rack_code'],
                                'job_seq' => $stock['job_seq'],
                            ];
                            $outgoingItem->addListNeedScans($stock['job_seq'], $stock['rack_code'], $stockAvailable, $stock['uom']);
                        }
                    } else {
                        throw new \Exception('Not enough stock available. Required: ' . $item['quantity_needed'] . ', Available: ' . $totalAvailable);
                    }
                }
                unset($request['material_code']);
            }

            // Commit MongoDB transaction
            $session->commitTransaction();

            return response()->json([
                'success' => true,
                'message' => 'Outgoing request created successfully',
                'data' => $outgoingGood->load('items'),
                'qr_code_url' => asset('storage/' . $qrPath)
            ], 201);

        } catch (\Exception $e) {
            // Rollback MongoDB transaction
            $session->abortTransaction();
            
            // Delete QR code if it was created
            if (isset($qrPath) && Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create outgoing request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified outgoing good
     */
    public function show($id)
    {
        $outgoingGood = OutgoingGood::with(['items', 'assignedTo'])->findOrFail($id);

        $outgoingGood->items->each(function ($item) {
            $item->getListNeedScans();
        });

        return response()->json([
            'success' => true,
            'data' => $outgoingGood
        ]);
    }

    /**
     * Assign outgoing goods to a user
     */
    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'outgoing_ids' => 'required|array|min:1',
            'outgoing_ids.*' => 'required|exists:outgoing_goods,_id',
            'user_id' => 'required|exists:users,_id',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        // Check if user type matches
        if (
            ($request->user_type === 'internal' && !in_array($user->type, [0])) ||
            ($request->user_type === 'external' && $user->type !== 1)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'User type does not match the selected type'
            ], 422);
        }

        foreach ($request->outgoing_ids as $id) {
            $outgoingGood = OutgoingGood::findOrFail($id);

            // Check if already assigned
            if ($outgoingGood->is_assigned) {
                continue;
            }

            $outgoingGood->assigned_to_id = $request->user_id;
            $outgoingGood->assigned_at = Carbon::now();
            $outgoingGood->is_assigned = true;
            $outgoingGood->status = 'in_progress';
            $outgoingGood->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Items successfully assigned'
        ]);
    }

    /**
     * Update the status of an outgoing good
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,ready,waiting_tp,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $outgoingGood = OutgoingGood::findOrFail($id);
        $outgoingGood->status = $request->status;

        // Handle completion
        if ($request->status === 'completed' && !$outgoingGood->is_completed) {
            $outgoingGood->is_completed = true;
            $outgoingGood->completed_at = Carbon::now();
            $outgoingGood->completed_by = auth()->user()->npk;

            if ($request->has('completion_notes')) {
                $outgoingGood->completion_notes = $request->completion_notes;
            }
        }

        $outgoingGood->save();

        // Update StockSlocTakeOutTemp records based on status
        if (in_array($request->status, ['completed', 'cancelled','waiting_tp'])) {
            $status = $request->status === 'completed' ? 'finished' : ($request->status === 'waiting_tp' ? 'waiting_tp' : 'cancelled');
            
            StockSlocTakeOutTemp::where('note', 'like', '%' . $outgoingGood->number . '%')
                ->update([
                    'status' => $status,
                    'is_success' => $request->status === 'completed',
                    'updated_at' => Carbon::now()
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $outgoingGood
        ]);
    }

    public function update(Request $request, $id)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);
        
        // adding condition if request has data
        if($request->has('note') && $request->note !== '') {                
            $outgoingGood->note = $request->note;
        }
        if($request->has('priority') && $request->priority !== '') {
            $outgoingGood->priority = $request->priority;
        }
        if($request->has('outgoing_location') && $request->outgoing_location !== '') {
            $outgoingGood->outgoing_location = $request->outgoing_location;
        }
        if($request->has('handle_for') && $request->handle_for !== '') {
            $outgoingGood->handle_for = $request->handle_for;
        }
        if($request->has('handle_for_type') && $request->handle_for_type !== '') {
            $outgoingGood->handle_for_type = $request->handle_for_type;
        }
        if($request->has('handle_for_id') && $request->handle_for_id !== '') {
            $outgoingGood->handle_for_id = $request->handle_for_id;
        }

        if($request->has('take_material_from_location') && $request->take_material_from_location !== '') {
            $outgoingGood->take_material_from_location = $request->take_material_from_location;
        }

        if($request->has('assigned_to') && $request->assigned_to !== '') {
            $outgoingGood->assigned_to = $request->assigned_to;
        }

        $outgoingGood->save();

        return response()->json([
            'success' => true,
            'message' => 'Outgoing good updated successfully',
            'data' => $outgoingGood
        ]);
    }

    public function changeAssign(Request $request, $id)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);
        $userData = User::findOrFail($request->user_id);

        if(!$userData) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        $outgoingGood->handle_for = $userData->full_name;
        $outgoingGood->handle_for_type = $userData->type;
        $outgoingGood->handle_for_id = $request->user_id;
        $outgoingGood->assigned_to = $request->user_id;
        $outgoingGood->save();

        return response()->json([
            'success' => true,
            'message' => 'Outgoing good assigned to updated successfully',
            'data' => $outgoingGood
        ]);
    }

    public function changeAssignReceiveBy(Request $request, $id)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);

        $outgoingGood->received_by = auth()->user()->npk;
        $outgoingGood->received_date = Carbon::now()->format('Y-m-d H:i:s');
        $outgoingGood->save();

        return response()->json([
            'success' => true,
            'message' => 'Outgoing good received by updated successfully',
            'data' => $outgoingGood
        ]);
    }

    public function changeAssignHandedOverBy(Request $request, $id)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);

        $outgoingGood->handed_over_by = auth()->user()->npk;
        $outgoingGood->handed_over_date = Carbon::now()->format('Y-m-d H:i:s');
    }

    public function changeAssignAcknowledgeBy(Request $request, $id)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);

        $outgoingGood->acknowledged_by = auth()->user()->npk;
        $outgoingGood->acknowledged_date = Carbon::now()->format('Y-m-d H:i:s');
    }

    public function changeAssignRequestedBy(Request $request, $id)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);

        $outgoingGood->requested_by = auth()->user()->npk;
        $outgoingGood->requested_date = Carbon::now()->format('Y-m-d H:i:s');
    }

    /**
     * Generate a receipt for a completed outgoing good
     */
    public function generateReceipt($id)
    {
        $outgoingGood = OutgoingGood::with(['items', 'assignedTo', 'completedBy'])->findOrFail($id);

        // Check if completed
        if (!$outgoingGood->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot generate receipt for incomplete request'
            ], 400);
        }

        // Logic to generate PDF receipt would go here
        // For example, using a package like barryvdh/laravel-dompdf

        // This is just a placeholder - implement actual PDF generation
        $pdf = "PDF receipt content";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $outgoingGood->number . '.pdf"',
        ]);
    }

    public function startScanBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'outgoing_good_id' => 'required|exists:outgoing_goods,_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $outgoingGood = OutgoingGood::findOrFail($request->outgoing_good_id);   
        
        $outgoingGood->status = 'in_progress';

        // $request->merge(['slock_code' => 'RAW01']);

        // $stockSlock = new StockSlockController();
        // $stockSlockData = $stockSlock->index($request);
        // $stockSlockData = $stockSlockData->original['data'] ?? [];

        // // $itemsData = [];

        // foreach ($outgoingGood->items as $item) {
        //     // check if stockSlockData has data with material_code = $item->material_code
        //     $filteredStockData = collect($stockSlockData)
        //         ->where('material_code', $item->material_code)
        //         ->sortBy(function($item) {
        //             return $item['date_income'] . ' ' . $item['time_income'];
        //         })
        //         ->values();

        //     if ($filteredStockData->count() > 0) {
        //         $stockNeeded = (int)$item->quantity_needed;
        //         $stockOut = 0;
        //         foreach ($filteredStockData as $stock) {
        //             if ($stockNeeded <= 0) {
        //                 break;
        //             }

        //             $stockOut = min($stockNeeded, $stock['valuated_stock']);
        //             $stockNeeded -= $stockOut;

        //             $itemsData[] = [
        //                 'rack_code' => $stock['rack_code'],
        //                 'job_seq' => $stock['job_seq'],
        //                 // 'quantity' => $stock['valuated_stock'],
        //                 // 'uom' => $stock['uom'],
        //                 // 'date_income' => $stock['date_income'],
        //                 // 'time_income' => $stock['time_income'],
        //                 // 'stockOut' => $stockOut,
        //                 // 'stockNeeded' => $stockNeeded,
        //             ];
        //             $item->addListNeedScans($stock['job_seq'], $stock['rack_code'], $stockOut, $stock['uom']);
        //         }
        //     }
        // }

        $outgoingGood->save();

        return response()->json([
            'success' => true,
            'message' => 'Outgoing good started',
            'data' => $itemsData
        ]);
    }

    public function checkBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string',
            'handle_for_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $barcode = $request->barcode;
        $handleForId = $request->handle_for_id;
        $outgoingGood = OutgoingGood::where('number', $barcode)->where('handle_for_id', $handleForId)->first();

        if (!$outgoingGood) {
            return response()->json([
                'success' => false,
                'message' => 'Outgoing good not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $outgoingGood
        ]);
    }

    public function markCompleted(Request $request, $id)
    {
        try {
            $outgoingGood = OutgoingGood::findOrFail($id);

            $outgoingGood->status = 'completed';
            $outgoingGood->completed_tp_at = Carbon::now()->format('Y-m-d H:i:s');
            $outgoingGood->completed_tp_by = auth()->user()->npk;
            $outgoingGood->save();

            return response()->json([
                'success' => true,
                'message' => 'Outgoing good marked as completed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark outgoing good as completed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeItems(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'material_code' => 'required|string',
            'quantity_needed' => 'required|numeric',
            'uom_needed' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $outgoingGood = OutgoingGood::findOrFail($id);

        if(!$outgoingGood) {
            return response()->json([
                'success' => false,
                'message' => 'Outgoing good not found'
            ], 404);
        }

        $material = Material::where('code', $request->material_code)->first();

        if(!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found'
            ], 404);
        }
        $request->merge(['slock_code' => 'RAW01']);

        $stockSlock = new StockSlockController();
        $stockSlockData = $stockSlock->index($request);
        $stockSlockData = $stockSlockData->original['data'] ?? [];

        $newItem = new OutgoingGoodItem();
        $newItem->outgoing_good_id = $outgoingGood->_id;
        $newItem->outgoing_good_number = $outgoingGood->number;
        $newItem->material_code = $request->material_code;
        $newItem->material_name = $material->description;
        $newItem->quantity_needed = $request->quantity_needed;
        $newItem->uom_needed = $request->uom_needed;
        $newItem->created_by = auth()->user()->npk;
        $newItem->quantity_out = 0;
        $newItem->uom_out = $material->unit;
        $newItem->save();

        $filteredStockData = collect($stockSlockData)
            ->where('material_code', $request->material_code)
            ->sortBy(function($item) {
                return $item['date_income'] . ' ' . $item['time_income'];
            })
            ->values();

        if($filteredStockData->count() > 0) {
            $stockNeeded = (int)$request->quantity_needed;
            $stockOut = 0;
            foreach ($filteredStockData as $stock) {
                if($stockNeeded <= 0) {
                    break;
                }

                $stockOut = min($stockNeeded, $stock['valuated_stock']);
                $stockNeeded -= $stockOut;

                $newItem->quantity_out = $stockOut;
                $newItem->uom_out = $stock['uom'];
                // Create temporary record for stock take out
                $stockSlocTakeOutTemp = new StockSlocTakeOutTemp();
                $stockSlocTakeOutTemp->job_seq = $stock['job_seq'];
                $stockSlocTakeOutTemp->material_code = $request->material_code;
                $stockSlocTakeOutTemp->sloc_code = $stock['slock_code'];
                $stockSlocTakeOutTemp->rack_code = $stock['rack_code'];
                $stockSlocTakeOutTemp->note = $outgoingGood->number;
                $stockSlocTakeOutTemp->qty = $stock['valuated_stock'];
                $stockSlocTakeOutTemp->qty_take_out = $stockOut;
                $stockSlocTakeOutTemp->uom = $stock['uom'];
                $stockSlocTakeOutTemp->uom_take_out = $material->unit;
                $stockSlocTakeOutTemp->user_id = auth()->user()->npk;
                $stockSlocTakeOutTemp->status = 'ready';
                $stockSlocTakeOutTemp->created_by = auth()->user()->npk;
                $stockSlocTakeOutTemp->is_success = false;
                $stockSlocTakeOutTemp->save();

                $newItem->addListNeedScans($stock['job_seq'], $stock['rack_code'], $stockOut, $stock['uom']);
            }
        }

        // Create the item with all fields
        // $item = $outgoingGood->items()->create([
        //     'material_code' => $request->material_code,
        //     'quantity_needed' => $request->quantity_needed,
        //     'uom_needed' => $request->uom_needed,
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Items added successfully',
            'data' => $newItem
        ]);
    }

    public function updateItem(Request $request, $id, $code_item)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);

        $outgoingGood->items()->where('material_code', $code_item)->update($request->all());
    }

    public function destroyItem(Request $request, $id, $code_item)
    {
        $outgoingGood = OutgoingGood::findOrFail($id);

        $outgoingItem = OutgoingGoodItem::where('outgoing_good_id', $outgoingGood->_id)->where('material_code', $code_item)->first();

        $outgoingGood->items()->where('material_code', $code_item)->delete();

        $stockSlocTakeOutTemp = StockSlocTakeOutTemp::where('job_seq', $outgoingItem->job_seq)->where('material_code', $code_item)->first();

        if($stockSlocTakeOutTemp) {
            $stockSlocTakeOutTemp->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    }

    /**
     * Get templates for outgoing goods
     */
    public function getTemplates(Request $request)
    {
        $query = OutgoingGoodTemplate::with('items');

        // Search by part_name
        if ($request->has('part_name') && $request->part_name !== '') {
            $query->where('part_name', 'like', '%' . $request->part_name . '%');
        }

        // Search by part_number
        if ($request->has('part_number') && $request->part_number !== '') {
            $query->where('part_number', 'like', '%' . $request->part_number . '%');
        }

        // Search by name_template
        if ($request->has('name_template') && $request->name_template !== '') {
            $query->where('name_template', 'like', '%' . $request->name_template . '%');
        }

        if($request->has('search') && $request->search !== '' && $request->search !== null) {
            $query->where('part_name', 'like', '%' . $request->search . '%')
                ->orWhere('part_number', 'like', '%' . $request->search . '%')
                ->orWhere('name_template', 'like', '%' . $request->search . '%');
        }

        // Handle sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortColumn, $sortOrder);

        // Handle pagination
        $perPage = $request->input('per_page',30);
        $page = $request->input('page', 1);

        // Get paginated results
        $templates = $query->paginate($perPage, ['*'], 'page', $page);

        // Handle column selection - only transform if columns are specified
        if ($request->has('columns') && !empty($request->columns) && is_array($request->columns)) {
            $templates->getCollection()->transform(function ($template) use ($request) {
                $data = $template->only($request->columns);
                // If items relationship exists and is loaded, add it to the response
                if ($template->relationLoaded('items')) {
                    $data['items'] = $template->items;
                }
                return $data;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $templates->items(),
            'current_page' => $templates->currentPage(),
            'last_page' => $templates->lastPage(),
            'per_page' => $templates->perPage(),
            'total' => $templates->total(),
        ]);
    }

    // /**
    //  * Store a new template
    //  */
    public function storeOrUpdateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_template' => 'nullable|string',
            'name_template' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // check if code_template is already exists
        $template = OutgoingGoodTemplate::where('code_template', $request->code_template)->first();

        if($template) {
            $isUpdate = true;   
        } else {
            $isUpdate = false;
        }

        $userNpk = auth()->user()->npk;

        if ($isUpdate) {
            $template = OutgoingGoodTemplate::where('code_template', $request->code_template)->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $template->updated_by = $userNpk;
        } else {
            $template = new OutgoingGoodTemplate();
            $template->code_template = 'OGT-' . date('Ymd') . '-' . Str::random(6);
            $template->created_by = $userNpk;
        }

        $template->name_template = $request->name_template;
        $template->material_code = $request->material_code;
        $template->part_name = $request->part_name;
        $template->part_number = $request->part_number;
        $template->notes = $request->notes;
        $template->save();

        // Clear old items if updating
        if ($isUpdate) {
            OutgoingGoodTemplateItem::where('code_template', $template->code_template)->delete();
        }

        foreach ($request->items as $item) {
            $material = Material::where('code', $item['material_code'])->first();

            if (!$material) {
                continue; // or handle as error
            }

            $templateItem = new OutgoingGoodTemplateItem();
            $templateItem->code_template = $template->code_template;
            $templateItem->created_by = $userNpk;
            $templateItem->part_number = $item['part_number'] ?? '';
            $templateItem->alias = $item['alias'] ?? '';
            $templateItem->material_code = $item['material_code'];
            $templateItem->material_name = $material->description;
            $templateItem->quantity_needed = $item['quantity_needed'] ?? 0;
            $templateItem->uom_needed = $material->unit;
            $templateItem->save();
        }

        return response()->json([
            'success' => true,
            'message' => $isUpdate ? 'Template updated successfully' : 'Template created successfully',
            'data' => $template
        ], $isUpdate ? 200 : 201);
    }

    public function updateTemplate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_template' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $template = OutgoingGoodTemplate::findOrFail($id);

        if($request->has('name_template') && $request->name_template !== '') {
            $template->name_template = $request->name_template;
        }
        if($request->has('part_name') && $request->part_name !== '') {
            $template->part_name = $request->part_name;
        }

        if($request->has('material_code') && $request->material_code !== '') {
            $template->material_code = $request->material_code;
        }
        if($request->has('part_number') && $request->part_number !== '') {
            $template->part_number = $request->part_number;
        }
        if($request->has('notes') && $request->notes !== '') {
            $template->notes = $request->notes;
        }
        $template->save();

        // Get existing items for comparison
        $existingItems = OutgoingGoodTemplateItem::where('code_template', $template->code_template)->get();
        $requestedMaterialCodes = collect($request->items)->pluck('material_code')->toArray();

        // Delete items that are not in the request
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->material_code, $requestedMaterialCodes)) {
                $existingItem->delete();
            }
        }

        // update for items
        foreach ($request->items as $item) {
            $material = Material::where('code', $item['material_code'])->first();

            if (!$material) {
                continue; // or handle as error
            }

            $templateItem = OutgoingGoodTemplateItem::where('code_template', $template->code_template)
                ->where('material_code', $item['material_code'])
                ->first();

            if($templateItem) {
                $templateItem->part_number = $item['part_number'] ?? $templateItem->part_number;
                $templateItem->alias = $item['alias'] ?? $templateItem->alias;
                $templateItem->quantity_needed = $item['quantity_needed'] ?? $templateItem->quantity_needed;
                $templateItem->uom_needed = $material->unit ?? $templateItem->uom_needed;
                $templateItem->save();
            } else {
                $templateItem = new OutgoingGoodTemplateItem();
                $templateItem->code_template = $template->code_template;
                $templateItem->part_number = $item['part_number'] ?? '';
                $templateItem->alias = $item['alias'] ?? '';
                $templateItem->material_code = $item['material_code'];
                $templateItem->material_name = $material->description;
                $templateItem->quantity_needed = $item['quantity_needed'] ?? 0;
                $templateItem->uom_needed = $material->unit ?? '';
                $templateItem->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully'
        ]);
    }

    // /**
    //  * Delete a template
    //  */
    public function deleteTemplate($id)
    {
        $template = OutgoingGoodTemplate::findOrFail($id);

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    }
}

