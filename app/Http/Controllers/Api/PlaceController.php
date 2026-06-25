<?php

namespace App\Http\Controllers\Api;

use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $q = $data['q'];

        $places = Place::query()
            ->where('name', 'like', "%{$q}%")
            ->orderByRaw('name LIKE ? DESC', ["{$q}%"])
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'category', 'lat', 'lng']);

        return response()->json($places);
    }
}
