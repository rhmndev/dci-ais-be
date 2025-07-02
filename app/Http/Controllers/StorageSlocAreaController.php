<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageSlocArea;
use App\StorageSlocAreaRack;

class StorageSlocAreaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = StorageSlocArea::query();

            // Apply filters if they exist in the request
            if ($request->has('plant')) {
                $query->where('plant', 'like', '%' . $request->plant . '%');
            }
            if ($request->has('sloc')) {
                $query->where('slock', 'like', '%' . $request->sloc . '%');
            }
            if ($request->has('code')) {
                $query->where('code', 'like', '%' . $request->code . '%');
            }
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->has('alias')) {
                $query->where('alias', 'like', '%' . $request->alias . '%');
            }

            // Get paginated results
            $perPage = $request->get('per_page', 15); // Default 15 items per page
            $storageSlocAreas = $query->paginate($perPage);

            return response()->json($storageSlocAreas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch storage sloc areas: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $SlocArea = StorageSlocArea::find($id);
            if (!$SlocArea) {
                return response()->json(['error' => 'Storage sloc area not found'], 404);
            }
            // If no ID is provided, return all records
            if (!$id) {
                return response()->json(StorageSlocArea::all());
            }
            // If an ID is provided, return the specific record

            return response()->json($SlocArea);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Storage sloc area not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch storage sloc area: ' . $e->getMessage()], 500);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'An unexpected error occurred: ' . $th->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = \Validator::make($request->all(), [
                'plant' => 'required',
                'slock' => 'required',
                'index' => 'required',
                'code' => 'required',
                'name' => 'required',
                'alias' => 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Check for existing record with same plant, sloc, and code
            $exists = StorageSlocArea::where('plant', $request->plant)
                ->where('slock', $request->slock)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'error' => 'A record with this plant, sloc, and code combination already exists'
                ], 409);
            }

            $data = $request->only(['plant', 'slock', 'code', 'name', 'alias']);
            $data['index'] = (int) $request->input('index'); 

            $storageSlocArea = StorageSlocArea::create($data);
            return response()->json($storageSlocArea, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create storage sloc area: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $storageSlocArea = StorageSlocArea::findOrFail($id);

            $validator = \Validator::make($request->all(), [
                'plant' => 'required',
                'slock' => 'required',
                'index' => 'required|integer',
                'code' => 'required',
                'name' => 'required',
                'alias' => 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Ambil data yang dibutuhkan saja, dan pastikan index adalah integer
            $data = $request->only(['plant', 'slock', 'code', 'name', 'alias']);
            $data['index'] = (int) $request->input('index');

            $storageSlocArea->update($data);

            return response()->json($storageSlocArea, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Storage sloc area not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update storage sloc area: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $storageSlocArea = StorageSlocArea::findOrFail($id);
            $storageSlocArea->delete();
            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Storage sloc area not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete storage sloc area: ' . $e->getMessage()], 500);
        }
    }

    public function getRack(Request $request)
    {
        try {
            $racks = StorageSlocAreaRack::where('storage_sloc_area_code',$request->storage_sloc_area_code)->get();

            return response()->json([
                'message' => 'success',
                'data' => $racks
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function storeRack(Request $request)
    {
        try {
            $request->validate([
                'storage_sloc_area_code' => 'required',
                'code_area' => 'required',
                'position'=> 'required',
                'name' => 'required',
            ]);

            $code = $request->code_area . '|'.$request->position;

            $Rack = StorageSlocAreaRack::create($request->all());
            $Rack->code = $code;
            $Rack->save();


            return response()->json($Rack,201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
