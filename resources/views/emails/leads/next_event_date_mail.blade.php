{{--@component('mail::message')--}}
    Hi,

<br><br>

The following lead next event date is overdue <span style="color: blue;"># {{ @$lead['id'] }}</span>

<br><br>

    Please follow the link below to view the leads and progress it.

<br><br>

{{--    @component('mail::button', ['url' => url('admin/leads')])--}}
        <a href="{{ url('admin/leads?lead_id=' . @$lead['id'] ) }}" style="color: white; background: #1d1d76; border-radius: 5px; padding: 10px; text-decoration: none;">View Lead</a>
{{--    @endcomponent--}}

<br><br>

    Thanks and good luck

<br><br>

    Regards,<br>
    {{ config('app.name') }}
{{--@endcomponent--}}
