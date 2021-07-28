<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\VendorImport;
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
}
