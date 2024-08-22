@extends('front.emails.emailMaster')
@section('content')
<table cellpadding="0" cellspacing="0" style="width: 100%; line-height: inherit;text-align: left; vertical-align: top;">
    <tr class="top">
        <td colspan="2">
            <table cellpadding="0" cellspacing="0"
                style="width: 100%; line-height: inherit;text-align: left;border-bottom:1px solid #ccc;">
                <tr>
                    <td class="title" style="text-align:left;padding:0 0 20px 0; vertical-align: top;"><img
                            src="https://workpermitcloud.co.uk/frontend/img/logo.png" width="30%" /> </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="information">
            <table width="560" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="left" valign="top"
                        style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size:15px;  color:#000; padding-top:10px;">
                        Hello,</td>
                </tr>
                <tr>
                    <td align="left" valign="top"
                        style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size:15px; line-height:24px;  color:#000;">
                        <p>New Contact form submission given below:</p>
                        <p>Name : {{$name}}</p>
                        <p>Email : {{$email}}</p>
                        <p>Phone : {{$phone}}</p>
                        <p>Inquery Type : {{$inquery_type}}</p>
                        <p>Message : {{$description}}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr class="information">
        <td colspan="2">
            <table cellpadding="0" cellspacing="0"
                style="width: 100%; line-height: inherit;text-align: left;padding-bottom:20px;">
                <tr>
                    <td style="padding:20px 0 0 0; vertical-align: top; text-align: center; border-top:1px solid #ccc;">
                        <p
                            style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555;text-decoration: none;padding: 0;margin:15px 0 15px 0;font-size: 12px;line-height: 18px;">
                            If you have any questions, contact us at<br />
                            <a href="mailto:contactus@wiseminesoftware.com"
                                style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555;text-decoration: none;">info@workpermitcloud.co.uk</a>
                            or call at : +44 020 8087 2343
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@stop