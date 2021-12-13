<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\Receiving;
use App\ReceivingDetails;
use App\GoodReceiving;
use App\GoodReceivingDetail;
use App\Settings;
use Carbon\Carbon;
use GuzzleHttp\Client;

class ReceivingController extends Controller
{
    //
    public function index(Request $request)
    {
        $request->validate([
            'columns'   => 'required',
            'perpage'   => 'required|numeric',
            'page'      => 'required|numeric',
            'sort'      => 'required|string',
            'order'     => 'string',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $vendor = auth()->user()->vendor_code;

        try {

            $data = array();
            $Receiving = new Receiving;
            $ReceivingDetails = new ReceivingDetails;
            $Settings = new Settings;

            $Material_Perpage = $Settings->scopeGetValue($Settings, 'Material_Perpage')[0]['name'];
            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $Receiving->getAllData($keyword, $request->columns, $request->sort, $order, $vendor);
            $results = $Receiving->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $vendor);

            foreach ($results as $result) {

                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['PO_Status'] = $POStatus[$result->PO_Status]['name'];
                $data_tmp['create_date'] = date('d-m-Y', strtotime($result->create_date));
                $data_tmp['delivery_date'] = date('d-m-Y', strtotime($result->delivery_date));
                $data_tmp['release_date'] = date('d-m-Y', strtotime($result->release_date));
                $data_tmp['data'] = array();
                $total_po = 0;

                $PODetails = $ReceivingDetails->getPODetails($result->PO_Number, $Material_Perpage, $result->vendor);

                foreach ($PODetails as $PODetail) {

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $PODetail->_id;
                    $data_tmp_d['PO_Number'] = $PODetail->PO_Number;
                    $data_tmp_d['create_date'] = date('d-m-Y', strtotime($result->create_date));
                    $data_tmp_d['delivery_date'] = date('d-m-Y', strtotime($result->delivery_date));
                    $data_tmp_d['release_date'] = date('d-m-Y', strtotime($result->release_date));
                    $data_tmp_d['material_id'] = $PODetail->material_id;
                    $data_tmp_d['material_name'] = $PODetail->material_name;
                    $data_tmp_d['item_po'] = $PODetail->item_po;
                    $data_tmp_d['index_po'] = $PODetail->index_po;
                    $data_tmp_d['qty'] = $PODetail->qty;
                    $data_tmp_d['unit'] = $PODetail->unit;
                    $data_tmp_d['price'] = $PODetail->price;
                    $data_tmp_d['currency'] = $PODetail->currency;
                    $data_tmp_d['vendor'] = $PODetail->vendor;
                    $data_tmp_d['ppn'] = $PODetail->ppn;
                    $data_tmp_d['QRCode'] = $result->_id . ';' . $PODetail->_id;

                    $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    foreach ($SettingPPNs as $SettingPPN) {
                        $ppn = explode(';', $SettingPPN['name']);
                        if ($ppn[0] === $PODetail->ppn) {
                            $data_tmp_d['ppnp'] = $ppn[1];
                        }
                    };

                    $data_tmp_d['del_note'] = $PODetail->del_note;
                    $data_tmp_d['del_date'] = date('d-m-Y', strtotime($PODetail->del_date));
                    $data_tmp_d['del_qty'] = $PODetail->del_qty;
                    $data_tmp_d['prod_date'] = date('d-m-Y', strtotime($PODetail->prod_date));
                    $data_tmp_d['prod_lot'] = $PODetail->prod_lot;
                    $data_tmp_d['material'] = $PODetail->material;
                    $data_tmp_d['o_name'] = $PODetail->o_name;
                    $data_tmp_d['o_code'] = $PODetail->o_code;

                    $total = $PODetail->qty * $PODetail->price;
                    $data_tmp_d['sub_total'] = $total;

                    $total = ((str_replace("%", "", $data_tmp_d['ppnp']) / 100) * $total) + $total;

                    $data_tmp_d['total'] = $total;


                    $total_po = $total_po + $total;

                    array_push($data_tmp['data'], $data_tmp_d);
                }
                $data_tmp['total'] = $total_po;

                if ($data_tmp['total'] > 0) {

                    array_push($data, $data_tmp);
                }
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
                'message' => 'Err: ' . $e . '.',
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

            if (count($results) > 0) {

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

                        $PR_Number = $this->stringtoupper($result->PurchaseReq);
                        $gudang_id = $this->stringtoupper($result->Warehouse);

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

                        $Receiving->created_by = auth()->user()->username;
                        $Receiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->updated_by = auth()->user()->username;
                        $Receiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->save();

                        $checkMaterial = $Material->checkMaterial($material_id);

                        if (count($checkMaterial) > 0) {

                            $ReceivingDetails = ReceivingDetails::firstOrNew([
                                'PO_Number' => $PO_Number,
                                'item_po' => $result->ItemNo,
                            ]);

                            $ReceivingDetails->PO_Number = $PO_Number;
                            $ReceivingDetails->create_date = $create_date;
                            $ReceivingDetails->delivery_date = $delivery_date;
                            $ReceivingDetails->release_date = $release_date;
                            $ReceivingDetails->PR_Number = $PR_Number;
                            $ReceivingDetails->material_id = $material_id;
                            $ReceivingDetails->material_name = $material_name;
                            $ReceivingDetails->item_po = $result->ItemNo;
                            $ReceivingDetails->index_po = intval($result->ItemNo / 10);
                            $ReceivingDetails->qty = $result->Quantity;
                            $ReceivingDetails->unit = $result->Meins;
                            $ReceivingDetails->price = $result->Price;
                            $ReceivingDetails->currency = $result->Currency;
                            $ReceivingDetails->vendor = $result->Vendor;
                            $ReceivingDetails->ppn = $result->Mwskz;

                            $ReceivingDetails->gudang_id = $gudang_id;

                            $SettingGudangDatas = $Settings->scopeGetValue($Settings, 'Gudang');
                            foreach ($SettingGudangDatas as $SettingGudangData) {
                                $gd = explode(';', $SettingGudangData['name']);
                                if ($gd[0] === $gudang_id) {
                                    $ReceivingDetails->gudang_nm = $gd[1];
                                }
                            };

                            if (!$ReceivingDetails->exists) {

                                $ReceivingDetails->del_note = null;
                                $ReceivingDetails->del_date = $delivery_date;
                                $ReceivingDetails->del_qty = $result->Quantity;
                                $ReceivingDetails->prod_date = $create_date;
                                $ReceivingDetails->prod_lot = null;
                                $ReceivingDetails->material = null;
                                $ReceivingDetails->o_name = null;
                                $ReceivingDetails->o_code = null;

                                $ReceivingDetails->flag = 0;
                            }

                            $ReceivingDetails->created_by = auth()->user()->username;
                            $ReceivingDetails->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $ReceivingDetails->updated_by = auth()->user()->username;
                            $ReceivingDetails->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $ReceivingDetails->save();
                        } else {
                            array_push($material_nf, $material_id);
                        }
                    } else {
                        array_push($vendor_nf, $result->Vendor);
                    }
                }

                if (count($vendor_nf) > 0) {

                    return response()->json([

                        "result" => false,
                        "msg_type" => 'failed',
                        "message" => 'Sync SAP unsuccessfully!',
                        "Not Found Vendor" => array_unique($vendor_nf),

                    ], 400);
                } elseif (count($material_nf) > 0) {

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
                "message" => 'err: ' . $e,

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

            if (count($inputs) > 0) {

                foreach ($inputs as $input) {
                    $checkData = GoodReceiving::where('SJ_Number', $reference)
                        ->where('PO_Number', $input->PO_Number)
                        ->where('vendor_id', $input->vendor)
                        ->first();
                    if ($checkData) {

                        return response()->json([
        
                            "result" => false,
                            "msg_type" => 'failed',
                            "message" => 'Data with No. Surat Jalan '.$reference.' & PO Number '.$input->PO_Number.' already exist.',
        
                        ], 400);
                    }
                }


                $dataGR = array();
                $dataPO = array();

                $getHeader = $this->getSAPToken();

                $Vendor = new Vendor;
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

                    array_push($dataGR, $input->PO_Number);
                    array_push($dataPO, $input->PO_Number);
                    array_push($data['GoodReceiptSet'], $data_tmp);
                }

                $postSAP = $this->postSAP($data, $getHeader);
                $postSAP = json_decode($postSAP);

                if (isset($postSAP->d)) {

                    if ($postSAP->d->Status === 'S') {

                        #region Insert to Receiving
                        $Material = new Material;

                        // $GR_Number = $this->genGR($dataGR).'-'.strtotime($data['PostingDate']);
                        $PO_Number_joins = $this->genPO($dataPO);

                        foreach ($inputs as $input) {

                            $PO_Number = $this->stringtoupper($input->PO_Number);
                            $material_id = $this->stringtoupper($input->material_id);
                            $material_name = $this->stringtoupper($input->material_name);

                            $Receiving = new Receiving;
                            $ReceivingData = $Receiving->getFirst($PO_Number);

                            $GoodReceiving = GoodReceiving::create([
                                'SJ_Number' => $reference,
                                'PO_Number' => join(", ", $PO_Number_joins)
                            ]);

                            $GoodReceiving->GR_Number = '-';
                            $GoodReceiving->PO_Number = join(", ", $PO_Number_joins);
                            $GoodReceiving->SJ_Number = $reference;

                            $GoodReceiving->create_date = $input->create_date;
                            $GoodReceiving->delivery_date = $input->delivery_date;
                            $GoodReceiving->release_date = $input->release_date;

                            $GoodReceiving->PO_Status = $ReceivingData->PO_Status;
                            $GoodReceiving->GR_Date = '-';

                            $GoodReceiving->vendor_id = $input->vendor;
                            $GoodReceiving->vendor_nm = $Vendor->checkVendor($input->vendor)[0]->name;
                            $GoodReceiving->warehouse_id = $input->gudang_id;
                            $GoodReceiving->warehouse_nm = $input->gudang_nm;
                            $GoodReceiving->description = null;
                            $GoodReceiving->headerText = $headerText;

                            $GoodReceiving->created_by = auth()->user()->username;
                            $GoodReceiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $GoodReceiving->updated_by = auth()->user()->username;
                            $GoodReceiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $GoodReceiving->save();

                            $checkMaterial = $Material->checkMaterial($material_id);

                            if (count($checkMaterial) > 0) {

                                $GoodReceivingDetail = GoodReceivingDetail::create([
                                    'reference' => $reference,
                                    'PO_Number' => $PO_Number,
                                    'item_po' => $input->item_po,
                                ]);

                                $GoodReceivingDetail->GR_Number = '-';

                                $GoodReceivingDetail->PO_Number = $PO_Number;
                                $GoodReceivingDetail->create_date = $input->create_date;
                                $GoodReceivingDetail->delivery_date = $input->delivery_date;
                                $GoodReceivingDetail->release_date = $input->release_date;

                                $GoodReceivingDetail->material_id = $material_id;
                                $GoodReceivingDetail->material_name = $material_name;
                                $GoodReceivingDetail->item_po = $input->item_po;
                                $GoodReceivingDetail->index_po = $input->index_po;
                                $GoodReceivingDetail->qty = $input->qty;
                                $GoodReceivingDetail->unit = $input->unit;
                                $GoodReceivingDetail->price = $input->price;
                                $GoodReceivingDetail->currency = $input->currency;
                                $GoodReceivingDetail->vendor = $input->vendor;
                                $GoodReceivingDetail->ppn = $input->ppn;

                                $GoodReceivingDetail->del_note = $input->del_note;
                                $GoodReceivingDetail->del_date = $input->del_date;
                                $GoodReceivingDetail->del_qty = $input->del_qty;
                                $GoodReceivingDetail->prod_date = $input->prod_date;
                                $GoodReceivingDetail->prod_lot = $input->prod_lot;
                                $GoodReceivingDetail->material = $input->material;
                                $GoodReceivingDetail->o_name = $input->o_name;
                                $GoodReceivingDetail->o_code = $input->o_code;

                                $GoodReceivingDetail->receive_qty = $input->receive_qty;
                                $GoodReceivingDetail->reference = $reference;
                                $GoodReceivingDetail->gudang_id = $input->gudang_id;
                                $GoodReceivingDetail->gudang_nm = $input->gudang_nm;
                                $GoodReceivingDetail->batch = $input->batch;

                                $GoodReceivingDetail->PR_Number = $input->PR_Number;
                                $GoodReceivingDetail->residual_qty = $input->qty - $input->receive_qty;
                                $GoodReceivingDetail->stock = null;
                                $GoodReceivingDetail->description = null;

                                $GoodReceivingDetail->created_by = auth()->user()->username;
                                $GoodReceivingDetail->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                $GoodReceivingDetail->updated_by = auth()->user()->username;
                                $GoodReceivingDetail->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                $GoodReceivingDetail->save();
                            }

                            // $sisa = $input->qty - $input->receive_qty;
                            $sisa = $input->del_qty - $input->receive_qty;

                            if ($input->qty > $input->receive_qty) {

                                $updateData = ReceivingDetails::where('PO_Number', $PO_Number)
                                    ->where('material_id', $material_id)
                                    ->where('index_po', $input->index_po)
                                    ->update(['del_qty' => $sisa]);
                            } else {

                                $updateData = ReceivingDetails::where('PO_Number', $PO_Number)
                                    ->where('material_id', $material_id)
                                    ->where('index_po', $input->index_po)
                                    ->update(['flag' => 1]);
                            }
                        }
                        #endregion

                        if ($GoodReceiving && $GoodReceivingDetail && $updateData) {

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
                    } elseif ($postSAP->d->Status === 'E') {

                        return response()->json([

                            "result" => false,
                            "msg_type" => 'failed',
                            "message" => 'SAP: ' . $postSAP->d->Message,

                        ], 400);
                    }
                } else {

                    return response()->json([

                        "result" => false,
                        "msg_type" => 'failed',
                        "message" => 'SAP: ' . $postSAP->error->message->value,

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
                "message" => 'err: ' . $e,

            ], 400);
        }
    }

    private function stringtoupper($string)
    {
        if ($string != '') {
            $string = strtolower($string);
            $string = strtoupper($string);
        }
        return $string;
    }

    private function dateMaking($date)
    {
        $date = explode('.', $date);
        $date = $date[0] . '-' . $date[1] . '-' . $date[2];
        $date = date('Y-m-d', strtotime($date));
        return $date;
    }

    private function getSAPToken()
    {

        $us = 'wcs-abap';
        $pw = 'Wilmar12';
        $account = $us . ':' . $pw;

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

            return 'err: ' . $e;
        }
    }

    private function ArrHeader($res)
    {
        $arr = array();

        $data = substr($res, 0, strpos($res, "\r\n\r\n"));

        foreach (explode("\r\n", $data) as $key => $value) {
            if ($key === 0) {

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
        $account = $us . ':' . $pw;

        $url = 'http://erpdev-dp.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/Headerset?sap-client=110';

        $header = array(
            'X-CSRF-TOKEN: ' . $headervalue['x-csrf-token'],
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
                'X-CSRF-TOKEN: ' . $headervalue['x-csrf-token'],
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

            return 'err: ' . $e;
        }
    }

    private function genGR($arr)
    {
        $data = array_unique($arr);
        $data = join("_", $data);
        return $data;
    }

    private function genPO($arr)
    {
        $data = array_unique($arr);
        return $data;
    }
}
