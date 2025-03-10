<?php

namespace App\Http\Controllers;

use App\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = Part::query();

            if ($request->has('code')) {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('category_code')) {
                $query->where('category_code', $request->category_code);
            }

            // if ($request->has('search')) {
            //     $searchTerm = $request->search;
            //     $query->where(function ($query) use ($searchTerm) {
            //         $query->where('code', 'like', '%' . $searchTerm . '%')
            //             ->orWhere('name', 'like', '%' . $searchTerm . '%');
            //     });
            // }

            $parts = $query->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $parts
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getPartList()
    {
        try {
            $parts = Part::select('id', 'code', 'name', 'category_code')->get();
            return response()->json($parts);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_code' => 'required|string|max:255',
            ]);

            $part = new Part([
                'code' => Part::generateNewCode(),
                'name' => $request->name,
                'description' => $request->description,
                'category_code' => $request->category_code,
            ]);

            $part->save();
            $part->generateQRCode();

            return response()->json([
                'message' => 'Part created successfully',
                'data' => $part
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $part = Part::findOrFail($id);
            return response()->json($part);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_code' => 'required|string|max:255',
            ]);

            $part = Part::findOrFail($id);
            $part->update($request->all());

            return response()->json([
                'message' => 'Part updated successfully',
                'data' => $part
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $part = Part::findOrFail($id);
            $part->delete();

            return response()->json([
                'message' => 'Part deleted successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
