<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\VendorsImport;
use App\Vendor;
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

        try {
    
            $Vendor = new Vendor;
            $data = array();

            $resultAlls = $Vendor->getAllData($keyword, $request->columns, $request->sort, $request->order);
            $results = $Vendor->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $request->order);

            return response()->json([
                'type' => 'success',
                'data' => $results,
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
        if ($request->id == null){
        
            $request->validate([
                'code' => 'required|string|unique:vendors,code',
                'name' => 'required|string',
                'address' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'contact' => 'required|string',
            ]);

            $Vendor = new Vendor;

        } else {
        
            $request->validate([
                'code' => 'required|string|unique:vendors,code,'.$request->id.',_id',
                'name' => 'required|string',
                'address' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'contact' => 'required|string',
            ]);

            $Vendor = Vendor::findOrFail($request->id);
        }

        try {
        
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
                        $data_tmp['created_at'] = date('Y-m-d H:i:s');

                        $data_tmp['updated_by'] = auth()->user()->username;
                        $data_tmp['updated_at'] = date('Y-m-d H:i:s');

                        // Converting to Array
                        array_push($data, $data_tmp);
                        
                    }

                }

                if (count($data) > 0){

                    $Vendor->insert($data);
    
                    return response()->json([
            
                        "result" => true,
                        "msg_type" => 'Success',
                        "msg" => 'Data stored successfully!',
                        // "msg" => $Excels,
            
                    ], 200);

                } else {

                    return response()->json([
            
                        "result" => false,
                        "msg_type" => 'error',
                        "msg" => 'Data already uploaded',
            
                    ], 200);

                }
            }

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "msg" => 'err: '.$e,
    
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
