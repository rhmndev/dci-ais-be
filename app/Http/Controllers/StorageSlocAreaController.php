<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageSlocArea;

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
                $query->where('sloc', 'like', '%' . $request->sloc . '%');
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

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = \Validator::make($request->all(), [
                'plant' => 'required',
                'sloc' => 'required',
                'code' => 'required',
                'name' => 'required',
                'alias' => 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Check for existing record with same plant, sloc, and code
            $exists = StorageSlocArea::where('plant', $request->plant)
                ->where('sloc', $request->sloc)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'error' => 'A record with this plant, sloc, and code combination already exists'
                ], 409);
            }

            $storageSlocArea = StorageSlocArea::create($request->all());
            return response()->json($storageSlocArea, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create storage sloc area: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $storageSlocArea = StorageSlocArea::findOrFail($id);
            $storageSlocArea->update($request->all());
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
}
