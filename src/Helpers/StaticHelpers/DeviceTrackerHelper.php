<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IvanoMatteo\LaravelDeviceTracking\LaravelDeviceTrackingFacade;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;
use IvanoMatteo\LaravelDeviceTracking\Models\DeviceUser;

class DeviceTrackerHelper
{


    public static function getUuid()
    {


        return LaravelDeviceTrackingFacade::detectFindAndUpdate()->device_uuid ?? "";
    }



    public static function getDeviceId()
    {
        return Device::where('device_uuid', LaravelDeviceTrackingFacade::detectFindAndUpdate()->device_uuid ?? "-1")->first()->id ?? null;
    }

    public static  function getVerifiedUsersFromCurrentDevice()
    {
        $device = LaravelDeviceTrackingFacade::detectFindAndUpdate();



        if(isset($device->id))
        {

            //  dd($device->id);

            $verified = DeviceUser::where('device_id', $device->id)
                ->whereNotNull('verified_at')
                ->orderBy("verified_at", 'desc')
                ->get();


            $user_ids = DB::select(DB::raw('select distinct user_id from device_user where device_id = '. $device->id .'
        '));


            return $user_ids;


        }

        return [];
    }

    public static  function getVerifiedDevice()
    {
        $device = LaravelDeviceTrackingFacade::detectFindAndUpdate();



        if(isset($device->id))
        {

          //  dd($device->id);

            $verified = DeviceUser::where('device_id', $device->id)
                ->whereNotNull('verified_at')
                ->orderBy("verified_at", 'desc')
                ->first();





            return $verified;


        }

        return null;
    }

    public static function getVerifiedDevices()
    {
        $device = LaravelDeviceTrackingFacade::detectFindAndUpdate();

        if(isset($device->id))
        {
            $verified = DeviceUser::where('device_id', $device->id)
                ->where('verified_at', '>', '2024-01-01 00:00:00')->get();


            //dd($verified);

            return $verified;


        }

        return null;
    }

    public static function isCurrentDeviceVerified()
    {

        $device =   LaravelDeviceTrackingFacade::detectFindAndUpdate();



        if(isset($device->id))
        {
            $verified = DeviceUser::where('device_id', $device->id)
                ->where('verified_at', '>', '2024-01-01 00:00:00')->first();


            Log::info("Verified_user_id");
            Log::info($verified->id ?? "no found");




            return isset($verified->id);


        }

        return false;
    }

    public static function isCurrentDeviceVerifiedForUserID($user_id = null)
    {

        if($user_id == null)
            $user_id = Auth::user()->id ?? null;

        $device =   LaravelDeviceTrackingFacade::detectFindAndUpdate();



        if(isset($device->id))
        {
            $verified = DeviceUser::where('device_id', $device->id)
                ->where('verified_at', '>', '2024-01-01 00:00:00')
                ->where("user_id", $user_id)
                ->first();



            return isset($verified->id);


        }

        return false;
    }

    public static function isUUIDVerified($uuid)
    {
        $device =   Device::where("device_uuid", $uuid)->first();



        if(isset($device->id))
        {
            $verified = DeviceUser::where('device_id', $device->id)
                ->where('verified_at', '>', '2024-01-01 00:00:00')->first();





            return isset($verified->id);


        }

        return false;
    }

    public static function markUuidAsNotVerified($uuid)
    {
        $device =   Device::where("device_uuid", $uuid)->first();



        if(isset($device->id))
        {
            $verified = DeviceUser::where('device_id', $device->id)
                ->where('verified_at', '>', '2024-01-01 00:00:00')->first();


            //dd($verified);

            if(isset($verified->id))
                $verified->update(['verified_at' => '1980-01-01 00:00:00']);


        }




    }

    public static function markDeviceIdAsVerified($device_id, $user_id = null)
    {

        $device_user = DeviceUser::where('device_id', $device_id)->first();

        if(isset($device_user->id))
            $device_user->update(['user_id' => $user_id, 'verified_at' => DateTimeHelper::getCurrentDatetimeSql()]);

        else
        {
            $device_user = DeviceUser::create([
                'device_id' => $device_id,
                'user_id' => $user_id,
                'verified_at' => DateTimeHelper::getCurrentDatetimeSql()
            ]);
        }






    }

    public static function markCurrentDeviceAsVerified($user_id)
    {
        $device = Device::where("device_uuid", self::getUuid())->first();

        if(isset($device->id))
        {
            self::markDeviceIdAsVerified($device->id, $user_id);
        }

    }
}
