<?php

namespace App\Http\Controllers;

use App\SLock;
use Illuminate\Http\Request;

class StorageLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'order' => 'string'
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';

        try {

            $storageLocation = new SLock();

            $resultAlls = $storageLocation->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $storageLocation->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);
            $total = count($resultAlls);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'total' => $total
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,
            ], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'description' => 'required|string',
        ]);

        $storageLocation = SLock::firstOrNew(['code' => $request->code]);
        $storageLocation->description = $request->description;
        $storageLocation->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Storage Location created successfully.',
            'data' => $storageLocation
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $storageLocation = SLock::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' => $storageLocation
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * 
     * */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|unique:storage_locations,code,' . $id,
            'name' => 'required|string',
        ]);

        $storageLocation = SLock::findOrFail($id);
        $storageLocation->code = $request->code;
        $storageLocation->name = $request->name;
        $storageLocation->save();

        return response()->json([
            'type' => 'success',
            'data' => $storageLocation
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $storageLocation = SLock::where('_id', $id)->delete();

        return response()->json([
            'type' => 'success',
            'data' => $storageLocation
        ], 201);
    }

    public function list(Request $request)
    {
        $storageLocations = SLock::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->take(10)
            ->get();

        return response()->json([
            'type' => 'success',
            'data' => $storageLocations
        ], 200);
    }
}
