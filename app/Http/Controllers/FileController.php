<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\File;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);

        $files = File::where(function ($where) use ($request) {
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

        $total = File::where(function ($where) use ($request) {
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
            'data' => $files,
            'total' => $total
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            // 1. Validate (file type, size, expiry days if applicable)
            $request->validate([
                'name' => 'required',
                'type' => 'required'
            ]);

            // 2. Store the file (using Storage facade)
            $filePath = $request->file('file')->store('uploads', 'public'); // Or your preferred storage

            // 3. Calculate expiry (if provided in the request)
            $expiresAt = $request->has('expiry_days')
                ? Carbon::now()->addDays($request->input('expiry_days'))
                : null;

            // 4. Create the database record
            File::create([
                'user_id' => auth()->id(),
                'name' => $request->file('file')->getClientOriginalName(),
                'path' => $filePath,
                'size' => $request->file('file')->getSize(),
                'type' => $request->type,
                'ext' => $request->file('file')->getClientOriginalExtension(),
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'File uploaded successfully!'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }
}
