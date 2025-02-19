<?php

namespace App\Http\Controllers;

use App\CompareDeliveryNote;
use App\CompareDeliveryNoteAHM;
use App\OrderCustomer;
use App\OrderCustomerAhm;
use App\TrackingBox;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TrackingBoxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TrackingBox::query();

            if ($request->has('number_box')) {
                $query->where('number_box', 'like', '%' . $request->input('number_box') . '%');
            }

            if ($request->has('kanban')) {
                $query->where('kanban', 'like', '%' . $request->input('kanban') . '%');
            }

            if ($request->has('dn_number')) {
                $query->where('dn_number', 'like', '%' . $request->input('dn_number') . '%');
            }

            if ($request->has('destination_code')) {
                $query->where('destination_code', 'like', '%' . $request->input('destination_code') . '%');
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('scanned_by')) {
                $query->where('scanned_by', 'like', '%' . $request->input('scanned_by') . '%');
            }

            if ($request->has('group') && $request->input('group') === 'dn') {
                // $perPage = $request->input('per_page', 15);
                // $page = $request->input('page', 1);
                // $skip = ($page - 1) * $perPage;

                $trackingBoxes = TrackingBox::raw(function ($collection) use ($query) {
                    return $collection->aggregate([
                        ['$match' => $query->getQuery()->wheres],
                        ['$group' => [
                            '_id' => [
                                'dn_number' => '$dn_number',
                                'destination_code' => '$destination_code'
                            ],
                            'total_boxes' => ['$sum' => 1],
                            'total_boxes_in' => ['$sum' => ['$cond' => [['$eq' => ['$status_box', 'IN']], 1, 0]]],
                            'total_boxes_out' => ['$sum' => ['$cond' => [['$eq' => ['$status_box', 'OUT']], 1, 0]]],
                        ]],
                        ['$project' => [
                            'dn_number' => '$_id.dn_number',
                            'destination_code' => '$_id.destination_code',
                            'total_boxes' => 1,
                            'total_boxes_in' => 1,
                            'total_boxes_out' => 1,
                            '_id' => 0
                        ]]
                    ]);
                });
            } else {
                $perPage = $request->input('per_page', 15);
                $trackingBoxes = $query->paginate($perPage);
            }

            return response()->json($trackingBoxes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve tracking boxes'], 500);
        }
    }

    public function getDataOrderCustomer(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            // Query OrderCustomer with pagination
            $orderCustomersQuery = OrderCustomer::query();

            if ($request->has('group_by') && $request->input('group_by') === 'dn_no') {
                $subQuery = OrderCustomer::select('dn_no', DB::raw('MAX(id) as last_id'), DB::raw('SUM(qty_kbn) as total_qty_kbn'))
                    ->groupBy('dn_no');

                $orderCustomersQuery = OrderCustomer::joinSub($subQuery, 'sub', function ($join) {
                    $join->on('order_customer.id', '=', 'sub.last_id');
                })->orderBy('sub.last_id', 'desc');
            } else {
                $orderCustomersQuery->orderBy('id', 'desc');
            }
            // $orderCustomersQuery->orderBy('id', 'desc');

            $orderCustomers = $orderCustomersQuery->paginate($perPage, ['*'], 'page', $page);

            // $orderCustomers = OrderCustomer::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

            // Query OrderCustomerAhm with paginationz

            $orderCustomers->getCollection()->transform(function ($item) {
                $item->source = '-';
                $item->tracking_boxes = $item->getTrackingBoxes();
                $item->compares = $item->compareDeliveryNotes;
                $item->parts = $item->getParts();
                return $item;
            });

            $data = new LengthAwarePaginator(
                $orderCustomers->getCollection(),
                $orderCustomers->total(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data from MySQL', 'message' => $e->getMessage()], 500);
        }
    }

    public function countBoxStatusByDN(Request $request): JsonResponse
    {
        $request->validate([
            'dn_no' => 'required|string',
        ]);

        try {
            $dnNo = $request->input('dn_no');

            $countOut = TrackingBox::where('dn_number', $dnNo)
                ->where('status', 'out')
                ->count();

            $countIn = TrackingBox::where('dn_number', $dnNo)
                ->where('status', 'in')
                ->count();

            return response()->json([
                'dn_no' => $dnNo,
                'count_out' => $countOut,
                'count_in' => $countIn,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve box counts', 'message' => $e->getMessage()], 500);
        }
    }

    public function getDataOrderCustomerAHM(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            // Query OrderCustomerAhm with pagination
            $orderCustomerAhmQuery = OrderCustomerAhm::query();

            if ($request->has('group_by') && $request->input('group_by') === 'dn_no') {
                $subQuery = OrderCustomerAhm::select('dn_no', DB::raw('MAX(id) as last_id'))
                    ->groupBy('dn_no');

                $orderCustomerAhmQuery = OrderCustomerAhm::joinSub($subQuery, 'sub', function ($join) {
                    $join->on('order_customer_ahm.id', '=', 'sub.last_id');
                })->orderBy('sub.last_id', 'desc');
            } else {
                $orderCustomerAhmQuery->orderBy('id', 'desc');
            }
            $orderCustomerAhms = $orderCustomerAhmQuery->paginate($perPage, ['*'], 'page', $page);

            $orderCustomerAhms->getCollection()->transform(function ($item) {
                $item->source = 'AHM';
                $item->tracking_boxes = $item->getTrackingBoxes();
                $item->compares = $item->compareDeliveryNotes;
                $item->parts = $item->getParts();
                return $item;
            });

            $data = new LengthAwarePaginator(
                $orderCustomerAhms->getCollection(),
                $orderCustomerAhms->total(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data from MySQL', 'message' => $e->getMessage()], 500);
        }
    }

    public function getDNCustomer(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Get the last id for each dn_no in OrderCustomer
            $lastIdsGeneral = OrderCustomer::select('dn_no', DB::raw('MAX(id) as last_id'))
                ->whereNotNull('dn_no')
                ->whereNotNull('customer')
                ->whereNotNull('part_no')
                ->whereNotNull('job_no')
                ->groupBy('dn_no')
                ->pluck('last_id');

            // Get all records starting from the last id in OrderCustomer
            $orderCustomers = OrderCustomer::whereIn('id', $lastIdsGeneral)
                ->whereNotNull('dn_no')
                ->whereNotNull('customer')
                ->whereNotNull('part_no')
                ->whereNotNull('job_no')
                ->select('*', DB::raw('"General" as source'))
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $orderCustomers->getCollection()->transform(function ($orderCustomer) {
                if (!is_null($orderCustomer->dn_no)) {
                    $orderCustomer->tracking_boxes = $orderCustomer->getTrackingBoxesByKanban();
                    $orderCustomer->compares = $orderCustomer->compareDeliveryNotes;
                }
                // $orderCustomer->tracking_boxes = $orderCustomer->getTrackingBoxes();
                return $orderCustomer;
            });

            $data = new LengthAwarePaginator(
                $orderCustomers->getCollection(),
                $orderCustomers->total(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data from MySQL', 'message' => $e->getMessage()], 500);
        }
    }

    public function getDNCustomerAHM(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Get the last id for each dn_no in OrderCustomerAhm
            $lastIdsAhm = OrderCustomerAhm::select('dn_no', DB::raw('MAX(id) as last_id'))
                ->whereNotNull('dn_no')
                ->whereNotNull('customer')
                ->whereNotNull('part_no')
                ->groupBy('dn_no')
                ->pluck('last_id');

            // Get all records starting from the last id in OrderCustomerAhm
            $orderCustomerAhms = OrderCustomerAhm::whereIn('id', $lastIdsAhm)
                ->whereNotNull('dn_no')
                ->whereNotNull('customer')
                ->whereNotNull('part_no')
                ->select('*', DB::raw('"Ahm" as source'))
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = new LengthAwarePaginator(
                $orderCustomerAhms->getCollection(),
                $orderCustomerAhms->total(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data from MySQL', 'message' => $e->getMessage()], 500);
        }
    }

    public function showDN(Request $request): JsonResponse
    {
        try {
            $dn = $request->input('dn');
            $orderCustomer = OrderCustomer::where('dn_no', $dn)->get();

            if ($orderCustomer->isEmpty()) {
                $orderCustomer = OrderCustomerAhm::where('dn_no', $dn)->get();
            }

            return response()->json([
                'data' => $orderCustomer
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Tracking boxes not found'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'number_box' => 'required|string',
                'dn_number' => 'nullable|string',
                'kanban' => 'nullable|string',
                'destination_code' => 'nullable|string',
                'destination_aliases' => 'nullable|string',
                'status' => 'required|string',
                'scanned_by' => 'nullable|string',
            ]);

            $latestTracking = TrackingBox::where('number_box', $request->input('number_box'))
                ->orderBy('date_time', 'desc')
                ->first();

            if ($request->input('type') === 'in') {
                if ($latestTracking && in_array($latestTracking->status, ['in', 'incoming'])) {
                    return response()->json([
                        'error' => 'Box is not valid for this action as its last status is ' . $latestTracking->status
                    ], 400);
                }

                if ($latestTracking && in_array($latestTracking->status, ['out', 'delivery'])) {
                    $request->merge([
                        'kanban' => $latestTracking->kanban,
                        'destination_code' => $latestTracking->destination_code,
                        'destination_aliases' => $latestTracking->destination_aliases,
                        'date_time' => Carbon::now()->toDateTimeString(),
                    ]);

                    $trackingBox = TrackingBox::create($request->all());

                    $trackingBox->scanned_by = $request->input('scanned_by') ?? auth()->user()->npk;
                    $trackingBox->save();

                    return response()->json([
                        'message' => 'Tracking box created successfully',
                        'data' => $trackingBox
                    ], 201);
                }
            }

            if ($latestTracking && in_array($latestTracking->status, ['out', 'delivery']) && !in_array($request->input('type'), ['in', 'incoming'])) {
                return response()->json([
                    'error' => 'Box is not valid for this action as its last status is ' . $latestTracking->status
                ], 400);
            }

            if (in_array($request->input('status'), ['delivery', 'out'])) {
                $request->validate([
                    'kanban' => 'required|string',
                ]);

                $kanban = $request->input('kanban');
                $compareDeliveryNote = CompareDeliveryNote::where('kbn_no', $kanban)->first();
                $compareDeliveryNoteAHM = CompareDeliveryNoteAHM::where('job_seq', $kanban)->first();

                if ($compareDeliveryNote) {
                    $customer = $compareDeliveryNote->orderCustomer->customer;
                    $plant = $compareDeliveryNote->orderCustomer->plant;
                    $request->merge(['destination_code' => 'ADM', 'destination_aliases' => 'ADM', 'dn_number' => $compareDeliveryNote->dn_no, 'customer' => $customer, 'plant' => $plant]);
                } elseif ($compareDeliveryNoteAHM) {
                    $request->merge(['destination_code' => 'AHM', 'destination_aliases' => 'AHM', 'dn_number' => $compareDeliveryNoteAHM->dn_no]);
                } else {
                    return response()->json([
                        'error' => 'Kanban not found in CompareDeliveryNote or CompareDeliveryNoteAHM'
                    ], 404);
                }
            }

            if (in_array($request->input('status'), ['go_back', 'return', 'in', 'incoming'])) {
                $previousDelivery = TrackingBox::where('number_box', $request->input('number_box'))
                    ->where('status', 'delivery')
                    ->orderBy('date_time', 'desc')
                    ->first();

                if ($previousDelivery) {
                    $request->merge([
                        'dn_number' => $previousDelivery->dn_number,
                        'kanban' => $previousDelivery->kanban,
                    ]);
                }
            }

            $request->merge(['date_time' => Carbon::now()->toDateTimeString()]);

            $trackingBox = TrackingBox::create($request->all());

            $trackingBox->scanned_by = $request->input('scanned_by') ?? auth()->user()->npk;

            $trackingBox->save();

            return response()->json([
                'message' => 'Tracking box created successfully',
                'data' => $trackingBox
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create tracking box',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBoxStatus(Request $request): JsonResponse
    {
        $request->validate([
            'number_box' => 'required|string',
            'last' => 'sometimes|boolean',
        ]);

        try {
            $numberBox = $request->input('number_box');
            $query = TrackingBox::where('number_box', $numberBox)->orderBy('date_time', 'desc');
            if ($request->input('last', false)) {
                $status = $query->first();
            } else {
                $status = $query->get();
            }

            if (!$status) {
                return response()->json(['error' => 'Box status not found'], 404);
            }

            return response()->json($status, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve box status'], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $trackingBox = TrackingBox::findOrFail($id);
            return response()->json($trackingBox, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Tracking box not found'], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'number_box' => 'string',
                'dn_number' => 'string',
                'destination_code' => 'string',
                'destination_aliases' => 'string',
                'status' => 'string',
                'date_time' => 'date',
                'scanned_by' => 'string',
            ]);

            $trackingBox = TrackingBox::findOrFail($id);
            $trackingBox->update($request->all());

            $trackingBox->scanned_by = $request->input('scanned_by') ?? auth()->user()->npk;
            $trackingBox->save();

            return response()->json($trackingBox, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update tracking box'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            TrackingBox::findOrFail($id)->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete tracking box'], 500);
        }
    }

    public function historyBox(Request $request): JsonResponse
    {
        $request->validate([
            'customer' => 'required|string',
        ]);

        try {
            $customer = $request->input('customer');
            $plant = $request->input('plant');
            $trackingBoxes = TrackingBox::with('Box')->where('destination_code', 'ADM')
                ->orderBy('date_time', 'desc')
                ->get();
            // $trackingBoxes = TrackingBox::where('customer', $customer)->where('plant', $plant)->get();

            return response()->json($trackingBoxes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve box history'], 500);
        }
    }

    public function getSummaryByPeriod(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month'); // Default to 'month' if not specified
            $validPeriods = ['week', 'month', 'year'];

            $start_date = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $end_date = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

            if (!in_array($period, $validPeriods)) {
                return response()->json(['error' => 'Invalid period specified'], 400);
            }

            $query = TrackingBox::query();

            switch ($period) {
                case 'week':
                    $query->whereBetween('date_time', [
                        Carbon::parse($start_date)->startOfWeek()->toDateTimeString(),
                        Carbon::parse($end_date)->endOfWeek()->toDateTimeString()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('date_time', [
                        Carbon::parse($start_date)->startOfMonth()->toDateTimeString(),
                        Carbon::parse($end_date)->endOfMonth()->toDateTimeString()
                    ]);
                    break;
                case 'year':
                    $query->whereBetween('date_time', [
                        Carbon::parse($start_date)->startOfYear()->toDateTimeString(),
                        Carbon::parse($end_date)->endOfYear()->toDateTimeString()
                    ]);
                    break;
            }

            $summary = $query->get();

            return response()->json([
                'type' => 'success',
                'data' => $summary,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve summary', 'message' => $e->getMessage()], 500);
        }
    }
}
