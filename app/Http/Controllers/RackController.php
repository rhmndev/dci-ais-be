<?php

namespace App\Http\Controllers;

use App\Rack;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index()
    {
        $racks = Rack::all();
        return response()->json($racks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:racks',
            'code_slock' => 'required',
            'name' => 'required',
            'segment' => 'required',
            'position' => 'required',
            'is_active' => 'required|boolean',
        ]);

        $rack = Rack::create($request->all());
        return response()->json($rack, 201);
    }

    public function show($id)
    {
        $rack = Rack::findOrFail($id);
        return response()->json($rack);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:racks,code,' . $id,
            'code_slock' => 'required',
            'name' => 'required',
            'position' => 'required',
            'is_active' => 'required|boolean',
        ]);

        $rack = Rack::findOrFail($id);
        $rack->update($request->all());
        return response()->json($rack);
    }

    public function destroy($id)
    {
        $rack = Rack::findOrFail($id);
        $rack->delete();
        return response()->json(null, 204);
    }
}
