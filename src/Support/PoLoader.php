<?php

namespace RepeatToolkit\Support;

use Gettext\Loader\PoLoader as GettextPoLoader;
use Gettext\Translations;

class PoLoader
{
    /** @var array<string, array<string,string>> */
    protected static array $cache = [];

    public static function translate(string $key, string $locale = 'sr_RS', string $domain = 'messages'): ?string
    {
        $cacheKey = "{$domain}|{$locale}";

        if (!array_key_exists($cacheKey, self::$cache)) {
            self::$cache[$cacheKey] = self::loadMap($locale, $domain);
        }

        return self::$cache[$key] ?? null;
    }

    protected static function loadMap(string $locale, string $domain): array
    {
        $poPath = resource_path("locale/{$locale}/LC_MESSAGES/{$domain}.po");
        if (!is_file($poPath)) {
            return [];
        }

        $loader = new GettextPoLoader();
        /** @var Translations $translations */
        $translations = $loader->loadFile($poPath);

        $map = [];
        foreach ($translations as $t) {
            $id = $t->getOriginal();
            if ($id !== null) {
                $map[$id] = $t->getTranslation() ?? '';
            }
        }

        return $map;
    }
}
