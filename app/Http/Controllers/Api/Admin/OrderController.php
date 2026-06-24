<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rider;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['customer', 'vendor', 'rider.user', 'items.menuItem']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load(['customer', 'vendor', 'rider.user', 'items.menuItem', 'statusLogs'])
        );
    }

    public function reassignRider(Request $request, Order $order)
    {
        $data = $request->validate([
            'rider_id' => ['required', 'exists:riders,id'],
        ]);

        if ($order->rider) {
            $order->rider->update(['status' => 'available']);
        }

        $order->update(['rider_id' => $data['rider_id']]);

        Rider::find($data['rider_id'])->update(['status' => 'busy']);

        return response()->json($order->fresh(['rider']));
    }
}
