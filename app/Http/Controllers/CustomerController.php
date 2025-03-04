<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Imports\CustomerImport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Excel;

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
        $Customer = Customer::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->get();

        return response()->json([
            'type' => 'success',
            'data' => $Customer
        ], 200);
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
            $Customer->plant = $request->plant;
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

    public function import(Request $request)
    {
        $data = array();

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            if ($files = $request->file('file')) {
                //store file into document folder
                $Excels = Excel::toArray(new CustomerImport, $files);
                $Excels = $Excels[0];
                // $Excels = json_decode(json_encode($Excels[0]), true);

                foreach ($Excels as $Excel) {
                    if ($Excel['code'] != null) {

                        //store your file into database
                        $Customer = Customer::firstOrNew(['code' => $Excel['code']]);
                        $Customer->code = $this->stringtoupper(strval($Excel['code']));
                        $Customer->name = $this->stringtoupper($Excel['name']);
                        $Customer->code_name = $this->stringtoupper($Excel['codename']);
                        $Customer->plant = $this->stringtoupper($Excel['plant']);
                        $Customer->address = $Excel['address'] ?? '';
                        $Customer->phone = $Excel['phone'] ?? '';
                        $Customer->contact = $Excel['contact'] ?? '';
                        $Customer->email = $Excel['email'] ?? '';

                        $Customer->created_by = auth()->user()->username;
                        $Customer->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Customer->updated_by = auth()->user()->username;
                        $Customer->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Customer->save();
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
        $Customer = Customer::find($id);

        $Customer->delete();

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

    public function listParts($id)
    {
        $Customer = Customer::findOrFail($id);

        $parts = isset($Customer->partcomponents) ? $Customer->partcomponents : [];

        return response()->json([
            'type' => 'success',
            'data' => $parts
        ], 200);
    }

    public function getCustomerAliasList(Request $request)
    {
        if ($request->has('pluck_code_name')) {
            $Customer = Customer::groupBy('code_name')->pluck('code_name');
        } else {
            $Customer = Customer::select('code', 'code_name')->get();
        }

        return response()->json([
            'type' => 'success',
            'data' => $Customer
        ], 200);
    }
}
