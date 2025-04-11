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
use Excel;
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'minQty' => 'nullable|numeric',
            'maxQty' => 'nullable|numeric',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
        ]);

        try {
            $Material = Material::findOrFail($id);

            $Material->minQty = $request->minQty;
            $Material->maxQty = $request->maxQty;

            $Material->updated_by = auth()->user()->username;
            $Material->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());

            $Material->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data updated successfully!',
                'data' => NULL,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $th->getMessage() . '.',
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
            'category' => 'nullable|string'
        ]);

        try {

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
                        $Material->description = $this->stringtoupper($Excel['description']);
                        $Material->type = $this->stringtoupper($Excel['type']);
                        $Material->unit = $this->stringtoupper($Excel['unit_uom']);
                        $Material->photo = null;

                        $Material->category = $this->stringtoupper($request->category ?? null);
                        $Material->minQty = $this->stringtoupper($Excel['min_qty']);
                        $Material->maxQty = $this->stringtoupper($Excel['max_qty']);

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
}
