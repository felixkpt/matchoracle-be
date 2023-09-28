<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
{{--    <link href="{{ url("css/email.css") }}" rel="stylesheet" type="text/css">--}}
    <title>Email</title>
    <style type="text/css">
        @media only screen and (max-width: 600px) {
            .inner-body {

                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
                background: {{ (@$organization->nav_bar_background_color) ? @$organization->nav_bar_background_color : "#003399" }}     !important;
            }
        }

        .button{
            padding: 10px 15px;
            background: {{ (@$organization->nav_bar_background_color) ? @$organization->nav_bar_background_color : "#003399" }}     !important;
        }

        .header1 {
            padding-left: 2px;
            text-align: center;
            background-color: #003399!important;
            height: 10px
        }

        /* Base */

        body,
        body *:not(html):not(style):not(br):not(tr):not(code) {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif,
            'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            position: relative;
        }

        body {
            -webkit-text-size-adjust: none;
            background-color: #ffffff;
            color: #718096;
            height: 100%;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        p,
        ul,
        ol,
        blockquote {
            line-height: 1.4;
            text-align: left;
        }

        a {
            color: #3869d4;
        }

        a img {
            border: none;
        }

        /* Typography */

        h1 {
            color: #3d4852;
            font-size: 18px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        h2 {
            font-size: 16px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        h3 {
            font-size: 14px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        p {
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
        }

        p.sub {
            font-size: 12px;
        }

        img {
            max-width: 100%;
        }

        /* Layout */

        .wrapper {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            background-color: #edf2f7;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .content {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        /* Header */

        .header {
            padding: 25px 0;
            text-align: center;
        }

        .header a {
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
        }

        /* Logo */

        .logo {
            height: 70px;
            width: 130px;
        }

        /* Body */

        .body {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            background-color: #edf2f7;
            border-bottom: 1px solid #edf2f7;
            border-top: 1px solid #edf2f7;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .inner-body {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
            background-color: #ffffff;
            border-color: #e8e5ef;
            border-radius: 2px;
            border-width: 1px;
            box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025), 2px 4px 0 rgba(0, 0, 150, 0.015);
            margin: 0 auto;
            padding: 0;
            width: 570px;
        }

        /* Subcopy */

        .subcopy {
            border-top: 1px solid #e8e5ef;
            margin-top: 25px;
            padding-top: 25px;
        }

        .subcopy p {
            font-size: 14px;
        }

        /* Footer */

        .footer {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
            margin: 0 auto;
            padding: 0;
            text-align: center;
            width: 570px;
        }

        .footer p {
            color: #b0adc5;
            font-size: 12px;
            text-align: center;
        }

        .footer a {
            color: #b0adc5;
            text-decoration: underline;
        }

        /* Tables */

        .table table {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            margin: 30px auto;
            width: 100%;
        }

        .table th {
            border-bottom: 1px solid #edeff2;
            margin: 0;
            padding-bottom: 8px;
        }

        .table td {
            color: #74787e;
            font-size: 15px;
            line-height: 18px;
            margin: 0;
            padding: 10px 0;
        }

        .content-cell {
            max-width: 100vw;
            padding: 32px;
        }

        /* Buttons */

        .action {
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
            margin: 30px auto;
            padding: 0;
            text-align: center;
            width: 100%;
        }

        .button {
            -webkit-text-size-adjust: none;
            border-radius: 4px;
            color: #fff;
            display: inline-block;
            overflow: hidden;
            text-decoration: none;
        }

        .button-blue,
        .button-primary {
            background-color: #2d3748;
            border-bottom: 8px solid #2d3748;
            border-left: 18px solid #2d3748;
            border-right: 18px solid #2d3748;
            border-top: 8px solid #2d3748;
        }

        .button-green,
        .button-success {
            background-color: #48bb78;
            border-bottom: 8px solid #48bb78;
            border-left: 18px solid #48bb78;
            border-right: 18px solid #48bb78;
            border-top: 8px solid #48bb78;
        }

        .button-red,
        .button-error {
            background-color: #e53e3e;
            border-bottom: 8px solid #e53e3e;
            border-left: 18px solid #e53e3e;
            border-right: 18px solid #e53e3e;
            border-top: 8px solid #e53e3e;
        }

        /* Panels */

        .panel {
            border-left: #2d3748 solid 4px;
            margin: 21px 0;
        }

        .panel-content {
            background-color: #edf2f7;
            color: #718096;
            padding: 16px;
        }

        .panel-content p {
            color: #718096;
        }

        .panel-item {
            padding: 0;
        }

        .panel-item p:last-of-type {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Utilities */

        .break-all {
            word-break: break-all;
        }

    </style>
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               role="presentation" style="margin-top: 50px !important;">
                            <!-- Body content -->
                            <tr>
                                <td class="header" style="padding-top: 2px!important; padding-bottom: 2px!important;">
                                    <?php

                                        $logo_image = asset('/') . "images/calltronix_logo.png";
                                    ?>
                                    <a href="" style="display: inline-block; text-align:center !important;">
                                        <img src="{{ $logo_image }}" class="logo" alt="Demo CRM Logo" width="100px">
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="header1">
                                    <a href="" style="display: inline-block; float:left !important;"></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="content-cell">
                                    <table class="small" align="center" width="570" cellpadding="0" cellspacing="0"
                                           role="presentation">
                                        <tr>
                                            <td class="title small">
                                                Dear
                                                <b>
                                                    {{ isset($data['sender_name']) && @$data['sender_name'] ? @$data['sender_name'] : @$ticket['assigned_to_name']  }}
                                                </b>
                                            </td>
                                        </tr>
                                        <br>
                                        <br>
                                        <tr>
                                            <td class="paragraph">
                                                {{@$data['message']}}
                                                <hr>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td height="10"></td>
                                        </tr>

                                        <tr>
                                            <td style="text-align: center;"><h4>Customer Information</h4></td>
                                        </tr>
                                        <tr>
                                            <td width="100%">

                                                <table
                                                    style="width:100%; border: 1px solid black;border-collapse: collapse;">
                                                    <tr>
                                                        <th style=" padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Customer:
                                                        </th>
                                                        <td style=" padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{@$ticket['customer']['name']}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style=" padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Customer Phone:
                                                        </th>
                                                        <td style=" padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ $ticket['phone'] }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="10"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;"><h4>Ticket Information</h4></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class="small"
                                                       style="width:100%; border: 1px solid black;border-collapse: collapse;">
                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Ticket Number:
                                                        </th>
                                                        <td style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            #TKT-{{ @$ticket['id']}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Issue Source:
                                                        </th>
                                                        <td style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ @$ticket['issue_source']}}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Issue Category:
                                                        </th>
                                                        <td style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ @$ticket['issue_category']}}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Ticket Status:
                                                        </th>
                                                        <td style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ @$ticket['ticket_status'] }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Disposition:
                                                        </th>
                                                        <td style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ @$ticket['disposition'] }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Assigned To
                                                        </th>
                                                        <td style="width:65% !important;   padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ @$ticket['assigned_to_name']}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Created By
                                                        </th>
                                                        <td style="width:65% !important;   padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{ @$ticket['created_by']}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                                            Created At
                                                        </th>
                                                        <td style="width:65% !important;   padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                                            {{\Illuminate\Support\Carbon::parse($ticket['created_at'])->toDateTimeString() }}

                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>

                                        </tr>
                                        <tr>
                                            <td style="border-left: 2px">

                                                <h4 class="secondary"><strong>Ticket Updates</strong></h4>
                                                <ul style="border-left: solid; border-left-color: #85bdad; ">
                                                    @foreach($ticket_updates as $key=> $ticket_update)
                                                        <li>
                                                            {!! $ticket_update->comment !!}
                                                            <br>
                                                            <hr>
                                                            Update by <b>{{@$ticket_update->user->name}}</b> at <b>{{\Illuminate\Support\Carbon::parse($ticket_update->created_at)->toDayDateTimeString()}}</b>
                                                            <br> <br>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class="action" align="center" width="100%" cellpadding="0"
                                                       cellspacing="0" role="presentation">
                                                    <tr>
                                                        <td align="center">
                                                            <table width="100%" border="0" cellpadding="0"
                                                                   cellspacing="0" role="presentation">
                                                                <tr>
                                                                    <td align="center">
                                                                        <table class="small" border="0" cellpadding="0"
                                                                               cellspacing="0" role="presentation">
                                                                            <tr>
                                                                                <td>
                                                                                    <a href="{{ $data['link'] }}"
                                                                                       class="button "
                                                                                       target="_blank" rel="noopener" style="color: #fff">View Ticket</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="10"></td>
                                        </tr>
                                        <tr>
                                            <td class="content-cell">
                                                <p class="paragraph">
                                                    {{env("APP_NAME")}}
                                                </p>
                                                <hr/>
                                                <p>Note: This is a system generated mail. Please <b>DO NOT</b> reply to it.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td class="content-cell" align="center">
                                    © {{ date('Y') }} {{env("APP_NAME")}}. All rights reserved.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>