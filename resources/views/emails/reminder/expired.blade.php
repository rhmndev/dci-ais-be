@component('mail::message')
# Reminder Expired

Your reminder "{{ $reminder->title }}" has expired. Please mark it as complete if you have addressed it.


@component('mail::button', ['url' => config('app.front_url').'/my-reminder/' . $reminder->_id])
View Reminder
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
