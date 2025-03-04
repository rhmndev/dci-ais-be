<?php

namespace App\Http\Controllers;

use App\ShippingAddress;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function index()
    {
        $shippingAddresses = ShippingAddress::all();
        return response()->json($shippingAddresses);
    }
}
