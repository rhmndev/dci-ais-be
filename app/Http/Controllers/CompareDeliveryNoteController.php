<?php

namespace App\Http\Controllers;

use App\CompareDeliveryNote;
use App\CompareDeliveryNoteAHM;
use Illuminate\Http\Request;

class CompareDeliveryNoteController extends Controller
{
    public function getCompareDN(Request $request)
    {
        $request->validate([
            'dn_no' => 'required',
            'customer' => 'string',
        ]);

        try {
            $customer = 'adm';

            $dnNo = $request->input('dn_no');
            if ($request->has('customer')) {
                // make this lowercase
                $customer = strtolower($request->input('customer'));
            }
            switch ($customer) {
                case 'ahm':
                    $compare = CompareDeliveryNoteAHM::where('dn_no',  $dnNo)->get();
                    break;
                default:
                    $compare = CompareDeliveryNote::where('dn_no',  $dnNo)->get();
            }
            // $compare = CompareDeliveryNote::where('dn_no', $dnNo)->get();

            if (!$compare) {
                return response()->json(['error' => 'Compare not found'], 404);
            }

            $compare->each(function ($item) {
                $item->tracking_boxes = $item->trackingBoxes();
            });

            return response()->json([
                'message' => 'Successfully retrieved compare details',
                'data' => $compare,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve compare details',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCurrentlyTrackingBoxStatus()
    {
        try {
            $compare = CompareDeliveryNote::all();

            if (!$compare) {
                return response()->json(['error' => 'Compare not found'], 404);
            }

            $compare->each(function ($item) {
                $item->tracking_boxes = $item->trackingBoxes();
            });

            return response()->json([
                'message' => 'Successfully retrieved compare details',
                'data' => $compare,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve compare details',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getKanban(Request $request)
    {
        $request->validate([
            'kbn_no' => 'required',
            'customer' => 'string',
        ]);

        try {
            $customer = 'adm';

            $kbnNo = $request->input('kbn_no');
            if ($request->has('customer')) {
                // make this lowercase
                $customer = strtolower($request->input('customer'));
            }

            switch ($customer) {
                case 'ahm':
                    $compare = CompareDeliveryNoteAHM::where('job_seq',  $kbnNo)->first();
                    break;
                default:
                    $compare = CompareDeliveryNote::where('kbn_no',  $kbnNo)->first();
            }

            if (!$compare) {
                return response()->json(['error' => 'Kanban not found'], 404);
            }

            return response()->json([
                'message' => 'Successfully retrieved compare details',
                'data' => $compare,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve compare details: ' . $e->getMessage()], 500);
        }
    }
}
