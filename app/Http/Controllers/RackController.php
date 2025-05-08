<?php

namespace App\Http\Controllers;

use App\Rack;
use App\SegmentRack;
use App\StockSlock;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade as PDF;

class RackController extends Controller
{
    public function index(Request $request)
    {
        $racks = Rack::with('segmentRack')->orderBy('segment', 'asc')->orderBy('position', 'asc');

        if ($request->has('segment')) {
            $racks->where('segment', $request->segment);
        }

        if ($request->has('code')) {
            $racks->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->has('code_slock')) {
            $racks->where('code_slock', $request->code_slock);
        }

        if ($request->has('name')) {
            $racks->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('position')) {
            $racks->where('position', 'like', '%' . $request->position . '%');
        }

        if ($request->has('is_active')) {
            $racks->where('is_active', $request->is_active);
        }

        $racks = $racks->get();

        return response()->json([
            'message' => 'success',
            'data' => $racks
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code_slock' => 'required',
            'name' => 'required',
            'segment' => 'required',
            'position' => 'required',
        ]);

        $code = $request->segment . '.' . $request->code_slock . '.' . $request->position;

        $rack = Rack::create($request->all());
        $rack->code = $code;
        $rack->save();
        $rack->generateQrCode();

        return response()->json($rack, 201);
    }

    public function generateQrCode($id)
    {
        try {
            $rack = Rack::findOrFail($id);
            $rack->generateQrCode();

            return response()->json([
                'message' => 'QR Code generated successfully',
                'data' => $rack,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to generate QR Code',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $rack = Rack::findOrFail($id);
        return response()->json($rack);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:racks,code,' . $id,
            'code_slock' => 'required',
            'name' => 'required',
            'position' => 'required',
            'is_active' => 'required|boolean',
        ]);

        $rack = Rack::findOrFail($id);
        $rack->update($request->all());
        return response()->json($rack);
    }

    public function updateSegment(Request $request, $id)
    {
        $request->validate([
            'plant' => 'required',
            'code' => 'string',
            'name' => 'required',
            'slock' => 'required',
        ]);

        $segment = SegmentRack::findOrFail($id);
        $segment->update($request->all());
        return response()->json($segment);
    }

    public function destroy($id)
    {
        try {
            $rack = Rack::findOrFail($id);
            $rack->delete();
            return response()->json([
                'message' => 'Rack deleted successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to delete rack',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function createSegment(Request $request)
    {
        $request->validate([
            'plant' => 'required',
            'code' => 'string',
            'name' => 'required',
            'slock' => 'required',
        ]);

        $segment = SegmentRack::create($request->all());
        $segment->is_active = true;
        $segment->save();
        return response()->json($segment, 201);
    }

    public function getSegmentList(Request $request)
    {
        $segments = SegmentRack::query();

        if ($request->has('plant')) {
            $segments->where('plant', $request->plant);
        }

        if ($request->has('pluck_code')) {
            $segments->pluck('code');
        }

        $segments = $segments->get();

        return response()->json([
            'message' => 'success',
            'data' => $segments
        ]);
    }

    public function deleteSegment($id)
    {
        $segment = SegmentRack::findOrFail($id);
        $segment->delete();
        return response()->json(null, 204);
    }

    // generate for handle print to pdf qr rack with selected sloc
    public function printQrRackSloc($sloc)
    {
        $racks = Rack::where('code_slock', $sloc)->get();

        if ($racks->isEmpty()) {
            return response()->json([
                'message' => 'No racks found for the given sloc',
            ], 404);
        }

        // Prepare data for the PDF
        $qrCodes = [];
        foreach ($racks as $rack) {
            if ($rack->qrcode) {
                $qrPath = storage_path('app/public/' . $rack->qrcode);
                if (file_exists($qrPath)) {
                    // Convert image to base64
                    $imageData = base64_encode(file_get_contents($qrPath));
                    $src = 'data:image/png;base64,' . $imageData;
                    
                    $qrCodes[] = [
                        'code' => $rack->segment . '   ' . $rack->name,
                        'qrcode' => $src
                    ];
                } else {
                    $qrCodes[] = [
                        'code' => $rack->code,
                    ];
                }
            } else {
                $qrCodes[] = [
                    'code' => $rack->code,
                ];
            }
        }

        if (empty($qrCodes)) {
            return response()->json([
                'message' => 'No valid QR codes found for the given sloc',
            ], 404);
        }

        // Generate the PDF using a view
        $pdf = PDF::loadView('pdf.qr_racks', ['qrCodes' => $qrCodes]);
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        
        // Stream the PDF back to the client
        return $pdf->stream('qr_racks_' . $sloc . '.pdf');
    }

    public function showDataByQrCode($qrCode)
    {
        $rack = Rack::where('code', $qrCode)->first();

        if (!$rack) {
            return response()->json([
                'message' => 'QR Code not found',
            ], 404);
        }

        $MaterialInRack = StockSlock::where('rack_code', $qrCode)->first();
        if ($MaterialInRack) {
            $MaterialInRack->load('material', 'WhsMatControl', 'CreatedBy');
        }
        $rack->load('SegmentRack');
        return response()->json([
            'message' => 'success',
            'data' => [
                'rack' => $rack,
                'material_in_rack' => $MaterialInRack,
            ]
        ]);
    }
}
