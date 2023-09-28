{{--@component('mail::message')--}}
    Hi,

<br><br>

The following lead has been updated to signed up

<br>
<br>

    Please follow the link below to view the leads and progress it.

<br><br>

{{--    @component('mail::button', ['url' => url('admin/leads')])--}}
        <a href="{{ url('admin/leads?lead_id=' . @$lead->lead_id ) }}" style="color: white; background: #1d1d76; border-radius: 5px; padding: 10px; text-decoration: none;">View Lead</a>
{{--    @endcomponent--}}

<br><br>

    Thanks and good luck

<br><br>

    Regards,<br>
    {{ config('app.name') }}
{{--@endcomponent--}}
