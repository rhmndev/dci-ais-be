<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
use App\GoodReceiving;
use App\GoodReceivingDetail;
use App\Settings;
use Carbon\Carbon;
use GuzzleHttp\Client;

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
            $Settings = new Settings;

            $POStatus = $Settings->scopeGetValue($Settings, 'POStatus');

            $resultAlls = $GoodReceiving->getAllData($keyword, $request->columns, $request->sort, $order, $vendor);
            $results = $GoodReceiving->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $vendor);

            foreach ($results as $result) {
                
                $data_tmp = array();
                $data_tmp['_id'] = $result->_id;
                $data_tmp['GR_Number'] = '-';
                $data_tmp['PO_Number'] = $result->PO_Number;
                $data_tmp['SJ_Number'] = $result->SJ_Number;
                $data_tmp['PO_Status'] = $POStatus[$result->PO_Status]['name'];
                $data_tmp['create_date'] = $result->create_date;
                $data_tmp['delivery_date'] = $result->delivery_date;
                $data_tmp['release_date'] = $result->release_date;
                $data_tmp['data'] = array();
                $total_po = 0;

                $GRDetails = $GoodReceivingDetail->getDetails($result->GR_Number, $result->vendor);

                foreach ($GRDetails as $GRDetail) {

                    // $data_tmp_d = array();
                    // $data_tmp_d['_id'] = $GRDetail->_id;
                    // $data_tmp_d['PO_Number'] = $PODetail->PO_Number;
                    // $data_tmp_d['create_date'] = $result->create_date;
                    // $data_tmp_d['delivery_date'] = $result->delivery_date;
                    // $data_tmp_d['release_date'] = $result->release_date;
                    // $data_tmp_d['material_id'] = $PODetail->material_id;
                    // $data_tmp_d['material_name'] = $PODetail->material_name;
                    // $data_tmp_d['item_po'] = $PODetail->item_po;
                    // $data_tmp_d['index_po'] = $PODetail->index_po;
                    // $data_tmp_d['qty'] = $PODetail->qty;
                    // $data_tmp_d['unit'] = $PODetail->unit;
                    // $data_tmp_d['price'] = $PODetail->price;
                    // $data_tmp_d['currency'] = $PODetail->currency;
                    // $data_tmp_d['vendor'] = $PODetail->vendor;
                    // $data_tmp_d['ppn'] = $PODetail->ppn;
                    // $data_tmp_d['QRCode'] = $result->_id.';'.$PODetail->_id;
            
                    // $SettingPPNs = $Settings->scopeGetValue($Settings, 'PPN');
                    // foreach ($SettingPPNs as $SettingPPN) {
                    //     $ppn = explode(';', $SettingPPN['name']);
                    //     if ($ppn[0] === $PODetail->ppn){
                    //         $data_tmp_d['ppnp'] = $ppn[1];
                    //     }
                    // };

                    // $data_tmp_d['del_note'] = $PODetail->del_note;
                    // $data_tmp_d['del_date'] = $PODetail->del_date;
                    // $data_tmp_d['del_qty'] = $PODetail->del_qty;
                    // $data_tmp_d['prod_date'] = $PODetail->prod_date;
                    // $data_tmp_d['prod_lot'] = $PODetail->prod_lot;
                    // $data_tmp_d['material'] = $PODetail->material;
                    // $data_tmp_d['o_name'] = $PODetail->o_name;
                    // $data_tmp_d['o_code'] = $PODetail->o_code;

                    // $total = $PODetail->qty * $PODetail->price;
                    // $data_tmp_d['sub_total'] = $total;

                    // $total = ((str_replace("%", "", $data_tmp_d['ppnp']) / 100) * $total) + $total;

                    // $data_tmp_d['total'] = $total;

    
                    // $total_po = $total_po + $total;

                    array_push($data_tmp['data'], $GRDetail);

                }
                // $data_tmp['total'] = $total_po;

                $xxx = array_unique($data_tmp);
    
                array_push($data, $xxx);
            }

            return response()->json([
                'type' => 'success',
                'data' => $data,
                'total' => count($resultAlls)
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
