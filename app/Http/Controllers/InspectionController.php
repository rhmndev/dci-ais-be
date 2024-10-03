<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function getCustomer(Request $request)
    {
        try {
            $data = [
                "id" => 11,
                "customer_name" => "AHM",
                "data_items" => [
                    [
                        "id" => 1,
                        "name" => "CABLE COMP A THROTTLE K1A",
                        "part_number" => ["17910-K1A -N020-M2"],
                    ],
                    [
                        "id" => 2,
                        "name" => "CABLE COMP B THROTTLE K1A",
                        "part_number" => ["17920-K1A -N020-M2"],
                    ],
                    [
                        "id" => 3,
                        "name" => "CABLE COMP CLUTCH K45 N40",
                        "part_number" => ["22870-K45 -N400"],
                    ],
                ]
            ];
            return response()->json([
                'code' => 200,
                'message' => 'success',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
