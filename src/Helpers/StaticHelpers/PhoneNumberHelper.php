<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class PhoneNumberHelper
{

    public static function formatTelNumberWithoutZerosPlus($phone)
    {

        $phone = str_replace("+", "", $phone);
        $phone = str_replace(" ", "", $phone);



        $first_two  = substr($phone, 0, 2);

        if($first_two == '00')
        {
            $phone = substr($phone, 2, strlen($phone) - 2);
        }

        $first = substr($phone, 0, 1);

        if($first == "0")
        {
            $phone = substr($phone, 1, strlen($phone) - 1);

            $phone = "49" . $phone;
        }

        return $phone;
    }

    public static function getMobileExampleInput()
    {
        return '4917612345678';
    }

    public static function getTelephoneFaxExampleInput()
    {
        return '49 89 123456789';
    }
}
