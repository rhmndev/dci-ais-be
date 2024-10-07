<?php

namespace App\Http\Controllers;

use App\Inspection;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';

        try {
            $Inspection = new Inspection;
            $data = array();

            $resultAlls = $Inspection->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Inspection->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'total' => count($resultAlls)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'report_date' => 'required',
        //     'line_number' => 'required',
        //     'lot_number' => 'required',
        //     'check' => 'required',
        //     'qty_ok' => 'required',
        // ]);

        try {
            // $Inspection = Inspection::firstOrNew(['code' => $request->code]);
            $Inspection = new Inspection;
            $Inspection->code = isset($request->code) ? $request->code : "DEVELOPMENT-AAAA";
            $Inspection->report_date = $request->report_date;
            $Inspection->line_number = $request->line_number;
            $Inspection->lot_number = $request->lot_number;

            $Inspection->customer_id = $request->customer_id;
            // $Inspection->customer_name = Inspection::getNameCustomerById($request->customer_id);
            $Inspection->customer_name = "Tarjo";

            $Inspection->part_component_id = "A12313";
            $Inspection->part_component_number = "X11111";
            $Inspection->check = $request->check;
            $Inspection->qty_ok = $request->qty_ok;
            // $Inspection->inspection_by = isset($request->inspection_by) ? $request->inspection_by : auth()->user()->name;
            $Inspection->inspection_by = "developer";

            $Inspection->qrcode_path = Inspection::GenerateQR();

            $Inspection->save();

            return response()->json([

                'type' => 'success',
                'message' => '',
                'data' => $Inspection->qrcode_path,

            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,
            ], 400);
        }
    }
}
