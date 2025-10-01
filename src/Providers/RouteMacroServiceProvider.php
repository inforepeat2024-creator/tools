<?php

namespace RepeatToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {


        //dd('ssss');



        Route::macro('moduleResource', function ($uri, $controller) {

            Route::get("{$uri}/view", "{$controller}@view")->name("{$uri}.view");
            Route::post("{$uri}/datatable", "{$controller}@datatable")->name("{$uri}.datatable");
            Route::get("{$uri}/show/{id}", "{$controller}@show")->name("{$uri}.show");
            Route::get("{$uri}/delete/{id}", "{$controller}@destroy")->name("{$uri}.get_delete");
            Route::get("{$uri}/create-partial/{slug?}/{id?}", "{$controller}@createPartial")->name("{$uri}.create_partial");
            Route::post("{$uri}/store-partial/{id?}", "{$controller}@storePartial")->name("{$uri}.store_partial");

            Route::post("{$uri}/get-one-from-params", "{$controller}@getOneFromParams")->name("{$uri}.get_one_from_params");
            Route::post("{$uri}/get-all-from-params", "{$controller}@getAllFromParams")->name("{$uri}.get_all_from_params");
            Route::post("{$uri}/get-all-for-select", "{$controller}@getAllForSelect")->name("{$uri}.get_all_for_select");
            Route::post("{$uri}/get-all-paginate", "{$controller}@getAllPaginate")->name("{$uri}.get_all_paginate");
            Route::any("{$uri}/get-all-autocomplete", "{$controller}@getAllAutocomplete")->name("{$uri}.get_all_autocomplete");
            Route::post("{$uri}/find-by-id/{id}", "{$controller}@findById")->name("{$uri}.find_by_id");
            Route::post("{$uri}/delete-from-params", "{$controller}@deleteFromParams")->name("{$uri}.delete_from_params");
        });



        // Add other macros here...
    }
}