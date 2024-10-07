<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StatusPartComponent;

class StatusPartComponentController extends Controller
{
    public function list(Request $request)
    {
        $StatusPartComponent = StatusPartComponent::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->get();

        return response()->json([
            'type' => 'success',
            'data' => $StatusPartComponent
        ], 200);
    }
}
