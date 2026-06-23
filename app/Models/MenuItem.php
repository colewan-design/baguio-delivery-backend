<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'price',
        'photo_url',
        'is_available',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
