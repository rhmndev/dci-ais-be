<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\Receiving;
use App\ReceivingDetails;
use App\ReceivingVDetails;
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
            'flag' => 'required|numeric|max:1',
            
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $flag = ($request->flag != 0) ? 1 : 0;
        $vendor = auth()->user()->vendor_code;

        try {
    
            $data = array();
            $Receiving = new Receiving;
            $ReceivingDetails = new ReceivingDetails;
            $ReceivingVDetails = new ReceivingVDetails;
            $Settings = new Settings;

            $Material_Perpage = $Settings->scopeGetValue($Settings, 'Material_Perpage')[0]['name'];
            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $Receiving->getAllData($keyword, $request->columns, $request->sort, $order, $flag, $vendor);
            $results = $Receiving->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $flag, $vendor);

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

                if ( $flag == 0 ){

                    $PODetails = $ReceivingVDetails->getPODetails($result->PO_Number, $Material_Perpage, $result->vendor);
        
                } elseif ( $flag == 1 ) {

                    $PODetails = $ReceivingDetails->getPODetails($result->PO_Number, $Material_Perpage, $result->vendor);
        
                }

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

                    $data_tmp_d['del_note'] = $PODetail->del_note;
                    $data_tmp_d['del_date'] = $PODetail->del_date;
                    $data_tmp_d['del_qty'] = $PODetail->qty;
                    $data_tmp_d['prod_date'] = $PODetail->prod_date;
                    $data_tmp_d['prod_lot'] = $PODetail->prod_lot;
                    $data_tmp_d['material'] = $PODetail->material;
                    $data_tmp_d['o_name'] = $PODetail->o_name;
                    $data_tmp_d['o_code'] = $PODetail->o_code;

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

                $Material = new Material;
                $Vendor = new Vendor;

                $vendor_nf = array();
                $material_nf = array();

                foreach ($results as $result) {

                    $checkVendor = $Vendor->checkVendor($result->Vendor);
    
                    if (count($checkVendor) > 0) {

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
                        $Receiving->vendor = $result->Vendor;
                        $Receiving->PO_Status = 0;
                        $Receiving->flag = 0;
                        $Receiving->reference = null;
                        $Receiving->HeaderText = null;
    
                        $Receiving->created_by = auth()->user()->username;
                        $Receiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->updated_by = auth()->user()->username;
                        $Receiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->save();
    
                        $checkMaterial = $Material->checkMaterial($material_id);
    
                        if (count($checkMaterial) > 0) {
    
                            $ReceivingVDetails = ReceivingVDetails::firstOrNew([
                                'PO_Number' => $PO_Number,
                                'item_po' => $result->ItemNo,
                            ]);

                            $ReceivingVDetails->PO_Number = $PO_Number;
                            $ReceivingVDetails->create_date = $create_date;
                            $ReceivingVDetails->delivery_date = $delivery_date;
                            $ReceivingVDetails->release_date = $release_date;
                            $ReceivingVDetails->material_id = $material_id;
                            $ReceivingVDetails->material_name = $material_name;
                            $ReceivingVDetails->item_po = $result->ItemNo;
                            $ReceivingVDetails->index_po = intval($result->ItemNo / 10);
                            $ReceivingVDetails->qty = $result->Quantity;
                            $ReceivingVDetails->unit = $result->Meins;
                            $ReceivingVDetails->price = $result->Price;
                            $ReceivingVDetails->currency = $result->Currency;
                            $ReceivingVDetails->vendor = $result->Vendor;
                            $ReceivingVDetails->ppn = $result->Mwskz;
                            
                            if (!$ReceivingVDetails->exists) {

                                $ReceivingVDetails->del_note = null;
                                $ReceivingVDetails->del_date = $delivery_date;
                                $ReceivingVDetails->del_qty = $result->Quantity;
                                $ReceivingVDetails->prod_date = $create_date;
                                $ReceivingVDetails->prod_lot = null;
                                $ReceivingVDetails->material = null;
                                $ReceivingVDetails->o_name = null;
                                $ReceivingVDetails->o_code = null;

                                $ReceivingVDetails->receive_qty = $result->Quantity;
                                $ReceivingVDetails->reference = null;
                                $ReceivingVDetails->gudang_id = null;
                                $ReceivingVDetails->gudang_nm = null;
                                $ReceivingVDetails->batch = null;

                            }
    
                            $ReceivingVDetails->created_by = auth()->user()->username;
                            $ReceivingVDetails->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $ReceivingVDetails->updated_by = auth()->user()->username;
                            $ReceivingVDetails->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $ReceivingVDetails->save();
                            
                        } else {
                            array_push($material_nf, $material_id);
                        }
    
                    } else {
                        array_push($vendor_nf, $result->Vendor);
                    }

                }

                if (count($vendor_nf) > 0){

                    return response()->json([
            
                        "result" => false,
                        "msg_type" => 'failed',
                        "message" => 'Sync SAP unsuccessfully!',
                        "Not Found Vendor" => array_unique($vendor_nf),
            
                    ], 400);

                } elseif (count($material_nf) > 0){

                    return response()->json([
            
                        "result" => true,
                        "msg_type" => 'Success',
                        "message" => 'Sync SAP successfully with skiped material!',
                        "Not Found Material" => array_unique($material_nf),
            
                    ], 200);

                } else {

                    return response()->json([
            
                        "result" => true,
                        "msg_type" => 'success',
                        "message" => 'Sync SAP Success',
            
                    ], 200);

                }

            } else {

                return response()->json([
        
                    "result" => false,
                    "msg_type" => 'failed',
                    "message" => 'Data not found',
        
                ], 400);

            }


        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: '.$e,
    
            ], 400);

        }

    }

    public function postGR(Request $request)
    {

        $json = $request->getContent();

        try {

            $inputs = json_decode($json);

            $reference = $this->stringtoupper($request->reference);
            $documentDate = date('Y-m-d\TH:i:s', strtotime($request->documentDate));
            $headerText = $request->headerText != '' ? $this->stringtoupper($request->headerText) : '';

            if (count($inputs) > 0){

                $getHeader = $this->getSAPToken();

                $Settings = new Settings;
                $code_sap = $Settings->scopeGetValue($Settings, 'code_sap');
                $code = $code_sap[1]['name'];

                $data = array(
                    'PostingDate' => date('Y-m-d\T00:00:00'),
                    'DocumentDate' => $documentDate,
                    'Reference' => $reference,
                    'HeaderText' => $headerText,
                    'GoodReceiptSet' => array(),
                );

                foreach ($inputs as $input) {
    
                    $data_tmp = array();
                    $data_tmp['Reference'] = $reference;
                    $data_tmp['Item'] = strval($input->index_po);
                    $data_tmp['PoNo'] = $input->PO_Number;
                    $data_tmp['ItemNo'] = $input->item_po;
                    $data_tmp['Matnr'] = $input->material_id;
                    $data_tmp['Werks'] = $code;
                    $data_tmp['Lgort'] = $input->gudang_id;
                    $data_tmp['Batch1'] = $input->batch;
                    $data_tmp['EntryQty'] = strval($input->receive_qty);
                    $data_tmp['Satuan'] = $input->unit;

                    array_push($data['GoodReceiptSet'], $data_tmp);

                }

                $postSAP = $this->postSAP($data, $getHeader);
                $postSAP = json_decode($postSAP);

                if ( isset($postSAP->d) ){

                    if ( $postSAP->d->Status === 'S' ){

                        #region Insert to Receiving
                        $Material = new Material;
        
                        foreach ($inputs as $input) {
        
                            $PO_Number = $this->stringtoupper($input->PO_Number);
                            $material_id = $this->stringtoupper($input->material_id);
                            $material_name = $this->stringtoupper($input->material_name);
        
                            $updateFlag = Receiving::where('PO_Number', $PO_Number)->update(['flag' => 1]);
            
                            // $Receiving = Receiving::firstOrNew(['PO_Number' => $PO_Number]);
            
                            // $Receiving->PO_Number = $PO_Number;
                            // $Receiving->create_date = $input->create_date;
                            // $Receiving->delivery_date = $input->delivery_date;
                            // $Receiving->release_date = $input->release_date;
                            // $Receiving->vendor = $input->vendor;
                            // $Receiving->PO_Status = 1;
                            // $Receiving->reference = $reference;
                            // $Receiving->HeaderText = $headerText;
            
                            // $Receiving->created_by = auth()->user()->username;
                            // $Receiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            // $Receiving->updated_by = auth()->user()->username;
                            // $Receiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            // $Receiving->save();
            
                            $checkMaterial = $Material->checkMaterial($material_id);
        
                            if (count($checkMaterial) > 0) {
        
                                $ReceivingDetails = ReceivingDetails::firstOrNew([
                                    'PO_Number' => $PO_Number,
                                    'item_po' => $input->item_po,
                                ]);
        
                                $ReceivingDetails->PO_Number = $PO_Number;
                                $ReceivingDetails->create_date = $input->create_date;
                                $ReceivingDetails->delivery_date = $input->delivery_date;
                                $ReceivingDetails->release_date = $input->release_date;
                                $ReceivingDetails->material_id = $material_id;
                                $ReceivingDetails->material_name = $material_name;
                                $ReceivingDetails->item_po = $input->item_po;
                                $ReceivingDetails->index_po = $input->index_po;
                                $ReceivingDetails->qty = $input->qty;
                                $ReceivingDetails->unit = $input->unit;
                                $ReceivingDetails->price = $input->price;
                                $ReceivingDetails->currency = $input->currency;
                                $ReceivingDetails->vendor = $input->vendor;
                                $ReceivingDetails->ppn = $input->ppn;
        
                                $ReceivingDetails->del_note = $input->del_note;
                                $ReceivingDetails->del_date = $input->del_date;
                                $ReceivingDetails->del_qty = $input->del_qty;
                                $ReceivingDetails->prod_date = $input->prod_date;
                                $ReceivingDetails->prod_lot = $input->prod_lot;
                                $ReceivingDetails->material = $input->material;
                                $ReceivingDetails->o_name = $input->o_name;
                                $ReceivingDetails->o_code = $input->o_code;
        
                                $ReceivingDetails->receive_qty = $input->receive_qty;
                                $ReceivingDetails->reference = $reference;
                                $ReceivingDetails->gudang_id = $input->gudang_id;
                                $ReceivingDetails->gudang_nm = $input->gudang_nm;
                                $ReceivingDetails->batch = $input->batch;
        
                                $ReceivingDetails->created_by = auth()->user()->username;
                                $ReceivingDetails->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                $ReceivingDetails->updated_by = auth()->user()->username;
                                $ReceivingDetails->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                $ReceivingDetails->save();
                                
                            }
        
                        }
                        #endregion

                        if ( $updateFlag && $ReceivingDetails ){

                            return response()->json([
                        
                                "result" => true,
                                "msg_type" => 'Success',
                                "message" => 'Data success sended',
                    
                            ], 200);

                        } else {

                            return response()->json([
                        
                                "result" => false,
                                "msg_type" => 'failed',
                                "message" => 'Update data failed',
                    
                            ], 400);

                        }

                    } elseif( $postSAP->d->Status === 'E' ) {

                        return response()->json([
                    
                            "result" => false,
                            "msg_type" => 'failed',
                            "message" => 'SAP: '.$postSAP->d->Message,
                
                        ], 400);

                    }

                } else {

                    return response()->json([
                
                        "result" => false,
                        "msg_type" => 'failed',
                        "message" => 'SAP: '.$postSAP->error->message->value,
            
                    ], 400);

                }

            } else {

                return response()->json([
        
                    "result" => false,
                    "msg_type" => 'failed',
                    "message" => 'Data not found!',
        
                ], 400);

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

    private function dateMaking($date)
    {
        $date = explode('.', $date);
        $date = $date[0].'-'.$date[1].'-'.$date[2];
        $date = date('Y-m-d', strtotime($date));
        return $date;
    }

    private function getSAPToken()
    {

        $us = 'wcs-abap';
        $pw = 'Wilmar12';
        $account = $us.':'.$pw;
            
        $url = 'http://erpdev-dp.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/Headerset?sap-client=110&\$format=json';

        try {

            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, $url);
            $header = array('x-csrf-token: Fetch', 'Connection: keep-alive');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERPWD, $account);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
    
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            curl_close($ch);

            $data = $this->ArrHeader($header);

            return [
                'x-csrf-token' => $data['x-csrf-token'],
                'set-cookie' => $data['set-cookie'],
            ];

        } catch (\Exception $e) {

            return 'err: '.$e;

        }
    }

    private function ArrHeader($res)
    {
        $arr = array();

        $data = substr($res, 0 , strpos($res, "\r\n\r\n"));

        foreach (explode("\r\n", $data) as $key => $value) {
            if ($key === 0){

                $arr['http_code'] = $value;

            } else {
                list($k, $v) = explode(': ', $value);

                $arr[$k] = $v;
            }
        }

        return $arr;
    }

    private function postSAP($data, $headervalue)
    {

        $us = 'wcs-abap';
        $pw = 'Wilmar12';
        $account = $us.':'.$pw;
            
        $url = 'http://erpdev-dp.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/Headerset?sap-client=110';

        $header = array(
            'X-CSRF-TOKEN: '.$headervalue['x-csrf-token'],
            'Content-Type: application/json',
            'Accept: application/json',
        );
        $postdata = json_encode($data);

        try {

            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_COOKIE, 'sap-usercontext=sap-client=110; path=/; Domain=erpdev-dp.dharmap.com;');
            curl_setopt($ch, CURLOPT_COOKIE, $headervalue['set-cookie']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-CSRF-TOKEN: '.$headervalue['x-csrf-token'],
                'Content-Type: application/json',
                'Accept: application/json',
            ));
            curl_setopt($ch, CURLOPT_USERPWD, $account);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            curl_close($ch);

            return $response;

        } catch (\Exception $e) {

            return 'err: '.$e;

        }
    }
}
