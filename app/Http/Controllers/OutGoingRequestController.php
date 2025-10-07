<?php

namespace App\Http\Controllers;

use App\OutgoingGood;
use App\OutgoingRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;


class OutGoingRequestController extends Controller
{
    public function archive(Request $request)
{
    $request->validate([
        'outgoing_number' => 'required|string',
        'good_receipt_ref' => 'nullable|string',
        'archived_by' => 'nullable|string',
        'archived_reason' => 'nullable|string',
    ]);

    $outgoing = OutgoingGood::where('number', $request->outgoing_number)->first();
    if (!$outgoing) {
        return response()->json(['success' => false, 'message' => 'Outgoing request not found'], 404);
    }

    $outgoing->is_archived = true;
    $outgoing->archived_at = now();
    $outgoing->archived_by = $request->archived_by;
    $outgoing->archived_reason = $request->archived_reason;
    $outgoing->good_receipt_ref = $request->good_receipt_ref;
    $outgoing->save();

    return response()->json(['success' => true, 'message' => 'Outgoing request archived', 'data' => $outgoing]);
}
}
