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
        // TODO Bug when remove user not destroyed in db
        $allTypes = ['knowed', 'checked', 'approved'];
        $payload = $request->all();
        try {
            $providedTypes = collect($payload)->pluck('type')->toArray();
            $missingTypes = array_diff($allTypes, $providedTypes);

            foreach ($payload as $signerData) {
                $request->validate([
                    '*.type' => 'required|in:knowed,checked,approved',
                    '*.user_ids' => 'required|array',
                ]);

                foreach ($signerData['user_ids'] as $user_id) {
                    $existingSigner = PurchaseOrderSigner::where('type', $signerData['type'])
                        ->where('user_id', $user_id)
                        ->first();

                    if ($existingSigner) {
                        $existingSigner->update([
                            'updated_at' => now(),
                        ]);
                    } else {
                        PurchaseOrderSigner::create([
                            'type' => $signerData['type'],
                            'user_id' => $user_id,
                        ]);
                    }
                }
            }

            // Handle deletion of missing types
            if (!empty($missingTypes)) {
                // Delete signers of missing types for this Purchase Order
                PurchaseOrderSigner::whereIn('type', $missingTypes)->delete();
            }

            return response()->json([
                'type' => 'success',
                'data' => '',
                'message' => 'Signers created successfully'
            ], 201);
        } catch (\Throwable $th) {

            return response()->json([
                'type' => 'error',
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
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

    public function getByTypeName(Request $request)
    {
        $request->validate([
            'type' => 'required'
        ]);
        $typeName = $request->type;
        try {
            $signers = PurchaseOrderSigner::where('type', $typeName)->with('user')->get();

            // Check if any signers were found
            if ($signers->isEmpty()) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'No signers found for type: ' . $typeName,
                    'data' => NULL,
                ], 404);
            }

            return response()->json([
                'type' => 'success',
                'data' => $signers,
                'message' => '',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error:' . $e->getMessage(),
                'data' => NULL,
            ], 500);
        }
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
