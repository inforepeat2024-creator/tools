<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use App\Models\User;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Modules\Core\Utilities\Models\Standard\UserModelUtilities;
use Modules\Membership\Utilities\Models\Standard\MembershipProductEticketRowUtilities;

class AppHelper
{

    const BUCHALTUNGS_BATLER_ID = 1;


    public static function getHtmlCacheKey()
    {
        $num = 167;
        return "v" . $num;
    }


    public static function editMode()
    {
        //return true;
        return Session::get("edit_mode") == 1 ? 1 : 0;
    }

    public static function getDticketPrice($start_date)
    {
        $price = 49;

        if(date("Y-m-d", strtotime($start_date)) >= '2025-01-01')
            $price = 58;

        return $price;
    }


    public static function rand_color() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public static function isDticketSaleBlocked()
    {
        $block_start = '2024-12-31 14:00:00';
        $block_end = '2024-12-31 23:59:59';

        $date = date("Y-m-d H:i:s");

        if($date < $block_start || $date > $block_end)
            return false;

        return true;
    }

    public static function getFormHeaderLogo()
    {

        $active_theme = 'metronic';

        if(UrlHelper::currentUrlContains('/web/'))
            return URL::asset("themes/". $active_theme ."/media/illustrations/unitedpalms-1/3.png");

        return URL::asset("themes/". $active_theme ."/media/illustrations/unitedpalms-1/9-dark.png");

    }

    public static function getFilemanagerUserId()
    {
        $user_id = Session::get('filemanager_user_id');

        if($user_id != null)
            return $user_id;

        return Auth::user()->id;
    }

    public static function getFilemanagerUser()
    {
        $utils = new UserModelUtilities();

        return $utils->findById(self::getFilemanagerUserId());
    }

    public static function setFilemanagerUserId($user_id)
    {
        Session::put('filemanager_user_id', $user_id);
    }

    public static function needDeviceVerification()
    {
        return false;

        if(UrlHelper::isLocalhost())
            return false;

        if(Auth::user()->id == 5)
            return false;

        if(Auth::user()->id == 3)
            return false;

        if(Auth::user()->id == 18028)
            return false;

        return UrlHelper::currentUrlContainsWords([
            'postbox/postbox_received_posts',
            'postbox/postbox_received_posts/outbound',
            'postbox_mailboxes/two-column-index',
        ]);
    }

    public static function getHandyAppUrl()
    {
        if(UrlHelper::isLocalhost())
            return 'http://localhost/biromio/public/core/clients-app/verify';

        return 'https://handyticket.app/core/clients-app/verify';
    }

    public static function getHandyAppSlipsUrl()
    {
        if(UrlHelper::isLocalhost())
            return 'http://localhost/biromio/public/core/clients-app/get-pay-request';

        return 'https://handyticket.app/core/clients-app/get-pay-request';
    }

    public static function getAppLogo($logged_in_user_id = null)
    {
        $logged_user = null;

        if($logged_in_user_id == null)
            $logged_user = Auth::user();
        else
        {
            $utils = new UserModelUtilities();

            $logged_user = $utils->findById($logged_in_user_id);
        }




        if(isset($logged_user->subaccount_user_id) && $logged_user->subaccount_user_id != null && $logged_user->subaccount_user_id != 1)
        {




            return $logged_user->subaccount_user->logo;
        }

        return $logged_user->logo ?? "";



    }

    public static function getAppMiniLogo($logged_in_user_id = null)
    {
        $logged_user = null;


        if(UrlHelper::currentUrlContains('handy'))
        {
            return User::find(2)->mini_logo ?? "";
        }

        if(UrlHelper::currentUrlContains('ticket-abo'))
        {
            return User::find(2)->mini_logo ?? "";
        }

        if(UrlHelper::currentUrlContains('biromio.com'))
        {
            return User::find(2)->mini_logo ?? "";
        }

        if($logged_in_user_id == null)
            $logged_user = Auth::user();
        else
        {
            $utils = new UserModelUtilities();

            $logged_user = $utils->findById($logged_in_user_id);
        }


        if(isset($logged_user->subaccount_user_id) && $logged_user->subaccount_user_id != null)
        {
            return $logged_user->subaccount_user->mini_logo;
        }

        return $logged_user->mini_logo ?? "";



    }


    public static function getNextDeepLinkQrCode($data, $width = null)
    {


// quick and simple:
        echo '<img style="width:'.$width.'" src="'.(new QRCode())->render($data).'" alt="DeepLink QR Code" />';
    }


    public static function getUserIconImage()
    {
        return URL::asset("themes/metronic/media/avatars/blank.png");
    }



    public static function getEmptyImagePlaceholder()
    {
        return URL::asset("themes/metronic/custom/img/image_placeholder.png");
    }

    public static function getInvoiceImagePlaceholder()
    {
        return URL::asset("themes/metronic/custom/img/invoice.png");
    }

    public static function getDticketPaymentBaseUrlForClient($client_id)
    {
        $user_utils = new UserModelUtilities();

        $user = $user_utils->findById($client_id);

        if(!isset($user->id))
        {
            return UserHelper::getAdminAppUrl();
        }


        if($client_id == 960)
        {
            return UserHelper::getUserAppUrlOrAdmin(UserModelUtilities::SNG);
        }


        //1. Ako ima kartu to je taj operator url
        $eticket_utils = new MembershipProductEticketRowUtilities();


        $eticket = $eticket_utils->getOneFromParams([
            'filter__user_id__equal__membership_product' => $client_id
        ]);



        // 2. Ako nema kartu onda roditelj prvo ako je operator
        if(!isset($eticket->membership_product->id))
        {



            if($user->parent_id != 2)
            {

                return UserHelper::getUserAppUrlOrAdmin($user->parent_id);
            }

            return UserHelper::getAdminAppUrl();
        }
        else
        {
            $operator_id = $eticket->membership_product->operator_id;


            return UserHelper::getUserAppUrlOrAdmin($operator_id);
        }



    }


    public static function getAppUrlForClient($client_id)
    {
        if($client_id == 'null')
            $client_id = null;

        if($client_id == null)
        {

            if(isset(Auth::user()->id))
            {
                $client_id = Auth::user()->id;

                if(Auth::user()->app_url != null)
                    return Auth::user()->app_url;

                if(isset(Auth::user()->parent->id))
                {
                    if(Auth::user()->parent->app_url != null)
                    {
                        return Auth::user()->parent->app_url;
                    }
                }

                return UserHelper::getUserAppUrlOrAdmin($client_id);

            }
        }

        $user_utils = new UserModelUtilities();

        $client = $user_utils->findById($client_id);

        if(!isset($client->id))
            return UserHelper::getAdminAppUrl();

        if($client->app_url != null)
            return $client->app_url;


        if(isset($client->parent->id) && $client->parent->app_url != null)
            return $client->parent->app_url;


        return UserHelper::getAdminAppUrl();


    }

    public static function canAssignNfcTokens()
    {
        if(!isset(Auth::user()->id))
            return false;

        return in_array(Auth::user()->id, [UserModelUtilities::SEMIR]);


    }

    public static function getOperatorIdFromUrl($given_url = null)
    {
        if($given_url == null)
            $given_url = UrlHelper::getCurrentUrl();


        $users_with_urls = User::whereNotNull("app_url")->get();


        foreach ($users_with_urls as $user)
        {
            if($user->id <= 2 )
                continue;

            if(TextHelper::stringContains($given_url, [$user->app_url]))
                return $user->id;
        }


        return UserModelUtilities::SEMITIMES_DIGITAL;
    }

    public static function getSubaccountUserIdFromUrl($given_url = null)
    {

        if($given_url == null)
            $given_url = UrlHelper::getCurrentUrl();


        $users_with_urls = User::whereNotNull("app_url")->get();


        foreach ($users_with_urls as $user)
        {
            if(TextHelper::stringContains($given_url, [$user->app_url]))
                return $user->id;
        }


        return null;
    }

    public static function getGooglePlacesApiKey()
    {
        return 'AIzaSyCi-wErkHNyingGiixbYcnXo3dSbltiKZg';
    }

    public static function getStationTypes()
    {
        return [
            '1' => _i("Regular station"),
            '2' => _i("Border"),
            '3' => _i("On demand"),
            '4' => _i("Ferry"),
        ];
    }

    public static function getModuleName($id)
    {
        switch ($id)
        {
            case 1:
                return _i("Basic");
            case 2:
                return _i("postBOX");
            case 3:
                return _i("TICKeTAVIS");
            case 4:
                return _i("Faktura");
            case 5:
                return _i("banqu");
            case 6:
                return _i("semiPAY");
            case 7:
                return _i("Statistics");

            default:
                return _i("Basic");
        }
    }

    public static function getModuleIcon($id)
    {
        switch ($id)
        {
            case 1:
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M1.34375 3.9463V15.2178C1.34375 16.119 2.08105 16.8563 2.98219 16.8563H8.65093V19.4594H6.15702C5.38853 19.4594 4.75981 19.9617 4.75981 20.5757V21.6921H19.2403V20.5757C19.2403 19.9617 18.6116 19.4594 17.8431 19.4594H15.3492V16.8563H21.0179C21.919 16.8563 22.6562 16.119 22.6562 15.2178V3.9463C22.6562 3.04516 21.9189 2.30786 21.0179 2.30786H2.98219C2.08105 2.30786 1.34375 3.04516 1.34375 3.9463ZM12.9034 9.9016C13.241 9.98792 13.5597 10.1216 13.852 10.2949L15.0393 9.4353L15.9893 10.3853L15.1297 11.5727C15.303 11.865 15.4366 12.1837 15.523 12.5212L16.97 12.7528V13.4089H13.9851C13.9766 12.3198 13.0912 11.4394 12 11.4394C10.9089 11.4394 10.0235 12.3198 10.015 13.4089H7.03006V12.7528L8.47712 12.5211C8.56345 12.1836 8.69703 11.8649 8.87037 11.5727L8.0107 10.3853L8.96078 9.4353L10.148 10.2949C10.4404 10.1215 10.759 9.98788 11.0966 9.9016L11.3282 8.45467H12.6718L12.9034 9.9016ZM16.1353 7.93758C15.6779 7.93758 15.3071 7.56681 15.3071 7.1094C15.3071 6.652 15.6779 6.28122 16.1353 6.28122C16.5926 6.28122 16.9634 6.652 16.9634 7.1094C16.9634 7.56681 16.5926 7.93758 16.1353 7.93758ZM2.71385 14.0964V3.90518C2.71385 3.78023 2.81612 3.67796 2.94107 3.67796H21.0589C21.1839 3.67796 21.2861 3.78023 21.2861 3.90518V14.0964C15.0954 14.0964 8.90462 14.0964 2.71385 14.0964Z" fill="currentColor"></path>
</svg>';
            case 2:
                return '  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" >
																<path d="M6 8.725C6 8.125 6.4 7.725 7 7.725H14L18 11.725V12.925L22 9.725L12.6 2.225C12.2 1.925 11.7 1.925 11.4 2.225L2 9.725L6 12.925V8.725Z" fill="currentColor"></path>
																<path opacity="0.3" d="M22 9.72498V20.725C22 21.325 21.6 21.725 21 21.725H3C2.4 21.725 2 21.325 2 20.725V9.72498L11.4 17.225C11.8 17.525 12.3 17.525 12.6 17.225L22 9.72498ZM15 11.725H18L14 7.72498V10.725C14 11.325 14.4 11.725 15 11.725Z" fill="currentColor"></path>
															</svg>';
            case 3:
                return '<svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
<path opacity="0.3" d="M8.9 21L7.19999 22.6999C6.79999 23.0999 6.2 23.0999 5.8 22.6999L4.1 21H8.9ZM4 16.0999L2.3 17.8C1.9 18.2 1.9 18.7999 2.3 19.1999L4 20.9V16.0999ZM19.3 9.1999L15.8 5.6999C15.4 5.2999 14.8 5.2999 14.4 5.6999L9 11.0999V21L19.3 10.6999C19.7 10.2999 19.7 9.5999 19.3 9.1999Z" fill="currentColor"></path>
<path d="M21 15V20C21 20.6 20.6 21 20 21H11.8L18.8 14H20C20.6 14 21 14.4 21 15ZM10 21V4C10 3.4 9.6 3 9 3H4C3.4 3 3 3.4 3 4V21C3 21.6 3.4 22 4 22H9C9.6 22 10 21.6 10 21ZM7.5 18.5C7.5 19.1 7.1 19.5 6.5 19.5C5.9 19.5 5.5 19.1 5.5 18.5C5.5 17.9 5.9 17.5 6.5 17.5C7.1 17.5 7.5 17.9 7.5 18.5Z" fill="currentColor"></path>
</svg>';
            case 4:
                return '<i class="fas fa-file-invoice fs-2x "><span class="path1"></span><span class="path2"></span></i>';
            case 5:
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M20 19.725V18.725C20 18.125 19.6 17.725 19 17.725H5C4.4 17.725 4 18.125 4 18.725V19.725H3C2.4 19.725 2 20.125 2 20.725V21.725H22V20.725C22 20.125 21.6 19.725 21 19.725H20Z" fill="currentColor"></path>
<path opacity="0.3" d="M22 6.725V7.725C22 8.325 21.6 8.725 21 8.725H18C18.6 8.725 19 9.125 19 9.725C19 10.325 18.6 10.725 18 10.725V15.725C18.6 15.725 19 16.125 19 16.725V17.725H15V16.725C15 16.125 15.4 15.725 16 15.725V10.725C15.4 10.725 15 10.325 15 9.725C15 9.125 15.4 8.725 16 8.725H13C13.6 8.725 14 9.125 14 9.725C14 10.325 13.6 10.725 13 10.725V15.725C13.6 15.725 14 16.125 14 16.725V17.725H10V16.725C10 16.125 10.4 15.725 11 15.725V10.725C10.4 10.725 10 10.325 10 9.725C10 9.125 10.4 8.725 11 8.725H8C8.6 8.725 9 9.125 9 9.725C9 10.325 8.6 10.725 8 10.725V15.725C8.6 15.725 9 16.125 9 16.725V17.725H5V16.725C5 16.125 5.4 15.725 6 15.725V10.725C5.4 10.725 5 10.325 5 9.725C5 9.125 5.4 8.725 6 8.725H3C2.4 8.725 2 8.325 2 7.725V6.725L11 2.225C11.6 1.925 12.4 1.925 13.1 2.225L22 6.725ZM12 3.725C11.2 3.725 10.5 4.425 10.5 5.225C10.5 6.025 11.2 6.725 12 6.725C12.8 6.725 13.5 6.025 13.5 5.225C13.5 4.425 12.8 3.725 12 3.725Z" fill="currentColor"></path>
</svg>';
            case 6:
                return '<i class="fas fa-money-bill fs-2x"></i>';
            case 7:
                return '<i class="fas fa-file-csv fs-2x "><span class="path1"></span><span class="path2"></span></i>';

            default:
                return _i("Basic");
        }


    }



    public static function isRestrictedToOperator()
    {
        $url_user_id = UserModelUtilities::getUserIdFromUrl();

        return $url_user_id != UserModelUtilities::SEMITIMES_DIGITAL;
    }




    public static function isEmailBlacklisted($email)
    {

        $user_utils = new UserModelUtilities();

        $user = $user_utils->getOneFromParams(['filter__email__equal' => $email]);


        if(!isset($user->id))
            $user = $user_utils->getOneFromParams(['filter__email__equal__user_contact' => $email]);

        if(!isset($user->id))
            $user = $user_utils->getOneFromParams(['filter__username__equal' => $email]);


        if(isset($user->id) && $user->can_login == 0)
            return true;

        return false;


    }

    public static function isUserBlacklisted($user_id)
    {
        $user_utils = new UserModelUtilities();

        $user = $user_utils->findById($user_id);

        if(isset($user->id) && $user->can_login == 0)
            return true;

        return false;
    }
    public static function isPhoneBlacklisted($phone)
    {
        $user_utils = new UserModelUtilities();

        $user = $user_utils->getOneFromParams(['filter__username__equal' => $phone]);

        if(!isset($user->id))
            $user = $user_utils->getOneFromParams(['filter__email__equal' => $phone]);

        if(!isset($user->id))
            $user = $user_utils->getOneFromParams(['filter__mobile__equal__user_contact' => $phone]);

        if(!isset($user->id))
            $user = $user_utils->getOneFromParams(['filter__viber_phone__equal__user_contact' => $phone]);

        if(!isset($user->id))
            $user = $user_utils->getOneFromParams(['filter__whatsapp_phone__equal__user_contact' => $phone]);


        if(isset($user->id) && $user->can_login == 0)
            return true;

        return false;
    }

    public static function isFilteringOn()
    {
        return !UrlHelper::currentUrlContains('/clearing/') && !UrlHelper::currentUrlContains('oduct_eticket_rows/statistics/tw');
    }


    public static function getProjectCreateSteps($model_id = null)
    {

        $steps =  [
            1 => [
                'name' => _i("Project details"),
                'description' => _i("Provide project name, description..."),
                'route' => route("projects_plans.create_project_details", $model_id),
                'is_active' => url()->current() ==  route("projects_plans.create_project_details", $model_id),
            ],

            2 => [
                'name' => _i("Client details"),
                'description' => _i("Choose client or create new"),
                'route' => route("projects_plans.create_client_details", $model_id),
                'is_active' => url()->current() ==  route("projects_plans.create_client_details", $model_id),
            ],

            3 => [
                'name' => _i("Billing details"),
                'description' => _i("Add card, bank account..."),
                'route' => route("projects_plans.create_billing_details", $model_id),
                'is_active' => url()->current() ==  route("projects_plans.create_billing_details", $model_id),
            ],

            4 => [
                'name' => _i("Optional details"),
                'description' => _i("Notifications, priorities, tags..."),
                'route' => route("projects_plans.create_optional_details", $model_id),
                'is_active' => url()->current() ==  route("projects_plans.create_optional_details", $model_id),
            ],

            5 => [
                'name' => _i("Add employees"),
                'description' => _i("Select employees"),
                'route' => route("projects_plans.create_employees_details", $model_id),
                'is_active' => url()->current() ==  route("projects_plans.create_employees_details", $model_id),
            ],
        ];

        return $steps;

    }

    public static function getTicketavisBookingMaskUrl()
    {
        return 'https://birom.io/ticketavis/external-booking/home?custom_operator_id=21922&lang=de';
    }

    public static function getPublicProfileUrlForUser($user_id)
    {
        return 'https://biromio.com/core/public-user-profile/profile-details/' . $user_id;
    }


    public static function needTableActions()
    {
        return (!UrlHelper::currentUrlContainsWords(['postbox_received_posts/two-column-index', 'outbound/two-column']));
    }

    public static function hideAside()
    {
        if(UrlHelper::currentUrlContainsWords(['postbox_', 'hide_aside', 'filemanager', 'core_products', 'choose', 'core_user_clients', 'create', 'edit', 'two-column']))
            return true;

        return false;
    }

    public static function getTransactionPrintDeeplinkUrl($transaction_code)
    {
        return "sunmiprint://print?html_code=$transaction_code";
    }

    public static function getWidthForDevice($device)
    {
        if(in_array($device, ['V3|SUNMI', 'V2sNC_EEA|SUNMI', 'sdk_gphone64_x86_64|Google', 'CS50|Ciontek']))
            return 384;


        if(TextHelper::stringContains($device, ['V3', 'V2']))
            return 384;

        if(in_array($device, ['D3 MINI|SUNMI']))
            return 550;



        return 384;
    }

    public static function getReceiptViewForDevice($device)
    {

        if(in_array($device, ['V3|SUNMI', 'V2sNC_EEA|SUNMI', 'sdk_gphone64_x86_64|Google', 'CS50|Ciontek']))
            return 'sunmi_v2_receipt';


        if(TextHelper::stringContains($device, ['V3', 'V2']))
            return 'sunmi_v2_receipt';

        if(in_array($device, ['D3 MINI|SUNMI']))
            return 'sunmi_wide_receipt';



        return 'receipt';

    }
}
