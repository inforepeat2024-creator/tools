<?php
$componentsDir = __DIR__ . '/resources/js/components';
$outputFile = __DIR__ . '/.web-types.json';

function toKebabCase(string $str): string {
    // Ako ime fajla bude CamelCase, zadrži i ovu konverziju
    // (dodatno: zameni underscore/space u dash)
    $str = preg_replace('/(?<!^)[A-Z]/', '-$0', $str); // CamelCase -> Camel-Case
    $str = preg_replace('/[_\s]+/', '-', $str);
    return strtolower($str);
}

/**
 * Keš za već parsirane fajlove (da izbegnemo ponovna čitanja i cikluse)
 */
$__PARSE_CACHE = [];

/**
 * Izvlači sve atribute iz JS fajla:
 * - iz this.state = { ... }
 * - iz Object.assign(this.state, { ... })
 * - iz this.state.key = this.getAttribute('attr-name')
 * Takođe, ako klasa extends drugu klasu i import postoji sa putanjom,
 * rekurzivno merguje i atribute roditelja.
 *
 * @param string $filePath
 * @param int $depth
 * @return array ['description' => string, 'attributes' => array{name, description}]
 */
function parseComponentFile(string $filePath, int $depth = 0): array {
    global $__PARSE_CACHE;

    if (isset($__PARSE_CACHE[$filePath])) {
        return $__PARSE_CACHE[$filePath];
    }

    if (!is_file($filePath)) {
        return [
            'description' => 'Component auto-generated description',
            'attributes'  => [],
        ];
    }

    $content = file_get_contents($filePath);
    $description = 'Component auto-generated description';
    $attributes = [];

    // Helper: dodaj atribut bez duplikata po imenu
    $addAttr = function(string $name) use (&$attributes) {
        $name = trim($name);
        if ($name === '') return;
        foreach ($attributes as $a) {
            if ($a['name'] === $name) return;
        }
        $attributes[] = [
            'name' => $name,
            'description' => "State attribute '$name' (from this.getAttribute)"
        ];
    };

    // 1) this.state = { ... } blokovi
    if (preg_match_all('/this\.state\s*=\s*\{(.*?)\};/s', $content, $mStateAssign)) {
        foreach ($mStateAssign[1] as $stateBody) {
            // uhvati linije poput: key: this.getAttribute('attr') ?? ...
            if (preg_match_all('/[\'"]?([a-zA-Z0-9_\-]+)[\'"]?\s*:\s*this\.getAttribute\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $stateBody, $m, PREG_SET_ORDER)) {
                foreach ($m as $mm) {
                    $attrName = $mm[2]; // uzmi stvarni argument iz getAttribute(...)
                    $addAttr($attrName);
                }
            }
        }
    }

    // 2) Object.assign(this.state, { ... }) — može više puta
    if (preg_match_all('/Object\.assign\s*\(\s*this\.state\s*,\s*\{(.*?)\}\s*\)/s', $content, $mAssignBlocks)) {
        foreach ($mAssignBlocks[1] as $assignBody) {
            if (preg_match_all('/[\'"]?([a-zA-Z0-9_\-]+)[\'"]?\s*:\s*this\.getAttribute\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $assignBody, $m, PREG_SET_ORDER)) {
                foreach ($m as $mm) {
                    $attrName = $mm[2];
                    $addAttr($attrName);
                }
            }
        }
    }

    // 3) Direktne dodele: this.state.foo = this.getAttribute('bar')
    if (preg_match_all('/this\.state\.[a-zA-Z0-9_\$]+\s*=\s*this\.getAttribute\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $mDirect, PREG_SET_ORDER)) {
        foreach ($mDirect as $mm) {
            $attrName = $mm[1];
            $addAttr($attrName);
        }
    }

    // 4) Nasleđivanje: class X extends Y; import Y from './y.js'
    //    Pokušaj da pronađe "extends <Base>" i zatim odgovarajući import putanju
    if ($depth < 5) {
        if (preg_match('/class\s+[A-Za-z0-9_]+\s+extends\s+([A-Za-z0-9_]+)/', $content, $mExt)) {
            $baseClass = $mExt[1];

            // Nađi import liniju za baznu klasu
            // import Base from './relative/path.js';
            if (preg_match_all('/import\s+' . preg_quote($baseClass, '/') . '\s+from\s+[\'"]([^\'"]+)[\'"]\s*;/', $content, $mImports)) {
                $dir = dirname($filePath);
                foreach ($mImports[1] as $rel) {
                    // Reši relativnu putanju (.js je obavezan/ili doda)
                    $candidate = realpath($dir . DIRECTORY_SEPARATOR . $rel);
                    if ($candidate === false) {
                        // Dodaj .js ako nema
                        $candidate = realpath($dir . DIRECTORY_SEPARATOR . $rel . '.js');
                    }
                    if ($candidate && is_file($candidate)) {
                        $parentParsed = parseComponentFile($candidate, $depth + 1);
                        // spoji atribute iz roditelja
                        foreach ($parentParsed['attributes'] as $pa) {
                            $addAttr($pa['name']);
                        }
                        // uzmi najkompletniji description (ako želiš)
                        // $description = $parentParsed['description'] ?? $description;
                        break; // prvi uspeli import je dovoljan
                    }
                }
            }
        }
    }

    $result = [
        'description' => $description,
        'attributes'  => $attributes,
    ];
    $__PARSE_CACHE[$filePath] = $result;
    return $result;
}

if (!is_dir($componentsDir)) {
    echo "Components directory does not exist: $componentsDir\n";
    exit(1);
}

$elements = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($componentsDir, FilesystemIterator::SKIP_DOTS));

foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (strtolower($file->getExtension()) !== 'js') continue;

    $filePath = $file->getPathname();
    $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $filePath);
    $fileName = $file->getBasename('.js');

    // Pretpostavka: ime HTML elementa iz imena fajla
    $componentName = toKebabCase($fileName);

    $parsed = parseComponentFile($filePath);

    $elements[] = [
        'name' => $componentName,
        'description' => $parsed['description'],
        'source' => [
            'file' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath)
        ],
        'attributes' => array_values($parsed['attributes']),
    ];
}

$output = [
    '$schema' => 'https://raw.githubusercontent.com/JetBrains/web-types/master/schema/web-types.schema.json',
    'name' => 'my-custom-components',
    'version' => '0.0.2',
    'description-markup' => 'markdown',
    'contributions' => [
        'html' => [
            'elements' => $elements
        ]
    ]
];

file_put_contents($outputFile, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Generated $outputFile with " . count($elements) . " components\n";
