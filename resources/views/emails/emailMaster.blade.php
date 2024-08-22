<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Neon Edu</title>
</head>
<body style="margin:0;padding:0;width:100%;height:100%;background-color:#f1f5f9;">
    <div class="invoice-box"
        style="max-width:600px;margin:20px auto;padding: 30px;border:1px solid #eee;box-shadow: 0 0 10px rgba(0, 0, 0, .15);font-size: 16px; line-height: 24px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555;background-color: #ffffff;">
        <table cellpadding="0" cellspacing="0"
            style="width: 100%; line-height: inherit;text-align: left; vertical-align: top;">
            <tr class="top">
                <td colspan="2" style="background-color:#00315a;">
                    <table cellpadding="0" cellspacing="0"
                        style="width: 100%; line-height: inherit;text-align: left;border-bottom:1px solid #ccc;">
                        <tr>
                            <td class="title" style="text-align:left;padding:10px; vertical-align: baseline;">
                                @if(isset($tenant_info) && !empty($tenant_info) && $tenant_info['logo']!=null)
                                <img src="<?php echo config('app.base_url') . $tenant_info['logo']; ?>"
                                    width="15%" />
                                @else
                                <img src="https://neon-edu.com/wp-content/uploads/2021/09/Neon-Logo_272-x-160.svg"
                                    width="15%" />
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="information">
                    @yield('content')
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0"
                        style="width: 100%; line-height: inherit;text-align: left;padding-bottom:20px;">
                        <tr>
                            <td
                                style="padding:20px 0 0 0; vertical-align: top; text-align: center; border-top:1px solid #ccc;">
                                <p
                                    style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555;text-decoration: none;padding: 0;margin:15px 0 15px 0;font-size: 12px;line-height: 18px;">
                                    If you have any questions, contact us at<br />
                                    <a href="mailto:info@neon-edu.com<"
                                        style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555;text-decoration: none;">info@neon-edu.com</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
