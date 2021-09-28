<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\VendorsImport;
use App\Vendor;
use App\Settings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Excel;

class VendorController extends Controller
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
    
            $Vendor = new Vendor;
            $data = array();

            $resultAlls = $Vendor->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Vendor->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

            return response()->json([
                'type' => 'success',
                'data' => $results,
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

    public function show(Request $request, $id)
    {
        $Vendor = Vendor::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $Vendor
        ]);
    }

    public function list(Request $request)
    {

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $data = array();

        try {

            $Vendor = new Vendor;
            $results = $Vendor->getList($keyword);

            return response()->json([
                'type' => 'success',
                'message' => 'Success.',
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

    public function store(Request $request)
    {
        
        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'contact' => 'required|string',
        ]);

        try {

            $Vendor = Vendor::firstOrNew(['code' => $request->code]);
        
            $Vendor->code = $this->stringtoupper($request->code);
            $Vendor->name = $this->stringtoupper($request->name);
            $Vendor->address = $this->stringtoupper($request->address);
            $Vendor->phone = $request->phone;
            $Vendor->email = $request->email;
            $Vendor->contact = $request->contact;

            $Vendor->created_by = auth()->user()->username;
            $Vendor->updated_by = auth()->user()->username;

            $Vendor->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => NULL,
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
        $data = array();

        $request->validate([
            'date' => 'required|date',
        ]);

        try {

            $Settings = new Settings;
            $code_sap = $Settings->scopeGetValue($Settings, 'code_sap');
            $code = $code_sap[0]['name'];

            $date = date('Y-m-d\TH:i:s', strtotime($request->date));

            $client = new Client;
            $json = $client->get("http://erpdev-dp.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/vendorSet?\$filter=Comp eq '$code' and Ersda eq datetime'$date'&sap-client=110&\$format=json", [
                'auth' => [
                    'wcs-abap',
                    'Wilmar12'
                ],
            ]);
            $results = json_decode($json->getBody())->d->results;

            if (count($results) > 0){

                foreach ($results as $result) {

                    $code = $this->stringtoupper($result->Vendor);
                    $name = $this->stringtoupper($result->Name);
                    $address = $this->stringtoupper($result->Street);
                    $phone = strval($result->Telephone);

                    $Vendor = Vendor::firstOrNew(['code' => $code]);
                    $Vendor->code = $code;
                    $Vendor->name = $name;
                    $Vendor->address = $address;
                    $Vendor->phone = $phone;
                    $Vendor->email = $result->Smtp_addr;
                    $Vendor->contact = $result->Persnumber;

                    $Vendor->created_by = auth()->user()->username;
                    $Vendor->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $Vendor->updated_by = auth()->user()->username;
                    $Vendor->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $Vendor->save();

                }

                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'success',
                    "message" => 'Sync SAP Success. '.count($results).' data synced',
        
                ], 200);

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

    public function import(Request $request)
    {
        $data = array();
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
            
        try {

            if ($files = $request->file('file')) {
                
                //store file into document folder
                $Excels = Excel::toArray(new VendorsImport, $files);
                $Excels = $Excels[0];
                // $Excels = json_decode(json_encode($Excels[0]), true);
    
                //store your file into database
                $Vendor = new Vendor();

                foreach ($Excels as $Excel) {

                    $QueryGetDataByFilter = Vendor::query();

                    $QueryGetDataByFilter = $QueryGetDataByFilter->where('code', $this->stringtoupper($Excel['code']));

                    if (count($QueryGetDataByFilter->get()) == 0){

                        $data_tmp = array();
                        
                        $data_tmp['code'] = $this->stringtoupper($Excel['code']);
                        $data_tmp['name'] = $this->stringtoupper($Excel['name']);
                        $data_tmp['address'] = $this->stringtoupper($Excel['address']);
                        $data_tmp['phone'] = $Excel['phone'];
                        $data_tmp['email'] = $Excel['email'];
                        $data_tmp['contact'] = $Excel['contact'];

                        $data_tmp['created_by'] = auth()->user()->username;
                        $data_tmp['created_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                        $data_tmp['updated_by'] = auth()->user()->username;
                        $data_tmp['updated_at'] = new \MongoDB\BSON\UTCDateTime(Carbon::now());

                        // Converting to Array
                        array_push($data, $data_tmp);
                        
                    }

                }

                if (count($data) > 0){

                    $Vendor->insert($data);
    
                    return response()->json([
            
                        "result" => true,
                        "msg_type" => 'Success',
                        "message" => 'Data stored successfully!',
                        // "message" => $Excels,
            
                    ], 200);

                } else {

                    return response()->json([
            
                        "result" => false,
                        "msg_type" => 'error',
                        "message" => 'Data already uploaded',
            
                    ], 200);

                }
            }

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: '.$e,
    
            ], 400);

        }

    }
    
    public function destroy($id)
    {
        $Vendor = Vendor::find($id);

        $Vendor->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }

    private function stringtoupper($string)
    {
        $string = strtolower($string);
        $string = strtoupper($string);
        return $string;
    }
}
