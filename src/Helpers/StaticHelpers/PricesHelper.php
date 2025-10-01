<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class PricesHelper
{


    public static function getDefaultDticketsSmsPrice()
    {
        return config('default_prices')['dticket_sms'] ?? 0;
    }

}
