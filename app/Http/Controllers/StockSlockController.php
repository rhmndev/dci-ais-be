<?php

namespace App\Http\Controllers;

use App\Imports\StockSlockImport;
use App\StockSlock;
use App\StockSlocTakeOutTemp;
use App\StockSlockHistory;
use App\OutgoingGood;
use App\OutgoingGoodItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;

class StockSlockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $stockSlocks = StockSlock::query();

            
            $stockSlocks->with('material', 'RackDetails', 'WhsMatControl','CreatedBy');
            
            if ($request->has('slock_code')) {
                $stockSlocks->where('slock_code', $request->slock_code);
            }

            if ($request->has('rack_code')) {
                $stockSlocks->where('rack_code', $request->rack_code);
            } 

            if ($request->has('tag') && $request->tag != null) {
                $stockSlocks->where('tag', $request->tag);
            }

            if ($request->has('material_code')) {
                $stockSlocks->where('material_code', $request->material_code);
            }
            if ($request->has('s_material_code')) {
                $stockSlocks->where('material_code', 'like', '%'.$request->s_material_code.'%');
            }
            if ($request->has('s_job_seq')) {
                $stockSlocks->where('job_seq', 'like', '%'.$request->s_job_seq.'%');
            }
            // find by material description
            if ($request->has('s_material_desc')) {
                $stockSlocks->where('material.description', 'like', '%'.$request->s_material_desc.'%');
            }

            if ($request->has('s_description')) {
                $stockSlocks->whereHas('material', function($query) use ($request) {
                    $query->where('description', 'like', '%'.$request->s_description.'%');
                });
            }

            // pkg no
            if ($request->has('s_pkg_no')) {
                $stockSlocks->where('pkg_no', 'like', '%'.$request->s_pkg_no.'%');
            }

            // inventory no
            if ($request->has('s_inventory_no')) {
                $stockSlocks->where('inventory_no', 'like', '%'.$request->s_inventory_no.'%');
            }

            // slock code and rack code
            if ($request->has('s_slock_code')) {
                $stockSlocks->where('slock_code', 'like', '%'.$request->s_slock_code.'%');
            }

            if ($request->has('s_rack_code')) {
                $stockSlocks->where('rack_code', 'like', '%'.$request->s_rack_code.'%');
            }

            if($request->has('show_all') && $request->show_all === 'true') {
                $stockSlocks->where('slock_code', '!=', '000000000000000000000000');
            }

            $stockSlocks = $stockSlocks->get();
            return response()->json([
                'message' => 'success',
                'data' => $stockSlocks
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to retrieve stock slock',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'slock_code' => 'required|string',
                'rack_code' => 'required|string',
                'material_code' => 'required|string',
                'val_stock_value' => 'required|numeric',
                'valuated_stock' => 'required|numeric',
                'uom' => 'required|string',
                'date_income' => 'required|date',
                'time_income' => 'required|date_format:H:i',
                'tag' => 'required|string|in:ok,ng,hold',
            ]);

            // ✅ Explicitly convert val_stock_value & valuated_stock to float
            $stockSlock = StockSlock::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
                'material_code' => $request->material_code,
                'val_stock_value' => floatval($request->val_stock_value),
                'valuated_stock' => floatval($request->valuated_stock),
                'uom' => $request->uom,
                'tag' => $request->tag,
                'date_income' => $request->date_income ?? Carbon::now()->toDateString(),
                'time_income' => $request->time_income ?? Carbon::now()->toTimeString(),
                'take_in_at' => null,
                'take_out_at' => null,
                'last_time_take_in' => null,
                'last_time_take_out' => null,
                'user_id' => auth()->user()->npk
            ]);

            $stockSlockHistroy = StockSlockHistory::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
                'material_code' => $request->material_code,
                'val_stock_value' => $request->val_stock_value,
                'valuated_stock' => $request->valuated_stock,
                'uom' => $request->uom,
                'date_time' => Carbon::now()->toDateTimeString(),
                'scanned_by' => auth()->user()->npk,
                'status' => 'add',
                'date_income' => $stockSlock->date_income,
                'time_income' => Carbon::parse($stockSlock->time_income)->format('H:i'),
                'last_time_take_in' => $stockSlock->last_time_take_in,
                'last_time_take_out' => $stockSlock->last_time_take_out,
                'user_id' => auth()->user()->npk,
                'tag' => $request->tag,
                'is_success' => true
            ]);

            return response()->json([
                'message' => 'Stock successfully added!',
                'data' => $stockSlock
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to add stock.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(StockSlock $stockSlock)
    {
        return response()->json($stockSlock);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'slock_code' => 'nullable|string',
            'rack_code' => 'nullable|string',
            'material_code' => 'nullable|string',
            'val_stock_value' => 'nullable|numeric',
            'valuated_stock' => 'nullable|numeric',
            'uom' => 'nullable|string',
            'tag' => 'nullable|string|in:ok,ng,hold',
            'date_income' => 'nullable|date',
            'time_income' => 'nullable|date_format:H:i',
        ]);

        $stockSlock = StockSlock::findOrFail($id);

        // spesific update check exist request or not
        // if ($request->has('val_stock_value') && $request->val_stock_value !== null) {
        //     $stockSlock->val_stock_value = $request->val_stock_value;
        // }
        
        if ($request->has('valuated_stock') && $request->valuated_stock !== null) {
            $stockSlock->valuated_stock = floatval($request->valuated_stock);
        }

        // if ($request->has('uom') && $request->uom !== null) {
        //     $stockSlock->uom = $request->uom;
        // }

        if ($request->has('tag') && $request->tag !== null) {
            $stockSlock->tag = $request->tag;
        }

        if ($request->has('date_income') && $request->date_income !== null) {
            $stockSlock->date_income = $request->date_income;
        }

        if ($request->has('time_income') && $request->time_income !== null) {
            $stockSlock->time_income = $request->time_income;
        }
 
        if ($request->has('pkg_no') && $request->pkg_no !== null) {
            $stockSlock->pkg_no = $request->pkg_no;
        }

        if ($request->has('inventory_no') && $request->inventory_no !== null) {
            $stockSlock->inventory_no = $request->inventory_no;
        }

        $stockSlock->save();
        return response()->json([
            'message' => 'Stock slock has been updated',
            'data' => $stockSlock
        ], 200);
    }

    public function destroy($id)
    {
        $stockSlock = StockSlock::findOrFail($id);

        $stockSlockHistroy = StockSlockHistory::create([
            'slock_code' => $stockSlock->slock_code,
            'rack_code' => $stockSlock->rack_code,
            'material_code' => $stockSlock->material_code,
            'val_stock_value' => $stockSlock->val_stock_value,
            'valuated_stock' => $stockSlock->valuated_stock,
            'uom' => $stockSlock->uom,
            'date_time' => Carbon::now()->toDateTimeString(),
            'scanned_by' => auth()->user()->npk,
            'status' => 'delete',
            'date_income' => $stockSlock->date_income,
            'time_income' => Carbon::parse($stockSlock->time_income)->format('H:i'),
            'last_time_take_in' => $stockSlock->last_time_take_in,
            'last_time_take_out' => $stockSlock->last_time_take_out,
            'user_id' => auth()->user()->npk,
        ]);
        if ($stockSlock) {
            $stockSlock->delete();

            $stockSlockHistroy->update([
                'is_success' => true
            ]);

            return response()->json([
                'message' => 'Stock slock has been deleted',
            ], 200);
        }
        return response()->json(null, 204);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $data = Excel::toArray(new StockSlockImport, $request->file('file'));

        foreach ($data[0] as $key => $row) {
            if ($row[0] == null) {
                continue;
            }

            if ($key == 0) {
                continue;
            }
            $dateIncome = Date::excelToDateTimeObject($row[8])->format('Y-m-d');
            $timeIncome = Date::excelToDateTimeObject($row[9])->format('H:i:s');

            StockSlock::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $row[7] ?? null,
                'material_code' => $row[0],
                'val_stock_value' => $row[1],
                'valuated_stock' => $row[3],
                'uom' => $row[4],
                'date_income' => $dateIncome,
                'time_income' => Carbon::parse($timeIncome)->format('H:i'),
                'take_in_at' => null,
                'take_out_at' => null,
                'last_time_take_in' => null,
                'last_time_take_out' => null,
                'user_id' => auth()->user()->npk
            ]);
        }
        return response()->json([
            'message' => 'File imported successfully',
        ], 200);
    }

    public function takeOut(Request $request)
    {
        $request->validate([
            'job_seq' => 'required|string',
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'material_code' => 'required|string',
            // 'valuated_stock' => 'required|numeric',
            'stock' => 'required|numeric',
            'uom' => 'required|string',
            'take_location' => 'required|string',
            'outgoing_good_id' => 'required|exists:outgoing_goods,_id',
            'outgoing_good_item_id' => 'required|exists:outgoing_good_items,_id',
            'is_ng_item' => 'required|boolean',
            'ng_job_seq' => 'required_if:is_ng_item,true|string|nullable',
            'ng_quantity' => 'required_if:is_ng_item,true|numeric|nullable',
        ]);

        // Additional validation for NG items
        if ($request->is_ng_item) {
            $request->validate([
                'ng_job_seq' => 'required|string',
                'ng_quantity' => 'required|numeric',
            ]);
        }

        $stockSlock = StockSlock::where('slock_code', $request->slock_code)
            ->where('rack_code', $request->rack_code)
            ->where('material_code', $request->material_code)
            ->where('uom', $request->uom)
            ->where('job_seq', $request->job_seq)
            ->first();

        if (!$stockSlock) {
            return response()->json(['error' => 'Stock slock not found'], 404);
        }

        if (floatval($stockSlock->valuated_stock) < floatval($request->stock)) {
            return response()->json(['error' => 'Stock slock is not enough', 'data' => $stockSlock, 'stock' => $request->stock], 400);
        }

        $remainingStock = (float)$stockSlock->valuated_stock - (float)$request->stock;

        $logStockSlock = StockSlockHistory::create([
            'slock_code' => $request->slock_code,
            'rack_code' => $request->rack_code,
            'job_seq' => $request->is_ng_item ? $request->ng_job_seq : $stockSlock->job_seq,
            'material_code' => $request->material_code,
            'val_stock_value' => $stockSlock->val_stock_value,
            'valuated_stock' => $remainingStock,
            'stock' => floatval($request->stock),
            'uom' => $request->uom,
            'date_time' => Carbon::now()->toDateTimeString(),
            'scanned_by' => auth()->user()->npk,
            'status' => $request->is_ng_item ? 'take_out_ng' : 'take_out',
            'inventory_no' => $stockSlock->inventory_no,
            'date_income' => $stockSlock->date_income,
            'time_income' => Carbon::parse($stockSlock->time_income)->format('H:i'),
            'last_time_take_in' => $stockSlock->last_time_take_in,
            'last_time_take_out' => Carbon::now()->toDateTimeString(),
            'take_location' => $request->take_location,
            'user_id' => auth()->user()->npk,
            'is_success' => false,
            'is_ng_item' => $request->is_ng_item,
            'ng_job_seq' => $request->is_ng_item ? $request->ng_job_seq : null,
            'ng_quantity' => $request->is_ng_item ? floatval($request->ng_quantity) : null,
        ]);

        if ($remainingStock <= 0) {
            $stockSlock->update(['valuated_stock' => 0, 'last_time_take_out' => Carbon::now()->toDateTimeString()]);
        } else {
            $stockSlock->update(['valuated_stock' => $remainingStock, 'last_time_take_out' => Carbon::now()->toDateTimeString()]);
        }

        $logStockSlock->update(['is_success' => true]);

        $outWhsControl = new WhsMaterialControlController();
        $request->merge([
            'material_code' => $request->material_code,
            'loc_out_to' => $request->take_location,
            'uom' => $request->uom,
            'stock_out' => floatval($request->stock),
            'note' => $request->note ?? null,
            'is_ng_item' => $request->is_ng_item,
            'ng_job_seq' => $request->is_ng_item ? $request->ng_job_seq : null,
            'ng_quantity' => $request->is_ng_item ? floatval($request->ng_quantity) : null,
        ]);
        $resWhsOut = $outWhsControl->outWhsMaterial($request, $request->is_ng_item ? $request->ng_job_seq : $stockSlock->job_seq ?? '');

        if (isset($resWhsOut->original['message']) && $resWhsOut->original['message'] === 'success') {
            $jobSeq = $resWhsOut->original['data']->job_seq ?? null;

            if ($jobSeq) {
                $logStockSlock->update([
                    'job_seq' => $jobSeq,
                    'is_success' => true
                ]);

                // Update StockSlocTakeOutTemp status to finished
                StockSlocTakeOutTemp::where('job_seq', $request->is_ng_item ? $request->ng_job_seq : $stockSlock->job_seq)
                    ->where('material_code', $request->material_code)
                    ->where('sloc_code', $request->slock_code)
                    ->update([
                        'status' => 'finished',
                        'is_success' => true,
                        'updated_at' => Carbon::now()
                    ]);

                // Add scanned data to OutgoingGoodItem
                $outgoingGoodItem = OutgoingGoodItem::find($request->outgoing_good_item_id);
                if ($outgoingGoodItem) {
                    $scans = $outgoingGoodItem->scans ?? [];
                    $scans[] = [
                        'job_seq' => $request->is_ng_item ? $request->ng_job_seq : $stockSlock->job_seq,
                        'rack_code' => $request->rack_code,
                        'quantity' => floatval($request->stock),
                        'uom' => $request->uom,
                        'scanned_at' => Carbon::now()->toDateTimeString(),
                        'scanned_by' => auth()->user()->npk,
                        'is_ng_item' => $request->is_ng_item,
                        'ng_job_seq' => $request->is_ng_item ? $request->ng_job_seq : null,
                        'ng_quantity' => $request->is_ng_item ? floatval($request->ng_quantity) : null,
                    ];
                    $outgoingGoodItem->scans = $scans;
                    $outgoingGoodItem->quantity_out += floatval($request->stock);
                    $outgoingGoodItem->save();
                }

                $outgoingGood = OutgoingGood::find($request->outgoing_good_id);
                if ($outgoingGood) {
                    $items = $outgoingGood->items()->get();
                
                    // If any item has quantity_out > 0 and status is 'ready', set to 'on_progress'
                    $anyScanned = $items->contains(function ($item) {
                        return $item->quantity_out > 0;
                    });

                    if ($anyScanned && $outgoingGood->status === 'ready') {
                        $outgoingGood->status = 'on_progress';
                        $outgoingGood->save();
                    }
                
                    $allItemsScanned = $items->every(function ($item) {
                        return $item->quantity_out >= $item->quantity_needed;
                    });
                    if ($allItemsScanned && ($outgoingGood->status !== 'waiting_tp' || $outgoingGood->status !== 'completed')) {
                        $outgoingGood->status = 'waiting_tp';
                        $outgoingGood->completed_at = Carbon::now()->toDateTimeString();
                        $outgoingGood->completed_by = auth()->user()->username;
                        $outgoingGood->is_completed = true;
                        $outgoingGood->save();
                    }
                }
            } else {
                $logStockSlock->update([
                    'is_success' => false
                ]);
            }
        } else {
            // Handle failure case
            return response()->json([
                'error' => 'Failed to take out stock slock',
                'data' => $resWhsOut,
                'message' => $resWhsOut->original['error'] ?? 'Unknown error',
            ], 400);
        }
        $stockSlock->load('material', 'WhsMatControl');

        $dataTempStockSlock = $stockSlock;

        if ($remainingStock <= 0) {
            $stockSlock->delete();
        }

        return response()->json(['message' => 'Stock has been taken out successfully', 'data' => $dataTempStockSlock], 200);
    }

    public function putIn(Request $request)
    {
        $request->validate([
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'date_income' => 'required|date',
            'time_income' => 'required|date_format:H:i',
            'material_code' => 'required|string',
            'valuated_stock' => 'required|numeric',
            'uom' => 'required|string',
            'tag' => 'required|string|in:ok,ng,hold',
            'note' => 'nullable|string',
        ]);

        try {
            // start mongodb transaction
            $session = DB::connection('mongodb')->getMongoClient()->startSession();
            $session->startTransaction();

            // if slock_code is RAW01, then check for not duplicate for pkg_no and inventory_no
            if ($request->slock_code === 'RAW01') {
                // Only check for duplicates if both inventory_no and pkg_no are provided
                if ($request->has('inventory_no') && $request->inventory_no !== null &&
                    $request->has('pkg_no') && $request->pkg_no !== null) {
                    
                    $duplicate = StockSlock::where('slock_code', $request->slock_code)
                        ->where('material_code', $request->material_code)
                        ->where(function($query) use ($request) {
                            $query->where('inventory_no', $request->inventory_no)
                                  ->where('pkg_no', $request->pkg_no);
                        })
                        ->first();

                    if ($duplicate) {
                        $errorMessage = 'Duplicate found: ';
                        if ($duplicate->inventory_no === $request->inventory_no) {
                            $errorMessage .= 'This inventory number is already in use';
                        }
                        if ($duplicate->pkg_no === $request->pkg_no) {
                            $errorMessage .= ($duplicate->inventory_no === $request->inventory_no ? ' and ' : '') . 
                                           'This package number is already in use';
                        }

                        return response()->json([
                            'error' => $errorMessage,
                            'data' => $duplicate,
                            'message' => $errorMessage,
                        ], 400);
                    }
                }
            }

            $stockSlock = StockSlock::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
                'inventory_no' => $request->inventory_no ?? null,
                'material_code' => $request->material_code,
                'valuated_stock' => floatval($request->valuated_stock),
                'uom' => $request->uom,
                'tag' => $request->tag,
                'pkg_no' => $request->pkg_no ?? null,
                'note' => $request->note ?? null,
                'user_id' => auth()->user()->npk
            ]);

            $stockSlock->update([
                'date_income' => $request->date_income ?? Carbon::now()->toDateString(),
                'time_income' => $request->time_income ?? Carbon::now()->toTimeString(),
                'take_out_at' => null,
                'take_in_at' => Carbon::now()->toDateTimeString(),
                'last_time_take_in' => Carbon::now()->toDateTimeString(),
            ]);

            $logStockSlock = StockSlockHistory::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
                'material_code' => $request->material_code,
                'val_stock_value' => floatval($request->val_stock_value),
                'valuated_stock' => floatval($request->valuated_stock),
                'uom' => $request->uom,
                'date_time' => Carbon::now()->toDateTimeString(),
                'scanned_by' => auth()->user()->npk,
                'status' => 'put_in',
                'inventory_no' => $stockSlock->inventory_no,
                'date_income' => $stockSlock->date_income,
                'time_income' => Carbon::parse($stockSlock->time_income)->format('H:i'),
                'last_time_take_in' => $stockSlock->last_time_take_in,
                'last_time_take_out' => $stockSlock->last_time_take_out,
                'user_id' => auth()->user()->npk,
                'tag' => $request->tag,
                'note' => $request->note ?? null,
                'is_success' => true
            ]);

            $request->merge([
                'material_code' => $request->material_code,
                'loc_in' => $request->slock_code,
                'uom' => $request->uom,
                'stock' => floatval($request->valuated_stock),
                'note' => $request->note ?? null,
            ]);

            $inWhsMaterial = new WhsMaterialControlController();
            $resWhsIn = $inWhsMaterial->inWhsMaterial($request);

            if (isset($resWhsIn->original['message']) && $resWhsIn->original['message'] === 'success') {
                $jobSeq = $resWhsIn->original['data']->job_seq ?? null; // Extract job_seq if available

                if ($jobSeq) {
                    $stockSlock->update([
                        'job_seq' => $jobSeq,
                    ]);
                    $logStockSlock->update([
                        'job_seq' => $jobSeq,
                        'is_success' => true
                    ]);
                    $session->commitTransaction();

                    $stockSlock->load('material', 'WhsMatControl','CreatedBy');

                    return response()->json([
                        'message' => 'Stock successfully put in!',
                        'data' => $stockSlock,
                        'job_seq' => $jobSeq
                    ], 201);
                } else {
                    $stockSlock->update([
                        'job_seq' => null,
                        'is_success' => false
                    ]);
                    $logStockSlock->update([
                        'is_success' => false
                    ]);
                    $stockSlock->load('material', 'WhsMatControl','CreatedBy');
                    $session->abortTransaction();

                    return response()->json([
                        'message' => 'Stock successfully put in, but job_seq not found!',
                        'data' => $stockSlock,
                    ], 201);
                }
            } else {
                // Handle failure case
                $session->abortTransaction();
                return response()->json([
                    'error' => 'Failed to put in stock slock',
                    'data' => $resWhsIn,
                    'message' => $resWhsIn->original['error'] ?? 'Unknown error',
                ], 400);
            }
        } catch (\Throwable $th) {
            // Rollback transaction in case of error
            $session->abortTransaction();
            return response()->json([
                'error' => 'Failed to put in stock slock',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function moveStock(Request $request)
    {
        $request->validate([
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'id' => 'required|string',
        ]);

        $session = null;
        try {
            // Start MongoDB session
            $session = DB::connection('mongodb')->getMongoClient()->startSession();
            $session->startTransaction();

            $stockSlock = StockSlock::where('_id', $request->id)->first();

            if (!$stockSlock) {
                $session->abortTransaction();
                return response()->json(['error' => 'Stock slock not found'], 404);
            }

            // check rack code is already used
            $rackCode = StockSlock::where('rack_code', $request->rack_code)
                ->where('slock_code', $request->slock_code)
                ->first();
                
            if ($rackCode) {
                $session->abortTransaction();
                return response()->json(['error' => 'Rack code already used'], 400);
            }

            // Update within transaction
            $stockSlock->update([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
            ]);
            
            // Commit transaction
            $session->commitTransaction();

            return response()->json(['message' => 'Stock slock moved successfully'], 200);
        } catch (\Throwable $th) {
            // Rollback transaction if it exists
            if ($session) {
                $session->abortTransaction();
            }
            
            return response()->json([
                'error' => 'Failed to move stock slock',
                'message' => $th->getMessage()
            ], 500);
        } finally {
            // Always end the session
            if ($session) {
                $session->endSession();
            }
        }
    }

    public function getStockByJobSeq(Request $request)
    {
        $request->validate([
            'job_seq' => 'required|string',
        ]);

        // adding try and catch and db transaction
        try {
            $session = DB::connection('mongodb')->getMongoClient()->startSession();
            $session->startTransaction();

            $stockSlock = StockSlock::where('job_seq', $request->job_seq)->first();

            $session->commitTransaction();

            return response()->json([
                'message' => 'success',
                'data' => $stockSlock
            ]);
            
        } catch (\Throwable $th) {
            $session->abortTransaction();
            return response()->json([
                'error' => 'Failed to get stock by job seq',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function getHistory(Request $request)
    {
        try {
            $stockSlockHistories = StockSlockHistory::query();

            $stockSlockHistories->with('material', 'UserCreateBy', 'UserActionBy', 'RackDetails');

            if ($request->has('slock_code')) {
                $stockSlockHistories->where('slock_code', $request->slock_code);
            }

            if ($request->has('rack_code')) {
                $stockSlockHistories->where('rack_code', $request->rack_code);
            }

            if ($request->has('material_code')) {
                $stockSlockHistories->where('material_code', $request->material_code);
            }

            // ✅ Get orderBy & order parameters (default: created_at, desc)
            $orderBy = $request->query('orderBy', 'created_at'); // Default sorting column
            $order = $request->query('order', 'desc'); // Default sorting direction

            // ✅ Validate order direction (only allow asc or desc)
            $order = in_array(strtolower($order), ['asc', 'desc']) ? $order : 'desc';

            // ✅ Apply sorting
            $stockSlockHistories->orderBy($orderBy, $order);

            if ($request->query('show_all') == 1) {
                $historyData = $stockSlockHistories->get();
            } elseif ($request->has('limit')) {
                $limit = (int) $request->query('limit', 10); // Default to 10
                $historyData = $stockSlockHistories->limit($limit)->get();
            } else {
                // ✅ Apply pagination if no limit or show_all is set
                $perPage = (int) $request->query('per_page', 10);
                $historyData = $stockSlockHistories->paginate($perPage);
            }

            return response()->json([
                'message' => 'success',
                'data' => $historyData
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to retrieve stock slock history',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function printToPdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // Ensure the end date is after or equal to start date
        ]);

        // Convert start and end dates to Carbon instances
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // Fetch the StockSlock records within the date range using MongoDB's whereBetween method
        $stockSlocks = StockSlock::with('material', 'RackDetails')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        if ($stockSlocks->isEmpty()) {
            return response()->json(['error' => 'No records found within the date range'], 404);
        }

        // Generate PDF
        $pdf = PDF::loadView('warehouse.stock_slock_pdf', ['stockSlocks' => $stockSlocks]);

        // Return the generated PDF
        return $pdf->download('stock_slock_report.pdf');
    }

    public function printHistoryStockSloc(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // Ensure the end date is after or equal to start date
        ]);

        // Convert start and end dates to Carbon instances
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // Fetch the StockSlockHistory records within the date range using MongoDB's whereBetween method
        $stockSlockHistories = StockSlockHistory::with('material', 'RackDetails', 'UserActionBy')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        if ($stockSlockHistories->isEmpty()) {
            return response()->json(['error' => 'No records found within the date range'], 404);
        }

        // Generate PDF
        $pdf = PDF::loadView('warehouse.stock_slock_history_pdf', ['stockSlockHistories' => $stockSlockHistories])->setPaper('a4', 'landscape');;

        // Return the generated PDF
        return $pdf->download('stock_slock_history_report.pdf');
    }

    public function getStockMaterialAvailable(Request $request)
    {
        $request->validate([
            'material_code' => 'required|string',
            'sloc_code' => 'nullable|string',
        ]);

        // Base query for stock slock records with tag 'ok' or 'OK'
        $stockSlockQuery = StockSlock::where('material_code', $request->material_code)
            ->where(function($query) {
                $query->where('tag', 'ok')
                      ->orWhere('tag', 'OK');
            });
        
        // Add sloc_code condition only if it's provided
        if ($request->has('sloc_code') && $request->sloc_code) {
            $stockSlockQuery->where('slock_code', $request->sloc_code);
        }

        $stockSlock = $stockSlockQuery->get();

        // Base query for active take out temp records
        $activeTakeOutTempsQuery = StockSlocTakeOutTemp::where('material_code', $request->material_code)
            ->whereNotIn('status', ['cancelled', 'finished']);
        
        // Add sloc_code condition only if it's provided
        if ($request->has('sloc_code') && $request->sloc_code) {
            $activeTakeOutTempsQuery->where('sloc_code', $request->sloc_code);
        }

        $activeTakeOutTemps = $activeTakeOutTempsQuery->get();

        // Create a map of material_code and sloc_code to track reserved quantities
        $reservedQuantities = [];
        foreach ($activeTakeOutTemps as $temp) {
            $key = $temp->material_code . '_' . $temp->job_seq;
            if (!isset($reservedQuantities[$key])) {
                $reservedQuantities[$key] = 0;
            }
            $reservedQuantities[$key] += $temp->qty_take_out;
        }
        // return response()->json([
        //     'message' => 'failed',
        //     'data' => $reservedQuantities,
        //     'activeTakeOutTemps' => $activeTakeOutTemps,
        //     'stockSlock' => $stockSlock
        // ], 400);


        // Process each stock slock record
        $stockSlock->each(function ($item) use ($reservedQuantities) {
            $key = $item->material_code . '_' . $item->job_seq;
            $reservedQty = $reservedQuantities[$key] ?? 0;

            // Calculate available quantity by subtracting reserved quantity
            $item->available_qty = max(0, $item->valuated_stock - $reservedQty);
            
            // Set default values for other fields
            // $item->rack_code = $item->rack_code ?? '-';
            // $item->job_seq = $item->job_seq ?? '-';
            // $item->date_income = $item->date_income ?? '-';
            // $item->time_income = $item->time_income ?? '-';
        });



        return response()->json([
            'message' => 'success',
            'data' => $stockSlock
        ]);
    }
}
