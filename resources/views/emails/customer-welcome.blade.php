<x-mail::message>
# Welcome to {{ config('app.name') }}

Hi {{ $user->name }},

Use the code below to verify your account. It expires in 10 minutes.

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

If you didn't create this account, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
