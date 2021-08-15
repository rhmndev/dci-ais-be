<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Imports\UsersImport;
use App\User;
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

        if ($request->id == null){
        
            $request->validate([
                'username' => 'required|string|unique:users,username',
                'full_name' => 'required|string',
                'department' => 'required|string',
                'phone_number' => 'required|string',
                'npk' => 'required|numeric',
                'email' => 'required|email',
                'type' => 'required|numeric',
                'password' => 'required|confirmed',
                'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
            ]);

            $User = new User;

        } else {
        
            $request->validate([
                'username' => 'required|string|unique:users,username,'.$request->id.',_id',
                'full_name' => 'required|string',
                'department' => 'required|string',
                'phone_number' => 'required|string',
                'npk' => 'required|numeric',
                'email' => 'required|email',
                'type' => 'required|numeric',
                'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
            ]);

            $User = User::findOrFail($request->id);
        }

        try {
        
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

                $image      = $request->file('photo');
                $fileName   = $User->username.'-'.$User->npk.'.' . $image->getClientOriginalExtension();
    
                $img = Image::make($image->getRealPath());
                $img->resize(120, 120, function ($constraint) {
                    $constraint->aspectRatio();                 
                });
    
                $img->stream(); // <-- Key point
                
                Storage::disk('public')->put('/images/users'.'/'.$fileName, $img, 'public');

                $User->photo = $fileName;
            }
            
            $User->vendor_id = $request->type === 0 ? null : $request->vendor_id;
            $User->vendor_name = $request->type === 0 ? null : $request->vendor_name;

            $User->role_id = $request->role_id;
            $User->role_name = $request->role_name;

            $User->created_by = auth()->user()->full_name;
            $User->updated_by = auth()->user()->full_name;

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
    
                //store your file into database
                $User = new User();

                foreach ($Excels as $Excel) {

                    $QueryGetDataByFilter = User::query();

                    $QueryGetDataByFilter = $QueryGetDataByFilter->where('username', $Excel['username']);
                    $QueryGetDataByFilter = $QueryGetDataByFilter->where('npk', $Excel['npk']);
                    $QueryGetDataByFilter = $QueryGetDataByFilter->where('phone_number', $Excel['phone_number']);
                    $QueryGetDataByFilter = $QueryGetDataByFilter->where('email', $Excel['email']);

                    if (count($QueryGetDataByFilter->get()) == 0){

                        $data_tmp = array();
                        
                        $data_tmp['username'] = $Excel['username'];
                        $data_tmp['full_name'] = $Excel['full_name'];
                        $data_tmp['department'] = $Excel['department'];
                        $data_tmp['phone_number'] = $Excel['phone_number'];
                        $data_tmp['npk'] = $Excel['npk'];
                        $data_tmp['email'] = $Excel['email'];
                        $data_tmp['password'] = Hash::make($Excel['password']);

                        $data_tmp['created_by'] = auth()->user()->full_name;
                        $data_tmp['created_at'] = date('Y-m-d H:i:s');

                        $data_tmp['updated_by'] = auth()->user()->full_name;
                        $data_tmp['updated_at'] = date('Y-m-d H:i:s');

                        // Converting to Array
                        array_push($data, $data_tmp);
                        
                    }

                }

                if (count($data) > 0){

                    $User->insert($data);
    
                    return response()->json([
            
                        "result" => true,
                        "msg_type" => 'Success',
                        "msg" => 'Data stored successfully!',
                        // "msg" => $data,
            
                    ], 200);

                } else {

                    return response()->json([
            
                        "result" => false,
                        "msg_type" => 'error',
                        "msg" => 'Data already uploaded',
            
                    ], 200);

                }
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
