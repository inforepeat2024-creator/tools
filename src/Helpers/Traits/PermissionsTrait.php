<?php

namespace RepeatToolkit\Helpers\Traits;

use Illuminate\Support\Facades\Auth;

trait PermissionsTrait
{

    public function currentUserHasPermission($permission)
    {




        return Auth::user()->hasPermission($permission, Auth::user()->master_id, 1);

    }

}
