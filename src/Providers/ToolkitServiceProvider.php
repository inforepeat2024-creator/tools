<?php

namespace RepeatToolkit\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use RepeatToolkit\Console\Commands\MakePoFromI;

class ToolkitServiceProvider extends ServiceProvider
{
    /** Quick helper za path */
    protected function pkgPath(string $rel): string
    {
        return \dirname(__DIR__, 2) . '/' . ltrim($rel, '/');
    }

    public function register(): void
    {
        // Glavni config paketa
        $this->mergeConfigFrom(
            $this->pkgPath('config/repeat-toolkit.php'),
            'repeat-toolkit'
        );

        // (opciono) i18n config — merge samo ako postoji
        $i18nPath = $this->pkgPath('config/i18n.php');
        if (\file_exists($i18nPath)) {
            $this->mergeConfigFrom($i18nPath, 'i18n');
        }
    }

    public function boot(Router $router): void
    {
        // Artisan komande
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePoFromI::class,
                \RepeatToolkit\Console\Commands\ExportRoutesForJs::class,
            ]);
        }

        $packageBase = \dirname(__DIR__, 2);

        // -----------------------------
        // PUBLISH MAPE (grupisano i čisto)
        // -----------------------------
        $publishAll = [];

        // Config (glavni)
        $publishAll[$this->pkgPath('config/repeat-toolkit.php')] = config_path('repeat-toolkit.php');

        // (opciono) i18n config
        $i18nConfigPath = $this->pkgPath('config/i18n.php');
        if (\file_exists($i18nConfigPath)) {
            $publishAll[$i18nConfigPath] = config_path('i18n.php');
        }

        // Views
        $views = [
            'src/resources/views/crud/view.blade.php'                  => resource_path('views/crud/view.blade.php'),
            'src/resources/views/crud/create_partial.blade.php'        => resource_path('views/crud/create_partial.blade.php'),
            'src/resources/views/layouts/app_layout.blade.php'         => resource_path('views/layouts/app_layout.blade.php'),
            'src/resources/views/email_sender/layouts/base_layout.blade.php' => resource_path('views/email_sender/layouts/base_layout.blade.php'),
        ];
        foreach ($views as $src => $dst) {
            $full = $this->pkgPath($src);
            if (\file_exists($full)) {
                $publishAll[$full] = $dst;
            }
        }

        // JS vendors/helpers/components
        $jsMap = [
            'resources/js/i18n.js'       => resource_path('js/vendor/repeat-toolkit/i18n.js'),
            'resources/js/route-lite.js' => resource_path('js/vendor/repeat-toolkit/route-lite.js'),
            'resources/js/components'    => resource_path('js/components'),
            'resources/js/helpers'       => resource_path('js/helpers'),
        ];
        foreach ($jsMap as $src => $dst) {
            $full = $this->pkgPath($src);
            if (\file_exists($full)) {
                $publishAll[$full] = $dst;
            }
        }

        // generate-web-types.php (root → root)
        $genWebTypes = $this->pkgPath('generate-web-types.php');
        if (\file_exists($genWebTypes)) {
            $publishAll[$genWebTypes] = base_path('generate-web-types.php');
        }

        // Vite plugin stub
        $viteStub = $this->pkgPath('resources/stubs/vite/repeat-vite-plugin.js');
        if (\file_exists($viteStub)) {
            $publishAll[$viteStub] = resource_path('js/vendor/repeat-toolkit/vite-plugin.js');
        }

        // Catch-all publish (omogućava publish bez taga)
        if (!empty($publishAll)) {
            $this->publishes($publishAll);
        }

        // Tagovi (selektivni publish)
        $this->publishes([
            $this->pkgPath('config/repeat-toolkit.php') => config_path('repeat-toolkit.php'),
        ], 'repeat-toolkit-config');

        if (\file_exists($i18nConfigPath)) {
            $this->publishes([
                $i18nConfigPath => config_path('i18n.php'),
            ], 'repeat-i18n-config');
        }

        if (\file_exists($this->pkgPath('resources/js/i18n.js'))) {
            $this->publishes([
                $this->pkgPath('resources/js/i18n.js') => resource_path('js/vendor/repeat-toolkit/i18n.js'),
            ], 'repeat-i18n-js');
        }

        if (\file_exists($this->pkgPath('resources/js/route-lite.js'))) {
            $this->publishes([
                $this->pkgPath('resources/js/route-lite.js') => resource_path('js/vendor/repeat-toolkit/route-lite.js'),
            ], 'repeat-js');
        }

        if (\file_exists($viteStub)) {
            $this->publishes([
                $viteStub => resource_path('js/vendor/repeat-toolkit/vite-plugin.js'),
            ], 'repeat-vite-merge');
        }

        $viewsToPublish = Arr::only($publishAll, array_keys($views));
        if (!empty($viewsToPublish)) {
            $this->publishes($viewsToPublish, 'repeat-views');
        }

        if (\file_exists($this->pkgPath('resources/js/components'))) {
            $this->publishes([
                $this->pkgPath('resources/js/components') => resource_path('js/components'),
            ], 'repeat-components');
        }

        if (\file_exists($this->pkgPath('resources/js/helpers'))) {
            $this->publishes([
                $this->pkgPath('resources/js/helpers') => resource_path('js/helpers'),
            ], 'repeat-helpers');
        }

        if (\file_exists($genWebTypes)) {
            $this->publishes([
                $genWebTypes => base_path('generate-web-types.php'),
            ], 'web-types');
        }

        // -----------------------------
        // RUTE + MIDDLEWARE
        // -----------------------------

        // Učitaj rute iz paketa (bez obzira na route:cache — Laravel će ih uključiti u cache)
        $routesFile = $this->pkgPath('routes/web.php');
        if (\file_exists($routesFile)) {
            $this->loadRoutesFrom($routesFile);
        }

        // Auto-inject SetLocale u web grupu (može da se isključi u configu)
        $autoInject = (bool) config('repeat-toolkit.auto_inject_locale_middleware', true);
        if ($autoInject && class_exists(\RepeatToolkit\Http\Middleware\SetLocale::class)) {
            $router->pushMiddlewareToGroup('web', \RepeatToolkit\Http\Middleware\SetLocale::class);
        }
    }
}
