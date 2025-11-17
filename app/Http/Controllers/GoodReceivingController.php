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
use Illuminate\Support\Facades\Log;

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

    public function update(Request $request)
    {
        try {
            // Accept both PascalCase (from frontend) and snake_case
            $request->validate([
                'GR_Number' => 'nullable|string',
                'gr_number' => 'nullable|string', 
                'SJ_Number' => 'nullable|string',
                'sj_number' => 'nullable|string',
                'PO_Number' => 'nullable|string',
                'po_number' => 'nullable|string',
                'vendor' => 'nullable|string',
                'vendor_id' => 'nullable|string',
                'GR_Date' => 'nullable|string',
                'gr_date' => 'nullable|string',
                'status' => 'nullable|string',
                'outgoing_no' => 'nullable|string',
                'supplier_code' => 'nullable|string',
                'details' => 'nullable|array'
            ]);

            // Get GR Number from either format
            $gr_number = $request->GR_Number ?? $request->gr_number;
            $sj_number = $request->SJ_Number ?? $request->sj_number;
            $po_number = $request->PO_Number ?? $request->po_number;
            $vendor_id = $request->vendor ?? $request->vendor_id ?? $request->supplier_code;
            $gr_date = $request->GR_Date ?? $request->gr_date;
            
            // Handle empty strings and null values
            $gr_number = !empty($gr_number) ? $gr_number : null;
            $sj_number = !empty($sj_number) ? $sj_number : null;
            $po_number = !empty($po_number) ? $po_number : null;
            $vendor_id = !empty($vendor_id) ? $vendor_id : null;
            $gr_date = !empty($gr_date) ? $gr_date : null;
            
            $vendor = auth()->user()->vendor_code ?? $vendor_id;

            // If no GR_Number provided, generate one for new creation
            if (!$gr_number) {
                $today = date('Ymd');
                $random = strtoupper(substr(md5(time() . rand()), 0, 4));
                $gr_number = "RCP-{$today}-{$random}";
            }

            // Try to find existing Good Receipt record
            $goodReceiving = GoodReceiving::where('GR_Number', $gr_number)->first();
            
            // If not found and we have SJ_Number, try to find by SJ_Number
            if (!$goodReceiving && $sj_number) {
                $goodReceiving = GoodReceiving::where('SJ_Number', $sj_number);
                if ($vendor) {
                    $goodReceiving = $goodReceiving->where('vendor_id', $vendor);
                }
                $goodReceiving = $goodReceiving->first();
            }

            // If still not found, create new Good Receipt
            if (!$goodReceiving) {
                $goodReceiving = new GoodReceiving();
                $goodReceiving->GR_Number = $gr_number;
                $goodReceiving->created_by = auth()->user()->username ?? 'system';
                $goodReceiving->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
            }

            // Update Good Receipt fields
            if ($sj_number) {
                $goodReceiving->SJ_Number = $sj_number;
            }
            if ($po_number) {
                $goodReceiving->PO_Number = $po_number;
            }
            if ($gr_date) {
                $goodReceiving->GR_Date = date('Y-m-d', strtotime($gr_date));
            }
            if ($vendor_id) {
                $goodReceiving->vendor_id = $vendor_id;
            }
            if ($request->has('status')) {
                $goodReceiving->status = $request->status;
            }
            if ($request->has('description')) {
                $goodReceiving->description = $request->description;
            }

            $goodReceiving->updated_by = auth()->user()->username ?? 'system';
            $goodReceiving->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
            $goodReceiving->save();

            // Handle Good Receipt Details if provided
            if ($request->has('details') && is_array($request->details)) {
                foreach ($request->details as $detail) {
                    // Create new detail if id not provided
                    if (!isset($detail['id'])) {
                        $goodReceivingDetail = new GoodReceivingDetail();
                        $goodReceivingDetail->GR_Number = $gr_number;
                        $goodReceivingDetail->reference = $sj_number;
                        $goodReceivingDetail->created_by = auth()->user()->username ?? 'system';
                        $goodReceivingDetail->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    } else {
                        $goodReceivingDetail = GoodReceivingDetail::find($detail['id']);
                        if (!$goodReceivingDetail) {
                            continue; // Skip if detail not found
                        }
                    }

                    // Update detail fields
                    if (isset($detail['material_id'])) {
                        $goodReceivingDetail->material_id = $detail['material_id'];
                    }
                    if (isset($detail['material_name'])) {
                        $goodReceivingDetail->material_name = $detail['material_name'];
                    }
                    if (isset($detail['qty'])) {
                        $goodReceivingDetail->qty = $detail['qty'];
                    }
                    if (isset($detail['unit'])) {
                        $goodReceivingDetail->unit = $detail['unit'];
                    }
                    if (isset($detail['receive_qty'])) {
                        $goodReceivingDetail->receive_qty = $detail['receive_qty'];
                    }

                    $goodReceivingDetail->updated_by = auth()->user()->username ?? 'system';
                    $goodReceivingDetail->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $goodReceivingDetail->save();
                }
            }

            // 🔄 TRIGGER ARCHIVE SYNC TO PORTAL SUPPLIER
            // Archive sync should happen when Good Receipt is created, not when SAP posts
            $this->triggerArchiveSync($goodReceiving, $sj_number);

            return response()->json([
                'type' => 'success',
                'message' => 'Good Receipt processed successfully',
                'data' => $goodReceiving,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'data' => null,
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error processing Good Receipt: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Trigger archive sync to Portal Supplier when Good Receipt is created
     */
    private function triggerArchiveSync($goodReceiving, $sj_number)
    {
        try {
            // Find corresponding outgoing good record by SJ_Number (which is outgoing_no)
            $outgoingNumber = $sj_number ?: $goodReceiving->SJ_Number;
            
            if (!$outgoingNumber) {
                Log::warning('⚠️ Cannot trigger archive sync: No outgoing number found', [
                    'gr_number' => $goodReceiving->GR_Number
                ]);
                return;
            }

            Log::info('🔄 Triggering archive sync for Good Receipt', [
                'gr_number' => $goodReceiving->GR_Number,
                'outgoing_number' => $outgoingNumber
            ]);

            // Portal Supplier URL
            $supplierPortalUrl = env('SUPPLIER_PORTAL_URL', 'http://localhost:3001');
            
            $syncData = [
                'outgoing_no' => $outgoingNumber,
                'po_number' => $goodReceiving->PO_Number ?? '',
                'good_receipt_number' => $goodReceiving->GR_Number,
                'archived_by' => $goodReceiving->updated_by ?? 'portal_dcci_admin',
                'archived_reason' => 'Auto-archived after Good Receipt creation',
                'archived_at' => now()->toISOString(),
                'archive_source' => 'good_receipt_creation'
            ];

            $client = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->post($supplierPortalUrl . '/api/supplier/templates/archive-sync', [
                'json' => $syncData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'DCCI-Portal-GR-Archive-Sync/1.0'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                Log::info('✅ Archive sync successful via Good Receipt trigger', [
                    'outgoing_number' => $outgoingNumber,
                    'gr_number' => $goodReceiving->GR_Number
                ]);
            } else {
                Log::warning('⚠️ Archive sync failed via Good Receipt trigger', [
                    'outgoing_number' => $outgoingNumber,
                    'gr_number' => $goodReceiving->GR_Number,
                    'status_code' => $response->getStatusCode()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ Archive sync error via Good Receipt trigger', [
                'outgoing_number' => $outgoingNumber ?? 'unknown',
                'gr_number' => $goodReceiving->GR_Number ?? 'unknown',
                'error' => $e->getMessage()
            ]);
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