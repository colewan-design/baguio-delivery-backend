<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrderLifecycleService
{
    /** @var array<string, string[]> */
    private const TRANSITIONS = [
        'pending' => ['accepted', 'rejected', 'cancelled'],
        'accepted' => ['rider_assigned', 'cancelled'],
        'rider_assigned' => ['ready_for_pickup'],
        'ready_for_pickup' => ['out_for_delivery'],
        'out_for_delivery' => ['completed'],
        'rejected' => [],
        'completed' => [],
        'cancelled' => [],
    ];

    public function accept(Order $order, User $changedBy): Order
    {
        $this->transitionTo($order, 'accepted', $changedBy);
        $this->assignNearestRider($order, $changedBy);

        return $order;
    }

    public function reject(Order $order, User $changedBy): Order
    {
        return $this->transitionTo($order, 'rejected', $changedBy);
    }

    public function markReadyForPickup(Order $order, User $changedBy): Order
    {
        return $this->transitionTo($order, 'ready_for_pickup', $changedBy);
    }

    public function markPickedUp(Order $order, User $changedBy): Order
    {
        return $this->transitionTo($order, 'out_for_delivery', $changedBy);
    }

    public function markDelivered(Order $order, User $changedBy): Order
    {
        $order = $this->transitionTo($order, 'completed', $changedBy);

        if ($order->rider) {
            $order->rider->update(['status' => 'available']);
        }

        return $order;
    }

    public function cancel(Order $order, User $changedBy): Order
    {
        return $this->transitionTo($order, 'cancelled', $changedBy);
    }

    // No broadcast() call here — clients subscribe to Supabase Realtime
    // Postgres Changes on `orders`/`order_status_logs` directly.
    public function transitionTo(Order $order, string $status, User $changedBy): Order
    {
        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition order from {$order->status} to {$status}.",
            ]);
        }

        $order->update(['status' => $status]);

        $order->statusLogs()->create([
            'status' => $status,
            'changed_by' => $changedBy->id,
        ]);

        return $order;
    }

    private function assignNearestRider(Order $order, User $changedBy): void
    {
        $vendor = $order->vendor;

        $nearestRider = Rider::query()
            ->where('status', 'available')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->get()
            ->sortBy(fn (Rider $rider) => DistanceService::haversineKm(
                (float) $vendor->lat,
                (float) $vendor->lng,
                (float) $rider->current_lat,
                (float) $rider->current_lng,
            ))
            ->first();

        if (! $nearestRider) {
            return;
        }

        $order->update(['rider_id' => $nearestRider->id]);
        $nearestRider->update(['status' => 'busy']);

        $this->transitionTo($order, 'rider_assigned', $changedBy);
    }
}
