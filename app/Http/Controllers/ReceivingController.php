<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Receiving;
use App\ReceivingMaterial;
use App\Material;
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
            'flag' => 'required|numeric',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $flag = ($request->flag != 0) ? 1 : 0;

        try {
    
            $data = array();
            $Receiving = new Receiving;
            $ReceivingMaterial = new ReceivingMaterial;
            $Settings = new Settings;

            $Material_Perpage = $Settings->scopeGetValue($Settings, 'Material_Perpage')[0]['name'];
            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $Receiving->getAllData($keyword, $request->columns, $request->sort, $order, $flag);
            $results = $Receiving->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $flag);

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

                $PODetails = $ReceivingMaterial->getPODetails($result->PO_Number, $Material_Perpage);
                foreach ($PODetails as $PODetail) {

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $PODetail->_id;
                    $data_tmp_d['PO_Number'] = $PODetail->PO_Number;
                    $data_tmp_d['material_id'] = $PODetail->material_id;
                    $data_tmp_d['material_name'] = $PODetail->material_name;
                    $data_tmp_d['qty'] = number_format($PODetail->qty);
                    $data_tmp_d['unit'] = $PODetail->unit;
                    $data_tmp_d['price'] = number_format($PODetail->price);
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
                    $data_tmp_d['sub_total'] = number_format($total);

                    $total = ((str_replace("%", "", $data_tmp_d['ppn']) / 100) * $total) + $total;

                    $data_tmp_d['total'] = number_format($total);

    
                    $total_po = $total_po + $total;

                    array_push($data_tmp['data'], $data_tmp_d);

                }
                $data_tmp['total'] = number_format($total_po);
    
                array_push($data, $data_tmp);
            }

            return response()->json([
                'type' => 'success',
                'data' => $data,
                'total' => count($resultAlls),
                'Material_Perpage' => $Material_Perpage
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

                // [Comp] =>
                // [Ersda] =>
                // [PoNo] => 5611000006
                // [ItemNo] => 00010
                // [Mtart] => ZOHP
                // [Matnr] => 01CI120DLF2OHP
                // [Maktx] => INNER CABLE DIA 1 0 CI FR DL 2004 02
                // [Quantity] => 1000.000
                // [Meins] => PCE
                // [Price] => 10000.000
                // [Currency] => IDR
                // [Vendor] => 0000100097
                // [Mwskz] => V1
                // [Crdate] => 10.08.2021
                // [Deldate] => 24.08.2021
                // [Reldate] => 10.08.2021

                $Material = new Material;

                foreach ($results as $result) {

                    $PO_Number = $this->stringtoupper($result->PoNo);
                    $material_id = $this->stringtoupper($result->Matnr);
                    $material_name = $this->stringtoupper($result->Maktx);

                    $create_date = $this->dateMaking($result->Crdate);
                    $delivery_date = $this->dateMaking($result->Deldate);
                    $release_date = $this->dateMaking($result->Reldate);

                    $Receiving = Receiving::firstOrNew(['PO_Number' => $PO_Number]);
                    $Receiving->PO_Number = $PO_Number;
                    $Receiving->create_date = $create_date;
                    $Receiving->delivery_date = $delivery_date;
                    $Receiving->release_date = $release_date;
                    $Receiving->PO_Status = 0;
                    $Receiving->flag = 0;

                    $Receiving->created_by = auth()->user()->username;
                    $Receiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $Receiving->updated_by = auth()->user()->username;
                    $Receiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $Receiving->save();

                    $checkMaterial = $Material->checkMaterial($material_id);

                    if ($checkMaterial > 0) {

                        $ReceivingMaterial = ReceivingMaterial::firstOrNew([
                            'PO_Number' => $PO_Number,
                            'material_id' => $material_id,
                        ]);
                        $ReceivingMaterial->PO_Number = $PO_Number;
                        $ReceivingMaterial->material_id = $material_id;
                        $ReceivingMaterial->material_name = $material_name;
                        $ReceivingMaterial->qty = $result->Quantity;
                        $ReceivingMaterial->unit = $result->Meins;
                        $ReceivingMaterial->price = $result->Price;
                        $ReceivingMaterial->currency = $result->Currency;
                        $ReceivingMaterial->vendor = $result->Vendor;
                        $ReceivingMaterial->ppn = $result->Mwskz;
                        $ReceivingMaterial->del_note = null;
                        $ReceivingMaterial->del_date = $delivery_date;
                        $ReceivingMaterial->del_qty = $result->Quantity;
                        $ReceivingMaterial->prod_date = $create_date;
                        $ReceivingMaterial->prod_lot = null;
                        $ReceivingMaterial->material = null;
                        $ReceivingMaterial->o_name = null;
                        $ReceivingMaterial->o_code = null;

                        $ReceivingMaterial->created_by = auth()->user()->username;
                        $ReceivingMaterial->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $ReceivingMaterial->updated_by = auth()->user()->username;
                        $ReceivingMaterial->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $ReceivingMaterial->save();
                        
                    }

                }

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'success',
                    "msg" => 'Sync SAP Success',
        
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

    private function dateMaking($date)
    {
        $date = explode('.', $date);
        $date = $date[0].'-'.$date[1].'-'.$date[2];
        $date = date('Y-m-d', strtotime($date));
        return $date;
    }
}
