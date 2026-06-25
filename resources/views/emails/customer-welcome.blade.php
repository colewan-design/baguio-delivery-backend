<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1f2937; max-width: 480px; margin: 0 auto; padding: 24px;">
    <h2 style="margin-bottom: 4px;">Welcome to {{ config('app.name') }}</h2>
    <p>Hi {{ $user->name }},</p>
    <p>Use the code below to verify your account. It expires in 10 minutes.</p>
    <p style="font-size: 32px; font-weight: bold; letter-spacing: 6px; background: #f3f4f6; padding: 16px; text-align: center; border-radius: 8px;">
        {{ $otp }}
    </p>
    <p>If you didn't create this account, you can ignore this email.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
