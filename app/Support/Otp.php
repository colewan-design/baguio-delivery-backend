<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Otp
{
    public static function issueFor(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp_code' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        return $code;
    }

    public static function verify(User $user, string $code): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($code, $user->otp_code)) {
            return false;
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        return true;
    }
}
