<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrderSigner extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'npk',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPurchaseOrdersWaitingForKnowing()
    {
        $knowingUsers = PurchaseOrderSigner::where('type', 'knowing')->get();

        $purchaseOrders = PurchaseOrder::where('status', 'waiting for knowing')->get();

        $results = [];
        foreach ($purchaseOrders as $po) {
            $names = [];
            foreach ($knowingUsers as $user) {
                $names[] = $user->user->name; // Assuming you have a relationship set up with the User model
            }

            $results[] = [
                'po_number' => $po->po_number,
                'waiting_for_knowing' => count($names) > 1 ? implode(', ', $names) : (count($names) == 1 ? $names[0] : "No users assigned"),  // Display names or "No users assigned"
            ];
        }


        return response()->json(['data' => $results], 200);
    }
}
