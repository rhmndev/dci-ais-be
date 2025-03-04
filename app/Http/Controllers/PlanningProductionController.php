<?php

namespace App\Http\Controllers;

use App\PlanningProduction;
use Illuminate\Http\Request;

class PlanningProductionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);

            $planningProductions = PlanningProduction::query();

            if ($request->has('code')) {
                $planningProductions->where('code', $request->code);
            }

            if ($request->has('sortBy') && in_array(strtolower($request->sortOrder), ['asc', 'desc'])) {
                $planningProductions->orderBy($request->sortBy, strtolower($request->sortOrder));
            }

            $planningProductions = $planningProductions->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $planningProductions
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'part_code' => 'required',
                'target_quantity' => 'required',
                'total_hours' => 'required',
                'status' => 'required',
            ]);

            $planning = new PlanningProduction([
                'code' => PlanningProduction::generateNewCode(),
                'name' => $request->name,
                'part_code' => $request->part_code,
                'part_description' => $request->part_description ?? null,
                'target_quantity' => $request->target_quantity,
                'total_hours' => $request->total_hours,
                'status' => $request->status,
                'is_active' => $request->is_active ?? true
            ]);

            $planning->save();
            $planning->generateQRCode();

            return response()->json([
                'message' => 'success',
                'data' => $planning
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'part_code' => 'required',
                'target_quantity' => 'required',
                'total_hours' => 'required',
                'status' => 'required',
            ]);

            $planning = PlanningProduction::find($request->id);

            if (!$planning) {
                return response()->json([
                    'message' => 'failed',
                    'error' => 'Data not found'
                ], 404);
            }

            $planning->name = $request->name;
            $planning->part_code = $request->part_code;
            $planning->part_description = $request->part_description ?? null;
            $planning->target_quantity = $request->target_quantity;
            $planning->total_hours = $request->total_hours;
            $planning->status =            $request->status;

            $planning->save();
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
