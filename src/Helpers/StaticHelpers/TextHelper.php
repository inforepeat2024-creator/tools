<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Str;

class TextHelper
{

    public static function isHTML($string) {
        if ($string != strip_tags($string)) {
            // contains HTML
            return true;
        }

        return false;
    }

    public static function processHtml($html)
    {
      /*  $html = str_replace('<style type="text/css">', "<style>", $html);

        $html = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/', ' ', $html );

        return $html;*/

        $html = str_replace('<style>', "<!-- <style>", $html);
        $html = str_replace('<style type="text/css">', "<!-- <style>", $html);
        $html = str_replace('class="header"', "", $html);

        $html = str_replace('</style>', "</style> -->", $html);

        return $html;
    }

    public static function replaceBadBiromioUrl($text)
    {
        return str_replace('com/biromio.com/', 'com/', $text);
    }

    public static function limitString($string, $length = 30)
    {

        if (strlen($string) <= $length)
            return $string;

        $cut_string = substr($string, 0, $length);

        return $cut_string . "..";


    }

    public static function generateUUIDV4()
    {
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));

        return $uuid;
    }

    public static function makeTextBoldViber($text)
    {
        return "*" . $text . "*";

    }

    public static function removeSpecialCharachters($string)
    {
        $string = str_replace('-', " ", $string);

        return $string;
    }


    public static function generateRandomString($length = 16)
    {

        return Str::random($length);

    }

    public static function generateRandomNumber($length = 6)
    {

        return random_int(100000, 999999);

    }

    public static function isAFilter($key)
    {
        return Str::contains($key, ['filter__', 'custom_filter']);
    }

    public static function getFiltersFromArray($array)
    {
        $filters = [];

        foreach ($array as $key => $val) {
            if ($val == "")
                continue;

            if (self::isAFilter($key))
                $filters[$key] = $val;
        }

        return $filters;
    }

    public static function stringContains($string, array $keywords)
    {

        return Str::contains($string, $keywords);

    }

    public static function replaceSpacesWithHyphens($string)
    {
        return str_replace(' ', '-', $string);
    }
}
