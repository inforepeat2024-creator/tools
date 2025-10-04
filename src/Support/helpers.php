<?php


use RepeatToolkit\Support\PoLoader;
use Illuminate\Support\Facades\Lang;

if (! function_exists('repeat_layout')) {
    function repeat_layout(): string
    {
        return config('repeat-toolkit.layout', 'layouts.app_layout');
    }
}

if (!function_exists('__i')) {
    function __i(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        // 1) Probaj direktno iz .po (bez gettext-a)
        if ($tr = PoLoader::translate($key, $locale, 'messages')) {
            foreach ($replace as $k => $v) {
                $tr = str_replace(':'.$k, $v, $tr);
            }
            return $tr;
        }

        // 2) Ako ima pravi gettext + .mo i domen, možeš i dalje da zadržiš:
        if (function_exists('gettext')) {
            $translated = gettext($key);
            if ($translated !== $key) {
                foreach ($replace as $k => $v) {
                    $translated = str_replace(':' . $k, $v, $translated);
                }
                return $translated;
            }
        }

        // 3) Fallback na Laravel Lang
        return Lang::get($key, $replace, $locale);
    }
}
