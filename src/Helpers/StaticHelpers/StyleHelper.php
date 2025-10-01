<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class StyleHelper
{

    public static function getRandomBackgroundClass()
    {
        $classes = [
            'bg-light',
            'bg-primary',
            'bg-secondary',
            'bg-success',
            'bg-info',
            'bg-warning',
            'bg-danger',
            'bg-dark',
        ];

        return $classes[array_rand($classes)];
    }

}
