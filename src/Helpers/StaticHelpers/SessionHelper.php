<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\Session;

class SessionHelper
{

    public static function getDatatableItems() : array
    {
        return Session::get('datatable_items') ?? [];
    }

    public static function resetDatatableItems()
    {
        Session::put("datatable_items", []);
    }

    public static function inSession($key, $id) : bool
    {
        if(Session::get($key) == null)
            return false;

        return in_array($id, Session::get($key));

    }
}
