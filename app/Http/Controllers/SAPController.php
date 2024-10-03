<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\Receiving;
use App\ReceivingDetails;
use App\ReceivingVDetails;
use App\GoodReceiving;
use App\GoodReceivingDetail;
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
                'message' => 'Err: ' . $e . '.',
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

                if (count($QueryGetDataByFilter->get()) > 0) {
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
                "message" => 'err: ' . $e,

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
                'message' => 'Err: ' . $e . '.',
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

                if (count($QueryGetDataByFilter->get()) > 0) {
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
                "message" => 'err: ' . $e,

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
            $ReceivingDetails = new ReceivingDetails;
            $Settings = new Settings;

            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

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

                $PODetails = $ReceivingDetails->getPODetails($result->PO_Number, $request->MPerpage, $request->vendor);
                foreach ($PODetails as $PODetail) {

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $PODetail->_id;
                    $data_tmp_d['PO_Number'] = $PODetail->PO_Number;
                    $data_tmp_d['create_date'] = $result->create_date;
                    $data_tmp_d['delivery_date'] = $result->delivery_date;
                    $data_tmp_d['release_date'] = $result->release_date;
                    $data_tmp_d['PR_Number'] = $PODetail->PR_Number;
                    $data_tmp_d['material_id'] = $PODetail->material_id;
                    $data_tmp_d['material_name'] = $PODetail->material_name;
                    $data_tmp_d['item_po'] = $PODetail->item_po;
                    $data_tmp_d['index_po'] = $PODetail->index_po;
                    $data_tmp_d['qty'] = $PODetail->qty;
                    $data_tmp_d['unit'] = $PODetail->unit;
                    $data_tmp_d['price'] = $PODetail->price;
                    $data_tmp_d['currency'] = $PODetail->currency;
                    $data_tmp_d['vendor'] = $PODetail->vendor;
                    $data_tmp_d['QRCode'] = $result->_id . ';' . $PODetail->_id;

                    $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    foreach ($SettingPPNs as $SettingPPN) {
                        $ppn = explode(';', $SettingPPN['name']);
                        if ($ppn[0] === $PODetail->ppn) {
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
                'message' => 'Err: ' . $e . '.',
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

            if (count($inputs) > 0) {

                $Material = new Material;
                $Vendor = new Vendor;
                $Settings = new Settings;

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

                        $Receiving->created_by = 'SAP';
                        $Receiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->updated_by = 'SAP';
                        $Receiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Receiving->save();

                        $details = $input->data;

                        if (count($details) > 0) {

                            foreach ($details as $detail) {

                                $material_id = $this->stringtoupper($detail->material_id);
                                $material_name = $this->stringtoupper($detail->material_name);

                                $PR_Number = $this->stringtoupper($detail->PurchaseReq);
                                $gudang_id = $this->stringtoupper($detail->Warehouse);

                                $qty = $this->checkNumber($detail->qty);
                                $price = $this->checkNumber($detail->price);

                                $checkMaterial = $Material->checkMaterial($material_id);

                                if (count($checkMaterial) > 0) {

                                    $ReceivingDetails = ReceivingDetails::firstOrNew([
                                        'PO_Number' => $PO_Number,
                                        'item_po' => $detail->item_po,
                                    ]);
                                    $ReceivingDetails->PO_Number = $PO_Number;
                                    $ReceivingDetails->create_date = $create_date;
                                    $ReceivingDetails->delivery_date = $delivery_date;
                                    $ReceivingDetails->release_date = $release_date;
                                    $ReceivingDetails->PR_Number = $PR_Number;
                                    $ReceivingDetails->material_id = $material_id;
                                    $ReceivingDetails->material_name = $material_name;
                                    $ReceivingDetails->item_po = $detail->item_po;
                                    $ReceivingDetails->index_po = intval($detail->item_po / 10);
                                    $ReceivingDetails->qty = $qty;
                                    $ReceivingDetails->unit = $detail->unit;
                                    $ReceivingDetails->price = $price;
                                    $ReceivingDetails->currency = $detail->currency;
                                    $ReceivingDetails->vendor = $input->vendor;
                                    $ReceivingDetails->ppn = $detail->ppn;

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
                                        $ReceivingDetails->del_qty = $qty;
                                        $ReceivingDetails->prod_date = $create_date;
                                        $ReceivingDetails->prod_lot = null;
                                        $ReceivingDetails->material = null;
                                        $ReceivingDetails->o_name = null;
                                        $ReceivingDetails->o_code = null;

                                        $ReceivingDetails->flag = 0;
                                    }

                                    $ReceivingDetails->created_by = 'SAP';
                                    $ReceivingDetails->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                    $ReceivingDetails->updated_by = 'SAP';
                                    $ReceivingDetails->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                                    $ReceivingDetails->save();
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

            if (count($vendor_nf) > 0) {

                return response()->json([

                    "result" => true,
                    "msg_type" => 'failed',
                    "message" => 'Data stored unsuccessfully!',
                    "Not Found Vendor" => array_unique($vendor_nf),

                ], 400);
            } elseif (count($material_nf) > 0) {

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
                "message" => 'err: ' . $e,

            ], 400);
        }
    }

    public function getGR(Request $request)
    {

        $request->validate([
            'code'      => 'nullable|string',
            'perpage'   => 'required|numeric',
            'page'      => 'required|numeric',
            'sort'      => 'required|string',
            'order'     => 'nullable|string',
            'vendor'    => 'nullable|string',
        ]);

        $code = ($request->code != null) ? $request->code : '';
        $vendor = ($request->vendor != null) ? $request->vendor : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $columns = array('GR_Number');

        try {

            $data = array();
            $GoodReceiving = new GoodReceiving;
            $GoodReceivingDetail = new GoodReceivingDetail;
            $Settings = new Settings;

            $code_sap = $Settings->scopeGetValue($Settings, 'code_sap');
            $code = $code_sap[1]['name'];

            $results = $GoodReceiving->getData($code, $columns, $request->perpage, $request->page, $request->sort, $order, $vendor);

            foreach ($results as $result) {

                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['GR_Number'] = $result->GR_Number;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['SJ_Number'] = $result->SJ_Number;
                $data_tmp['create_date'] = $result->create_date;
                $data_tmp['delivery_date'] = $result->delivery_date;
                $data_tmp['vendor_id'] = $result->vendor_id;
                $data_tmp['vendor_nm'] = $result->vendor_nm;
                $data_tmp['warehouse_id'] = $result->warehouse_id;
                $data_tmp['warehouse_nm'] = $result->warehouse_nm;
                $data_tmp['description'] = $result->description;
                $data_tmp['data'] = array();
                $total_po = 0;

                $GRDetails = $GoodReceivingDetail->getDetails($result->SJ_Number, $vendor);
                foreach ($GRDetails as $GRDetail) {

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $GRDetail->_id;
                    $data_tmp_d['GR_Number'] = $GRDetail->GR_Number;
                    $data_tmp_d['PO_Number'] = $GRDetail->PO_Number;
                    $data_tmp_d['material_id'] = $GRDetail->material_id;
                    $data_tmp_d['material_name'] = $GRDetail->material_name;
                    $data_tmp_d['PR_Number'] = $GRDetail->PR_Number;
                    $data_tmp_d['index'] = $GRDetail->index;

                    $data_tmp_d['receiving_qty'] = $GRDetail->receiving_qty;
                    $data_tmp_d['receiving_unit'] = $GRDetail->receiving_unit;

                    $data_tmp_d['order_qty'] = $GRDetail->order_qty;
                    $data_tmp_d['order_unit'] = $GRDetail->order_unit;

                    $data_tmp_d['residual_qty'] = $GRDetail->residual_qty;
                    $data_tmp_d['residual_unit'] = $GRDetail->residual_unit;

                    $data_tmp_d['stock'] = $GRDetail->stock;
                    $data_tmp_d['description'] = $GRDetail->description;

                    array_push($data_tmp['data'], $data_tmp_d);
                }

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
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function storeGR(Request $request)
    {
        $data = array();

        $json = $request->getContent();

        try {

            $inputs = json_decode($json);

            if (count($inputs) > 0) {

                $Ref_nf = array();

                foreach ($inputs as $input) {

                    $reference = $this->stringtoupper($input->ref);
                    $GR_Number = $this->stringtoupper($input->matdoc);
                    $vendor_id = $this->stringtoupper($input->vendor);

                    $GoodReceiving = GoodReceiving::where('SJ_Number', $reference)->where('vendor_id', $vendor_id)->first();
                    if ($GoodReceiving) {
                        $GoodReceiving->GR_Number = $GR_Number;
                        $GoodReceiving->GR_Date = date('Y-m-d', strtotime($input->grdate));
                        $GoodReceiving->updated_by = 'SAP';
                        $GoodReceiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $GoodReceiving->save();


                        $GoodReceivingDetail = GoodReceivingDetail::where('reference', $reference)->first();
                        if ($GoodReceivingDetail) {
                            $GoodReceivingDetail->GR_Number = $GR_Number;
                            $GoodReceivingDetail->updated_by = 'SAP';
                            $GoodReceivingDetail->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                            $GoodReceivingDetail->save();
                        }
                    } else {
                        array_push($Ref_nf, $input->ref);
                    }
                }

                if (count($Ref_nf) > 0) {

                    return response()->json([

                        "result" => false,
                        "msg_type" => 'failed',
                        "message" => 'Ref: ' . join(',', $Ref_nf) . ' Not found!',

                    ], 400);
                } else {

                    return response()->json([

                        "result" => true,
                        "msg_type" => 'Success',
                        "message" => 'Data Update successfully!',

                    ], 200);
                }
            } else {

                return response()->json([

                    "result" => false,
                    "msg_type" => 'failed',
                    "message" => 'Data Not found!',

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

    private function checkNumber($num)
    {
        $num = str_replace(' ', '', $num);
        $num = intval($num);
        return $num;
    }
}
