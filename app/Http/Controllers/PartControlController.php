<?php

namespace App\Http\Controllers;

use App\Part;
use App\PartControl;
use App\PartStock;
use App\PartStockLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Connection as MongoConnection;
use Barryvdh\DomPDF\Facade as PDF;


class PartControlController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = PartControl::query();

            $query = $query->with('part', 'PartStock', 'UserUpdatedBy', 'UserCreatedBy', 'UserOutBy');

            if ($request->has('part_code')) {
                $query->where('part_code', 'like', '%' . $request->part_code . '%');
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('type')) {
                if ($request->type == 'IN' || $request->type == 'in') {
                    $query->where('status', PartControl::STATUS_IN);
                } else if ($request->type == 'OUT' || $request->type == 'out') {
                    $query->where('status', PartControl::STATUS_OUT);
                }
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                if ($searchTerm != null) {
                    $query->where(function ($query) use ($searchTerm) {
                        $query->where('part_code', 'like', '%' . $searchTerm . '%')
                            ->orWhere('job_seq', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('part', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', '%' . $searchTerm . '%');
                            });
                    });
                }
            }

            if ($request->has('in_at_start') && $request->has('in_at_end')) {
                $inAtStart = $request->in_at_start;
                $inAtEnd = $request->in_at_end;
                $query->whereBetween('in_at', [$inAtStart, $inAtEnd]);
            }

            // Date filter for Out At range
            if ($request->has('out_at_start') && $request->has('out_at_end')) {
                $outAtStart = $request->out_at_start;
                $outAtEnd = $request->out_at_end;
                $query->whereBetween('out_at', [$outAtStart, $outAtEnd]);
            }

            // adding orderBy request
            if ($request->has('order_by')) {
                $orderBy = $request->order_by;
                $query->orderBy($orderBy, 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

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

    public function getSeqDetails($seq)
    {
        try {
            $partControls = PartControl::with('part')->where('job_seq', $seq)
                ->get();

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

    private function generateUniqueJobSeqNumber($yearMonthDate, $seq_no)
    {
        $startOfDay = Carbon::today()->startOfDay();
        $endOfDay = Carbon::today()->endOfDay();

        // Fetch the last PartControl record for today (using where to filter by date range)
        $lastSeqNoLabel = PartControl::where('created_at', '>=', $startOfDay)
            ->where('created_at', '<=', $endOfDay)
            ->orderBy('job_seq', 'desc')
            ->first();

        // Generate the new sequence number based on the latest one
        if ($lastSeqNoLabel && strpos($lastSeqNoLabel->job_seq, $yearMonthDate) !== false) {
            // Extract the last 4 digits from the job_seq and increment it
            $lastSeqNo = (int) substr($lastSeqNoLabel->job_seq, -4);
            $seq_no = $lastSeqNo + 1;
        } else {
            // Start from 0001 if no sequence found
            $seq_no = 1;
        }

        // If sequence exceeds 9999, return an error
        if ($seq_no > 9999) {
            return response()->json([
                'message' => 'failed',
                'error' => 'Sequence number has reached its limit'
            ], 400);
        }

        // Format sequence number to have leading zeros (4 digits)
        $seq_no = str_pad($seq_no, 4, '0', STR_PAD_LEFT);

        $JobSeqNumber = $yearMonthDate . $seq_no;

        return $JobSeqNumber;
    }

    public function inPart(Request $request)
    {
        try {
            $request->validate([
                'part_code' => 'required|string',
                'count' => 'required|integer',
                'stock_in' => 'required',
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

            $stocksTotal = 0;

            for ($i = 0; $i < $count; $i++) {
                $jobSeq = $this->generateUniqueJobSeqNumber(Carbon::now()->format('ymd'), $nextSeqNo);
                $partControl = new PartControl([
                    'part_code' => $request->part_code,
                    'seq_no' => $nextSeqNo,
                    'job_seq' => $jobSeq,
                    'qr_code' => PartControl::generateNewQRCode($jobSeq),
                    'in_at' => Carbon::now()->toDateTimeString(),
                    'status' => PartControl::STATUS_IN,
                    'stock_in' => $request->stock_in,
                    'note' => $request->note,
                    'created_by' => auth()->user()->npk,
                ]);

                $partControl->part()->associate($Part);

                $partControl->save();
                $parts[] = $partControl;
                $stocksTotal += floatval($request->stock_in);

                // add part stock log
                $partStockLog = new PartStockLog([
                    'part_code' => $request->part_code,
                    'ref_job_seq' => $jobSeq,
                    'stock_change' => $request->stock_in,
                    'new_stock' => $stocksTotal,
                    'action' => PartControl::STATUS_IN,
                    'created_by' => auth()->user()->npk,
                ]);

                $partStockLog->PartControl()->associate($partControl);
                $partStockLog->save();

                $nextSeqNo++;
            }
            // update stock
            PartStock::updateIncreaseStock($request->part_code, $stocksTotal, auth()->user());

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

    public function outPart(Request $request)
    {
        try {
            $request->validate([
                'part_code' => 'required|string',
                'job_seq' => 'required|integer',
                'note' => 'nullable|string',
                'is_partially_out' => 'nullable',
                'out_stock' => 'required',
            ]);

            $partControlQuery = PartControl::where('job_seq', $request->job_seq);

            if (!$request->is_partially_out) {
                $partControlQuery->where('status', PartControl::STATUS_IN);
            }

            $partControl = $partControlQuery->first();

            if (is_null($partControl)) {
                return response()->json([
                    'message' => 'failed',
                    'error' => 'Part control need to be IN first',
                ], 404);
            }

            $partControl->update([
                'out_at' => Carbon::now()->toDateTimeString(),
                'status' => PartControl::STATUS_OUT,
                'is_out' => true,
                'out_by' => auth()->user()->npk,
                'out_to' => $request->out_target,
                'stock_out' => $request->out_stock,
                'out_note' => $request->note,
                'updated_by' => auth()->user()->npk,
            ]);

            $stocksTotal = floatval($request->out_stock ?? 0);

            $partStockLog = new PartStockLog([
                'part_code' => $request->part_code,
                'ref_job_seq' => $partControl->job_seq,
                'stock_change' => $request->out_stock,
                'new_stock' => $stocksTotal,
                'action' => PartControl::STATUS_OUT,
                'out_to' => $request->out_target,
                'note' => $request->note,
                'created_by' => auth()->user()->npk,
            ]);

            $partStockLog->PartControl()->associate($partControl);
            $partStockLog->save();

            // update stock
            PartStock::updateReduceStock($request->part_code, $request->out_stock, auth()->user(), $request->out_target);

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

    public function saveScanPart(Request $request)
    {
        try {
            $request->validate([
                'job_seq' => 'required|string',
                'status' => 'required|string',
                'scan_action' => 'required|string',
                'out_target' => 'nullable',
                'out_stock' => 'nullable',
                'note' => 'nullable|string',
            ]);


            if ($request->scan_action == 'OUT') {
                $checkPartIsOuted = PartControl::with('part', 'PartStock')->where('job_seq', $request->job_seq)
                    ->where('status', PartControl::STATUS_OUT)
                    ->latest('updated_at')
                    ->first();

                $is_partially_out = false;
                if ($checkPartIsOuted) {
                    if ($checkPartIsOuted->part->is_partially_out !== true) {

                        return response()->json([
                            'message' => 'failed',
                            'error' => 'Part control ' . $request->job_seq . ' already out'
                        ], 400);
                    }
                    $is_partially_out = $checkPartIsOuted->part->is_partially_out;
                    if ($request->out_stock > $checkPartIsOuted->PartStock->stock) {
                        return response()->json([
                            'message' => 'failed',
                            'error' => 'Out stock for part ' . $checkPartIsOuted->part->description . ' not enough'
                        ], 400);
                    }
                }

                $partControlQuery = PartControl::where('job_seq', $request->job_seq);

                if (!$is_partially_out) {
                    $partControlQuery->where('status', PartControl::STATUS_IN);
                }

                $partControl = $partControlQuery->first();

                if (is_null($partControl)) {
                    return response()->json([
                        'message' => 'failed',
                        'error' => 'Part control need to be IN first'
                    ], 404);
                }

                // add request part_code
                $request->merge([
                    'part_code' => $partControl->part_code,
                    'out_stock' => $request->out_stock ?? 1,
                    'is_partially_out' => $is_partially_out,
                    'out_target' => $request->out_target,
                    'note' => $request->note,
                ]);

                return $this->outPart($request);
            } else if ($request->scan_action == 'IN') {
                // $partControl = PartControl::where('job_seq', $request->job_seq)
                // ->where('status', PartControl::STATUS_OUT)
                // ->first();

                // if ($partControl->isEmpty()) {
                //     return response()->json([
                //         'message' => 'failed',
                //         'error' => 'Part control need to be OUT first'
                //     ], 404);
                // }

                // $this->inPart($request);
            } else {
                return response()->json([
                    'message' => 'failed',
                    'error' => 'Scan action not valid'
                ], 400);
            }
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

            if (is_null($partControls->isEmpty())) {
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

    public function printPdf(Request $request)
    {
        try {
            $selectedData = $request->input('selectedData');
            $type = $request->input('type');
            $startDate = $request->input('start_date'); // Get start date from request
            $endDate = $request->input('end_date'); // Get end date from request

            if ($type == 'IN' || $type == 'in') {
                $typeData = PartControl::STATUS_IN;
            } else if ($type == 'OUT' || $type == 'out') {
                $typeData = PartControl::STATUS_OUT;
            } else {
                $typeData = null;
            }

            $query = PartStockLog::with('part', 'PartControl', 'UserCreatedBy')
                ->where('ref_job_seq', '!=', null)
                ->where('action', $typeData);

            if ($selectedData != 0) {
                $query->whereIn('ref_job_seq', $selectedData);
            }

            if ($startDate) {
                $startDate = Carbon::parse($startDate)->startOfDay(); // Start of the day for start_date
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $endDate = Carbon::parse($endDate)->endOfDay(); // End of the day for end_date
                $query->where('created_at', '<=', $endDate);
            }

            $partsStockLog = $query->orderBy('created_at', 'desc')->get();

            // If no parts were found, return a 404 response
            if ($partsStockLog->isEmpty()) {
                return response()->json(['message' => 'No parts stock found to print.'], 404);
            }

            $currentDate = Carbon::now()->toFormattedDateString(); // Get current date in a readable format
            $currentUser = auth()->user() ? auth()->user()->full_name : 'Guest'; // Get the current user's name

            // Generate the PDF
            $pdf = PDF::loadView('part-control.print', compact('partsStockLog', 'currentDate', 'currentUser'));

            // Return the PDF as a downloadable response
            return $pdf->stream('parts_report.pdf');
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

    public function getActivityData(Request $request)
    {
        try {
            // Default date range: today to 5 days before
            $endDate = $request->get('end_date', Carbon::today()->toDateString());
            $startDate = $request->get('start_date', Carbon::today()->subDays(5)->toDateString());

            // Convert dates to Carbon instances
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Query MongoDB to aggregate data
            $activityData = PartControl::raw(function ($collection) use ($start, $end) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'created_at' => [
                                '$gte' => new \MongoDB\BSON\UTCDateTime($start->timestamp * 1000),
                                '$lte' => new \MongoDB\BSON\UTCDateTime($end->timestamp * 1000),
                            ]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                '$dateToString' => [
                                    'format' => '%Y-%m-%d',
                                    'date' => '$created_at'
                                ]
                            ],
                            'in' => [
                                '$sum' => [
                                    '$cond' => [
                                        'if' => ['$eq' => ['$status', PartControl::STATUS_IN]],
                                        'then' => 1,
                                        'else' => 0
                                    ]
                                ]
                            ],
                            'out' => [
                                '$sum' => [
                                    '$cond' => [
                                        'if' => ['$eq' => ['$status', PartControl::STATUS_OUT]],
                                        'then' => 1,
                                        'else' => 0
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        '$sort' => ['_id' => 1] // Sort by date ascending
                    ]
                ]);
            });

            // Format the result
            $formattedData = [];
            foreach ($activityData as $data) {
                $formattedData[] = [
                    'date' => $data['_id'],
                    'in' => $data['in'],
                    'out' => $data['out']
                ];
            }

            return response()->json([
                'message' => 'success',
                'data' => $formattedData
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getPartStockLog(Request $request)
    {
        try {
            // Default date range: today to 5 days before
            // $endDate = $request->get('end_date', Carbon::today()->toDateString());
            // $startDate = $request->get('start_date', Carbon::today()->subDays(5)->toDateString());

            // // Convert dates to MongoDB UTCDateTime
            // $start = new \MongoDB\BSON\UTCDateTime(Carbon::parse($startDate)->startOfDay()->timestamp * 1000);
            // $end = new \MongoDB\BSON\UTCDateTime(Carbon::parse($endDate)->endOfDay()->timestamp * 1000);

            // // Query logs within the date range
            // $logs = PartStockLog::whereBetween('created_at', [$start, $end])
            //     ->orderBy('created_at', 'asc')
            //     ->get();

            // // Format the result
            // $formattedLogs = [];
            // foreach ($logs as $log) {
            //     $formattedLogs[] = [
            //         'date' => $log->created_at->toDateTime()->format('Y-m-d'), // Convert MongoDB UTCDateTime to string
            //         'part_code' => $log->part_code,
            //         'stock_change' => $log->stock_change,
            //         'new_stock' => $log->new_stock,
            //         'action' => $log->action,
            //         'created_by' => $log->created_by,
            //     ];
            // }

            // return response()->json([
            //     'message' => 'success',
            //     'data' => $formattedLogs
            // ]);
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $currentPage = $request->get('page', 1);
            $query = PartStockLog::query();

            $query = $query->with('part', 'PartControl', 'UserCreatedBy')->where('ref_job_seq', '!=', null);

            if ($request->has('type')) {
                $type = strtolower($request->type);
                if ($type === 'in') {
                    $query->where('action', PartControl::STATUS_IN);
                } elseif ($type === 'out') {
                    $query->where('action', PartControl::STATUS_OUT);
                } elseif ($type === 'inout') {
                    $query->whereIn('action', [PartControl::STATUS_IN, PartControl::STATUS_OUT]);
                }
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                if ($searchTerm != null) {
                    $query->where(function ($query) use ($searchTerm) {
                        $query->where('part_code', 'like', '%' . $searchTerm . '%')
                            ->orWhere('ref_job_seq', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('part', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', '%' . $searchTerm . '%');
                            });
                    });
                }
            }

            // Filter by additional fields (name, code, job_seq, start_date, end_date)
            if ($request->has('name') && $request->name != '') {
                $query->whereHas('part', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->name . '%');
                });
            }

            if ($request->has('code') && $request->code != '') {
                $query->where('part_code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('job_seq') && $request->job_seq != '') {
                $query->where('ref_job_seq', 'like', '%' . $request->job_seq . '%');
            }

            if ($request->has('start_date') && $request->start_date != '') {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $query->where('created_at', '>=', $startDate);
            }

            if ($request->has('end_date') && $request->end_date != '') {
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $query->where('created_at', '<=', $endDate);
            }

            // adding orderBy request
            if ($request->has('order_by')) {
                $orderBy = $request->order_by;
                $query->orderBy($orderBy, 'desc');
            } else {
                $query->orderBy('updated_at', 'desc');
            }

            if ($request->get('show_all') == 'true') {
                $partControlLogs = $query->get();
            } else {
                $partControlLogs = $query->paginate($perPage, ['*'], 'page', $currentPage);
            }

            return response()->json([
                'message' => 'success',
                'data' => $partControlLogs
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
