<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ReceivingMaterial;
use App\Settings;
use Carbon\Carbon;

class ReceivingMaterialController extends Controller
{
    //
    public function index()
    {
        # code...
    }

    public function show(Request $request)
    {
        # code...
    }

    public function filterByNoPo(Request $request)
    {
        $request->validate([
            'NoPo' => 'required|string',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
        ]);

        $order = ($request->order != null) ? $request->order : 'ascend';

        try {
    
            $data = array();
            $ReceivingMaterial = new ReceivingMaterial;
            $Settings = new Settings;

            $Material_Perpage = $Settings->scopeGetValue($Settings, 'Material_Perpage');

            $perpage = $request->perpage != null ? $request->perpage : $Material_Perpage[0];

            $resultAlls = $ReceivingMaterial->getAllData($request->NoPo, $request->sort, $order, $flag);
            $PODetails = $ReceivingMaterial->getPODetails($request->NoPo, $request->perpage);
            foreach ($PODetails as $PODetail) {

                $data_tmp = array();
                $data_tmp['_id'] = $PODetail->_id;
                $data_tmp['PO_Number'] = $PODetail->PO_Number;
                $data_tmp['material_id'] = $PODetail->material_id;
                $data_tmp['material_name'] = $PODetail->material_name;
                $data_tmp['qty'] = number_format($PODetail->qty);
                $data_tmp['unit'] = $PODetail->unit;
                $data_tmp['price'] = number_format($PODetail->price);
                $data_tmp['currency'] = $PODetail->currency;
                $data_tmp['vendor'] = $PODetail->vendor;
        
                $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                foreach ($SettingPPNs as $SettingPPN) {
                    $ppn = explode(';', $SettingPPN['name']);
                    if ($ppn[0] === $PODetail->ppn){
                        $data_tmp['ppn'] = $ppn[1];
                    }
                };

                $total = $PODetail->qty * $PODetail->price;
                $data_tmp['sub_total'] = number_format($total);

                $total = ((str_replace("%", "", $data_tmp['ppn']) / 100) * $total) + $total;

                $data_tmp['total'] = number_format($total);

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
}
