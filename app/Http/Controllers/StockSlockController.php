<?php

namespace App\Http\Controllers;

use App\Imports\StockSlockImport;
use App\StockSlock;
use App\StockSlockHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StockSlockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $stockSlocks = StockSlock::query();

            $stockSlocks->with('material');

            if ($request->has('slock_code')) {
                $stockSlocks->where('slock_code', $request->slock_code);
            }

            if ($request->has('rack_code')) {
                $stockSlocks->where('rack_code', $request->rack_code);
            }

            if ($request->has('material_code')) {
                $stockSlocks->where('material_code', $request->material_code);
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
            ]);

            // ✅ Explicitly convert val_stock_value & valuated_stock to float
            $stockSlock = StockSlock::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
                'material_code' => $request->material_code,
                'val_stock_value' => floatval($request->val_stock_value),
                'valuated_stock' => floatval($request->valuated_stock),
                'uom' => $request->uom,
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
                'time_income' => $stockSlock->time_income,
                'last_time_take_in' => $stockSlock->last_time_take_in,
                'last_time_take_out' => $stockSlock->last_time_take_out,
                'user_id' => auth()->user()->npk,
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

    public function update(Request $request, StockSlock $stockSlock)
    {
        $request->validate([
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'material_code' => 'required|string',
            'val_stock_value' => 'required|numeric',
            'valuated_stock' => 'required|numeric',
            'uom' => 'required|string',
        ]);

        $stockSlock->update($request->all());
        return response()->json($stockSlock);
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
            'time_income' => $stockSlock->time_income,
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
                'time_income' => $timeIncome,
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
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'material_code' => 'required|string',
            'valuated_stock' => 'required|numeric',
            'stock' => 'required|numeric|max:valuated_stock',
            'uom' => 'required|string',
            'take_location' => 'required|string',
        ]);

        $stockSlock = StockSlock::where('slock_code', $request->slock_code)
            ->where('rack_code', $request->rack_code)
            ->where('material_code', $request->material_code)
            ->where('uom', $request->uom)
            ->first();

        if (!$stockSlock) {
            return response()->json(['error' => 'Stock slock not found'], 404);
        }

        if ($stockSlock->valuated_stock < $request->stock) {
            return response()->json(['error' => 'Stock slock is not enough'], 400);
        }

        $remainingStock = $stockSlock->valuated_stock - $request->stock;


        $logStockSlock = StockSlockHistory::create([
            'slock_code' => $request->slock_code,
            'rack_code' => $request->rack_code,
            'material_code' => $request->material_code,
            'val_stock_value' => $stockSlock->val_stock_value,
            'valuated_stock' => $stockSlock->valuated_stock,
            'stock' => floatval($request->stock),
            'uom' => $request->uom,
            'date_time' => Carbon::now()->toDateTimeString(),
            'scanned_by' => auth()->user()->npk,
            'status' => 'take_out',
            'date_income' => $stockSlock->date_income,
            'time_income' => $stockSlock->time_income,
            'last_time_take_in' => $stockSlock->last_time_take_in,
            'last_time_take_out' => Carbon::now()->toDateTimeString(),
            'take_location' => $request->take_location,
            'user_id' => auth()->user()->npk,
            'is_success' => false
        ]);

        if ($remainingStock <= 0) {
            $stockSlock->delete();
        } else {
            $stockSlock->update(['valuated_stock' => $remainingStock, 'last_time_take_out' => Carbon::now()->toDateTimeString()]);
        }

        $logStockSlock->update(['is_success' => true]);

        return response()->json(['message' => 'Stock has been taken out successfully'], 200);
    }

    public function putIn(Request $request)
    {
        $request->validate([
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'date_income' => 'required|date',
            'time_income' => 'required|date_format:H:i',
            'material_code' => 'required|string',
            // 'val_stock_value' => 'required|numeric',
            'valuated_stock' => 'required|numeric',
            'uom' => 'required|string',
        ]);

        try {
            $stockSlock = StockSlock::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $request->rack_code,
                'material_code' => $request->material_code,
                // 'val_stock_value' => floatval($request->val_stock_value),
                'valuated_stock' => floatval($request->valuated_stock),
                'uom' => $request->uom,
                'user_id' => auth()->user()->npk
            ]);

            $stockSlock->update([
                'date_income' => Carbon::now()->toDateString(),
                'time_income' => Carbon::now()->toTimeString(),
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
                'date_income' => $stockSlock->date_income,
                'time_income' => $stockSlock->time_income,
                'last_time_take_in' => $stockSlock->last_time_take_in,
                'last_time_take_out' => $stockSlock->last_time_take_out,
                'user_id' => auth()->user()->npk,
                'is_success' => true
            ]);

            return response()->json([
                'message' => 'Stock slock has been put in',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to put in stock slock',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getHistory(Request $request)
    {
        try {
            $stockSlockHistories = StockSlockHistory::query();

            $stockSlockHistories->with('material');

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
}
