<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>

    <div>


        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation"
            style="margin-top: 50px !important;">
            <!-- Body content -->
            <tr>
                <td class="header" style="padding-top: 2px!important; padding-bottom: 2px!important;">
                    <?php
                    
                    $logo_image = asset('/') . '/images/calltronix_logo.png';
                    ?>
                    <a href="" style="display: inline-block; text-align:center !important;">
                        <img src="{{ $logo_image }}" class="logo" alt="Calltronix Logo" width="100px">
                    </a>
                </td>
            </tr>
            <tr>
                <td class="header1">
                    <a href="" style="display: inline-block; float:left !important;"></a>
                </td>
            </tr>
            <tr>
                <div>


                    <tr>
                        <td class="title small">
                            Dear
                            <b>
                                {{ @$lead->assignedTo->name }}
                            </b>


                        </td>
                    </tr>
                </div>
            </tr>
            <tr>
                <td class="content-cell">
                    <table class="small" align="center" width="570" cellpadding="0" cellspacing="0"
                        role="presentation">
                      
                        
                        <br>
                        <tr>
                            <td class="paragraph">
                                {{ @$info }}
                                <hr>
                            </td>
                        </tr>

                
                            <td height="10"></td>
                        </tr>

                        <tr>
                            <td style="text-align: center;">
                                <h4>Customer Information</h4>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%">

                                <table style="width:100%; border: 1px solid black;border-collapse: collapse;">
                                    <tr>
                                        <th
                                            style=" padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Customer:
                                        </th>
                                        <td
                                            style=" padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->customer->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th
                                            style=" padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Customer Phone:
                                        </th>
                                        <td
                                            style=" padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->customer->phone }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th
                                            style=" padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Customer Email:
                                        </th>
                                        <td
                                            style=" padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->customer->email }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="10"></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <h4>Lead Information</h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="small"
                                    style="width:100%; border: 1px solid black;border-collapse: collapse;">
                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Lead Number:
                                        </th>
                                        <td
                                            style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            #Demo-CRM Lead {{ @$lead['id'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Lead Source:
                                        </th>
                                        <td
                                            style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->lead_source->name }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Interest:
                                        </th>
                                        <td
                                            style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->lead_interest->name }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Lead Stage:
                                        </th>
                                        <td
                                            style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->lead_stage->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Products:
                                        </th>
                                        <td
                                            style="width:65% !important;  padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">

                                            @foreach ($lead->products as $product)
                                                <span>{{$product->name}}</span>
                                            @endforeach
                                        </td>
                                    </tr>

                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Assigned To
                                        </th>
                                        <td
                                            style="width:65% !important;   padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->assignedTo->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Created By
                                        </th>
                                        <td
                                            style="width:65% !important;   padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ @$lead->createdBy->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th
                                            style="width:35% !important;   padding: 5px;text-align: right;border: 1px solid black;border-collapse: collapse;">
                                            Created At
                                        </th>
                                        <td
                                            style="width:65% !important;   padding: 5px;text-align: left;border: 1px solid black;border-collapse: collapse;">
                                            {{ \Illuminate\Support\Carbon::parse($lead['created_at'])->toDateTimeString() }}

                                        </td>
                                    </tr>
                                </table>
                            </td>

                        </tr>
                        @if (isset($lead_updates))
                            
                            <tr>
                                <td style="border-left: 2px">

                                    <h4 class="secondary"><strong>Lead Updates</strong></h4>
                                    <ul style="border-left: solid; border-left-color: #85bdad; ">
                                        @foreach ($lead_updates as $key => $lead_update)
                                            <li>
                                                {!! $lead_update->comment !!}
                                                <br>
                                                <hr>
                                                Update by <b>{{ @$lead_update->user->name }}</b> at
                                                <b>{{ \Illuminate\Support\Carbon::parse($lead_update->created_at)->toDayDateTimeString() }}</b>
                                                <br> <br>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                <p>
                                Please follow the link below to view the leads and
                                progress it.</p>
                                <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0"
                                    role="presentation">
                                    <tr>
                                        <td align="center">
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                                role="presentation">
                                                <tr>
                                                    <td align="center">
                                                        <table class="small" border="0" cellpadding="0"
                                                            cellspacing="0" role="presentation">
                                                            <tr>
                                                                <td> 

                                                                                                                             


                                                                    <a href="{{ url('admin/leads?lead_id=' . @$lead->lead_id) }}"
                                                                        style="color: white; background: #1d1d76; border-radius: 5px; padding: 10px; text-decoration: none;">View
                                                                        Lead</a>


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
                                <br><br>

                                Regards,<br>
                                <p class="paragraph">
                                    {{ config('app.name') }}
                                </p>
                                <hr />
                                <p>Note: This is a system generated mail. Please <b>DO NOT</b> reply to it.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
