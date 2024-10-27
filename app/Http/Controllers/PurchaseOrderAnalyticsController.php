<?php

namespace App\Http\Controllers;

use App\PurchaseOrderAnalytics;
use Illuminate\Http\Request;

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
}
