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

            $query = $query->with('material', 'StockSlockDetails', 'StockSlockDetails.RackDetails', 'UserCreatedBy', 'UserUpdatedBy', 'UserOutBy');

            if ($request->has('material_code') && $request->material_code != '') {
                $query->where('material_code', 'LIKE', '%' . $request->material_code . '%');
            }
            if ($request->has('job_seq') && $request->job_seq != '') {
                $query->where('job_seq', 'LIKE', '%' . $request->job_seq . '%');
            }
            if ($request->has('status') && $request->status != '') {
                $query->where('status',  'like', $request->status);
            }
            if ($request->has('start_date') && $request->start_date != '') {
                $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            }
            if ($request->has('end_date') && $request->end_date != '') {
                $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
            }
            if ($request->has('loc_in') && $request->loc_in != '') {
                $query->where('loc_in', 'LIKE', '%' . $request->loc_in . '%');
            }

            if ($request->has('material_name') && $request->material_name != '') {
                $query->whereHas('material', function ($q) use ($request) {
                    $q->where('description', 'regexp', new \MongoDB\BSON\Regex($request->material_name, 'i'));
                });
            }

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
            $WhsControl->uom = $request->uom;
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
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function outWhsMaterial(Request $request, $job_seq = null)
    {
        try {
            $request->validate([
                'loc_out_to' => 'required|string',
                'note' => 'nullable|string',
                'remaining_stock' => 'nullable|numeric|min:0',
            ]);
            if ($job_seq != null) {
                $whsMatControl = WhsMaterialControl::where('job_seq', $job_seq)->first();
                if (!$whsMatControl) {
                    return response()->json([
                        'message' => 'Material not found',
                        'data' => null
                    ], 404);
                }

                if ($whsMatControl->status == WhsMaterialControl::STATUS_OUT) {
                    return response()->json([
                        'message' => 'Material already out',
                        'data' => null
                    ], 400);
                }

                $whsMatControl->loc_out_to = $request->loc_out_to;
                $whsMatControl->out_at = Carbon::now();
                $whsMatControl->stock_out = $request->stock_out;
                $whsMatControl->status = WhsMaterialControl::STATUS_OUT;
                $whsMatControl->out_by = auth()->user()->npk;
                $whsMatControl->out_note = $request->note;
                $whsMatControl->updated_by = auth()->user()->npk;
                $whsMatControl->save();
            }

            $newLabelData = null;

            if (!empty($request->remaining_stock) && $request->remaining_stock > 0) {
                $inRequest = new Request([
                    'material_code' => $whsMatControl->material_code,
                    'loc_in' => $whsMatControl->loc_in,
                    'uom' => $whsMatControl->uom,
                    'stock' => $request->remaining_stock,
                    'note' => $request->note,
                    'tag' => $whsMatControl->tag,
                ]);

                $newInResult = $this->inWhsMaterial($inRequest);

                if ($newInResult->status() === 200 || $newInResult->status() === 201) {
                    $newLabelData = $newInResult->original['data'];
                } else {
                    return response()->json([
                        'message' => 'failed',
                        'error' => $newInResult->original['error']
                    ], 500);
                }
            }

            return response()->json([
                'message' => 'success',
                'data' => $whsMatControl ?? $newLabelData,
                'new_label' => $newLabelData
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
