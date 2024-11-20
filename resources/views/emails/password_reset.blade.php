@component('mail::message')
# Password Reset

Hello {{ $user->full_name }},

Your password has been reset.  Your new password is: **{{ $newPassword }}**

You should log in and change this password immediately.


@component('mail::button', ['url' => config('app.client_url').'/login']) // Replace with your client URL
Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

