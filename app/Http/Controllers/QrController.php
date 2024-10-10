<?php

namespace App\Http\Controllers;

use App\Inspection;
use App\Qr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrController extends Controller
{
    public function index()
    {
        $qrs = Qr::all();
        return response()->json([
            'type' => 'success',
            'data' => $qrs
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'path' => 'required',
            'type' => 'required',
            'description' => 'required',
        ]);

        try {
            $qr = new Qr;
            $qr->uuid = Str::uuid()->toString();
            $qr->path = $request->path;
            $qr->type = $request->type;
            $qr->has_expired = $request->has_expired ?? 0;
            $qr->expired_date = $request->expired_date;
            $qr->description = $request->description;
            $qr->created_by = auth()->user()->id;
            $qr->save();

            return response()->json([
                'type' => 'success',
                'message' => 'QR code saved successfully.',
                'data' => $qr
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function show($id)
    {
        $qr = Qr::find($id);

        if (!$qr) {
            return response()->json([
                'type' => 'failed',
                'message' => 'QR code not found.',
                'data' => NULL,
            ], 404);
        }

        return response()->json([
            'type' => 'success',
            'data' => $qr
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'path' => 'required',
            'type' => 'required',
            'description' => 'required',
        ]);

        $qr = Qr::find($id);

        if (!$qr) {
            return response()->json([
                'type' => 'failed',
                'message' => 'QR code not found.',
                'data' => NULL,
            ], 404);
        }

        try {
            $qr->path = $request->path;
            $qr->type = $request->type;
            $qr->has_expired = $request->has_expired;
            $qr->expired_date = $request->expired_date;
            $qr->description = $request->description;
            $qr->updated_by = auth()->user()->id;
            $qr->save();

            return response()->json([
                'type' => 'success',
                'message' => 'QR code updated successfully.',
                'data' => $qr
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function destroy($id)
    {
        $qr = Qr::find($id);

        if (!$qr) {
            return response()->json([
                'type' => 'failed',
                'message' => 'QR code not found.',
                'data' => NULL,
            ], 404);
        }

        try {
            $qr->delete();

            return response()->json([
                'type' => 'success',
                'message' => 'QR code deleted successfully.',
                'data' => NULL
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }

    public function getData(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'data' => 'required',
        ]);

        $result = [];
        try {
            if ($request->has('type') && $request->has('data')) {
                switch ($request->type) {
                    case 'inspection':
                        $qrCode = Qr::where('uuid', $request->data)->first();
                        if ($qrCode) {
                            $inspection = Inspection::where('qr_uuid', $qrCode->uuid)->first();
                            if ($inspection) {
                                $inspection->qr_image_path = asset('storage/' . $qrCode->path);
                                $result = $inspection;
                            }
                        }
                        break;

                    default:

                        break;
                }
                return response()->json([
                    'type' => 'success',
                    'data' => $result
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $th->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }
}
