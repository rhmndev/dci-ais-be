<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
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

            $Customer = new Customer();
            $data = array();

            $resultAlls = $Customer->getAllData($keyword, $request->columns, $request->sort, $order);
            $results = $Customer->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order);

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

    public function show(Request $request, $id)
    {
        $Customer = Customer::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $Customer
        ]);
    }

    public function list(Request $request)
    {

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $data = array();

        try {

            $Customer = new Customer;
            $results = $Customer->getList($keyword);

            return response()->json([
                'type' => 'success',
                'message' => 'Success.',
                'data' => $results,
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
            'code' => 'required|string',
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'contact' => 'required|string',
        ]);

        try {

            $Customer = Customer::firstOrNew(['code' => $request->code]);
            $Customer->code = $request->code;
            $Customer->name = $request->name;
            $Customer->code_name = $request->code_name;
            $Customer->address = $request->address;
            $Customer->phone = $request->phone;
            $Customer->email = $request->email;
            $Customer->contact = $request->contact;

            $Customer->created_by = auth()->user()->username;
            $Customer->updated_by = auth()->user()->username;

            $Customer->save();

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
        $Customer = Customer::find($id);

        $Customer->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }
}
