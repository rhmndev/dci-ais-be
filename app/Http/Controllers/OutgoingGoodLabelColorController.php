<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OutgoingGoodLabelColor;

class OutgoingGoodLabelColorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = OutgoingGoodLabelColor::query(); 
        
            if ($request->has('type') && $request->type != ''){
                $query->where('type','like','%'. $request->type . '%');
            }
            
            $labelColor = $query->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $labelColor
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getListLabelColor(Request $request)
    {
        try {
            $query = OutgoingGoodLabelColor::query();

            if ($request->has('type') && $request->type != '') {
                $query->where('type', 'like', '%' . $request->type . '%');
            }

            $labelColors = $query->get();

            return response()->json([
                'message' => 'success',
                'data' => $labelColors
            ]);
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
                'type' => 'required|string',
                'month' => 'required|string',
                'color' => 'required|string',
                'text_color' => 'required|string',
            ]);

            $labelColor = OutgoingGoodLabelColor::create($request->only(['type', 'month', 'color']));

            return response()->json([
                'message' => 'success',
                'data' => $labelColor
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'validation_failed',
                'errors' => $e->errors()
            ], 422);
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
                'type' => 'sometimes|required|string',
                'month' => 'sometimes|required|string',
                'color' => 'sometimes|required|string',
                'text_color' => 'sometimes|required|string',
            ]);

            $labelColor = OutgoingGoodLabelColor::findOrFail($id);
            $labelColor->update($request->only(['type', 'month', 'color','text_color']));

            return response()->json([
                'message' => 'success',
                'data' => $labelColor
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'validation_failed',
                'errors' => $e->errors()
            ], 422);
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
            $labelColor = OutgoingGoodLabelColor::findOrFail($id);
            $labelColor->delete();

            return response()->json([
                'message' => 'success',
            ], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getAllList()
    {
        try {
            $labelColors = OutgoingGoodLabelColor::all();
            return response()->json([
                'message' => 'success',
                'data' => $labelColors
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
