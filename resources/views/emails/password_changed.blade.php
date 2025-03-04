@component('mail::message')
# Password Changed

Hello {{ $user->full_name }},

Your password has been successfully changed.

If you did not initiate this change, please contact us immediately.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
