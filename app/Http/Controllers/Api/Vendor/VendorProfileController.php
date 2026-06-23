<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VendorProfileController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'is_open' => ['required', 'boolean'],
        ]);

        $request->user()->vendor->update($data);

        return response()->json($request->user()->vendor->fresh());
    }
}
