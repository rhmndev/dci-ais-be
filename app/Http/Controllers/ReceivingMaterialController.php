<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ReceivingMaterial;
use App\Settings;
use Carbon\Carbon;

class ReceivingMaterialController extends Controller
{
    //
    public function index(Request $request)
    {
        $request->validate([
            'PO_Number' => 'required|string',
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
        ]);

        $search = ($request->search != null) ? $request->search : '';
        $order = ($request->order != null) ? $request->order : 'ascend';

        try {
    
            $data = array();
            $ReceivingMaterial = new ReceivingMaterial;
            $Settings = new Settings;

            $Material_Perpage = $Settings->scopeGetValue($Settings, 'Material_Perpage');

            $perpage = $request->perpage != null ? $request->perpage : $Material_Perpage[0];

            $resultAlls = $ReceivingMaterial->getAllData($request->PO_Number, $search, $request->columns, $request->sort, $order);

            $results = $ReceivingMaterial->getData($request->PO_Number, $search, $request->columns, $perpage, $request->page, $request->sort, $order);

            foreach ($results as $result) {
                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['material_id'] = $result->material_id;
                $data_tmp['material_name'] = $result->material_name;
                $data_tmp['qty'] = number_format($result->qty);
                $data_tmp['unit'] = $result->unit;
                $data_tmp['price'] = number_format($result->price);
                $data_tmp['currency'] = $result->currency;
                $data_tmp['vendor'] = $result->vendor;
                $data_tmp['ppn'] = $result->ppn;
                $data_tmp['del_note'] = null;
                $data_tmp['del_date'] = $result->del_date;
                $data_tmp['del_qty'] = number_format($result->qty);
                $data_tmp['prod_date'] = $result->prod_date;
                $data_tmp['prod_lot'] = null;
                $data_tmp['material'] = null;
                $data_tmp['o_name'] = null;
                $data_tmp['o_code'] = null;

                array_push($data, $data_tmp);
            }

            return response()->json([
                'type' => 'success',
                'data' => $data,
                'total' => count($resultAlls),
            ], 200);

        } catch (\Exception $e) {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }

    public function show(Request $request)
    {
        # code...
    }
}
