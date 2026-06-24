<x-mail::message>
# Welcome to {{ config('app.name') }}

Hi {{ $user->name }},

You've been approved as a {{ $roleLabel }}. Click the button below to set your password and activate your account.

<x-mail::button :url="$activationUrl">
Activate Account
</x-mail::button>

This link expires in 7 days. If you didn't expect this email, you can ignore it.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
