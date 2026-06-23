<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;

class SupabaseRealtimeTokenService
{
    public function issueFor(User $user): string
    {
        $now = time();

        $claims = [
            'role' => 'authenticated',
            'iat' => $now,
            'exp' => $now + (60 * 60),
            'user_id' => $user->id,
            'user_role' => $user->role,
            'vendor_id' => $user->vendor?->id,
            'rider_id' => $user->rider?->id,
        ];

        return JWT::encode($claims, config('services.supabase.jwt_secret'), 'HS256');
    }
}
