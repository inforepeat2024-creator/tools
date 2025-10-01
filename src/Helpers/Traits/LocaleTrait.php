<?php

namespace RepeatToolkit\Helpers\Traits;


use Illuminate\Support\Facades\Session;
use Modules\Core\Utilities\Models\Standard\UserModelUtilities;

trait LocaleTrait
{
    public function getCurrentLanguageId()
    {
        return $this->getLocaleIdFromLongCode(\LaravelGettext::getLocale());
    }


    public function getLocaleIdFromLongCode($long_code)
    {
        return array_search($long_code, config('laravel-gettext')['supported-locales']) ?? $this->getDefaultLocaleId();

    }

    public function getLocaleLongCodeFromId($id)
    {
        return config('laravel-gettext')['supported-locales'][$id] ?? 'en_US';
    }



    public function getDefaultLocaleId()
    {
        return 1;
    }

    public function getUserLocaleIdByCountry($user_id)
    {
        $model_utils = new UserModelUtilities();

        $user = $model_utils->findById($user_id);

        if(!isset($user->general_address->core_country_id))
            return 2;

        if($user->general_address->core_country_id == 1)
            return 2;

        if($user->general_address->core_country_id == 15)
            return 6;

        return 2;
    }

    public function changeLocale($locale)
    {
        Session::put(config('laravel-gettext')['session-identifier'], $locale);
        Session::put('locale', strtok($locale, '_'));

        setlocale(LC_TIME, $locale);

        \LaravelGettext::setLocale('ru_RU');
        \LaravelGettext::setLocale($locale);

        //\LaravelGettext::setLocale(session(config('laravel-gettext')['session-identifier']) ?? config('laravel-gettext')['fallback-locale']);
    }
}
