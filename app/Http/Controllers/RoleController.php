<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);
        $roles = Role::where(function($where) use ($request){
            
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

        $total = Role::where(function($where) use ($request){
            
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
            'data' => $roles,
            'total' => $total
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $role = new Role;

        $permissions = [];

        foreach ($request->permissions as $index => $permission) {
            $permissions[] = [
                'permission_id' => $index,
                'allow' => $permission
            ];
        }

        $role->name = $request->name;
        $role->description = $request->description;
        $role->permissions = $permissions;
        $role->created_by = auth()->user()->username;
        $role->changed_by = auth()->user()->username;
        $role->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data saved successfully!'
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $permissions = [];
        foreach ($role->permissions as $permission) {
            $permissions[$permission['permission_id']] = $permission['allow'];
        }

        $role->perms = $permissions;

        return response()->json([
            'type' => 'success',
            'data' => $role
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $role = Role::findOrFail($id);

        $permissions = [];

        foreach ($request->permissions as $index => $permission) {
            $permissions[] = [
                'permission_id' => $index,
                'allow' => $permission
            ];
        }

        $role->name = $request->name;
        $role->description = $request->description;
        $role->permissions = $permissions;
        $role->changed_by = auth()->user()->username;
        $role->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data updated successfully!'
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $role = Role::where('_id', $id)->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully!'
        ], 201);
    }

    public function list(Request $request)
    {
        $roles = Role::when($request->keyword, function($query) use ($request) {
                        if (!empty($request->keyword)) {
                            $query->where('name', 'like', '%'.$request->keyword.'%');
                        }
                    })->take(10)
                    ->get();

        return response()->json([
        'type' => 'success',
        'data' => $roles
        ], 200);
    }
}
