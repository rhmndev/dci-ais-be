<?php

namespace App\Http\Controllers;

use App\Rack;
use App\SegmentRack;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            'code' => 'required|unique:racks',
            'code_slock' => 'required',
            'name' => 'required',
            'segment' => 'required',
            'position' => 'required',
            'is_active' => 'required|boolean',
        ]);

        $rack = Rack::create($request->all());
        return response()->json($rack, 201);
    }

    public function generateQrCode($id)
    {
        $rack = Rack::findOrFail($id);
        $qrCode = QrCode::size(300)->generate($rack->code);

        return response()->json([
            'message' => 'QR code generated successfully',
            'data' => base64_encode($qrCode),
        ]);
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

    public function destroy($id)
    {
        $rack = Rack::findOrFail($id);
        $rack->delete();
        return response()->json(null, 204);
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
}
