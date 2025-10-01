<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class TestInputsHelper
{


    public static function getTestDticketMobileNumbers()
    {


        return ['491777079183', '491774576142', '491739140811', '491732059596', /*'4917649299770',*/ '497777'];
    }


    public static function isAtestDticketMobileNumber($mobile)
    {
        //return true;

        return in_array($mobile, self::getTestDticketMobileNumbers());
    }
}
