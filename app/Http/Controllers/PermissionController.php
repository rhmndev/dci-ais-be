<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);
        $permissions = Permission::where(function ($where) use ($request) {

            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%' . $request->keyword . '%');
                    } else {
                        $where->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            }
        })
            ->when(!empty($request->sort), function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order == 'ascend' ? 'asc' : 'desc');
            })
            ->take((int)$request->perpage)
            ->skip((int)$skip)
            ->get();

        $total = Permission::where(function ($where) use ($request) {

            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%' . $request->keyword . '%');
                    } else {
                        $where->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            }
        })
            ->count();

        return response()->json([
            'type' => 'success',
            'data' => $permissions,
            'total' => $total
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $permission = new Permission;

        $permission->name = $request->name;
        $permission->description = $request->description;
        $permission->url = $request->url;
        $permission->icon = $request->icon;
        $permission->parent_id = $request->parent_id;
        $permission->parent_name = $request->parent_name;
        $permission->order_number = $request->order_number;
        $permission->created_by = auth()->user()->created_by;
        $permission->changed_by = auth()->user()->changed_by;
        $permission->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data saved successfully'
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' => $permission
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $permission = Permission::findOrFail($id);

        $permission->name = $request->name;
        $permission->description = $request->description;
        $permission->url = $request->url;
        $permission->icon = $request->icon;
        $permission->parent_id = $request->parent_id;
        $permission->parent_name = $request->parent_name;
        $permission->order_number = $request->order_number;
        $permission->changed_by = auth()->user()->changed_by;
        $permission->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data updated successfully!'
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $permission = Permission::where('_id', $id)->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully!'
        ], 201);
    }

    public function list(Request $request)
    {
        $permissions = Permission::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->take(10)
            ->get();

        return response()->json([
            'type' => 'success',
            'data' => $permissions
        ], 200);
    }

    public function listParentId(Request $request)
    {
        $permissions = Permission::whereNull('parent_id')->get();

        return response()->json([
            'type' => 'success',
            'data' => $permissions
        ], 200);
    }

    public function get()
    {
        $permissions = Permission::all();

        return response()->json([
            'type' => 'success',
            'data' => $permissions
        ], 200);
    }
}
