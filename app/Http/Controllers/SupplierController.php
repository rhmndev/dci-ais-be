<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\SuppliersImport;
use App\Supplier;
use Carbon\Carbon;
use Excel;
use App\Exports\SuppliersExport;
use App\Role;
use App\User;

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
            'code' => 'required|string',
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'contact' => 'required|string',
        ]);

        try {

            $Supplier = Supplier::firstOrNew(['code' => $request->code]);

            $Supplier->code = $this->stringtoupper($request->code);
            $Supplier->name = $this->stringtoupper($request->name);
            $Supplier->address = $request->address;
            $Supplier->phone = $request->phone;
            $Supplier->email = $request->email;
            $Supplier->contact = $request->contact;

            $Supplier->created_by = auth()->user()->username;
            $Supplier->updated_by = auth()->user()->username;

            $Supplier->save();

            $user = User::firstOrNew(['username' => $request->email]);
            $user->email = $request->email;
            $user->full_name = $Supplier->name;
            $user->password = bcrypt($request->email);
            $user->type = 2;
            $user->role_id = Role::where('name', 'Supplier')->first()->id;
            $user->role_name = 'Supplier';
            $user->vendor_code = $request->code;
            $user->vendor_name = $Supplier->name;
            $user->created_by = auth()->user()->username;
            $user->updated_by = auth()->user()->username;
            $Supplier->user()->save($user);

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => NULL,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function list(Request $request)
    {
        $Supplier = Supplier::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->take(10)
            ->get();

        return response()->json([
            'type' => 'success',
            'data' => $Supplier
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $Supplier = Supplier::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $Supplier
        ]);
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
                $Excels = Excel::toArray(new SuppliersImport, $files);
                $Excels = $Excels[0];

                foreach ($Excels as $Excel) {

                    if ($Excel['code'] != null) {

                        //store your file into database
                        $Supplier = Supplier::firstOrNew(['code' => $Excel['code']]);
                        $Supplier->code = $this->stringtoupper($Excel['code']);
                        $Supplier->name = $this->stringtoupper($Excel['name']);
                        $Supplier->address = $Excel['address'];
                        $Supplier->phone = $Excel['phone'];
                        $Supplier->contact = $Excel['contact'];
                        $Supplier->email = $Excel['email'];
                        $Supplier->currency = $Excel['currency'];

                        $Supplier->created_by = auth()->user()->username;
                        $Supplier->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Supplier->updated_by = auth()->user()->username;
                        $Supplier->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $Supplier->save();
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
                "message" => 'err: ' . $e->getMessage(),

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

    private function stringtoupper($string)
    {
        if ($string != '') {
            $string = strtolower($string);
            $string = strtoupper($string);
        }
        return $string;
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

    public function export(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'sort' => 'required|string',
            'order' => 'string',
        ]);

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';

        $suppliers = Supplier::where(function ($query) use ($keyword, $request) {
            if (!empty($keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $query->where($column, 'like', '%' . $keyword . '%');
                    } else {
                        $query->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }
            }
        })
            ->orderBy($request->sort, $order == 'ascend' ? 'asc' : 'desc')
            ->get();

        return Excel::download(new SuppliersExport($suppliers), 'suppliers.xlsx');
    }
}
