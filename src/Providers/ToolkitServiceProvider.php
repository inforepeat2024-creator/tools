<?php

namespace RepeatToolkit\Providers;


use Illuminate\Support\ServiceProvider;
use RepeatToolkit\Console\Commands\MakePoFromI;
use Illuminate\Support\Facades\Log;
class ToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        //
    }

    public function boot(): void
    {


        if ($this->app->runningInConsole()) {



            $this->commands([
                MakePoFromI::class,
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

    }
}
