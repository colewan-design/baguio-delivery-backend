<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ActivationController extends Controller
{
    public function store(Request $request, User $user)
    {
        if ($user->email_verified_at) {
            return response()->json(['message' => 'This account has already been activated.'], 422);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        return response()->json(['message' => 'Account activated. You can now log in.']);
    }
}
