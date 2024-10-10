<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\SuppliersImport;
use App\Supplier;

class SupplierController extends Controller
{
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

            $Supplier = new Supplier;
            $data = array();

            $resultAlls = $Supplier->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Supplier->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'dataAll' => $resultAlls,
                'total' => count($resultAlls),
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'contact' => 'required|string',
        ]);

        try {

            $Supplier = new Supplier;

            $Supplier->name = $request->name;
            $Supplier->address = $request->address;
            $Supplier->phone = $request->phone;
            $Supplier->email = $request->email;
            $Supplier->contact = $request->contact;

            $Supplier->created_by = auth()->user()->username;
            $Supplier->updated_by = auth()->user()->username;

            $Supplier->save();

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

    public function destroy($id)
    {
        $Supplier = Supplier::find($id);

        $Supplier->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }

    private function phoneNumber($number)
    {

        if (substr($number, 0, 1) == 0) {

            $number = '+62' . substr($number, 1);
        } else {

            $number = '+' . $number;
        }

        if (strpos($number, '-')) {
            $number = str_replace('-', '', $number);
        }

        if (strpos($number, ' ')) {
            $number = str_replace(' ', '', $number);
        }

        return $number;
    }
}
