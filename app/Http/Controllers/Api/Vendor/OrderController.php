<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderLifecycleService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderLifecycleService $lifecycle)
    {
    }

    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        $query = $vendor->orders()->with(['items.menuItem', 'customer', 'rider']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function accept(Request $request, Order $order)
    {
        $this->authorizeVendorOrder($request, $order);

        $this->lifecycle->accept($order, $request->user());

        return response()->json($order->fresh(['items.menuItem', 'rider']));
    }

    public function reject(Request $request, Order $order)
    {
        $this->authorizeVendorOrder($request, $order);

        $this->lifecycle->reject($order, $request->user());

        return response()->json($order->fresh());
    }

    public function ready(Request $request, Order $order)
    {
        $this->authorizeVendorOrder($request, $order);

        $this->lifecycle->markReadyForPickup($order, $request->user());

        return response()->json($order->fresh());
    }

    private function authorizeVendorOrder(Request $request, Order $order): void
    {
        abort_unless($order->vendor_id === $request->user()->vendor->id, 403);
    }
}
