<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\ReceivingVendor;
use App\ReceivingDetails;
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
            $ReceivingVendor = new ReceivingVendor;
            $ReceivingVendorDetails = new ReceivingDetails;
            $Settings = new Settings;

            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $ReceivingVendor->getAllData($search, $columns, $request->sort, $order, 0, $vendor);
            $results = $ReceivingVendor->getData($search, $columns, $request->perpage, $request->page, $request->sort, $order, 0, $vendor);

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

                $PODetails = $ReceivingVendorDetails->getPODetails($result->PO_Number, $result->MPerpage, $vendor);
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
                    $data_tmp_d['index_po'] = $PODetail->index_po;
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
    
                        $ReceivingVendor = Receiving::firstOrNew(['PO_Number' => $PO_Number]);

                        $ReceivingVendor->PO_Number = $PO_Number;
                        $ReceivingVendor->create_date = $create_date;
                        $ReceivingVendor->delivery_date = $delivery_date;
                        $ReceivingVendor->release_date = $release_date;
                        $ReceivingVendor->vendor = $input->vendor;
                        $ReceivingVendor->PO_Status = 0;
                        $ReceivingVendor->reference = null;
                        $ReceivingVendor->HeaderText = null;
    
                        $ReceivingVendor->created_by = 'SAP';
                        $ReceivingVendor->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $ReceivingVendor->updated_by = 'SAP';
                        $ReceivingVendor->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $ReceivingVendor->save();
    
                        $details = $input->data;
    
                        if (count($details) > 0){
    
                            foreach ($details as $detail) {
    
                                $material_id = $this->stringtoupper($detail->material_id);
                                $material_name = $this->stringtoupper($detail->material_name);
    
                                $qty = $this->checkNumber($detail->qty);
                                $price = $this->checkNumber($detail->price);
    
                                $checkMaterial = $Material->checkMaterial($material_id);
    
                                if (count($checkMaterial) > 0) {
            
                                    $ReceivingVendorDetails = ReceivingDetails::firstOrNew([
                                        'PO_Number' => $PO_Number,
                                        'item_po' => $detail->item_po,
                                    ]);
                                    $ReceivingVendorDetails->PO_Number = $PO_Number;
                                    $ReceivingVendorDetails->create_date = $create_date;
                                    $ReceivingVendorDetails->delivery_date = $delivery_date;
                                    $ReceivingVendorDetails->release_date = $release_date;
                                    $ReceivingVendorDetails->material_id = $material_id;
                                    $ReceivingVendorDetails->material_name = $material_name;
                                    $ReceivingVendorDetails->item_po = $detail->item_po;
                                    $ReceivingVendorDetails->index_po = intval($detail->item_po / 10);
                                    $ReceivingVendorDetails->qty = $qty;
                                    $ReceivingVendorDetails->unit = $detail->unit;
                                    $ReceivingVendorDetails->price = $price;
                                    $ReceivingVendorDetails->currency = $detail->currency;
                                    $ReceivingVendorDetails->vendor = $input->vendor;
                                    $ReceivingVendorDetails->ppn = $detail->ppn;
                            
                                    if (!$ReceivingVendorDetails->exists) {
        
                                        $ReceivingVendorDetails->del_note = null;
                                        $ReceivingVendorDetails->del_date = $delivery_date;
                                        $ReceivingVendorDetails->del_qty = $result->Quantity;
                                        $ReceivingVendorDetails->prod_date = $create_date;
                                        $ReceivingVendorDetails->prod_lot = null;
                                        $ReceivingVendorDetails->material = null;
                                        $ReceivingVendorDetails->o_name = null;
                                        $ReceivingVendorDetails->o_code = null;

                                        $ReceivingVendorDetails->receive_qty = $result->Quantity;
                                        $ReceivingVendorDetails->reference = null;
                                        $ReceivingVendorDetails->gudang_id = null;
                                        $ReceivingVendorDetails->gudang_nm = null;
                                        $ReceivingVendorDetails->batch = null;
        
                                    }
            
                                    $ReceivingVendorDetails->created_by = 'SAP';
                                    $ReceivingVendorDetails->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                    $ReceivingVendorDetails->updated_by = 'SAP';
                                    $ReceivingVendorDetails->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                    $ReceivingVendorDetails->save();
                                    
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
                    "message" => 'Data stored unsuccessfully!',
                    "Not Found Vendor" => array_unique($vendor_nf),
        
                ], 400);

            } elseif (count($material_nf) > 0){

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'Success',
                    "message" => 'Data stored successfully with skiped material!',
                    "Not Found Material" => array_unique($material_nf),
        
                ], 200);

            } else {

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'Success',
                    "message" => 'Data stored successfully!',
        
                ], 200);

            }

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: '.$e,
    
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
