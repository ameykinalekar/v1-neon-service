<?php
namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Str;

class CommonHelper
{
    public static function SayHello()
    {
        return "SayHello";
    }
    /*
     * Function Name :  excerpt
     * Purpose       :  This function return excerpt string from original provisioned string
     * Author        :  SM
     * Created Date  :
     * Input Params  :  string original,int limit ,string end
     * Return Value  :  string excerpt
     */
    public static function excerpt($original, $limit = 100, $end = '...')
    {
        return $excerpt = Str::limit($original, $limit, $end);
    }

    /*
     * Function Name :  getCancelbuttonUrl
     * Purpose       :  This function return the redirect url on clicking cancel button of add/edit page
     * Author        :  KB
     * Created Date  :
     * Input Params  :  string $routePrefix, string $fromPage, array $extraParams
     * Return Value  :  string $url
     */

    public static function getCancelbuttonUrl($routePrefix, $fromPage, $extraParams = array())
    {

        if (trim($routePrefix) != '' && trim($fromPage) != '') {
            $url = \Route($routePrefix . '.' . $fromPage);
        } elseif (trim($fromPage) != '') {
            $url = \Route($fromPage);
        } else {
            if (count($extraParams)) {
                $url = \Route($routePrefix . '.list', $extraParams);
            } else {
                $url = \Route($routePrefix . '.list');
            }

        }
        return $url;
    }

    /*
     * Function Name :  getSiteSettingsData
     * Purpose       :  This function returns the common site settings data
     * Author        :  KB
     * Created Date  :
     * Input Params  :  NA
     * Return Value  :  array

     */
    public static function getSiteSettingsData()
    {
        $siteSettingData = Setting::first();
        return $siteSettingData;
    }

    /*
     * Function Name :  encrypt
     * Purpose       :  This function is use for encrypt a string.
     * Author        :  KB
     * Created Date  :
     * Input Params  :  string $value
     * Return Value  :  string
     */

    public static function encrypt($value)
    {
        $cipher = 'AES-128-ECB';
        $key = \Config::get('app.key');
        return openssl_encrypt($value, $cipher, $key);
    }

    /*
     * Function Name :  decrypt
     * Purpose       :  This function is use for decrypt the encrypted string.
     * Author        :  KB
     * Created Date  :
     * Input Params  :  string $value
     * Return Value  :  string
     */

    public static function decrypt($value)
    {
        $cipher = 'AES-128-ECB';
        $key = \Config::get('app.key');
        return openssl_decrypt($value, $cipher, $key);
    }

    /*
     * Function Name :  partialEmailidDisplay
     * Purpose       :  This function is use for hiding some characters of en email id.
     * Author        :  KB
     * Created Date  :
     * Input Params  :  string $value
     * Return Value  :  string
     */

    public static function partialEmailidDisplay($email)
    {
        $rightPartPos = strpos($email, '@');
        $leftPart = substr($email, 0, $rightPartPos);
        $displayChars = (strlen($leftPart) / 2);
        if ($displayChars < 1) {
            $displayChars = 1;
        }
        return substr($leftPart, 0, $displayChars) . '*******' . substr($email, $rightPartPos);
    }

    public static function encryptId($value)
    {
        // $hashids = new Hashids(\Config::get('app.key'));
        // return $hashids->encode($value);
        $cipher = 'AES-128-ECB';
        $key = \Config::get('app.key');
        return base64_encode(openssl_encrypt($value, $cipher, $key));
    }

    public static function decryptId($value)
    {
        // $hashids = new Hashids(\Config::get('app.key'));
        // return (count($decptid = $hashids->decode($value))? $decptid[0]: '');
        $cipher = 'AES-128-ECB';
        $key = \Config::get('app.key');
        return openssl_decrypt(base64_decode($value), $cipher, $key);
    }

    public static function getCmsContentBySlug($dataArr, $slug)
    {
        $matchedItem = [];
        foreach ($dataArr as $key => $content) {
            if ($content['slug'] == $slug) {
                $matchedItem = $dataArr[$key];
                break;
            }
        }
        return $matchedItem;

    }

    function studentCode($length_of_string = 6)
    {
        // String of all numeric character
        $str_result = '0123456789';
        // Shufle the $str_result and returns substring of specified length
        $unique_id = substr(str_shuffle($str_result), 0, $length_of_string);
        $splited_unique_id = str_split($unique_id, 4);
        $running_year = date('Y');
        $student_code = $running_year . '-' . $splited_unique_id[0] . '-' . $splited_unique_id[1];
        return $student_code;
    }

    function parentCode($length_of_string = 6)
    {
        // String of all numeric character
        $str_result = '0123456789';
        // Shufle the $str_result and returns substring of specified length
        $unique_id = substr(str_shuffle($str_result), 0, $length_of_string);
        $splited_unique_id = str_split($unique_id, 4);
        $running_year = date('Y');
        $code = $running_year . $splited_unique_id[0] . $splited_unique_id[1];
        return $code;
    }

    public static function setMailConfig($usettings)
    {

        //Get the data from settings table
        // $user = auth('tenant')->setToken(request()->bearerToken())->user();
        // // print_r(empty($user));
        // if ($user == null) {
        //     $user = JWTAuth::parseToken()->authenticate();
        // }
        // $usettings = Setting::where('tenant_id', '=', $user->tenant_id)->pluck('mail_settings')->first();
        $usettings = CommonHelper::decryptId($usettings);
        $usettings = json_decode($usettings);
        // dd($usettings->smtp_host);
        $settings = [
            'smtp_host' => $usettings->smtp_host,
            'smtp_port' => $usettings->smtp_port,
            'smtp_security' => $usettings->smtp_security,
            'smtp_username' => $usettings->smtp_username,
            'smtp_password' => $usettings->smtp_password,
        ];

        // dd($settings);

        //Set the data in an array variable from settings table
        $mailConfig = [
            'transport' => 'smtp',
            'host' => $settings['smtp_host'],
            'port' => $settings['smtp_port'],
            'encryption' => $settings['smtp_security'],
            'username' => $settings['smtp_username'],
            'password' => $settings['smtp_password'],
            'timeout' => null,
        ];

        //To set configuration values at runtime, pass an array to the config helper
        config(['mail.mailers.smtp' => $mailConfig]);
    }
}
