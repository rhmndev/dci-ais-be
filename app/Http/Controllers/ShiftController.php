<?php

namespace App\Http\Controllers;

use App\Shift;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ShiftImport;
use App\Exports\ShiftExport;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Shift::query();

            if ($request->has('code')) {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('search') && $request->search != '') {
                $searchTerm = $request->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('name', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('show_all') && $request->show_all) {
                $shifts = $query->get();
            } else {
                $perPage = $request->get('per_page', 15);
                $shifts = $query->paginate($perPage);
            }

            return response()->json([
                'message' => 'success',
                'data' => $shifts
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        try {
            $shifts = Shift::all([
                'code',
                'name',
                'alias',
                'start_time',
                'end_time',
            ])->sortBy('code')->values()->all();

            return response()->json([
                'message' => 'success',
                'data' => $shifts
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $shift = Shift::findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $shift
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
                'code' => 'required',
                'name' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
            ]);

            $shift = Shift::updateOrCreate(
                ['code' => $request->code],
                [
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]
            );

            $shift->created_by = auth()->user()->npk;
            $shift->updated_by = auth()->user()->npk;
            $shift->save();

            return response()->json([
                'message' => 'success',
                'data' => $shift
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $shift = Shift::findOrFail($id);
            $shift->update($request->all());

            $shift->updated_by = auth()->user()->npk;
            $shift->save();

            return response()->json([
                'message' => 'success',
                'data' => $shift
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $shift = Shift::findOrFail($id);
            $shift->delete();

            return response()->json([
                'message' => 'success',
                'data' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $Excels = Excel::toArray(new ShiftImport, $file);

            foreach ($Excels[0] as $row) {
                Shift::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'alias' => $row['alias'] ?? null,
                        'start_time' => $row['start_time'],
                        'end_time' => $row['end_time'],
                    ]
                );
            }

            return response()->json([
                'message' => 'Shifts imported successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $selectedShifts = $request->selectedShifts;

        $shiftQuery = Shift::query();

        if ($selectedShifts && $selectedShifts !== 0) {
            $shiftQuery->whereIn('_id', $selectedShifts);
        }

        $shifts = $shiftQuery->get();

        return Excel::download(new ShiftExport($shifts), 'shift_export.xlsx');
    }
}
