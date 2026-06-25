<?php

namespace App\Http\Controllers\Api;

use App\Mail\CustomerWelcomeMail;
use App\Support\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OtpController extends Controller
{
    public function verify(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'This account is already verified.']);
        }

        if (! Otp::verify($user, $data['code'])) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        return response()->json(['message' => 'Account verified.']);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'This account is already verified.']);
        }

        $code = Otp::issueFor($user);

        try {
            Mail::to($user->email)->send(new CustomerWelcomeMail($user, $code));
        } catch (Throwable $e) {
            Log::error('Failed to send OTP email.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Could not send the code. Try again shortly.'], 500);
        }

        return response()->json(['message' => 'Code sent.']);
    }
}
