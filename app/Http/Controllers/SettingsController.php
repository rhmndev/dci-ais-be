<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Settings;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);
        $settings = Settings::where(function($where) use ($request){
            
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

        $total = Settings::where(function($where) use ($request){
            
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
            'data' => $settings,
            'total' => $total
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'variable' => 'required',
            'value' => 'required'
        ]);

        $setting = new Settings;

        $setting->variable = $request->variable;
        $setting->value = $request->value;
        $setting->created_by = $request->created_by;
        $setting->changed_by = $request->changed_by;
        $setting->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data saved successfully!'
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $setting = Settings::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' => $setting
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'variable' => 'required',
            'value' => 'required'
        ]);

        $setting = Settings::findOrFail($id);

        $setting->variable = $request->variable;
        $setting->value = $request->value;
        $setting->changed_by = $request->changed_by;
        $setting->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Data updated successfully!'
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $setting = Settings::where('_id', $id)->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully!'
        ], 201);
    }

    public function find(Request $request)
    {
        $setting = Settings::where('variable', $request->variable)->first();

        return response()->json([
            'type' => 'success',
            'data' => $setting->value
        ], 200);
    }

}
