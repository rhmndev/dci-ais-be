<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Imports\UsersImport;
use App\User;
use App\Vendor;
use Carbon\Carbon;
use Image;
use Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);
        $users = User::where(function($where) use ($request){
            
            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%'.$request->keyword.'%');
                    } else {
                        $where->orWhere($column, 'like', '%'.$request->keyword.'%');
                    }
                }
                    
            }

        })
        ->when(!empty($request->sort), function($query) use ($request){
            $query->orderBy($request->sort, $request->order == 'ascend' ? 'asc' : 'desc');
        })
        ->take((int)$request->perpage)
        ->skip((int)$skip)
        ->get();

        $total = User::where(function($where) use ($request){
            
            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%'.$request->keyword.'%');
                    } else {
                        $where->orWhere($column, 'like', '%'.$request->keyword.'%');
                    }
                }
            
            }

        })->count();

        return response()->json([
            'type' => 'success',
            'data' => $users,
            'total' => $total
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $user
        ]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'username' => 'required|string',
            'full_name' => 'required|string',
            'department' => 'required|string',
            'phone_number' => 'required|string',
            'npk' => 'required|numeric',
            'email' => 'required|email',
            'type' => 'required|numeric',
            'password' => 'nullable|confirmed',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
        ]);

        try {

            $User = User::firstOrNew(['username' => $request->username]);
        
            $User->username = $request->username;
            $User->full_name = $request->full_name;
            $User->department = $request->department;
            $User->phone_number = $request->phone_number;
            $User->npk = $request->npk;
            $User->email = $request->email;
            $User->type = intval($request->type);
    
            if (!empty($request->password) && $request->password != null) {

                $User->password = Hash::make($request->password);

            }

            
            if ($request->photo != null && $request->hasFile('photo')) {
        
                if (Storage::disk('public')->exists('/images/users/'.$User->photo)) {
                    Storage::disk('public')->delete('/images/users/'.$User->photo);
                }

                $image      = $request->file('photo');
                $fileName   = $User->username.'-'.$User->npk.'.' . $image->getClientOriginalExtension();
    
                $img = Image::make($image->getRealPath());
                $img->resize(120, 120, function ($constraint) {
                    $constraint->aspectRatio();                 
                });
    
                $img->stream(); // <-- Key point
                
                Storage::disk('public')->put('/images/users'.'/'.$fileName, $img, 'public');

                $User->photo = $fileName;

            } else {

                $User->photo = null;

            }
            
            $User->vendor_code = $request->type === 0 ? null : $request->vendor_id;
            $User->vendor_name = $request->type === 0 ? null : $request->vendor_name;

            $User->role_id = $request->role_id;
            $User->role_name = $request->role_name;

            $User->created_by = auth()->user()->username;
            $User->updated_by = auth()->user()->username;

            $User->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => NULL,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
    
                'type' => 'failed',
                'message' => 'Err: '.$e.'.',
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
                $Excels = Excel::toArray(new UsersImport, $files);
                $Excels = $Excels[0];
                // $Excels = json_decode(json_encode($Excels[0]), true);

                foreach ($Excels as $Excel) {

                    if ($Excel['username'] != null){

                        if ($Excel['type'] == 1){
                            $vendor_code = $Excel['vendor_code'];
                            $Vendor = Vendor::where('code', $vendor_code)->first();
                            if ($Vendor){
                                $vendor_name = $Vendor->name;
                            } else {
                                $vendor_name = null;
                            }
                        } else {
                            $vendor_code = null;
                            $vendor_name = null;
                        }
        
                        //store your file into database
                        $User = User::firstOrNew(['username' => $Excel['username']]);
                        $User->username = $Excel['username'];
                        $User->full_name = $Excel['full_name'];
                        $User->department = $Excel['department'];
                        $User->phone_number = $Excel['phone_number'];
                        $User->npk = $Excel['npk'];
                        $User->email = $Excel['email'];
                        $User->password = Hash::make('dci12345');
                        $User->type = $Excel['type'];
                        $User->vendor_code = $vendor_code;
                        $User->vendor_name = $vendor_name;
    
                        $User->created_by = auth()->user()->username;
                        $User->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $User->updated_by = auth()->user()->username;
                        $User->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $User->save();

                    }
                }
    
                return response()->json([
        
                    "result" => true,
                    "msg_type" => 'Success',
                    "msg" => 'Data stored successfully!',
                    // "msg" => $Excels,
        
                ], 200);
            }

        } catch (\Exception $e) {

            return response()->json([
    
                "result" => false,
                "msg_type" => 'error',
                "msg" => 'err: '.$e,
    
            ], 400);

        }

    }

    public function destroy($id)
    {
        $User = User::find($id);
        
        if (Storage::disk('public')->exists('/images/users/'.$User->photo)) {
            Storage::disk('public')->delete('/images/users/'.$User->photo);
        }

        $User->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }
}
