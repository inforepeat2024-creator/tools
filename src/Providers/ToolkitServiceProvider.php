<?php

namespace RepeatToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use RepeatToolkit\Console\Commands\MakePoFromI;

class ToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            \dirname(__DIR__, 2) . '/config/repeat-toolkit.php',
            'repeat-toolkit'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePoFromI::class,
                \RepeatToolkit\Console\Commands\ExportRoutesForJs::class,
            ]);
        }

        $packageBase = \dirname(__DIR__, 2); // root paketa

        // -----------------------------
        // DEFINICIJA SVIH PUBLISH MAPI
        // -----------------------------
        $publishAll = [

            // Config
            $packageBase . '/config/repeat-toolkit.php' => config_path('repeat-toolkit.php'),

            // (opciono) dodatni config i18n ako postoji
            // postaviće se dole i kao tag, ali ga dodaj i u "all"
            // samo ako fajl postoji:
            // (ne smeta ako ne postoji – proverićemo ispod)
            // Views
            $packageBase . '/src/resources/views/crud/view.blade.php'        => resource_path('views/crud/view.blade.php'),
            $packageBase . '/src/resources/views/crud/create_partial.blade.php' => resource_path('views/crud/create_partial.blade.php'),
            $packageBase . '/src/resources/views/layouts/app_layout.blade.php'  => resource_path('views/layouts/app_layout.blade.php'),

            // JS helpers / vendor fajlovi
            $packageBase . '/resources/js/i18n.js'        => resource_path('js/vendor/repeat-toolkit/i18n.js'),
            $packageBase . '/resources/js/route-lite.js'  => resource_path('js/vendor/repeat-toolkit/route-lite.js'),

            // JS directories (komponente i helperi)
            $packageBase . '/resources/js/components'     => resource_path('js/components'),
            $packageBase . '/resources/js/helpers'        => resource_path('js/helpers'),

            // Vite plugin stub
            $packageBase . '/resources/stubs/vite/repeat-vite-plugin.js'
            => resource_path('js/vendor/repeat-toolkit/vite-plugin.js'),
        ];

        // Ako postoji dodatni i18n config u paketu, uključi ga i u "all"
        $i18nConfigPath = $packageBase . '/config/i18n.php';
        if (file_exists($i18nConfigPath)) {
            $publishAll[$i18nConfigPath] = config_path('i18n.php');
        }

        // --------------------------------
        // 1) CATCH-ALL PUBLISH (bez taga)
        // --------------------------------
        // Ovo omogućava: php artisan vendor:publish --provider="...ToolkitServiceProvider" --force
        // da objavi SVE bez navođenja tagova.
        $this->publishes($publishAll);

        // --------------------------------
        // 2) TAGOVI (radi kompatibilnosti)
        // --------------------------------

        // Config (glavni)
        $this->publishes([
            $packageBase . '/config/repeat-toolkit.php' => config_path('repeat-toolkit.php'),
        ], 'repeat-toolkit-config');

        // (opciono) i18n config ako postoji
        if (file_exists($i18nConfigPath)) {
            $this->publishes([
                $i18nConfigPath => config_path('i18n.php'),
            ], 'repeat-i18n-config');
        }

        // JS vendors (i18n helper)
        $this->publishes([
            $packageBase . '/resources/js/i18n.js' =>
                resource_path('js/vendor/repeat-toolkit/i18n.js'),
        ], 'repeat-i18n-js');

        // JS vendors (route-lite)
        $this->publishes([
            $packageBase . '/resources/js/route-lite.js' =>
                resource_path('js/vendor/repeat-toolkit/route-lite.js'),
        ], 'repeat-js');

        // Vite plugin stub
        $this->publishes([
            $packageBase . '/resources/stubs/vite/repeat-vite-plugin.js' =>
                resource_path('js/vendor/repeat-toolkit/vite-plugin.js'),
        ], 'repeat-vite-merge');

        // Views
        $this->publishes([
            $packageBase . '/src/resources/views/crud/view.blade.php'           => resource_path('views/crud/view.blade.php'),
            $packageBase . '/src/resources/views/crud/create_partial.blade.php' => resource_path('views/crud/create_partial.blade.php'),
            $packageBase . '/src/resources/views/layouts/app_layout.blade.php'  => resource_path('views/layouts/app_layout.blade.php'),
        ], 'repeat-views');

        // JS directories
        $this->publishes([
            $packageBase . '/resources/js/components' => resource_path('js/components'),
        ], 'repeat-components');

        $this->publishes([
            $packageBase . '/resources/js/helpers' => resource_path('js/helpers'),
        ], 'repeat-helpers');
    }
}
