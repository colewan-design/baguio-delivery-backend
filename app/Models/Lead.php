<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'type',
        'name',
        'contact',
        'message',
        'status',
    ];

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }
}
