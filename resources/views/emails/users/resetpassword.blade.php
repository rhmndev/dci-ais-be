@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => config('app.front_url').'/resetpassword/'.$token])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
