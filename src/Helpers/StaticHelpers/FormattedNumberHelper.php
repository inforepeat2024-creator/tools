<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class FormattedNumberHelper
{

    public static function unmaskNumber($number)
    {

        if(!TextHelper::stringContains($number, [',']))
            return $number;
        $removed_dots = str_replace('.', "", $number);
        $replaced_comma = str_replace(',', '.', $removed_dots);

        return round(doubleval($replaced_comma), 2);
    }

    public static function unmaskNumberFullDecimals($number)
    {

        if(!TextHelper::stringContains($number, [',']))
            return $number;
        $removed_dots = str_replace('.', "", $number);
        $replaced_comma = str_replace(',', '.', $removed_dots);

        return (doubleval($replaced_comma));
    }

    public static function maskNumber($number)
    {
        if(is_null($number))
            $number = 0;

        if(!is_numeric($number))
            $number = 0 ;

        return number_format($number, 2, ',', '.');
    }

    public static function maskNumberFullDecimals($number)
    {
        if(is_null($number))
            $number = 0;

        if(!is_numeric($number))
            $number = 0 ;

        return number_format($number, 4, ',', '.');
    }

    public static function maskOnlyDecimals($number)
    {
        return number_format($number, 2, ',', '');
    }


    public static function divideNumberInGivenUnits($number, $unit_size) : array
    {

        $transaction_amounts = [];

        $full_amounts_count = floor($number / $unit_size);

        for($i = 1; $i <= $full_amounts_count; $i++)
        {
            $transaction_amounts [] = $unit_size;
        }

        $reminder = $number - ($full_amounts_count * $unit_size);

        if($reminder > 0)
        {
            $transaction_amounts [] = $reminder;
        }

        return $transaction_amounts;

    }
}
