<?php

namespace App\Http\Controllers\Api\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiderController extends Controller
{
    public function __construct(private OrderLifecycleService $lifecycle)
    {
    }

    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['offline', 'available', 'busy'])],
        ]);

        $request->user()->rider->update($data);

        return response()->json($request->user()->rider->fresh());
    }

    public function updateLocation(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $request->user()->rider->update([
            'current_lat' => $data['lat'],
            'current_lng' => $data['lng'],
        ]);

        return response()->json(['message' => 'Location updated.']);
    }

    public function jobs(Request $request)
    {
        $rider = $request->user()->rider;

        return response()->json(
            $rider->orders()
                ->whereIn('status', ['rider_assigned', 'ready_for_pickup', 'out_for_delivery'])
                ->with(['items.menuItem', 'vendor', 'customer'])
                ->get()
        );
    }

    public function pickup(Request $request, Order $order)
    {
        $this->authorizeRiderOrder($request, $order);

        $this->lifecycle->markPickedUp($order, $request->user());

        return response()->json($order->fresh());
    }

    public function deliver(Request $request, Order $order)
    {
        $this->authorizeRiderOrder($request, $order);

        $this->lifecycle->markDelivered($order, $request->user());

        return response()->json($order->fresh());
    }

    private function authorizeRiderOrder(Request $request, Order $order): void
    {
        abort_unless($order->rider_id === $request->user()->rider->id, 403);
    }
}
