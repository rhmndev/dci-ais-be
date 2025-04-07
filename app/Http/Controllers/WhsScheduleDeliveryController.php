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
            $page = (int) $request->get('page', 1);

            $perPage = $perPage > 0 ? $perPage : 10;
            $page = $page > 0 ? $page : 1;
            $data = $lists->paginate($perPage, ['*'], 'page', $page);

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

    public function createCustomerDeliveryCycle(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'part_id' => 'required',
            'schedule_date' => 'required',
            'po_no' => 'required',
            'delivery' => 'required',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = auth()->user()->id;
            $data['updated_by'] = auth()->user()->id;
            return response()->json([
                'type' => 'failed',
                'message' => 'Data has been created',
                'data' => $data
            ], 401);

            $whsScheduleDelivery = WhsScheduleDelivery::updateOrCreate(
                [
                    'customer_id' => $request->customer_id,
                    'part_id' => $request->part_id,
                    'schedule_date' => $request->schedule_date,
                    'po_no' => $request->po_no,
                    'delivery' => $request->delivery,
                ],
                // $data
            );

            return response()->json([
                'type' => 'success',
                'message' => 'Data has been created',
                'data' => $whsScheduleDelivery
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
