<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'pending',
        'accepted',
        'rejected',
        'rider_assigned',
        'ready_for_pickup',
        'out_for_delivery',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'customer_id',
        'vendor_id',
        'rider_id',
        'status',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'subtotal',
        'delivery_fee',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_lat' => 'decimal:7',
            'delivery_lng' => 'decimal:7',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }
}
