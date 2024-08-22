@extends('emails.emailMaster')
@section('content')
<table width="560" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="left" valign="top"
            style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size:15px;  color:#000; padding-top:10px;">
            Hello,</td>
    </tr>
    <tr>
        <td align="left" valign="top"
            style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size:15px; line-height:24px;  color:#000;">
            <p>You have requested to reset your login password. Please click on the button below to reset it. </p>

            <p>Email : {{$email}}</p>
            <p><a style="text-decoration:none;font-family:Helvetica,Arial,sans-serif;font-size:20px;line-height:135%;"
                    href="{!!$url!!}" target="_blank">Reset Now</a></p>

        </td>
    </tr>
</table>
@stop