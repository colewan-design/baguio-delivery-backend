<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\URL;

class VerificationLink
{
    public static function for(User $user): string
    {
        $signedPath = URL::temporarySignedRoute(
            'verification.complete',
            now()->addDays(7),
            ['user' => $user->id],
            false
        );

        $query = parse_url($signedPath, PHP_URL_QUERY);

        return rtrim(config('app.frontend_url'), '/')."/verify-email/{$user->id}?{$query}";
    }
}
