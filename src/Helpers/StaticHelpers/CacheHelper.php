<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{


    public static function getUserDticketPayCodeKey($user_id)
    {
        return 'user_' . $user_id . '_dticket_pay_code';


    }

    public static function getUserDticketPayCode($user_id)
    {

       // Cache::put(self::getUserDticketPayCodeKey($user_id), null);

        return Cache::get(self::getUserDticketPayCodeKey($user_id));


    }

}
