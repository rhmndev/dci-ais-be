<?php

namespace App\Http\Controllers;

use App\Material;
use App\WhsMaterialControl;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhsMaterialControlController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = WhsMaterialControl::query();

            $query = $query->with('material', 'UserCreatedBy', 'UserUpdatedBy', 'UserOutBy');

            if ($request->has('material_code')) {
                $query->where('material_code', 'like', '%' . $request->material_code . '%');
            }
            if ($request->has('job_seq')) {
                $query->where('job_seq', 'like', '%' . $request->job_seq . '%');
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            // if ($request->has('search')) {
            //     $searchTerm = $request->search;
            //     $query->where(function ($query) use ($searchTerm) {
            //         $query->where('part_code', 'like', '%' . $searchTerm . '%')
            //             ->orWhere('note', 'like', '%' . $searchTerm . '%')
            //             ->orWhere('job_seq', 'like', '%' . $searchTerm . '%');
            //     });
            // }

            $query = $query->orderBy('created_at', 'desc');

            $whsMatControls = $query->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $whsMatControls
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
            $whsMatControl = WhsMaterialControl::with('material', 'UserCreatedBy', 'UserUpdatedBy', 'UserOutBy')->findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $whsMatControl
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getSeqDetails($seq)
    {
        try {
            $whsMatControl = WhsMaterialControl::with('material', 'UserCreatedBy', '
            UserUpdatedBy', 'UserOutBy')->where('job_seq', $seq)->get();
            return response()->json([
                'message' => 'success',
                'data' => $whsMatControl
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    private function generateUniqueJobSeqNumber($yearMonthDate, $seq_no)
    {
        $startOfDay = Carbon::today()->startOfDay();
        $endOfDay = Carbon::today()->endOfDay();

        $lastSeqNoLabel = WhsMaterialControl::where('created_at', '>=', $startOfDay)
            ->where('created_at', '<=', $endOfDay)
            ->orderBy('job_seq', 'desc')
            ->first();

        if ($lastSeqNoLabel && strpos($lastSeqNoLabel->job_seq, $yearMonthDate) !== false) {
            $lastSeqNo = (int) substr($lastSeqNoLabel->job_seq, -4);
            $seq_no = $lastSeqNo + 1;
        } else {
            $seq_no = 1;
        }

        if ($seq_no > 9999) {
            return response()->json([
                'message' => 'failed',
                'error' => 'Sequence number has reached its limit'
            ], 400);
        }

        $seq_no = str_pad($seq_no, 4, '0', STR_PAD_LEFT);
        $JobSeqNumber = $yearMonthDate . $seq_no;

        return $JobSeqNumber;
    }

    public function inWhsMaterial(Request $request)
    {
        try {
            $request->validate([
                'material_code' => 'required|string',
                'loc_in' => 'required|string',
                'uom' => 'required|string',
                'stock' => 'required',
                'note' => 'nullable|string',
            ]);
            $whsMat = Material::where('code', $request->material_code)->first();
            if (!$whsMat) {
                return response()->json([
                    'message' => 'Material not found',
                    'data' => null
                ], 404);
            }

            $yearMonthDate = Carbon::now()->format('ymd');

            $lastSeqNo = WhsMaterialControl::where('material_code', $request->material_code)
                ->max('seq_no');
            $nextSeqNo = $lastSeqNo ? $lastSeqNo + 1 : 1;

            $JobSeqNumber = $this->generateUniqueJobSeqNumber($yearMonthDate, $nextSeqNo);
            $WhsControl = new WhsMaterialControl();
            $WhsControl->material_code = $request->material_code;
            $WhsControl->job_seq = $JobSeqNumber;
            $WhsControl->stock = (float) $request->stock;
            $WhsControl->note = $request->note;
            $WhsControl->status = 'IN';
            $WhsControl->qr_code = WhsMaterialControl::generateNewQRCode($JobSeqNumber);
            $WhsControl->tag = $request->tag ?? null;
            $WhsControl->seq_no = $nextSeqNo;
            $WhsControl->created_by = auth()->user()->npk;
            $WhsControl->updated_by = auth()->user()->npk;
            $WhsControl->loc_in = $request->loc_in ?? null;
            $WhsControl->in_at = Carbon::now();
            $WhsControl->save();

            return response()->json([
                'message' => 'success',
                'data' => $WhsControl
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
