<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Receiving;
use App\ReceivingMaterial;
use App\Settings;
use Carbon\Carbon;
use GuzzleHttp\Client;

class ReceivingController extends Controller
{
    //
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
    
            $data = array();
            $Receiving = new Receiving;
            $ReceivingMaterial = new ReceivingMaterial;
            $Settings = new Settings;

            $resultAlls = $Receiving->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Receiving->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

            foreach ($results as $result) {
                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['data'] = array();

                $PODetails = $ReceivingMaterial->getPODetails($result->PO_Number);
                foreach ($PODetails as $PODetail) {

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $PODetail->_id;
                    $data_tmp_d['PO_Number'] = $PODetail->PO_Number;
                    $data_tmp_d['material_id'] = $PODetail->material_id;
                    $data_tmp_d['material_name'] = $PODetail->material_name;
                    $data_tmp_d['qty'] = $PODetail->qty;
                    $data_tmp_d['unit'] = $PODetail->unit;
                    $data_tmp_d['price'] = $PODetail->price;
                    $data_tmp_d['currency'] = $PODetail->currency;
                    $data_tmp_d['vendor'] = $PODetail->vendor;
            
                    $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    foreach ($SettingPPNs as $SettingPPN) {
                        $ppn = explode(';', $SettingPPN['name']);
                        if ($ppn[0] === $PODetail->ppn){
                            $data_tmp_d['ppn'] = $ppn[1];
                        }
                    };
                    // $code = $code_sap[0]['name'];

                    // $data_tmp_d['ppn'] = $PODetail->ppn;
                    // $data_tmp_d['ppn'] = $SettingPPNs;
    
                    array_push($data_tmp['data'], $data_tmp_d);

                }
    
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

    public function SyncSAP(Request $request)
    {

        $request->validate([
            'date' => 'required|date',
        ]);

        try {

            $Settings = new Settings;
            $code_sap = $Settings->scopeGetValue($Settings, 'code_sap');
            $code = $code_sap[0]['name'];

            $date = date('Y-m-d\TH:i:s', strtotime($request->date));

            $client = new Client;
            $json = $client->get("http://erpdev-dp.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/PurchaseOrderSet?\$filter=Comp eq '$code' and Ersda eq datetime'$date'&sap-client=110&\$format=json", [
                'auth' => [
                    'wcs-abap',
                    'Wilmar12'
                ],
            ]);
            $results = json_decode($json->getBody())->d->results;

            if (count($results) > 0){

                $data_po = array();
                $data_material = array();

                $Receiving = new Receiving;
                $ReceivingMaterial = new ReceivingMaterial;

                foreach ($results as $result) {

                    $data_tmp = array();
                    $data_tmp_m = array();

                    $PO_Number = $this->stringtoupper($result->PoNo);
                    $material_id = $this->stringtoupper($result->Matnr);
                    $material_name = $this->stringtoupper($result->Maktx);

                    $QueryGetDataByFilter = Receiving::query();

                    $QueryGetDataByFilter = $QueryGetDataByFilter->where('PO_Number', $PO_Number);

                    if (count($QueryGetDataByFilter->get()) > 0){
                        $QueryGetDataByFilter = $QueryGetDataByFilter->delete();
                        ReceivingMaterial::where('PO_Number', $PO_Number)->delete();
                    }

                    if (count($data_po) > 0){

                        if (end($data_po)['PO_Number'] != $PO_Number){

                            $data_tmp['PO_Number'] = $PO_Number;

                            $data_tmp['created_by'] = auth()->user()->username;
                            $data_tmp['created_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());
        
                            $data_tmp['updated_by'] = auth()->user()->username;
                            $data_tmp['updated_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());
    
                            array_push($data_po, $data_tmp);
                            
                        }

                    } else {

                        $data_tmp['PO_Number'] = $PO_Number;

                        $data_tmp['created_by'] = auth()->user()->username;
                        $data_tmp['created_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());
    
                        $data_tmp['updated_by'] = auth()->user()->username;
                        $data_tmp['updated_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                        array_push($data_po, $data_tmp);

                    }
                    
                    $data_tmp_m['PO_Number'] = $PO_Number;
                    $data_tmp_m['material_id'] = $material_id;
                    $data_tmp_m['material_name'] = $material_name;
                    $data_tmp_m['qty'] = $result->Quantity;
                    $data_tmp_m['unit'] = $result->Meins;
                    $data_tmp_m['price'] = $result->Price;
                    $data_tmp_m['currency'] = $result->Currency;
                    $data_tmp_m['vendor'] = $result->Vendor;
                    $data_tmp_m['ppn'] = $result->Mwskz;

                    $data_tmp_m['created_by'] = auth()->user()->username;
                    $data_tmp_m['created_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                    $data_tmp_m['updated_by'] = auth()->user()->username;
                    $data_tmp_m['updated_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                    // Converting to Array
                    array_push($data_material, $data_tmp_m);

                }

                $Receiving->insert($data_po);
                $ReceivingMaterial->insert($data_material);

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'success',
                    "msg" => 'Sync SAP Success',
                    // "msg" => $data_material,
        
                ], 200);

            } else {

                return response()->json([
        
                    "result" => false,
                    "msg_type" => 'failed',
                    "msg" => 'Data not found',
        
                ], 400);

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
}
