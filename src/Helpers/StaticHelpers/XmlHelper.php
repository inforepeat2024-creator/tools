<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class XmlHelper
{

    public static function toArray($xml)
    {
        $xml = simplexml_load_string($xml);

        $json = json_encode($xml);

        $result_array = json_decode($json,TRUE);

        return $result_array;

    }

}
