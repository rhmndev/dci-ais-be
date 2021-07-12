<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\User;

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

        })
        ->count();

        return response()->json([
            'type' => 'success',
            'data' => $users,
            'total' => $total
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'required|confirmed',
            'full_name' => 'required|string'
        ]);

        $user = new User;
        
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->full_name = $request->full_name;
        if (!empty($request->photo) && $request->photo != 'null') {

            $file = $request->file('photo');
            $file_extension = $file->extension();
            $filename = rand(0, 99).time().'.'.$file_extension;
            $file->storeAs('public/images', $filename);

            $user->photo = $filename;
        }
        
        $user->role_id = $request->role_id;
        $user->role_name = $request->role_name;
        $user->created_by = auth()->user()->full_name;
        $user->changed_by = auth()->user()->full_name;
        $user->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data saved successfully!'
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $user
        ]);
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'username' => 'required|string|unique:users,username,'.$id.',_id',
            'full_name' => 'required|string',
        ]);

        $user = User::findOrFail($id);

        $user->username = $request->username;

        if (!empty($request->password)) {
            $request->validate([
                'password' => 'confirmed'
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->full_name = $request->full_name;
        if (!empty($request->photo) && $request->photo != 'null') {

            if (Storage::drive('images')->exists($user->photo)) {
                Storage::drive('images')->delete($user->photo);
            }

            $file = $request->file('photo');
            $file_extension = $file->extension();
            $filename = rand(0, 99).time().'.'.$file_extension;
            $file->storeAs('public/images', $filename);

            $user->photo = $filename;
        }
        
        $user->role_id = $request->role_id;
        $user->role_name = $request->role_name;

        // $user->created_by = auth()->user()->full_name;
        $user->changed_by = auth()->user()->full_name;
        $user->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data updated successfully!'
        ], 201);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        
        if (Storage::drive('images')->exists($user->photo)) {
            Storage::drive('images')->delete($user->photo);
        }

        $user->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }
}
