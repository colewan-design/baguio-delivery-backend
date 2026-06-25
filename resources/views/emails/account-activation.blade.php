<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1f2937; max-width: 480px; margin: 0 auto; padding: 24px;">
    <h2 style="margin-bottom: 4px;">Welcome to {{ config('app.name') }}</h2>
    <p>Hi {{ $user->name }},</p>
    <p>You've been approved as a {{ $roleLabel }}. Click the button below to set your password and activate your account.</p>
    <p style="text-align: center;">
        <a href="{{ $activationUrl }}" style="display: inline-block; background: #16a34a; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">
            Activate Account
        </a>
    </p>
    <p>This link expires in 7 days. If you didn't expect this email, you can ignore it.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
