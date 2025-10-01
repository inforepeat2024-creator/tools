<?php

namespace RepeatToolkit\Helpers\Traits;

trait ModuleTrait
{

    public function getModuleName()
    {
        $class = get_called_class(); // or $class = static::class;
        $arr_class = explode("\\", $class);
        return strtolower($arr_class[1]);
    }


}
