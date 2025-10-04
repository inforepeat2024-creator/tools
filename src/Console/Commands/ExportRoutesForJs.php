<?php

namespace RepeatToolkit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class ExportRoutesForJs extends Command
{
    protected $signature = 'repeat:routes:export
                            {path=resources/js/routes.gen.json : Output fajl (json ili js)}
                            {--pretty : Lepši JSON}
                            {--include-non-named : Uključi i neimenovane rute (bez imena)}
                            {--absolute : Snimi i absolutni baseUrl}
                            {--url= : Ručno prosleđen baseUrl (ako izostavljeno, koristi app.url)}';

    protected $description = 'Export svih (imenovanih) Laravel ruta u JSON/JS za korišćenje iz JavaScript-a.';

    public function handle(): int
    {
        $routes = [];
        foreach (Route::getRoutes() as $r) {
            $name = $r->getName();

            if (!$this->option('include-non-named') && !$name) {
                continue;
            }

            $uri     = '/'.ltrim($r->uri(), '/');        // npr. /users/{user}/posts/{post?}
            $domain  = method_exists($r, 'domain') ? $r->domain() : null; // npr. {tenant}.example.com
            $methods = array_values(array_diff($r->methods(), ['HEAD']));

            $wheres  = method_exists($r, 'wheres') ? $r->wheres : ($r->getAction()['wheres'] ?? []);
            $defaults= $r->defaults ?? [];

            $routes[$name ?: $uri] = [
                'name'    => $name,
                'uri'     => $uri,
                'domain'  => $domain,     // može biti null ili pattern sa {param}
                'methods' => $methods,
                'wheres'  => $wheres,     // regex ograničenja
                'defaults'=> $defaults,   // default parametri
            ];
        }

        $payload = [
            'baseUrl' => null,
            'routes'  => $routes,
        ];

        if ($this->option('absolute')) {
            $payload['baseUrl'] = rtrim($this->option('url') ?: config('app.url'), '/');
        }

        $path = base_path($this->argument('path'));
        @mkdir(dirname($path), 0777, true);

        $json = json_encode($payload, $this->option('pretty')
            ? JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
            : JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
        );

        if (str_ends_with($path, '.js')) {
            file_put_contents($path, "export default ".$json.";\n");
        } else {
            file_put_contents($path, $json);
        }

        $this->info("Exported ".count($routes)." routes to {$path}");
        return self::SUCCESS;
    }
}
