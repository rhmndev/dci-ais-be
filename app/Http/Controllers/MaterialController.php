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
use App\Exports\MaterialsExport;
use App\Exports\MaterialsExport2;
use Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
// use Maatwebsite\Excel\Facades\Excel;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string',
            'category' => 'nullable|string',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $category = $request->category;

        try {
            $Material = new Material;
            $data = array();

            $resultAlls = $Material->getAllData($keyword, $request->columns, $request->sort, $order, $category);
            $results = $Material->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $category);

            if ($request->has('showall')) {
                $results = $resultAlls;
            }

            return response()->json([
                'type' => 'success',
                'data' => $results,
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

    public function index2(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = Material::query();
             // Get user's role
             $userRole = auth()->user()->role;
            
            // Apply role filtering
            $query = $query->whereHas('roleMaterialTypes', function($q) use ($userRole) {
                $q->where('role_id', $userRole->_id);
            }); 
            if ($request->has('code') && $request->code != '') {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('description') && $request->description != '') {
                $query->where('description', 'like', '%' . $request->description . '%');
            }   
            
            if ($request->has('type') && $request->type != '') {
                $query->where('type', $request->type);
            }

            if ($request->has('unit') && $request->unit != '') {
                $query->where('unit', $request->unit);
            } 

            if($request->has('category') && $request->category != ''){
                $query->where('category', $request->category);
            }

            if($request->has('order') && $request->order != ''){
                $order = $request->order;
            }else{
                $order = 'ascend';
            }

             // Apply keyword search if exists
             if (!empty($keyword)) {
                 foreach ($request->columns as $index => $column) {
                     if ($index == 0) {
                         $query = $query->where($column, 'like', '%' . $keyword . '%');
                     } else {
                         $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                     }
                 }
             }
 
             // Apply category filter if exists
             if(isset($category)){
             if ($category && $category != '') {
                 $query = $query->where('category', $category);
             }
             }
 
             // Apply sorting
             $query = $query->orderBy($request->sort ?? 'code', $order == 'ascend' ? 'asc' : 'desc');
 
             // Get all results for total count
             $resultAlls = $query->get();
 
             // Get paginated results
             $results = $query->paginate($request->perpage, ['*'], 'page', $request->page);

            return response()->json([
                'type' => 'success',
                'data' => $results
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }
    public function getStocksMaterial(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = Material::query();
             // Get user's role
             $userRole = auth()->user()->role;

            $query = $query->with('StockSlockData');
            // Apply role filtering
            $query = $query->whereHas('roleMaterialTypes', function($q) use ($userRole) {
                $q->where('role_id', $userRole->_id);
            }); 
            if ($request->has('code') && $request->code != '') {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('description') && $request->description != '') {
                $query->where('description', 'like', '%' . $request->description . '%');
            }   
            
            if ($request->has('type') && $request->type != '') {
                $query->where('type', $request->type);
            }

            if ($request->has('unit') && $request->unit != '') {
                $query->where('unit', $request->unit);
            } 

            if($request->has('category') && $request->category != ''){
                $query->where('category', $request->category);
            }

            if($request->has('order') && $request->order != ''){
                $order = $request->order;
            }else{
                $order = 'ascend';
            }

             // Apply keyword search if exists
             if (!empty($keyword)) {
                 foreach ($request->columns as $index => $column) {
                     if ($index == 0) {
                         $query = $query->where($column, 'like', '%' . $keyword . '%');
                     } else {
                         $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                     }
                 }
             }
 
             // Apply category filter if exists
             if(isset($category)){
                if ($category && $category != '') {
                    $query = $query->where('category', $category);
                }
             }
 
             // Apply sorting
             $query = $query->orderBy($request->sort ?? 'code', $order == 'ascend' ? 'asc' : 'desc');
 
             // Get all results for total count
             $resultAlls = $query->get();
 
             // Get paginated results
             $results = $query->paginate($perPage, ['*'], 'page', $request->page);

            return response()->json([
                'type' => 'success',
                'data' => $results, 
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function getStocksMaterialPrint(Request $request)
    {
        try {
            $query = Material::query();
            $selectedSlock = $request->selectedSlock ?? '';

            if($request->has('selectedSlock') && $request->selectedSlock != ''){
                $query->where('type', $request->selectedSlock);
            }

            $query = $query->with('StockSlockData');

            $materials = $query->get();

            // Calculate total quantity for each material
            $materials->each(function ($material) {
                $material->quantity = $material->StockSlockData->sum('valuated_stock');
            });

            $pdf = PDF::loadView('reports.materials-stock', [
                'materials' => $materials,
                'date' => now()->format('d/m/Y H:i:s'),
                'title' => 'Material Stock Report'
            ]);

            return $pdf->download('material-stock-report.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error generating PDF: ' . $e->getMessage()
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
            'origin' => 'nullable|string',
            'minQty' => 'nullable|numeric',
            'maxQty' => 'nullable|numeric',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
        ]);

        try {

            $Material = Material::firstOrNew(['code' => $request->code]);

            $Material->code = $this->stringtoupper($request->code);
            $Material->description = $this->stringtoupper($request->description);
            $Material->type = $this->stringtoupper($request->type);
            $Material->unit = $this->stringtoupper($request->unit);

            $Material->origin = $this->stringtoupper($request->origin);
            $Material->minQty = $request->minQty;
            $Material->maxQty = $request->maxQty;

            if ($request->has('is_partially_out')) {
                $Material->is_partially_out = $request->is_partially_out === true;
            }

            if ($request->photo != null && $request->hasFile('photo')) {

                if (Storage::disk('public')->exists('/images/material/' . $Material->photo)) {
                    Storage::disk('public')->delete('/images/material/' . $Material->photo);
                }

                $image      = $request->file('photo');
                $fileName   = $Material->code . '.' . $image->getClientOriginalExtension();

                $img = Image::make($image->getRealPath());
                $img->resize(250, 250, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $img->stream(); // <-- Key point

                Storage::disk('public')->put('/images/material' . '/' . $fileName, $img, 'public');

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
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function generateQR(Request $request,$id)
    {
        try{
            $Material = Material::findOrFail($id);

            $code = $Material->code;

            // generate qr and save to storage 
            $qr = QrCode::format('png')->size(200)->generate($code);
            $fileName = $code . '.png';
            $path = '/images/material' . '/' . $fileName;
            Storage::disk('public')->put($path, $qr, 'public');

            $Material->qr = $path;
            $Material->save();

            return response()->json([
                'type' => 'success',
                'data' => $Material
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'minQty' => 'nullable|numeric',
            'maxQty' => 'nullable|numeric',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
        ]);

        try {
            $Material = Material::findOrFail($id);

            if ($request->has('type') && $request->type != null || $request->type != '') {
                $Material->type = $this->stringtoupper($request->type);
            }

            if ($request->has('unit') && $request->unit != null || $request->unit != '') {
                $Material->unit = $this->stringtoupper($request->unit);
            }

            if ($request->has('description') && $request->description != null || $request->description != '') {
                $Material->description = $this->stringtoupper($request->description);
            }

            if ($request->has('minQty') && $request->minQty != null || $request->minQty != '') {
                $Material->minQty = floatval($request->minQty);
            }

            if ($request->has('maxQty') && $request->maxQty != null || $request->maxQty != '') {
                $Material->maxQty = floatval($request->maxQty);
            }

            // Handle is_partially_out in update method
            if ($request->has('is_partially_out')) {
                $Material->is_partially_out = $request->is_partially_out === true;
            }

            $Material->updated_by = auth()->user()->username;
            $Material->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());

            $Material->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data updated successfully!',
                'data' => $Material
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $th->getMessage() . '.',
                'data' => $Material,
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
            $json = $client->get("http://erpprd-app1.dharmap.com:8001/sap/opu/odata/SAP/ZDCI_SRV/MaterialSet?\$filter=Werks eq '$code' and Ersda eq datetime'$date'&\$format=json&sap-300", [
                'auth' => [
                    // 'wcs-abap',
                    // 'Wilmar12'
                    'DCI-DGT01',
                    'DCI0001'
                ],
            ]);
            $results = json_decode($json->getBody())->d->results;

            if (count($results) > 0) {

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
                    "message" => 'Sync SAP Success. ' . count($results) . ' data synced',

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
                "message" => 'err: ' . $e,

            ], 400);
        }
    }

    public function import(Request $request)
    {
        $data = array();

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'type' => 'nullable|string'
        ]);

        try {
            // make delete all data from material but still use soft delete
            // $materials = Material::all();
            // foreach ($materials as $material) {
            //     $material->delete();
            // }
    
            if ($files = $request->file('file')) {

                //store file into document folder
                $Excels = Excel::toArray(new MaterialsImport, $files);
                $Excels = $Excels[0];
                // $Excels = json_decode(json_encode($Excels[0]), true);

                foreach ($Excels as $Excel) {

                    if ($Excel['code'] != null) {
                        //store your file into database
                        $Material = Material::firstOrNew(['code' => $Excel['code']]);
                        $Material->code = $this->stringtoupper(strval($Excel['code']));

                        // if($Material->exists){
                        //     $Material->restore();
                        // }
                        
                        // Only update fields if they exist in the import data
                        if (isset($Excel['description'])) {
                            $Material->description = $this->stringtoupper($Excel['description']);
                        }
                        if (isset($Excel['type'])) {
                            $Material->type = $this->stringtoupper($Excel['type']);
                        }
                        if (isset($Excel['unit_uom'])) {
                            $Material->unit = $this->stringtoupper($Excel['unit_uom']);
                        }
                        if (isset($Excel['min_qty'])) {
                            $Material->minQty = $this->stringtoupper($Excel['min_qty']);
                        }
                        if (isset($Excel['max_qty'])) {
                            $Material->maxQty = $this->stringtoupper($Excel['max_qty']);
                        }
                        if (isset($Excel['origin'])) {
                            $Material->origin = $this->stringtoupper($Excel['origin']);
                        }
                        if ($request->has('category')) {
                            $Material->category = $this->stringtoupper($request->category);
                        }

                        // Only set photo to null if it's a new record
                        if (!$Material->exists) {
                            $Material->photo = null;
                        }

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
                    // "message" => 'Data stored successfully!',
                    "message" => $data,

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

    public function destroy($id)
    {
        $Material = Material::find($id);

        if (Storage::disk('public')->exists('/images/material/' . $Material->photo)) {
            Storage::disk('public')->delete('/images/material/' . $Material->photo);
        }

        $Material->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }

    private function stringtoupper($string)
    {
        if ($string != '') {
            $string = strtolower($string);
            $string = strtoupper($string);
        }
        return $string;
    }

    public function export(Request $request)
    {
        try {
            $fileName = 'materials_' . date('YmdHis') . '.xlsx';

            return Excel::download(new MaterialsExport($request), $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 500);
        }
    }

    public function export2(Request $request)
    {
        $selectedMaterials = $request->selectedMaterials;

        $materialsQuery = Material::query();

        if ($selectedMaterials && $selectedMaterials !== 0) {
            $materialsQuery->whereIn('_id', $selectedMaterials);
        }

        $materials = $materialsQuery->get();

        return Excel::download(new MaterialsExport2($materials), 'materials.xlsx');
    }
    public function list(Request $request)
    {
        $materials = Material::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })
            ->when($request->type, function ($query) use ($request) {
                if (!empty($request->type)) {
                    $query->where('type', $request->type);
                }
            })
            ->take($request->take ?? 10)
            ->get();

        return response()->json([
            'type' => 'success',
            'data' => $materials
        ], 200);
    }

    public function getMaterialType()
    {
        // get material type by material data then get type from material type data
        $materialTypes = ['ZRAW','ZOHP','ZMNT','ZFIN','ZGSS','ZSEM','ZCUS','ZWST','ZCON'];
        return response()->json([
            'type' => 'success',
            'data' => $materialTypes
        ], 200);
    }
}
