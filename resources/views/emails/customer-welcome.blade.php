<x-mail::message>
# Welcome to {{ config('app.name') }}

Hi {{ $user->name }},

Thanks for signing up! Please confirm your email address to finish setting up your account.

<x-mail::button :url="$verificationUrl">
Confirm Email
</x-mail::button>

This link expires in 7 days. If you didn't create this account, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
