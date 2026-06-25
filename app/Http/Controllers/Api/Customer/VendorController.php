<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\DistanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'category' => ['sometimes', Rule::in(['food', 'groceries', 'pharmacy', 'errands'])],
        ]);

        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');

        $vendors = Vendor::query()
            ->where('status', 'approved')
            ->where('is_open', true)
            ->when($request->query('category'), fn ($query, $category) => $query->where('category', $category))
            ->get()
            ->map(function (Vendor $vendor) use ($lat, $lng) {
                $vendor->distance_km = round(DistanceService::haversineKm(
                    $lat,
                    $lng,
                    (float) $vendor->lat,
                    (float) $vendor->lng,
                ), 2);

                return $vendor;
            })
            ->sortBy('distance_km')
            ->values();

        return response()->json($vendors);
    }

    public function show(Vendor $vendor)
    {
        abort_unless($vendor->status === 'approved', 404);

        return response()->json($vendor);
    }

    public function menu(Vendor $vendor)
    {
        return response()->json(
            $vendor->menuItems()->where('is_available', true)->get()
        );
    }
}
