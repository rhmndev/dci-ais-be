<?php

namespace App\Http\Controllers;

use App\PurchaseOrderActivities;
use Illuminate\Http\Request;

class PurchaseOrderActivitiesController extends Controller
{
    public function getActivityByPO(Request $request, $po_number)
    {
        try {
            $PurchaseOrderActivity = PurchaseOrderActivities::where('po_number', $po_number)->first();

            if (!$PurchaseOrderActivity) {
                $PurchaseOrderActivity = PurchaseOrderActivities::create([
                    'po_number' => $po_number,
                    'seen' => 0,
                    'last_seen_at' => null,
                    'downloaded' => 0,
                    'last_downloaded_at' => null,
                ]);
            }
            return response()->json([
                'type' => 'success',
                'message' => '',
                'data' => $PurchaseOrderActivity
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => '',
                'data' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }
}
