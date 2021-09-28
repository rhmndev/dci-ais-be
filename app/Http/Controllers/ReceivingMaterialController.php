<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ReceivingMaterial;
use App\Vendor;
use App\Settings;
use App\Scale;
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
        $vendor = auth()->user()->vendor_code;

        try {
    
            $data = array();
            $ReceivingMaterial = new ReceivingMaterial;
            $Settings = new Settings;

            $Material_Perpage = $Settings->scopeGetValue($Settings, 'Material_Perpage');

            $perpage = $request->perpage != null ? $request->perpage : $Material_Perpage[0];

            $resultAlls = $ReceivingMaterial->getAllData($request->PO_Number, $search, $request->columns, $request->sort, $order, $vendor);

            $results = $ReceivingMaterial->getData($request->PO_Number, $search, $request->columns, $perpage, $request->page, $request->sort, $order, $vendor);

            foreach ($results as $result) {
                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['create_date'] = $result->create_date;
                $data_tmp['delivery_date'] = $result->delivery_date;
                $data_tmp['release_date'] = $result->release_date;
                $data_tmp['material_id'] = $result->material_id;
                $data_tmp['material_name'] = $result->material_name;
                $data_tmp['item_po'] = $result->item_po;
                $data_tmp['index_po'] = $result->index_po;
                $data_tmp['qty'] = $result->qty;
                $data_tmp['unit'] = $result->unit;
                $data_tmp['price'] = $result->price;
                $data_tmp['currency'] = $result->currency;
                $data_tmp['vendor'] = $result->vendor;
                $data_tmp['ppn'] = $result->ppn;
                $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                foreach ($SettingPPNs as $SettingPPN) {
                    $ppn = explode(';', $SettingPPN['name']);
                    if ($ppn[0] === $result->ppn){
                        $data_tmp['ppn_p'] = $ppn[1];
                    }
                };
                $data_tmp['del_note'] = $result->del_note;
                $data_tmp['del_date'] = $result->del_date;
                $data_tmp['del_qty'] = $result->del_qty;
                $data_tmp['prod_date'] = $result->prod_date;
                $data_tmp['prod_lot'] = $result->prod_lot;
                $data_tmp['material'] = $result->material;
                $data_tmp['o_name'] = $result->o_name;
                $data_tmp['o_code'] = $result->o_code;

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

    public function show(Request $request, $id)
    {
        $vendor = auth()->user()->vendor_code;

        $ReceivingMaterial = ReceivingMaterial::where('_id', $id);
        if ($vendor != ''){
            $ReceivingMaterial = $ReceivingMaterial->where('vendor', $vendor);
        }
        $ReceivingMaterial = $ReceivingMaterial->first();

        if ($ReceivingMaterial){

            $Vendor = new Vendor;
    
            $vendor_data = $Vendor->checkVendor($ReceivingMaterial->vendor);
            if (count($vendor_data) > 0){
    
                $vendor_data = $vendor_data[0];
                $ReceivingMaterial->vendor_name = $vendor_data->name;
    
            } else {
    
                $ReceivingMaterial->vendor_name = '';
    
            }
    
            $Settings = new Settings;
            $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
            
            foreach ($SettingPPNs as $SettingPPN) {
                $ppn = explode(';', $SettingPPN['name']);
                if ($ppn[0] === $ReceivingMaterial->ppn){
                    $ReceivingMaterial->ppn_p = $ppn[1];
                }
            };

            return response()->json([
                'type' => 'success',
                'data' => $ReceivingMaterial,
            ], 200);

        } else {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'data not found.',
                'data' => NULL,
    
            ], 400);

        }
    }

    public function scanData(Request $request)
    {
        $request->validate([
            'PO_Number' => 'required|string',
            'material_id' => 'required|string',
            'item_no' => 'required|string',
        ]);

        $vendor = auth()->user()->vendor_code;

        try {

            $ReceivingMaterial = new ReceivingMaterial;

            $data = $ReceivingMaterial->scanData($request->PO_Number, $request->material_id, $request->item_no, $vendor);

            if ($data){

                $Vendor = new Vendor;
        
                $vendor_data = $Vendor->checkVendor($data->vendor);
                if (count($vendor_data) > 0){
        
                    $vendor_data = $vendor_data[0];
                    $data->vendor_name = $vendor_data->name;
        
                } else {
        
                    $data->vendor_name = '';
        
                }
        
                $Settings = new Settings;
                $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                
                foreach ($SettingPPNs as $SettingPPN) {
                    $ppn = explode(';', $SettingPPN['name']);
                    if ($ppn[0] === $data->ppn){
                        $data->ppn_p = $ppn[1];
                    }
                };

                $SettingGudangDatas = $Settings->scopeGetValue($Settings, 'Gudang');
                $gudangData = array();
                foreach ($SettingGudangDatas as $SettingGudangData) {
                    $gd = explode(';', $SettingGudangData['name']);
                    $temp = array(
                        'id' => $gd[0],
                        'name' => $gd[1],
                    );
                    array_push($gudangData, $temp);
                };
                $data->gudangData = $gudangData;

                $Scale = new Scale;
                $ScaleData = $Scale->getData(1);
                $data->scale_qty = $ScaleData->qty;

                $data->receive_qty = intval($data->del_qty);

                $data->reference = '';
                $data->gudang_id = '';
                $data->gudang_nm = '';
                $data->batch = '';

                return response()->json([
                    'type' => 'success',
                    'message' => NULL,
                    'data' => $data,
                ], 200);

            } else {
    
                return response()->json([
        
                    'type' => 'failed',
                    'message' => 'Data not found.',
                    'data' => NULL,
        
                ], 400);

            }

        } catch (\Exception $e) {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }

    public function update(Request $request)
    {

        $json = $request->getContent();

        try {

            $inputs = json_decode($json);

            if (count($inputs) > 0){

                foreach ($inputs as $input) {
            
                    $ReceivingMaterial = ReceivingMaterial::where('_id', $input->_id)->first();

                    $ReceivingMaterial->del_note = $input->del_note;
                    $ReceivingMaterial->del_date = $input->del_date;
                    $ReceivingMaterial->del_qty = $input->del_qty;
                    $ReceivingMaterial->prod_date = $input->prod_date;
                    $ReceivingMaterial->prod_lot = $input->prod_lot;
                    $ReceivingMaterial->material = $input->material;
                    $ReceivingMaterial->o_name = $input->o_name;
                    $ReceivingMaterial->o_code = $input->o_code;

                    $ReceivingMaterial->updated_by = auth()->user()->username;
                    $ReceivingMaterial->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                    $ReceivingMaterial->save();

                }

            }

            return response()->json([
    
                "result" => true,
                "msg_type" => 'Success',
                "message" => 'Data stored successfully!',
    
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: '.$e,
    
            ], 400);

        }
    }
}
