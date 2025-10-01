<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Core\Utilities\Models\Standard\UserModelUtilities;

class UrlHelper
{

    public static function isLocalhost()
    {
        //return false;
        Log::info(url()->current());

        return Str::contains(url()->current(), ['localhost', '127.0.0.1']);

    }

    public static function isAFilter($key)
    {
        return Str::contains($key, ['filter__', 'custom_filter']);
    }

    public static function getFiltersFromRequest()
    {
        return TextHelper::getFiltersFromArray($_REQUEST);
    }

    public static function currentUrlContains($string)
    {

        return Str::contains(url()->full(), [$string]);

    }

    public static function currentUrlContainsWords(array $strings)
    {

        return Str::contains(url()->full(), $strings);

    }

    public static function previousUrlContainsWords(array $strings)
    {

        return Str::contains(url()->previous(), $strings);

    }

    public static function getModuleNameFromUrl($url = null)
    {
        if($url == null)
            $url = url()->current();

        if(Str::contains($url, ['sync/']))
            return 'core';

        if(self::isLocalhost())
        {
            $url_split = explode( '/', url()->current());

         //   dd($url_split);

            $module = $url_split[5] ?? "core";
        }
        else
        {
            $url_split = explode( '/', url()->current());

            $module = $url_split[3] ?? "core";
        }


        if($module == 'logout')
            return 'core';

        return $module;


    }

    public static function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return the server IP if the client IP is not found using this method.
    }


    public static function getCurrentRouteName()
    {



    }

    public static function getAppTitleBasedOnUrl()
    {
        if(self::currentUrlContains('ubb-eticket'))
            return "UBB-ETICKET";

        if(self::currentUrlContains('sng-eticket'))
            return "SNG-ETICKET";


        if(self::currentUrlContains('handyticket'))
            return "Handy-TICKeT";

        return "TICKET-ABO";
    }

    public static function getCurrentRouteNameFormatted()
    {
        return str_replace('.', '--', Route::currentRouteName());
    }

    public static function getCurrentUrl()
    {
        if(UrlHelper::isLocalhost())
        {
            return  "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }

        return  "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    }

    public static function refererUrlContains(array $terms)
    {

        $referer = $_REQUEST['HTTP_REFERER'] ?? "";

        return Str::contains(request()->headers->get('referer'), $terms);
    }




}
