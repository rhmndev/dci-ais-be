<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendor;
use App\Material;
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
                "msg" => 'Data stored successfully!',
    
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "msg" => 'err: '.$e,
    
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
                "msg" => 'Data stored successfully!',
    
            ], 200);

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
