<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\MaterialsImport;
use App\Material;
use App\Settings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Image;
use Excel;

class MaterialController extends Controller
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
    
            $Material = new Material;
            $data = array();

            $resultAlls = $Material->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Material->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

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
        $Material = Material::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $Material
        ]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'code' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string',
            'unit' => 'required|string',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
        ]);

        try {

            $Material = Material::firstOrNew(['code' => $request->code]);
        
            $Material->code = $this->stringtoupper($request->code);
            $Material->description = $this->stringtoupper($request->description);
            $Material->type = $this->stringtoupper($request->type);
            $Material->unit = $this->stringtoupper($request->unit);
            
            if ($request->photo != null && $request->hasFile('photo')) {
        
                if (Storage::disk('public')->exists('/images/material/'.$Material->photo)) {
                    Storage::disk('public')->delete('/images/material/'.$Material->photo);
                }

                $image      = $request->file('photo');
                $fileName   = $Material->code.'.' . $image->getClientOriginalExtension();
    
                $img = Image::make($image->getRealPath());
                $img->resize(250, 250, function ($constraint) {
                    $constraint->aspectRatio();                 
                });
    
                $img->stream(); // <-- Key point
                
                Storage::disk('public')->put('/images/material'.'/'.$fileName, $img, 'public');

                $Material->photo = $fileName;
                
            }

            $Material->created_by = auth()->user()->username;
            $Material->updated_by = auth()->user()->username;

            $Material->save();

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
            $code = $code_sap[1]['name'];

            $date = date('Y-m-d\TH:i:s', strtotime($request->date));

            $client = new Client;
            $json = $client->get("http://erpdev-dp.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/MaterialSet?\$filter=Werks eq '$code' and Ersda eq datetime'$date'&\$format=json&sap-client=110", [
                'auth' => [
                    'wcs-abap',
                    'Wilmar12'
                ],
            ]);
            $results = json_decode($json->getBody())->d->results;

            if (count($results) > 0){

                foreach ($results as $result) {

                    $code = $this->stringtoupper($result->Matnr);
                    $description = $this->stringtoupper($result->Maktx);

                    $Material = Material::firstOrNew(['code' => $code]);
                    $Material->code = $code;
                    $Material->description = $description;
                    $Material->type = $result->Mtart;
                    $Material->unit = $result->Meins;

                    $Material->created_by = auth()->user()->username;
                    $Material->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $Material->updated_by = auth()->user()->username;
                    $Material->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                    $Material->save();

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
                $Excels = Excel::toArray(new MaterialsImport, $files);
                $Excels = $Excels[0];
                // $Excels = json_decode(json_encode($Excels[0]), true);

                foreach ($Excels as $Excel) {

                    if ($Excel['code'] != null){

                        //store your file into database
                        $Material = Material::firstOrNew(['code' => $Excel['code']]);
                        $Material->code = $this->stringtoupper(strval($Excel['code']));
                        $Material->description = $this->stringtoupper($Excel['description']);
                        $Material->type = $this->stringtoupper($Excel['type']);
                        $Material->unit = $this->stringtoupper($Excel['unit_uom']);
                        $Material->photo = null;

                        $Material->created_by = auth()->user()->username;
                        $Material->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Material->updated_by = auth()->user()->username;
                        $Material->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Material->save();

                    }

                }
    
                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'Success',
                    "message" => 'Data stored successfully!',
                    // "message" => $data,
        
                ], 200);
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
        $Material = Material::find($id);
        
        if (Storage::disk('public')->exists('/images/material/'.$Material->photo)) {
            Storage::disk('public')->delete('/images/material/'.$Material->photo);
        }

        $Material->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }

    private function stringtoupper($string)
    {
        if ($string != ''){
            $string = strtolower($string);
            $string = strtoupper($string);
        }
        return $string;
    }
}
