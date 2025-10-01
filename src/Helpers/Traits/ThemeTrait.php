<?php

namespace RepeatToolkit\Helpers\Traits;

use Qirolab\Theme\Theme;

trait ThemeTrait
{

    public function getActiveTheme()
    {
       // $active_theme = Theme::active();
        $active_theme = 'metronic';


        return $active_theme;
    }

    public function getDefaultTheme()
    {
        return 'default';
    }

    public static function getActiveThemeStatic()
    {
        //$active_theme = Theme::active();
        $active_theme = 'metronic';


        return $active_theme;
    }


}
