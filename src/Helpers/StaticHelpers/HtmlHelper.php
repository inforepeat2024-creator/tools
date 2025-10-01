<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Modules\Core\Utilities\Models\Standard\UserModelUtilities;

class HtmlHelper
{


    public static function getNoHref()
    {
        return "javascript:void(0);";
    }


    public static function getModelIcon($table_name)
    {
        if($table_name == "users")
            return 'fa fa-user';

        if($table_name == 'auctions')
            return 'fa fa-gavel';

        if($table_name == 'motorcycles')
            return 'fa fa-motorcycle';

        return "";
    }

    public static function ticketavis()
    {
        if(Auth::user()->is_cashier && !UserHelper::isSystemAdmin() && Auth::user()->id != '20747')
            return false;

        return true;
    }

    public static function projects()
    {
        if(Auth::user()->is_cashier && !UserHelper::isSystemAdmin())
            return false;

        return true;
    }

    public static function semipay()
    {
        if(Auth::user()->is_cashier && !UserHelper::isSystemAdmin())
            return false;

        return true;
    }




    public static function getImageUrlFromData($base64Data, $filename)
    {
        // Remove the data URL scheme if present (e.g., "data:image/png;base64,")
        if (strpos($base64Data, 'base64,') !== false) {
            $base64Data = explode('base64,', $base64Data)[1];
        }

        // Decode the Base64 string
        $imageData = base64_decode($base64Data);

        // Generate a unique filename
        $filename = 'qrcodes/' . $filename;
        $path = public_path($filename);

        // Ensure the directory exists
        if (!file_exists(public_path('qrcodes'))) {
            mkdir(public_path('qrcodes'), 0777, true);
        }

        // Save the image to the public folder
        file_put_contents($path, $imageData);

        // Return the image URL
        return asset($filename);
    }


    public static function getTableIcon($table_number, $number = true)
    {


        if($table_number === null)
            return "";



        if($table_number == 0)
        {
            return   '<svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
<path opacity="0.3" d="M20 21H3C2.4 21 2 20.6 2 20V10C2 9.4 2.4 9 3 9H20C20.6 9 21 9.4 21 10V20C21 20.6 20.6 21 20 21Z" fill="currentColor"></path>
<path d="M20 7H3C2.4 7 2 6.6 2 6V3C2 2.4 2.4 2 3 2H20C20.6 2 21 2.4 21 3V6C21 6.6 20.6 7 20 7Z" fill="currentColor"></path>
</svg>';
        }

        if($table_number == 1000)
        {
            return '<svg class="to_go" width="17" style="margin-top:3px;margin-right:8px;" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="currentColor" d="M208 96c26.5 0 48-21.5 48-48S234.5 0 208 0s-48 21.5-48 48 21.5 48 48 48zm94.5 149.1l-23.3-11.8-9.7-29.4c-14.7-44.6-55.7-75.8-102.2-75.9-36-.1-55.9 10.1-93.3 25.2-21.6 8.7-39.3 25.2-49.7 46.2L17.6 213c-7.8 15.8-1.5 35 14.2 42.9 15.6 7.9 34.6 1.5 42.5-14.3L81 228c3.5-7 9.3-12.5 16.5-15.4l26.8-10.8-15.2 60.7c-5.2 20.8 .4 42.9 14.9 58.8l59.9 65.4c7.2 7.9 12.3 17.4 14.9 27.7l18.3 73.3c4.3 17.1 21.7 27.6 38.8 23.3 17.1-4.3 27.6-21.7 23.3-38.8l-22.2-89c-2.6-10.3-7.7-19.9-14.9-27.7l-45.5-49.7 17.2-68.7 5.5 16.5c5.3 16.1 16.7 29.4 31.7 37l23.3 11.8c15.6 7.9 34.6 1.5 42.5-14.3 7.7-15.7 1.4-35.1-14.3-43zM73.6 385.8c-3.2 8.1-8 15.4-14.2 21.5l-50 50.1c-12.5 12.5-12.5 32.8 0 45.3s32.7 12.5 45.2 0l59.4-59.4c6.1-6.1 10.9-13.4 14.2-21.5l13.5-33.8c-55.3-60.3-38.7-41.8-47.4-53.7l-20.7 51.5z"></path></svg>';
        }

        if(!$number)
            $table_number = "";
        return  '<div class="text-center"> '.$table_number .'
<svg width="25" height="25" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
<path opacity="0.6" d="M11.8 5.2L17.7 8.6V15.4L11.8 18.8L5.90001 15.4V8.6L11.8 5.2ZM11.8 2C11.5 2 11.2 2.1 11 2.2L3.8 6.4C3.3 6.7 3 7.3 3 7.9V16.2C3 16.8 3.3 17.4 3.8 17.7L11 21.9C11.3 22 11.5 22.1 11.8 22.1C12.1 22.1 12.4 22 12.6 21.9L19.8 17.7C20.3 17.4 20.6 16.8 20.6 16.2V7.9C20.6 7.3 20.3 6.7 19.8 6.4L12.6 2.2C12.4 2.1 12.1 2 11.8 2Z" fill="currentColor"></path>
<path d="M11.8 8.69995L8.90001 10.3V13.7L11.8 15.3L14.7 13.7V10.3L11.8 8.69995Z" fill="currentColor"></path>
</svg>
</div>' ;
    }


    public static function getDticketLogoImg()
    {
        return URL::asset("themes/metronic/custom/img/dticket_logo_2.png");
    }

    public static function getImageWithSideTexts($dst_url, $image_url, array $texts)
    {

        $target_blank = "";

        if(!TextHelper::stringContains($dst_url, ['create-partial', '/create', '/edit']))
            $target_blank = 'target="_blank"';

        $html = '<div class="d-flex align-items-center">
                                                                                                    <div class="symbol symbol-50px me-3">
                                                        <img src="'. ($image_url ?? AppHelper::getEmptyImagePlaceholder()) .'" class="" alt="">
                                                    </div>


                                                <div class="d-flex justify-content-start flex-column">
                                                    <a '.$target_blank.' href="'. $dst_url .'" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">'. ($texts[0] ?? "") .'</a>
                                                    <span class="text-gray-500 fw-semibold d-block fs-7">'. ($texts[1] ?? "") .'</span>
                                                    <span class="text-gray-500 fw-semibold d-block fs-7">'. ($texts[2] ?? "") .'</span>
                                                </div>
                                            </div>';

        return $html;
    }

    public static function getIconWithSideTexts($icon, array $texts)
    {
        $html = '<div class="d-flex flex-stack">
                <!--begin::Symbol-->
                <div class="symbol symbol-40px me-4">
                    <div class="symbol-label fs-2 fw-semibold "><span class="'. $icon .'"></span></div>
                </div>
                <!--end::Symbol-->

                <!--begin::Section-->
                <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                    <!--begin:Author-->
                    <div class="flex-grow-1 me-2">
                        <a href="/metronic8/demo1/pages/user-profile/overview.html" class="text-gray-800 text-hover-primary fs-6 fw-bold">'. ($texts[0] ?? "") .'</a>

                        <span class="text-muted fw-semibold d-block fs-7">'. ($texts[1] ?? "") .'</span>
                    </div>
                    <!--end:Author-->


                </div>
                <!--end::Section-->
            </div>';

        return $html;
    }


    public static function getEmailTrackingHtml(array $params)
    {

        $img = '<img style="display:inline;width: 1px;height:1px;" src="'.route("email_tracking.read_source", $params).'">';


      /*  $params['embed'] = 1;
        $embed =  ' <embed type="image/png" src="'. route("email_tracking.read_source", $params) .'" style="display:inline;width: 1px;height:1px;"> ';*/

        return  $img ;
    }

    public static function getRandomColorClass()
    {
        $classes = ['dark', 'primary', 'danger', 'warning', 'info', 'success'];

        return $classes[array_rand($classes)];
    }

    public static function getAgencyAppMenuItems()
    {
        $regular_items = [
            0 => 'dticket',
            1 => 'booking',
            2 => 'terminal',
            3 => 'dticket_box',
            4 => 'make_payment',
        ];

        if(UserModelUtilities::getUserIdFromUrl() == UserModelUtilities::SNG)
        {
            return [
                0 => 'dticket',
                1 => 'terminal',
                2 => 'dticket_box',
                3 => 'make_payment',
            ];
        }

        return $regular_items;
    }


    public static function getAgencyAppMenuCount()
    {

        return count(self::getAgencyAppMenuItems());
    }


    public static function getAgencyNavSlugFromUrl()
    {
        if( UrlHelper::currentUrlContains('cash-register-process/choose-product'))
        {
            return 'dticket';
        }


        if( UrlHelper::currentUrlContains('cash-register-process/booking'))
        {
            return "booking";
        }

        if( UrlHelper::currentUrlContains('cash-register-process/terminal'))
        {
            return "terminal";
        }

        if( UrlHelper::currentUrlContains('cash-register-process/dticket-box'))
        {
            return "dticket_box";
        }

        if( UrlHelper::currentUrlContains('cash-register-process/make-payment'))
        {
            return "make_payment";
        }


        return 'dticket';
    }



    public static function getAgencyAppNavIndexFromUrl()
    {

        $items = self::getAgencyAppMenuItems();

        foreach ($items as $key => $name)
        {
            if($name == self::getAgencyNavSlugFromUrl())
                return $key;
        }

        return 0;

    }


    public static function getClientsAppNavIndexFromUrl()
    {


        if( UrlHelper::currentUrlContains('clients-app/verify'))
        {
            return 0;
        }


        if( UrlHelper::currentUrlContains('phone-number/credit'))
        {
            return 1;
        }

        if( UrlHelper::currentUrlContains('pin/credit'))
        {
            return 2;
        }

        if( UrlHelper::currentUrlContains('get-pay-request'))
        {
            return 3;
        }

        return 0;

    }
}
