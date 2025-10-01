<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;
use IvanoMatteo\LaravelDeviceTracking\Models\DeviceUser;
use Modules\Core\Entities\CoreElementClient;
use Modules\Core\Utilities\Models\Standard\UserModelUtilities;
use OwenIt\Auditing\Models\Audit;

class UserHelper
{

    public static function getAuthUserId()
    {
        return Auth::user()->id ?? "";
    }

    public static function isSystemAdmin($user_id = null)
    {


        if($user_id == null)
            $user_id = Auth::user()->id ?? null;

        return in_array($user_id, [1 ,2, 3, 5, 37, 95]);
    }

    public static function getSystemAdmins()
    {
        return [1 ,2, 5, 37, 95];
    }

    public static function getIsSupervisorForUser($user, $client)
    {



        foreach ($user->core_user_clients as $core_user_client)
        {
            if(Auth::user()->id == $core_user_client->client_id && $core_user_client->postbox_supervisor == 1)
                return true;
        }

        return false;

    }

    public static function getAdminAppUrl()
    {
        $user_utils = new UserModelUtilities();
        $admin = $user_utils->findById(UserModelUtilities::ADMIN_ID);
        return $admin->app_url ?? "";
    }

    public static function getUserAppUrlOrAdmin($user_id)
    {

        $user_utils = new UserModelUtilities();

        $user = $user_utils->findById($user_id);

        if(!isset($user->id))
            return self::getAdminAppUrl();

        if($user->app_url != null)
            return $user->app_url;

        return self::getAdminAppUrl();

    }

    public static function getSemirUser()
    {
        return User::find(UserModelUtilities::SEMIR);
    }

    public static function getClientByPhone($phone)
    {
        $utils = new UserModelUtilities();

        return $utils->findUserByTelephoneNumber($phone);
    }

    public static function getEmployeesIds()
    {
        return [37, 5, 95];
    }

    public static function verifyUserDevicesFromDtickets()
    {
        $audits = DB::select(DB::raw('select distinct device_uuid, user_id from audits where ((url like "%eticket_onboarding%" and url like "%step-6%") or url like "%membership_products/show-active-mobile%") and user_id >= 1
        '));


        foreach ($audits as $audit)
        {
            if($audit->device_uuid != null)
            {
                $device = Device::where('device_uuid', $audit->device_uuid)->select(['id'])->first();

                if(isset($device->id))
                {
                    $device_user = DeviceUser::where('user_id', $audit->user_id)->where("device_id", $device->id)->select(['id'])->first();

                    if(isset($device_user->id))
                        $device_user->update(['verified_at' => DateTimeHelper::getCurrentDatetimeSql()]);
                    else
                        $device_user = DeviceUser::create([
                            'device_id' => $device->id,
                            'user_id' => $audit->user_id,
                            'verified_at' => DateTimeHelper::getCurrentDatetimeSql(),
                        ]);
                }
            }
        }

        return 1;

    }

    public function isWebShopWorker($web_shop_id, $worker_id = null)
    {
        if($worker_id == null);
            $worker_id = Auth::user()->id ?? null;

        $worker_count = CoreElementClient::where("web_shop_id", $web_shop_id)->where('client_id', $worker_id)->count();

        return $worker_count > 0;
    }
}
