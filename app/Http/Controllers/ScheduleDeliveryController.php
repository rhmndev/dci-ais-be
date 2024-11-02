<?php

namespace App\Http\Controllers;

use App\PurchaseOrderScheduleDelivery;
use Illuminate\Http\Request;

class ScheduleDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'po_number' => 'nullable|string',
            'order' => 'string',
        ]);

        $user = auth()->user();

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $po_number = ($request->po_number != null) ? $request->po_number : '';
        try {

            $scheduleDelivery = new PurchaseOrderScheduleDelivery;
            $data = array();
            $resultAlls = $scheduleDelivery->getAllData($keyword, $request->columns, $request->sort, $order, $po_number);
            $results = $scheduleDelivery->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $po_number);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'dataAll' => $resultAlls,
                'total' => count($resultAlls),
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,

            ], 400);
        }
    }
}
