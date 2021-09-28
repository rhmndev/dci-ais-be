<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\Receiving;
use App\ReceivingMaterial;
use App\Settings;
use Carbon\Carbon;

class SAPController extends Controller
{
    //
    public function getVendor(Request $request)
    {
        
        $request->validate([
            'search'    => 'nullable|string',
            'sort'      => 'required|string',
            'order'     => 'required|string',
        ]);

        $order = ($request->order != null) ? $request->order : 'ascend';
        $columns = array('code', 'name');

        try {
    
            $Vendor = new Vendor;

            $results = $Vendor->getAllData($request->search, $columns, $request->sort, $order);

            return response()->json([
                
                'type' => 'success',
                'message' => '',
                'data' => $results,
                
            ], 200);

        } catch (\Exception $e) {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }

    public function storeVendor(Request $request)
    {
        $data = array();

        $json = $request->getContent();
            
        try {

            $Vendor = new Vendor();

            $inputs = json_decode($json);

            foreach ($inputs as $input) {


                $QueryGetDataByFilter = Vendor::query();

                $QueryGetDataByFilter = $QueryGetDataByFilter->where('code', $this->stringtoupper($input->code));

                if (count($QueryGetDataByFilter->get()) > 0){
                    $QueryGetDataByFilter = $QueryGetDataByFilter->delete();
                }

                $data_tmp = array();
                
                $data_tmp['code'] = $this->stringtoupper($input->code);
                $data_tmp['name'] = $this->stringtoupper($input->name);
                $data_tmp['address'] = $this->stringtoupper($input->address);
                $data_tmp['phone'] = $input->phone;
                $data_tmp['email'] = $input->email;
                $data_tmp['contact'] = $input->contact;

                $data_tmp['created_by'] = 'SAP';
                $data_tmp['created_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                $data_tmp['updated_by'] = 'SAP';
                $data_tmp['updated_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                // Converting to Array
                array_push($data, $data_tmp);

            }

            $Vendor->insert($data);

            return response()->json([
    
                "result" => true,
                "msg_type" => 'Success',
                "msg" => 'Data stored successfully!',
    
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "msg" => 'err: '.$e,
    
            ], 400);

        }
    }

    public function getMaterial(Request $request)
    {
        
        $request->validate([
            'search'    => 'nullable|string',
            'sort'      => 'required|string',
            'order'     => 'required|string',
        ]);

        $order = ($request->order != null) ? $request->order : 'ascend';
        $columns = array('code', 'description');

        try {
    
            $Material = new Material;

            $results = $Material->getAllData($request->search, $columns, $request->sort, $order);

            return response()->json([
                
                'type' => 'success',
                'message' => '',
                'data' => $results,
                
            ], 200);

        } catch (\Exception $e) {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }

    public function storeMaterial(Request $request)
    {
        $data = array();

        $json = $request->getContent();
            
        try {

            $Material = new Material();

            $inputs = json_decode($json);

            foreach ($inputs as $input) {


                $QueryGetDataByFilter = Material::query();

                $QueryGetDataByFilter = $QueryGetDataByFilter->where('code', $this->stringtoupper($input->code));

                if (count($QueryGetDataByFilter->get()) > 0){
                    $QueryGetDataByFilter = $QueryGetDataByFilter->delete();
                }

                $data_tmp = array();
                
                $data_tmp['code'] = $this->stringtoupper($input->code);
                $data_tmp['description'] = $this->stringtoupper($input->description);
                $data_tmp['type'] = $this->stringtoupper($input->type);
                $data_tmp['unit'] = $this->stringtoupper($input->unit);

                $data_tmp['created_by'] = 'SAP';
                $data_tmp['created_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                $data_tmp['updated_by'] = 'SAP';
                $data_tmp['updated_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                // Converting to Array
                array_push($data, $data_tmp);

            }

            $Material->insert($data);

            return response()->json([
    
                "result" => true,
                "msg_type" => 'Success',
                "msg" => 'Data stored successfully!',
    
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "msg" => 'err: '.$e,
    
            ], 400);

        }
    }

    public function getPO(Request $request)
    {
        
        $request->validate([
            'search'    => 'nullable|string',
            'perpage'   => 'required|numeric',
            'page'      => 'required|numeric',
            'sort'      => 'required|string',
            'order'     => 'required|string',
            'MPerpage'  => 'required|numeric',
            'vendor'    => 'nullable|numeric',
        ]);

        $search = ($request->search != null) ? $request->search : '';
        $vendor = ($request->vendor != null) ? $request->vendor : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $columns = array('PO_Number');

        try {
    
            $data = array();
            $Receiving = new Receiving;
            $ReceivingMaterial = new ReceivingMaterial;
            $Settings = new Settings;

            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $Receiving->getAllData($search, $columns, $request->sort, $order, 0, $vendor);
            $results = $Receiving->getData($search, $columns, $request->perpage, $request->page, $request->sort, $order, 0, $vendor);

            foreach ($results as $result) {
                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['PO_Status'] = $POStatus[$result->PO_Status]['name'];
                $data_tmp['create_date'] = $result->create_date;
                $data_tmp['delivery_date'] = $result->delivery_date;
                $data_tmp['release_date'] = $result->release_date;
                $data_tmp['data'] = array();
                $total_po = 0;

                $PODetails = $ReceivingMaterial->getPODetails($result->PO_Number, $result->MPerpage, $vendor);
                foreach ($PODetails as $PODetail) {

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $PODetail->_id;
                    $data_tmp_d['PO_Number'] = $PODetail->PO_Number;
                    $data_tmp_d['create_date'] = $result->create_date;
                    $data_tmp_d['delivery_date'] = $result->delivery_date;
                    $data_tmp_d['release_date'] = $result->release_date;
                    $data_tmp_d['material_id'] = $PODetail->material_id;
                    $data_tmp_d['material_name'] = $PODetail->material_name;
                    $data_tmp_d['item_po'] = $PODetail->item_po;
                    $data_tmp_d['qty'] = $PODetail->qty;
                    $data_tmp_d['unit'] = $PODetail->unit;
                    $data_tmp_d['price'] = $PODetail->price;
                    $data_tmp_d['currency'] = $PODetail->currency;
                    $data_tmp_d['vendor'] = $PODetail->vendor;
                    $data_tmp_d['QRCode'] = $result->_id.';'.$PODetail->_id;
            
                    $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    foreach ($SettingPPNs as $SettingPPN) {
                        $ppn = explode(';', $SettingPPN['name']);
                        if ($ppn[0] === $PODetail->ppn){
                            $data_tmp_d['ppn'] = $ppn[1];
                        }
                    };

                    $total = $PODetail->qty * $PODetail->price;
                    $data_tmp_d['sub_total'] = $total;

                    $total = ((str_replace("%", "", $data_tmp_d['ppn']) / 100) * $total) + $total;

                    $data_tmp_d['total'] = $total;

    
                    $total_po = $total_po + $total;

                    array_push($data_tmp['data'], $data_tmp_d);

                }
                $data_tmp['total'] = $total_po;
    
                array_push($data, $data_tmp);
            }

            return response()->json([
                
                'type' => 'success',
                'message' => '',
                'data' => $data,
                
            ], 200);

        } catch (\Exception $e) {
    
            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
                'data' => NULL,
    
            ], 400);

        }
    }

    public function storePO(Request $request)
    {
        $data = array();

        $json = $request->getContent();
            
        try {

            $inputs = json_decode($json);

            if (count($inputs) > 0){

                $Material = new Material;
                $Vendor = new Vendor;

                $vendor_nf = array();
                $material_nf = array();

                foreach ($inputs as $input) {

                    $checkVendor = $Vendor->checkVendor($input->vendor);
    
                    if (count($checkVendor) > 0) {

                        $PO_Number = $this->stringtoupper($input->po_number);
                        $create_date = $input->create_date;
                        $delivery_date = $input->delivery_date;
                        $release_date = $input->release_date;
    
                        $Receiving = Receiving::firstOrNew(['PO_Number' => $PO_Number]);
                        $Receiving->PO_Number = $PO_Number;
                        $Receiving->create_date = $create_date;
                        $Receiving->delivery_date = $delivery_date;
                        $Receiving->release_date = $release_date;
                        $Receiving->vendor = $input->vendor;
                        $Receiving->PO_Status = 0;
                        $Receiving->flag = 0;
    
                        $Receiving->created_by = 'SAP';
                        $Receiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->updated_by = 'SAP';
                        $Receiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->save();
    
                        $details = $input->data;
    
                        if (count($details) > 0){
    
                            foreach ($details as $detail) {
    
                                $material_id = $this->stringtoupper($detail->material_id);
                                $material_name = $this->stringtoupper($detail->material_name);
    
                                $qty = $this->checkNumber($detail->qty);
                                $price = $this->checkNumber($detail->price);
    
                                $checkMaterial = $Material->checkMaterial($material_id);
    
                                if (count($checkMaterial) > 0) {
            
                                    $ReceivingMaterial = ReceivingMaterial::firstOrNew([
                                        'PO_Number' => $PO_Number,
                                        'material_id' => $material_id,
                                    ]);
                                    $ReceivingMaterial->PO_Number = $PO_Number;
                                    $ReceivingMaterial->create_date = $create_date;
                                    $ReceivingMaterial->delivery_date = $delivery_date;
                                    $ReceivingMaterial->release_date = $release_date;
                                    $ReceivingMaterial->material_id = $material_id;
                                    $ReceivingMaterial->material_name = $material_name;
                                    $ReceivingMaterial->item_po = $detail->item_po;
                                    $ReceivingMaterial->qty = $qty;
                                    $ReceivingMaterial->unit = $detail->unit;
                                    $ReceivingMaterial->price = $price;
                                    $ReceivingMaterial->currency = $detail->currency;
                                    $ReceivingMaterial->vendor = $input->vendor;
                                    $ReceivingMaterial->ppn = $detail->ppn;
                                    $ReceivingMaterial->del_note = null;
                                    $ReceivingMaterial->del_date = $delivery_date;
                                    $ReceivingMaterial->del_qty = $qty;
                                    $ReceivingMaterial->prod_date = $create_date;
                                    $ReceivingMaterial->prod_lot = null;
                                    $ReceivingMaterial->material = null;
                                    $ReceivingMaterial->o_name = null;
                                    $ReceivingMaterial->o_code = null;
            
                                    $ReceivingMaterial->created_by = 'SAP';
                                    $ReceivingMaterial->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                    $ReceivingMaterial->updated_by = 'SAP';
                                    $ReceivingMaterial->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                    $ReceivingMaterial->save();
                                    
                                } else {
                                    array_push($material_nf, $material_id);
                                }
    
                            }
    
                        }

                    } else {
                        array_push($vendor_nf, $input->vendor);
                    }

                }

            }

            if (count($vendor_nf) > 0){

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'failed',
                    "msg" => 'Data stored unsuccessfully!',
                    "Not Found Vendor" => array_unique($vendor_nf),
        
                ], 400);

            } elseif (count($material_nf) > 0){

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'Success with skiped material',
                    "msg" => 'Data stored successfully!',
                    "Not Found Material" => array_unique($material_nf),
        
                ], 200);

            } else {

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'Success',
                    "msg" => 'Data stored successfully!',
        
                ], 200);

            }

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "msg" => 'err: '.$e,
    
            ], 400);

        }
    }

    private function stringtoupper($string)
    {
        $string = strtolower($string);
        $string = strtoupper($string);
        return $string;
    }

    private function checkNumber($num)
    {
        $num = str_replace(' ', '', $num);
        $num = intval($num);
        return $num;
    }
}
