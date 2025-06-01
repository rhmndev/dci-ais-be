<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageSlocArea;

class StorageSlocAreaController extends Controller
{
    public function index()
    {
        $storageSlocAreas = StorageSlocArea::all();
        return response()->json($storageSlocAreas);
    }

    public function store(Request $request)
    {
        $storageSlocArea = StorageSlocArea::create($request->all());
        return response()->json($storageSlocArea, 201);
    }


    public function update(Request $request, $id)
    {
        $storageSlocArea = StorageSlocArea::findOrFail($id);
        $storageSlocArea->update($request->all());
        return response()->json($storageSlocArea, 200);
    }


    public function destroy($id)
    {
        $storageSlocArea = StorageSlocArea::findOrFail($id);
        $storageSlocArea->delete();
        return response()->json(null, 204);
    }
    
    
    
}
