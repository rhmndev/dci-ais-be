<?php

namespace App\Http\Controllers;

use App\PurchaseOrder;
use App\PurchaseOrderAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

    public function getPurchaseOrderAnalyticsByStorageLocation()
    {
        $analytics = Cache::remember('purchase_order_analytics_by_location', 60, function () {
            // Your existing MongoDB aggregation code here
            $result = PurchaseOrder::raw(function ($collection) {
                return $collection->aggregate([
                    [
                        '$group' => [
                            '_id' => [
                                'storageLocationCode' => '$s_locks_code',
                                'month' => [
                                    '$month' => '$order_date'
                                ],
                                'year' => [
                                    '$year' => '$order_date'
                                ]
                            ],
                            'totalAmount' => [
                                '$sum' => '$total_amount'
                            ],
                            'totalOrders' => [
                                '$sum' => 1
                            ]
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 's_locks',
                            'localField' => '_id.storageLocationCode',
                            'foreignField' => 'code',
                            'as' => 'storageLocation'
                        ]
                    ],
                    [
                        '$unwind' => '$storageLocation'
                    ],
                    [
                        '$project' => [
                            '_id' => 0,
                            'storageLocationCode' => '$_id.storageLocationCode',
                            'storageLocationDescription' => '$storageLocation.description',
                            'month_year' => [ // Combine month and year
                                '$dateToString' => [
                                    'format' => '%Y-%m',
                                    'date' => '$order_date'
                                ]
                            ],
                            'totalAmount' => 1,
                            'totalOrders' => 1
                        ]
                    ],
                    [
                        '$sort' => [
                            'month_year' => -1
                        ]
                    ],
                    [
                        '$group' => [ // Group again to get unique month_year values
                            '_id' => '$month_year',
                            'data' => [
                                '$push' => [
                                    'storageLocationCode' => '$storageLocationCode',
                                    'storageLocationDescription' => '$storageLocationDescription',
                                    'totalAmount' => '$totalAmount',
                                    'totalOrders' => '$totalOrders'
                                ]
                            ]
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 0,
                            'month_year' => '$_id',
                            'data' => 1
                        ]
                    ],
                    [
                        '$sort' => [
                            'month_year' => -1
                        ]
                    ]
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
