<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->vendor->menuItems);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'photo'       => ['nullable', 'image', 'max:2048'],
            'category'    => ['nullable', 'string'],
            'is_available'=> ['boolean'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_url'] = Storage::disk('public')->url(
                $request->file('photo')->store('menu-items', 'public')
            );
        }

        unset($data['photo']);
        $menuItem = $request->user()->vendor->menuItems()->create($data);

        return response()->json($menuItem, 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        abort_unless($menuItem->vendor_id === $request->user()->vendor->id, 403);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'photo'       => ['nullable', 'image', 'max:2048'],
            'category'    => ['nullable', 'string'],
            'is_available'=> ['boolean'],
        ]);

        if ($request->hasFile('photo')) {
            if ($menuItem->photo_url) {
                Storage::disk('public')->delete(
                    str_replace('/storage/', '', parse_url($menuItem->photo_url, PHP_URL_PATH))
                );
            }
            $data['photo_url'] = Storage::disk('public')->url(
                $request->file('photo')->store('menu-items', 'public')
            );
        }

        unset($data['photo']);
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
