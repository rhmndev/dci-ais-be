<?php

namespace App\Http\Controllers;

use App\Imports\StockSlockImport;
use App\StockSlock;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockSlockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $stockSlocks = StockSlock::query();

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
        $request->validate([
            'slock_code' => 'required|string',
            'rack_code' => 'required|string',
            'material_code' => 'required|string',
            'val_stock_value' => 'required|numeric',
            'valuated_stock' => 'required|numeric',
            'uom' => 'required|string',
        ]);

        $stockSlock = StockSlock::create($request->all());
        return response()->json($stockSlock, 201);
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
        $stockSlock->delete();
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
            StockSlock::create([
                'slock_code' => $request->slock_code,
                'rack_code' => $row[7] ?? null,
                'material_code' => $row[0],
                'val_stock_value' => $row[1],
                'valuated_stock' => $row[3],
                'uom' => $row[4],
                'take_in_at' => null,
                'take_out_at' => null,
                'user_id' => auth()->user()->npk
            ]);
        }
        return response()->json([
            'message' => 'File imported successfully',
        ], 200);
    }
}
