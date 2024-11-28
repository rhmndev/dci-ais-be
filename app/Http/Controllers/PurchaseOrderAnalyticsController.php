<?php

namespace App\Http\Controllers;

use App\PurchaseOrder;
use App\PurchaseOrderAnalytics;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\UTCDateTime;

class PurchaseOrderAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $analytics = PurchaseOrderAnalytics::all();

            return response()->json([
                'type' => 'success',
                'data' => $analytics,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getPurchaseOrderAnalyticsByStorageLocation(Request $request)
    {
        $request->validate([
            'groupBy' => 'required|in:year,month',
            'year' => 'required_if:groupBy,year,month|numeric',
            'month' => 'required_if:groupBy,month,date|numeric|between:1,12',
            'storageLocations' => 'sometimes|array',
            'storageLocations.*' => 'string',
        ]);

        $groupBy = $request->groupBy;
        $year = (int)$request->year;
        $month = $request->month ? (int)$request->month : null;
        $storageLocations = $request->storageLocations;

        $cacheKey = "purchase_order_analytics_by_location_{$groupBy}_{$year}" . ($month ? "_$month" : "")  . (is_array($storageLocations) ? "_" . implode('_', $storageLocations) : "");
        $analytics = Cache::remember($cacheKey, 60, function () use ($groupBy, $year, $month, $storageLocations) {
            $match = ['purchase_currency_type' => 'IDR'];
            switch ($groupBy) {
                case 'year':
                    $match['order_date'] = ['$gte' => new UTCDateTime(Carbon::create($year, 1, 1)->startOfDay()), '$lte' => new UTCDateTime(Carbon::create($year, 12, 31)->endOfDay())];
                    break;
                case 'month':
                    $match['order_date'] = ['$gte' => new UTCDateTime(Carbon::create($year, $month, 1)->startOfDay()), '$lte' => new UTCDateTime(Carbon::create($year, $month, Carbon::create($year, $month)->daysInMonth)->endOfDay())];
                    break;
            }
            if (is_array($storageLocations) && count($storageLocations) > 0) {
                $match['s_locks_code'] = ['$in' => $storageLocations];
            }

            $result = PurchaseOrder::raw(function ($collection) use ($match, $groupBy, $year) {
                $groupStage = [
                    '_id' => [
                        'storageLocationCode' => '$s_locks_code',
                        'year' => ['$year' => '$order_date'],
                        'month' => ['$month' => '$order_date']
                    ],
                    'totalAmount' => ['$sum' => '$total_amount'],
                    'totalOrders' => ['$sum' => 1],
                ];
                switch ($groupBy) {
                    case 'year':
                        $groupStage['_id']['year'] = ['$year' => '$order_date'];
                        break;
                    case 'month':
                        $groupStage['_id']['month'] = ['$month' => '$order_date'];
                        $groupStage['_id']['year'] = ['$year' => '$order_date'];
                        break;
                }

                return $collection->aggregate([
                    ['$match' => $match],
                    ['$group' => $groupStage],
                    [
                        '$lookup' => [
                            'from' => 's_locks',
                            'localField' => '_id.storageLocationCode',
                            'foreignField' => 'code',
                            'as' => 'storageLocation'
                        ]
                    ],
                    ['$unwind' => '$storageLocation'],
                    [
                        '$project' => [
                            '_id' => 0,
                            'storageLocationCode' => '$_id.storageLocationCode',
                            'storageLocationDescription' => '$storageLocation.description',
                            'totalAmount' => 1,
                            'totalOrders' => 1,
                            'year' => '$_id.year',
                            'month' => '$_id.month',
                        ]
                    ],

                    ['$sort' => ['storageLocationCode' => 1]],
                    [
                        '$group' => [
                            '_id' => [
                                'month_year' => [
                                    '$cond' => [
                                        ['$eq' => [$groupBy, 'month']],
                                        ['$concat' => [['$toString' => '$year'], '-', ['$toString' => '$month']]],
                                        ['$toString' => '$year']
                                    ]
                                ]
                            ],
                            'data' => [
                                '$push' => [
                                    'storageLocationCode' => '$storageLocationCode',
                                    'storageLocationDescription' => '$storageLocationDescription',
                                    'totalAmount' => '$totalAmount',
                                    'totalOrders' => '$totalOrders'
                                ]
                            ],
                            'yearlyData' => ['$push' => ['$cond' => [['$eq' => ['$year', $year]], ['$sum' => '$totalAmount'], 0]]], // Add yearlyData calculation

                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 0,
                            'month_year' => '$_id.month_year',
                            'data' => 1,
                            'yearlyData' => 1,
                        ]
                    ],
                    ['$sort' => ['month_year' => 1]]

                ]);
            });

            return $result;
        });

        return response()->json([
            'type' => 'success',
            'message' => 'Get Purchase Order Analytics By Storage Location',
            'data' => $analytics
        ]);
    }
}
