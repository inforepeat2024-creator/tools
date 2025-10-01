<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class ExceptionsHelper
{

    public static function getFullMessage(\Exception $e)
    {
        return $e->getMessage() . " | " . $e->getFile() . " | " . $e->getLine();
    }

}
