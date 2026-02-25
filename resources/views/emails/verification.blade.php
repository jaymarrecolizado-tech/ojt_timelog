<x-mail::message>
# Verify Your Email Address

Hello {{ $userName }},

Thank you for registering with the OJT Time Log Management System!

Please verify your email address by clicking the button below:

<x-mail::button :url="$verificationUrl">
Verify Email Address
</x-mail::button>

This verification link will expire in 60 minutes. If you did not create an account, no further action is required.

If you're having trouble clicking the button, copy and paste the URL below into your web browser:

{{ $verificationUrl }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
