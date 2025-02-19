<?php

namespace App\Http\Controllers;

use App\WhsScheduleDelivery;
use Illuminate\Http\Request;

class WhsScheduleDeliveryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lists = WhsScheduleDelivery::query();

            if ($request->has('part_type')) {
                $lists->where('part_type', $request->part_type);
            }
            $perPage = $request->get('per_page', 10);
            $data = $lists->paginate($perPage);

            return response()->json([
                'type' => 'success',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
