<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->vendor->menuItems);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'photo_url' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'is_available' => ['boolean'],
        ]);

        $menuItem = $request->user()->vendor->menuItems()->create($data);

        return response()->json($menuItem, 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        abort_unless($menuItem->vendor_id === $request->user()->vendor->id, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'photo_url' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'is_available' => ['boolean'],
        ]);

        $menuItem->update($data);

        return response()->json($menuItem);
    }

    public function destroy(Request $request, MenuItem $menuItem)
    {
        abort_unless($menuItem->vendor_id === $request->user()->vendor->id, 403);

        $menuItem->delete();

        return response()->json(null, 204);
    }
}
