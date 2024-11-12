    Hi {{@$lead['send_to_name']}},

<br><br>

{{ @$lead['name'] }} is requesting assistance with the following lead.

<br><br>

    Please follow the link below to view the leads and progress it.

<br><br>

<a href="{{ url('admin/leads?lead_id=' . @$lead->lead_id ) }}" style="color: white; background: #1d1d76; border-radius: 5px; padding: 10px; text-decoration: none;">View Lead</a>

<br><br>

    Thanks and good luck

<br><br>
Regards,<br>
{{ config('app.name') }}
