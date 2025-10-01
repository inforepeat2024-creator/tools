<?php

namespace RepeatToolkit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class MakePoFromI extends Command
{
    protected $signature = 'i18n:make 
        {--locales= : CSV locales, npr: sr,en} 
        {--paths= : CSV putanje za skeniranje} 
        {--ext= : CSV ekstenzije, npr: php,blade.php,js} 
        {--po-out= : Folder za .po fajlove} 
        {--json-out= : Folder za JSON fajlove (default: public/i18n)} 
        {--format=both : po|json|both} 
        {--prune : U JSON ukloni ključeve kojih više nema u kodu}';

    protected $description = 'Skenira __i/_i ključeve i generiše .po i/ili JSON prevode (za frontend).';

    public function handle(): int
    {
        $fs = new Filesystem();

        // 1) Konfig (možeš u config/i18n.php i povući odatle; ovde radi self-contained)
        $defaultLocales = ['sr','en'];
        $defaultPaths   = [base_path('app'), resource_path('views'), resource_path('js')];
        $defaultExt     = ['php','blade.php','js'];
        $defaultJsonOut = public_path('i18n');

        $locales = $this->optCsv('locales', $defaultLocales);
        $paths   = $this->optCsv('paths', $defaultPaths);
        $exts    = $this->optCsv('ext', $defaultExt);
        $poOut   = $this->option('po-out') ?: base_path('lang-po');     // prilagodi po želji
        $jsonOut = $this->option('json-out') ?: $defaultJsonOut;
        $format  = strtolower((string)$this->option('format') ?: 'both');
        $prune   = (bool) $this->option('prune');

        // 2) Skeniraj ključeve
        $this->info('Skeniram fajlove za i18n ključeve…');
        $keys = $this->collectKeys($paths, $exts);
        $this->info('Nađeno ključeva: '.count($keys));

        if (empty($keys)) {
            $this->warn('Nema ključeva — nema šta da generišem.');
            return self::SUCCESS;
        }

        // 3) Generiši PO (ako treba)
        if ($format === 'po' || $format === 'both') {
            $this->generatePo($fs, $locales, $poOut, $keys);
        }

        // 4) Generiši JSON (ako treba)
        if ($format === 'json' || $format === 'both') {
            $this->generateJson($fs, $locales, $jsonOut, $keys, $prune);
        }

        $this->info('Gotovo.');
        return self::SUCCESS;
    }

    /** --- helpers --- */

    protected function optCsv(string $name, array $fallback): array
    {
        $opt = $this->option($name);
        if (!$opt) return $fallback;
        return collect(explode(',', $opt))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();
    }

    protected function collectKeys(array $paths, array $extensions): array
    {
        $finder = new Finder();
        $finder->files()
            ->in($paths)
            ->ignoreVCS(true)
            ->exclude(['vendor','node_modules','storage','bootstrap/cache'])
            ->filter(function (\SplFileInfo $file) use ($extensions) {
                $name = $file->getFilename();
                foreach ($extensions as $ext) {
                    if (str_ends_with($name, $ext)) return true;
                }
                return false;
            });

        $patterns = [
            '/__i\s*\(\s*(["\'])(.*?)\1/s',
            '/_i\s*\(\s*(["\'])(.*?)\1/s',
        ];

        $keys = [];
        foreach ($finder as $file) {
            $content = @file_get_contents($file->getPathname());
            if ($content === false) continue;

            foreach ($patterns as $re) {
                if (preg_match_all($re, $content, $m, PREG_SET_ORDER)) {
                    foreach ($m as $match) {
                        $key = $match[2];
                        if ($key !== '') $keys[] = $key;
                    }
                }
            }
        }

        $keys = array_values(array_unique($keys));
        sort($keys, SORT_NATURAL | SORT_FLAG_CASE);
        return $keys;
    }

    protected function generatePo(Filesystem $fs, array $locales, string $poOut, array $keys): void
    {
        if (!$fs->exists($poOut)) {
            $fs->makeDirectory($poOut, 0775, true);
        }

        foreach ($locales as $locale) {
            // npr: lang-po/sr/LC_MESSAGES/messages.po
            $dir  = rtrim($poOut, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.'LC_MESSAGES';
            if (!$fs->exists($dir)) $fs->makeDirectory($dir, 0775, true);

            $poFile = $dir.DIRECTORY_SEPARATOR.'messages.po';

            // Ako već postoji, očuvaj postojeće prevode (basic merge):
            $existing = [];
            if ($fs->exists($poFile)) {
                $existing = $this->parsePoToArray($fs->get($poFile)); // minimalistički parser dole
            }

            // Merge: postojeći prevod zadrži, za nov ključ ubaci prazan
            $merged = $existing;
            foreach ($keys as $k) {
                if (!array_key_exists($k, $merged)) {
                    $merged[$k] = '';
                }
            }
            // (Ne radimo prune za .po; možeš dodati opciju po želji)

            // Upis nazad u .po
            $fs->put($poFile, $this->arrayToPo($merged));
            $this->line("PO ➤ {$poFile} (".count($merged)." msgid)");
        }
    }

    protected function generateJson(Filesystem $fs, array $locales, string $jsonOut, array $keys, bool $prune): void
    {
        // napravi folder ako ne postoji
        if (!$fs->exists($jsonOut)) {
            $fs->makeDirectory($jsonOut, 0775, true, true);
        }

        foreach ($locales as $locale) {
            $jsonFile = rtrim($jsonOut, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR."{$locale}.json";
            $existing = [];

            if ($fs->exists($jsonFile)) {
                $json = json_decode($fs->get($jsonFile), true);
                if (is_array($json)) $existing = $json;
            }

            // Dodaj nove ključeve
            foreach ($keys as $k) {
                if (!array_key_exists($k, $existing)) {
                    $existing[$k] = $k; // ili "" ako želiš prazno
                }
            }

            // Prune (ako treba)
            if ($prune) {
                $existing = array_intersect_key($existing, array_flip($keys));
            }

            ksort($existing, SORT_NATURAL | SORT_FLAG_CASE);

            $fs->put(
                $jsonFile,
                json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL
            );

            $this->line("JSON ➤ {$jsonFile} (".count($existing)." keys)");
        }
    }


    /** Minimalistički PO parser (msgid/msgstr samo, bez plural/ctx) */
    protected function parsePoToArray(string $content): array
    {
        $lines = preg_split("/\R/", $content);
        $map = [];
        $currentId = null;
        $currentStr = null;
        $state = null; // 'id' or 'str'

        $unquote = fn($s) => stripcslashes(trim($s, '"'));

        foreach ($lines as $line) {
            $t = trim($line);

            if (str_starts_with($t, 'msgid ')) {
                $currentId = $unquote(substr($t, 6));
                $currentStr = '';
                $state = 'id';
            } elseif ($state === 'id' && isset($t[0]) && $t[0] === '"') {
                $currentId .= $unquote($t);
            } elseif (str_starts_with($t, 'msgstr ')) {
                $currentStr = $unquote(substr($t, 7));
                $state = 'str';
            } elseif ($state === 'str' && isset($t[0]) && $t[0] === '"') {
                $currentStr .= $unquote($t);
            } elseif ($t === '' && $currentId !== null) {
                $map[$currentId] = $currentStr;
                $currentId = null;
                $currentStr = null;
                $state = null;
            }
        }

        if ($currentId !== null) {
            $map[$currentId] = $currentStr ?? '';
        }

        return $map;
    }

    protected function arrayToPo(array $map): string
    {
        $escape = fn($s) => addcslashes($s, "\0..\37\"\\");
        $out = [];
        // header (minimal)
        $out[] = 'msgid ""';
        $out[] = 'msgstr ""';
        $out[] = '"Content-Type: text/plain; charset=UTF-8\n"';
        $out[] = '"Content-Transfer-Encoding: 8bit\n"';
        $out[] = '';

        foreach ($map as $id => $str) {
            $out[] = 'msgid "'.$escape($id).'"';
            $out[] = 'msgstr "'.$escape($str).'"';
            $out[] = '';
        }
        return implode(PHP_EOL, $out);
    }
}
