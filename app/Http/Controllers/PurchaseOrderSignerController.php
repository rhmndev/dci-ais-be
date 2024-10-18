<?php

namespace App\Http\Controllers;

use App\PurchaseOrderSigner;
use Illuminate\Http\Request;

class PurchaseOrderSignerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $signers = PurchaseOrderSigner::all();
        return response()->json($signers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:knowed,checked,approved',
            'user_id' => 'required|string',
        ]);

        $signer = PurchaseOrderSigner::create($request->all());
        return response()->json($signer, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PurchaseOrderSigner  $purchaseOrderSigner
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrderSigner $purchaseOrderSigner)
    {
        return response()->json($purchaseOrderSigner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PurchaseOrderSigner  $purchaseOrderSigner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrderSigner $purchaseOrderSigner)
    {
        $request->validate([
            'type' => 'sometimes|in:knowed,checked,approved',
            'user_id' => 'sometimes|string',
        ]);

        $purchaseOrderSigner->update($request->all());
        return response()->json($purchaseOrderSigner);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PurchaseOrderSigner  $purchaseOrderSigner
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrderSigner $purchaseOrderSigner)
    {
        $purchaseOrderSigner->delete();
        return response()->json(null, 204);
    }

    public function mySigner()
    {
        try {
            $userId = auth()->user()->id;
            $signers = PurchaseOrderSigner::where('user_id', $userId)->get();

            $hasKnowed = false;
            $hasChecked = false;
            $hasApproved = false;

            foreach ($signers as $signer) {
                if ($signer->type === 'knowed') {
                    $hasKnowed = true;
                } elseif ($signer->type === 'checked') {
                    $hasChecked = true;
                } elseif ($signer->type === 'approved') {
                    $hasApproved = true;
                }
            }

            $result = [
                'knowed' => $hasKnowed,
                'checked' => $hasChecked,
                'approved' => $hasApproved,
            ];

            return response()->json([
                'type' => 'success',
                'data' => $result,
                'message' => ''
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
