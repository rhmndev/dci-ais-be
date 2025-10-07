<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\GoodReceiving;
use App\GoodReceivingDetail;
use App\Settings;
use App\Scale;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\OutgoingGoodTemplate;

class GoodReceivingController extends Controller
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
            $GoodReceiving = new GoodReceiving;
            $GoodReceivingDetail = new GoodReceivingDetail;
            $Vendor = new Vendor;
            $Settings = new Settings;

            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $GoodReceiving->getAllData($keyword, $request->columns, $request->sort, $order, $vendor);
            $results = $GoodReceiving->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $vendor);

            foreach ($results as $result) {

                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['GR_Number'] = $result->GR_Number;
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['SJ_Number'] = $result->SJ_Number;
                $data_tmp['PO_Status'] = $POStatus[$result->PO_Status]['name'];
                $data_tmp['GR_Date'] = $result->GR_Date;
                $data_tmp['create_date'] = date('d-m-Y', strtotime($result->create_date));
                $data_tmp['delivery_date'] = date('d-m-Y', strtotime($result->delivery_date));
                $data_tmp['release_date'] = date('d-m-Y', strtotime($result->release_date));
                $data_tmp['data'] = array();
                $total_po = 0;

                $GRDetails = $GoodReceivingDetail->getDetails($result->SJ_Number, $result->PO_Number, $result->vendor);

                foreach ($GRDetails as $GRDetail) {

                    $getVendor = $Vendor->checkVendor($GRDetail->vendor);
                    if (count($getVendor) > 0) {
                        $vendor_nm = $getVendor[0]->name;
                    } else {
                        $vendor_nm = '-';
                    }

                    $data_tmp_d = array();
                    $data_tmp_d['_id'] = $GRDetail->_id;
                    $data_tmp_d['PO_Number'] = $GRDetail->PO_Number;
                    $data_tmp_d['create_date'] = date('d-m-Y', strtotime($result->create_date));
                    $data_tmp_d['delivery_date'] = date('d-m-Y', strtotime($result->delivery_date));
                    $data_tmp_d['release_date'] = date('d-m-Y', strtotime($result->release_date));
                    $data_tmp_d['material_id'] = $GRDetail->material_id;
                    $data_tmp_d['material_name'] = $GRDetail->material_name;
                    $data_tmp_d['item_po'] = $GRDetail->item_po;
                    $data_tmp_d['index_po'] = $GRDetail->index_po;
                    $data_tmp_d['qty'] = $GRDetail->qty;
                    $data_tmp_d['unit'] = $GRDetail->unit;
                    $data_tmp_d['price'] = $GRDetail->price;
                    $data_tmp_d['currency'] = $GRDetail->currency;
                    $data_tmp_d['vendor'] = $GRDetail->vendor;
                    $data_tmp_d['vendor_name'] = $vendor_nm;
                    $data_tmp_d['ppn'] = $GRDetail->ppn;
                    $data_tmp_d['QRCode'] = $result->_id . ';' . $GRDetail->_id;

                    $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    foreach ($SettingPPNs as $SettingPPN) {
                        $ppn = explode(';', $SettingPPN['name']);
                        if ($ppn[0] === $GRDetail->ppn) {
                            $data_tmp_d['ppn_p'] = $ppn[1];
                        }
                    };

                    $data_tmp_d['del_note'] = $GRDetail->del_note;
                    $data_tmp_d['del_date'] = date('d-m-Y', strtotime($GRDetail->del_date));
                    $data_tmp_d['del_qty'] = $GRDetail->del_qty;
                    $data_tmp_d['prod_date'] = date('d-m-Y', strtotime($GRDetail->prod_date));
                    $data_tmp_d['prod_lot'] = $GRDetail->prod_lot;
                    $data_tmp_d['material'] = $GRDetail->material;
                    $data_tmp_d['o_name'] = $GRDetail->o_name;
                    $data_tmp_d['o_code'] = $GRDetail->o_code;

                    $Scale = new Scale;
                    $ScaleData = $Scale->getData(1);
                    $data_tmp_d['scale_qty'] = $ScaleData->qty;

                    $data_tmp_d['receive_qty'] = $GRDetail->receive_qty;

                    $data_tmp_d['gudang_id'] = $GRDetail->gudang_id;
                    $data_tmp_d['gudang_nm'] = $GRDetail->gudang_nm;
                    $data_tmp_d['batch'] = $GRDetail->batch;

                    $total = $GRDetail->qty * $GRDetail->price;
                    $data_tmp_d['sub_total'] = $total;

                    $total = ((str_replace("%", "", $data_tmp_d['ppn_p']) / 100) * $total) + $total;

                    $data_tmp_d['total'] = $total;


                    $total_po = $total_po + $total;

                    array_push($data_tmp['data'], $data_tmp_d);
                }

                $data_tmp['total'] = $total_po;

                array_push($data, $data_tmp);
            }

            // $data = array_unique($data);

            return response()->json([
                'type' => 'success',
                'data' => $data,
                'total' => count($resultAlls)
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function show(Request $request, $id)
    {
        $vendor = auth()->user()->vendor_code ? auth()->user()->vendor_code : '';

        try {

            $data = array();
            $Settings = new Settings;
            $GoodReceivingDetail = new GoodReceivingDetail;

            $GoodReceiving = GoodReceiving::where('_id', $id);
            if ($vendor != '') {

                $GoodReceiving = $GoodReceiving->where('vendor_id', $vendor);
            }
            $GoodReceiving = $GoodReceiving->first();

            if ($GoodReceiving) {

                $total_po = 0;

                $GRDetails = $GoodReceivingDetail->getDetails($GoodReceiving->SJ_Number, $GoodReceiving->PO_Number, $GoodReceiving->vendor);

                foreach ($GRDetails as $GRDetail) {

                    $data_tmp = array();
                    $data_tmp['_id'] = $GRDetail->_id;
                    $data_tmp['PO_Number'] = $GRDetail->PO_Number;
                    $data_tmp['create_date'] = date('d-m-Y', strtotime($GRDetail->create_date));
                    $data_tmp['delivery_date'] = date('d-m-Y', strtotime($GRDetail->delivery_date));
                    $data_tmp['release_date'] = date('d-m-Y', strtotime($GRDetail->release_date));
                    $data_tmp['material_id'] = $GRDetail->material_id;
                    $data_tmp['material_name'] = $GRDetail->material_name;
                    $data_tmp['item_po'] = $GRDetail->item_po;
                    $data_tmp['index_po'] = $GRDetail->index_po;
                    $data_tmp['qty'] = $GRDetail->qty;
                    $data_tmp['unit'] = $GRDetail->unit;
                    $data_tmp['price'] = $GRDetail->price;
                    $data_tmp['currency'] = $GRDetail->currency;
                    $data_tmp['vendor'] = $GRDetail->vendor;
                    $data_tmp['ppn'] = $GRDetail->ppn;

                    // $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    // foreach ($SettingPPNs as $SettingPPN) {
                    //     $ppn = explode(';', $SettingPPN['name']);
                    //     if ($ppn[0] === $GRDetail->ppn){
                    //         $data_tmp['ppn_p'] = $ppn[1];
                    //     }
                    // };

                    $data_tmp['del_note'] = $GRDetail->del_note;
                    $data_tmp['del_date'] = date('d-m-Y', strtotime($GRDetail->del_date));
                    $data_tmp['del_qty'] = $GRDetail->del_qty;
                    $data_tmp['prod_date'] = date('d-m-Y', strtotime($GRDetail->prod_date));
                    $data_tmp['prod_lot'] = $GRDetail->prod_lot;
                    $data_tmp['material'] = $GRDetail->material;
                    $data_tmp['o_name'] = $GRDetail->o_name;
                    $data_tmp['o_code'] = $GRDetail->o_code;

                    $data_tmp['receive_qty'] = $GRDetail->receive_qty;
                    $data_tmp['reference'] = $GRDetail->reference;
                    $data_tmp['gudang_id'] = $GRDetail->gudang_id;
                    $data_tmp['gudang_nm'] = $GRDetail->gudang_nm;
                    $data_tmp['batch'] = $GRDetail->batch;

                    $data_tmp['GR_Number'] = $GRDetail->GR_Number;
                    $data_tmp['PR_Number'] = $GRDetail->PR_Number;
                    $data_tmp['residual_qty'] = $GRDetail->residual_qty;
                    // $data_tmp['residual_qty'] = $GRDetail->del_qty - $GRDetail->receive_qty;
                    $data_tmp['stock'] = $GRDetail->stock;
                    $data_tmp['description'] = $GRDetail->description;

                    $total = $GRDetail->qty * $GRDetail->price;
                    $data_tmp['sub_total'] = $total;

                    array_push($data, $data_tmp);
                }
                $GoodReceiving->details = $data;

                return response()->json([
                    'type' => 'success',
                    'message' => '',
                    'data' => $GoodReceiving,
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
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function updateStatusTemplate($codeTemplate)
    {
        $outgoingGoodTemplate = OutgoingGoodTemplate::where('code_template', $codeTemplate)->first();
        if ($outgoingGoodTemplate) {
            $outgoingGoodTemplate->status = 'Scanned'; // atau status sesuai kebutuhan
            $outgoingGoodTemplate->save();
        }
    }
}
