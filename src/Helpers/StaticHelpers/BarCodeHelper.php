<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class BarCodeHelper
{


    public static function generate($code)
    {
        return 'https://barcode.tec-it.com/barcode.ashx?data=' . $code . '&code=EAN13&dpi=96&dataseparator=';

    }

    public static function generate128($code)
    {
        return 'https://barcode.tec-it.com/barcode.ashx?data=' . $code . '&code=Code128&dpi=300&dataseparator=';

    }



}
