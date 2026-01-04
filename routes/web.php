<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->get('/lang/{code}', function (Request $request, string $code) {

    $locales = config('languages', []);
    if (! array_key_exists($code, $locales)) {
        abort(404);
    }

    // 2) Upis u session + cookie (1 godina)
    session([
        'app.locale'      => $code,
        'app.language_id' => $locales[$code],
    ]);
    Cookie::queue('app_locale', $code, 60 * 24 * 365);


    // 4) Primeni odmah u ovom requestu
    App::setLocale($code);



    // 6) Bezbedan redirect nazad (ili na / ako nema referera)
    //    Takođe podrži ?redirect=/neka/putanja (samo lokalni path)
    $redirect = $request->query('redirect');
    if (is_string($redirect) && str_starts_with($redirect, '/')) {
        return redirect($redirect);
    }

    return back()->withInput([]) ?: redirect(url('/'));
})->where('code', '[A-Za-z_]+')->name('lang.switch');
