<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    public function index(Request $request)
    {
        $query = Rider::query()->with('user');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->paginate(20));
    }
}
