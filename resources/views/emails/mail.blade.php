@component('mail::message')
    # Dear {{$content['user_name']}}

    {{ wordwrap($content['message'],60,"\n") }}
    {{"\n"}}
    {{ wordwrap($content['password'],60,"\n") }}
    {{"\n"}}
    {{ wordwrap($content['instruction'],60,"\n") }}

   Kind Regards, <br>
    {{ config('app.name') }}
@endcomponent
