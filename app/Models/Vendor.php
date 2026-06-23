<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'address',
        'lat',
        'lng',
        'phone',
        'is_open',
        'opens_at',
        'closes_at',
        'logo_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
