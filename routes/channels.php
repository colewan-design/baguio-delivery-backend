<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);

    if (! $order) {
        return false;
    }

    return $user->id === $order->customer_id
        || $user->id === $order->vendor?->user_id
        || $user->id === $order->rider?->user_id
        || $user->role === 'admin';
});
