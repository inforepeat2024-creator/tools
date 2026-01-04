<?php

namespace RepeatToolkit\Support;

use Gettext\Loader\PoLoader as GettextPoLoader;
use Gettext\Translations;

class PoLoader
{
    /** @var array<string, array<string,string>> cacheKey => [id => translation] */
    protected static array $cache = [];

    /**
     * Vraća prevod za dati ključ.
     * - Kešira mapu po (domain|locale)
     * - Ako je prevod prazan ili ne postoji -> vraća ključ
     */
    public static function translate(string $key, string $locale = 'sr', string $domain = 'messages'): string
    {
        $cacheKey = "{$domain}|{$locale}";

        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = self::loadMap($locale, $domain);
        }

        // Ako prevod ne postoji, vrati ključ (fallback)
        return self::$cache[$cacheKey][$key] ?? $key;
    }

    /**
     * Vrati celu mapu prevoda za dati (locale, domain).
     * @return array<string,string>
     */
    public static function all(string $locale = 'sr', string $domain = 'messages'): array
    {
        $cacheKey = "{$domain}|{$locale}";
        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = self::loadMap($locale, $domain);
        }
        return self::$cache[$cacheKey];
    }

    /**
     * JSON za window.translations (bez duplog JSON-ovanja u Blade-u).
     */
    public static function toJson(string $locale = 'sr', string $domain = 'messages'): string
    {
        return json_encode(self::all($locale, $domain), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * Inline <script> koji popunjava window.translations.
     */
    public static function inlineScript(string $locale = 'sr', string $domain = 'messages'): string
    {
        $json = self::toJson($locale, $domain);
        return "<script>window.translations = {$json};</script>";
    }

    /**
     * Učita .po i napravi [id => translation] mapu sa fallback-om:
     * - Ako je msgstr null ili "" -> koristi originalni ključ (id)
     */
    protected static function loadMap(string $locale, string $domain): array
    {
        $poPath = base_path("lang-po/{$locale}/LC_MESSAGES/{$domain}.po");
        if (!is_file($poPath)) {
            // Keširaj prazan niz da izbegneš ponovne disk hitove
            return [];
        }

        $loader = new GettextPoLoader();
        /** @var Translations $translations */
        $translations = $loader->loadFile($poPath);

        $map = [];
        foreach ($translations as $t) {
            $id = $t->getOriginal();
            if ($id === null) {
                continue;
            }

            // Uzmemo prevod; ako je prazan ili null -> fallback na ključ
            $translated = $t->getTranslation();
            if ($translated === null || $translated === '') {
                // Opcioni pokušaj plural[0] kao fallback:
                $plural0 = $t->getPluralTranslations()[0] ?? null;
                $translated = ($plural0 !== null && $plural0 !== '') ? $plural0 : $id;
            }

            $map[$id] = $translated;
        }

        return $map;
    }
}
