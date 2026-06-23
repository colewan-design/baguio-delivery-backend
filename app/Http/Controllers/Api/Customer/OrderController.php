<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->orders()
                ->with(['items.menuItem', 'vendor', 'rider'])
                ->latest()
                ->paginate(20)
        );
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        return response()->json(
            $order->load(['items.menuItem', 'vendor', 'rider', 'statusLogs'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'delivery_address' => ['required', 'string'],
            'delivery_lat' => ['required', 'numeric'],
            'delivery_lng' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $menuItems = MenuItem::whereIn('id', collect($data['items'])->pluck('menu_item_id'))
                ->where('vendor_id', $data['vendor_id'])
                ->where('is_available', true)
                ->get()
                ->keyBy('id');

            abort_if($menuItems->count() !== count($data['items']), 422, 'One or more menu items are unavailable.');

            $subtotal = collect($data['items'])->sum(
                fn ($item) => $menuItems[$item['menu_item_id']]->price * $item['quantity']
            );

            $deliveryFee = $data['delivery_fee'] ?? 0;

            $order = Order::create([
                'customer_id' => $request->user()->id,
                'vendor_id' => $data['vendor_id'],
                'status' => 'pending',
                'delivery_address' => $data['delivery_address'],
                'delivery_lat' => $data['delivery_lat'],
                'delivery_lng' => $data['delivery_lng'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $menuItem = $menuItems[$item['menu_item_id']];

                $order->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'price_at_order' => $menuItem->price,
                ]);
            }

            $order->statusLogs()->create([
                'status' => 'pending',
                'changed_by' => $request->user()->id,
            ]);

            return $order;
        });

        return response()->json($order->load('items.menuItem'), 201);
    }
}
