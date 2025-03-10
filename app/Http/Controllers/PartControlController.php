<?php

namespace App\Http\Controllers;

use App\Part;
use App\PartControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Connection as MongoConnection;


class PartControlController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = PartControl::query();

            if ($request->has('part_code')) {
                $query->where('part_code', 'like', '%' . $request->part_code . '%');
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // if ($request->has('search')) {
            //     $searchTerm = $request->search;
            //     $query->where(function ($query) use ($searchTerm) {
            //         $query->where('part_code', 'like', '%' . $searchTerm . '%')
            //             ->orWhere('note', 'like', '%' . $searchTerm . '%');
            //     });
            // }

            $query = $query->orderBy('created_at', 'desc');

            $partControls = $query->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $partControls
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
            $partControl = PartControl::findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $partControl
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function inPart(Request $request)
    {
        try {
            $request->validate([
                'part_code' => 'required|string',
                'count' => 'required|integer',
                'note' => 'nullable|string',
            ]);
            $Part = Part::where('code', $request->part_code)->first();
            if (!$Part) {
                return response()->json([
                    'message' => 'failed',
                    'error' => 'Part not found'
                ], 404);
            }

            $count = $request->count;
            if ($count <= 0) {
                return response()->json([
                    'message' => 'failed',
                    'error' => 'Count must be greater than 0'
                ], 400);
            }

            $lastSeqNo = PartControl::where('part_code', $request->part_code)
                ->max('seq_no');

            $nextSeqNo = $lastSeqNo ? $lastSeqNo + 1 : 1;

            $parts = [];

            for ($i = 0; $i < $count; $i++) {
                $jobSeq = $Part->code . '-' . $nextSeqNo;
                $partControl = new PartControl([
                    'part_code' => $request->part_code,
                    'seq_no' => $nextSeqNo,
                    'job_seq' => $jobSeq,
                    'qr_code' => PartControl::generateNewQRCode($jobSeq),
                    'in_at' => Carbon::now()->toDateTimeString(),
                    'status' => 'IN',
                    'note' => $request->note,
                    'created_by' => auth()->user()->npk,
                ]);

                $partControl->save();
                $parts[] = $partControl;
                $nextSeqNo++;
            }

            return response()->json([
                'message' => 'success',
                'data' => $parts
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function printPdfLabel(Request $request)
    {
        try {
            $request->validate([
                'part_code' => 'required|string',
                'seq_no' => 'required|integer',
                'count' => 'required|integer',
            ]);

            $partControls = PartControl::where('part_code', $request->part_code)
                ->where('seq_no', $request->seq_no)
                ->limit($request->count)
                ->get();

            if ($partControls->isEmpty()) {
                return response()->json([
                    'message' => 'failed',
                    'error' => 'Part control not found'
                ], 404);
            }

            $pdf = \PDF::loadView('pdf.label', ['partControls' => $partControls]);
            return $pdf->download('label.pdf');
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
            $request->validate([
                'part_code' => 'required|string',
                'seq_no' => 'required|integer',
                'qr_code' => 'required|string',
                'in_at' => 'required|date',
                'out_at' => 'nullable|date',
                'status' => 'required|string',
                'note' => 'nullable|string',
                'created_by' => 'required|string',
                'updated_by' => 'nullable|string',
                'out_by' => 'nullable|string',
            ]);

            $partControl = PartControl::findOrFail($id);
            $partControl->update($request->all());
            return response()->json([
                'message' => 'success',
                'data' => $partControl
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
            $partControl = PartControl::findOrFail($id);
            $partControl->delete();
            return response()->json([
                'message' => 'success',
                'data' => $partControl
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
