<?php

namespace RepeatToolkit\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = session('app.locale')
            ?? Cookie::get('app_locale')
            ?? config('app.locale');

        App::setLocale($locale);



        return $next($request);
    }
}
