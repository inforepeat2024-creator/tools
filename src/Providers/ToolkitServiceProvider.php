<?php

namespace RepeatToolkit\Providers;


use Illuminate\Support\ServiceProvider;
use RepeatToolkit\Console\Commands\MakePoFromI;
use Illuminate\Support\Facades\Log;
class ToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/repeat-toolkit.php',
            'repeat-toolkit'
        );
        //
    }

    public function boot(): void
    {


        if ($this->app->runningInConsole()) {



            $this->commands([
                MakePoFromI::class,
                \RepeatToolkit\Console\Commands\ExportRoutesForJs::class,
            ]);
        }

        $packageBase = \dirname(__DIR__, 2); // iz src/Providers -> paket root

        $this->publishes([
            $packageBase . '/resources/js/i18n.js' =>
                resource_path('js/vendor/repeat-toolkit/i18n.js'),
        ], 'repeat-i18n-js');

        // (opciono) ako imaš config/i18n.php u /resources ili /config paketa:
        if (file_exists($packageBase . '/config/i18n.php')) {
            $this->publishes([
                $packageBase . '/config/i18n.php' => config_path('i18n.php'),
            ], 'repeat-i18n-config');
        }

        $this->publishes([
            __DIR__.'/../../resources/stubs/vite/repeat-vite-plugin.js'
            => resource_path('js/vendor/repeat-toolkit/vite-plugin.js'),
        ], 'repeat-vite-merge');


        $this->publishes([
            __DIR__.'/../../resources/js/route-lite.js' => resource_path('js/vendor/repeat-toolkit/route-lite.js'),
        ], 'repeat-js');

        // publish views
        $this->publishes([
            __DIR__.'/../resources/views/crud/view.blade.php' => resource_path('views/crud/view.blade.php'),
            __DIR__.'/../resources/views/crud/create_partial.blade.php' => resource_path('views/crud/create_partial.blade.php'),
            __DIR__.'/../resources/views/layouts/app_layout.blade.php' => resource_path('views/layouts/app_layout.blade.php'),
        ], 'repeat-views');

        $this->publishes([
            __DIR__.'/../../resources/js/components'      => resource_path('js/components'),
        ], 'repeat-components');

        $this->publishes([
            __DIR__.'/../../resources/js/helpers'      => resource_path('js/helpers'),
        ], 'repeat-helpers');

        $this->publishes([
            __DIR__.'/../../config/repeat-toolkit.php' => config_path('repeat-toolkit.php'),
        ], 'repeat-toolkit-config');
    }
}
