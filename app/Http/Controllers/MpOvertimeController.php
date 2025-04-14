<?php

namespace App\Http\Controllers;

use App\Exports\MpOvertimeExport;
use App\MpOvertime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as Pdf;

class MpOvertimeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MpOvertime::query();

            if ($request->has('start_date') && $request->has('end_date')) {
                $start = Carbon::parse($request->start_date)->format('Y-m-d');
                $end = Carbon::parse($request->end_date)->format('Y-m-d');

                $query->whereBetween('date', [$start, $end]);
            }

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

            // Return as simple collection
            $overtimes = $query->get([
                'department_id',
                'dept_code',
                'date',
                'shift_code',
                'place_code',
                'total_mp'
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $overtimes
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
            $isGuest = auth()->guest();
            $rules = [
                'dept_code'     => 'required|string',
                'date'          => 'required|date',
                'shift_code'    => 'required|string',
                'place_code'    => 'required|string',
                'total_mp'      => 'required|integer|min:0',
                'created_by'    => $isGuest ? 'required|string' : 'nullable|string',
                // 'updated_by'    => $isGuest ? 'required|string' : 'nullable|string',
            ];

            $validated = $request->validate($rules);

            // Format the date
            $validated['date'] = Carbon::parse($validated['date'])->format('Y-m-d');

            $mpOvertime = MpOvertime::create($validated);

            return response()->json([
                'message' => 'created',
                'data' => $mpOvertime
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
            $validated = $request->validate([
                'department_id' => 'required|string',
                'dept_code'     => 'required|string',
                'date'          => 'required|date',
                'shift_code'    => 'required|string',
                'place_code'    => 'required|string',
                'total_mp'      => 'required|integer|min:0',
            ]);

            $validated['date'] = Carbon::parse($validated['date'])->format('Y-m-d');

            $mpOvertime = MpOvertime::findOrFail($id);
            $mpOvertime->update($validated);

            return response()->json([
                'message' => 'updated',
                'data' => $mpOvertime
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
            $mpOvertime = MpOvertime::findOrFail($id);
            $mpOvertime->delete();

            return response()->json([
                'message' => 'deleted',
                'data' => $mpOvertime
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function print(Request $request)
    {
        try {
            $query = MpOvertime::query();

            if ($request->has('start_date') && $request->has('end_date')) {
                $start = \Carbon\Carbon::parse($request->start_date)->format('Y-m-d');
                $end = \Carbon\Carbon::parse($request->end_date)->format('Y-m-d');
                $query->whereBetween('date', [$start, $end]);
            }

            $data = $query->get([
                'dept_code',
                'date',
                'shift_code',
                'place_code',
                'total_mp'
            ]);

            $filename = 'mp_overtime_report_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new MpOvertimeExport($data), $filename);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function printPdf(Request $request)
    {
        try {
            $query = MpOvertime::query();

            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');

            $query->whereBetween('date', [$start, $end]);

            $data = $query->get([
                'dept_code',
                'date',
                'shift_code',
                'place_code',
                'total_mp'
            ]);

            $pdf = Pdf::loadView('exports.mp_overtimes', [
                'overtimes' => $data,
                'start_date' => $start,
                'end_date' => $end
            ]);

            return $pdf->stream("mp_overtime_report_{$start}_to_{$end}.pdf");
            // Or: return $pdf->download("mp_overtime_report.pdf");
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
