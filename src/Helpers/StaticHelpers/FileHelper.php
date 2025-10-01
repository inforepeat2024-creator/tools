<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Str;

class FileHelper
{

    public static function isLocalhost()
    {
        return Str::contains(url()->current(), ['localhost']);

    }


    public static function createFolderIfNotExist($path)
    {
        if(self::isLocalhost())
            $path = utf8_decode($path);
        if (!file_exists(($path))) {
            mkdir(($path), 0777, true);
        }
    }
}
