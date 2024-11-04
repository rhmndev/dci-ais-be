<?php

namespace App\Http\Controllers;

use App\SupplierPart;
use Illuminate\Http\Request;

class SupplierPartController extends Controller
{
    public function show(Request $request, $id)
    {
        $SupplierPart = SupplierPart::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $SupplierPart
        ]);
    }

    public function getBySupplier(Request $request, $supplier_id)
    {
        $SupplierPart = SupplierPart::where('supplier_id', $supplier_id)->get();

        $SupplierPart->transform(function ($sp) {
            // Access properties on individual $supplierPart object
            $sp->part_name = $sp->part ? $sp->part->description : $sp->part_id;
            $unit = isset($sp->part) ? $sp->part->unit : $sp->part_id;
            $sp->unit = isset($unit) ? $unit : $sp->unit_id;
            return $sp;
        });
        // $SupplierPart->part = isset($SupplierPart->part) ? $SupplierPart->part : $SupplierPart->part_id;
        // $SupplierPart->unit = isset($SupplierPart->unit) ? $SupplierPart->unit->name : $SupplierPart->unit_id;

        return response()->json([
            'type' => 'success',
            'data' =>  $SupplierPart
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'part_id' => 'required',
            'part_number' => 'required|string',
            'unit_id' => 'required',
        ]);


        try {
            $SupplierPart = new SupplierPart();
            $SupplierPart->supplier_id = $request->supplier_id;
            $SupplierPart->part_id = $request->part_id;
            $SupplierPart->part_number = $request->part_number;
            $SupplierPart->unit_id = $request->unit_id;

            $SupplierPart->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => $SupplierPart,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,
            ], 400);
        }
    }
}
