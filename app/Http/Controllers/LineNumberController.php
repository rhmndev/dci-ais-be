<?php

namespace App\Http\Controllers;

use App\LineNumber;
use Illuminate\Http\Request;

class LineNumberController extends Controller
{
    public function list(Request $request)
    {
        $lineNumbers = LineNumber::when($request->keyword, function ($query) use ($request) {
            if (!empty($request->keyword)) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
        })->take(10)
            ->get();

        return response()->json([
            'type' => 'success',
            'data' => $lineNumbers
        ], 200);
    }
}
