<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class FiltersHelper
{

    public static function getFiltersFromInput($input)
    {
        $filters = [];

        foreach ($input as $key => $val)
        {
            if(TextHelper::stringContains($key, ['filter__']))
            {
                $filters[str_replace('-', '.', $key)] = $val;
            }
        }

        return $filters;
    }

}
