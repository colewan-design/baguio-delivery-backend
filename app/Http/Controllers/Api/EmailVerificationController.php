<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function store(Request $request, User $user)
    {
        if ($user->email_verified_at) {
            return response()->json(['message' => 'This email is already confirmed.']);
        }

        $user->update(['email_verified_at' => now()]);

        return response()->json(['message' => 'Email confirmed.']);
    }
}
