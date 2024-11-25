@component('mail::send_mail',['data'=>$data])
{{--{{dd($data)}}--}}

{{--    {{ ($data['signature']) ? $data['signature']: "Kind Regards" }}, <br>--}}
{{--    {{ config('app.name') }}--}}
{{--    <hr>--}}
{{--    <div style="width:100%">--}}
{{--        <div style="align-content: center">--}}
{{--            <small>Note: This is a system generated mail. Please <b>DO NOT</b> reply to it.</small>--}}
{{--        </div>--}}
{{--    </div>--}}
@endcomponent
