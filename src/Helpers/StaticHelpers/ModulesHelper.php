<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Str;
use Modules\Banqu\Entities\BanquAccountUser;
use Modules\Core\Entities\CoreUserModuleSection;
use Modules\Core\Utilities\Models\Standard\CoreMessageTemplateCategoryUtilities;
use Modules\Core\Utilities\Models\Standard\CoreMessageTemplateUtilities;
use Modules\Postbox\Entities\PostboxUserSettingsRow;

class ModulesHelper
{


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

    public static function getModuleNameFromUrl($url = null)
    {
        if($url == null)
            $url = url()->current();

        if(Str::contains($url, ['sync/']))
            return 'core';

        if(UrlHelper::isLocalhost())
        {
            $url_split = explode( '/', url()->current());

            //   dd($url_split);

            $module = $url_split[5] ?? "core";
        }
        else
        {
            $url_split = explode( '/', url()->current());

            $module = $url_split[3] ?? "core";
        }


        if($module == 'logout')
            return 'core';

        return $module;


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
                return '  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																<path d="M6 8.725C6 8.125 6.4 7.725 7 7.725H14L18 11.725V12.925L22 9.725L12.6 2.225C12.2 1.925 11.7 1.925 11.4 2.225L2 9.725L6 12.925V8.725Z" fill="black"></path>
																<path opacity="0.3" d="M22 9.72498V20.725C22 21.325 21.6 21.725 21 21.725H3C2.4 21.725 2 21.325 2 20.725V9.72498L11.4 17.225C11.8 17.525 12.3 17.525 12.6 17.225L22 9.72498ZM15 11.725H18L14 7.72498V10.725C14 11.325 14.4 11.725 15 11.725Z" fill="black"></path>
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

    public static function isModuleSectionActiveForUser($core_module_section_id, $user_id)
    {
        $row =  CoreUserModuleSection::where("user_id", $user_id)->where('core_module_section_id', $core_module_section_id)->first();

        return isset($row->id);
    }

    public static function getModuleOptions($module_id, $user_id = null)
    {
        switch ($module_id)
        {
            case 1:
                return [
                    [
                        'name' => _i("Basic functionality"),
                        'description' => _i("Core features every user has, managing clients etc."),
                        'field' => "basic_options",
                        'checked' => true,
                        'disabled' => true,
                        'core_module_section_id' => 1,
                    ],
                    [
                        'name' => _i("Impressum, Contact, Terms templates"),
                        'description' => _i("Create templates for impressum, contact, terms of use etc. Please login as this client and update templates content, for example signature etc."),
                        'field' => "basic_options",

                        'core_module_section_id' => 1001,
                       /* 'url_description' => _i("You will have to click here to edit templates for this user"),
                        'side_drawer' => true,
                        'url' => "javascript:void(0)",*/
                    ],

                ];
            case 2:
                return [
                    [
                        'name' => _i("postBOX Inbox"),
                        'description' => _i("Individual inbox to import eMails to. Configure eMail address etc."),
                        'url_description' => _i("You will have to click here to configure postBOX eMail and url"),
                        'side_drawer' => true,
                        'url' => "javascript:void(0)",
                        'field' => "postbox_inbox",
                        'core_module_section_id' => 12,

                    ],

                ];
            case 3:
                return [
                    [
                        'name' => _i("Deutschlandticket"),
                        'description' => _i("Configure D-TICKETS selling"),
                        'field' => "dtickets",
                        /*'url_description' => _i("You will have to click here to configure D-TICKET onboarding process"),
                        'url' => "#",*/
                        'core_module_section_id' => 8,
                    ],
                    [
                        'name' => _i("School TICKeTS"),
                        'description' => _i("Configure School TICKeTS selling"),
                        'field' => "school_tickets",
                        'core_module_section_id' => 100,

                    ],

                ];
            case 4:
                return [
                    [
                        'name' => _i("Cash register"),
                        'description' => _i("Include cash register process"),
                        'field' => "offers",
                        'core_module_section_id' => 206,
                    ],
                    [
                        'name' => _i("Orders"),
                        'description' => _i("Include orders"),
                        'field' => "offers",
                        'core_module_section_id' => 205,
                    ],
                    [
                        'name' => _i("Offers"),
                        'description' => _i("Include offers"),
                        'field' => "offers",
                        'core_module_section_id' => 203,
                    ],
                    [
                        'name' => _i("Outgoing bills"),
                        'description' => _i("Include outgoing bills"),
                        'field' => "outgoing",
                        'core_module_section_id' => 201,
                    ],
                    [
                        'name' => _i("Incoming bills"),
                        'description' => _i("Include incoming bills"),
                        'field' => "incoming",
                        'core_module_section_id' => 202,
                    ],

                    [
                        'name' => _i("Recurring bills"),
                        'description' => _i("Include recurring bills"),
                        'field' => "recurring_bills",
                        'core_module_section_id' => 204,
                    ],

                ];
            case 5:
                return [
                    [
                        'name' => _i("Share PayPal account"),
                        'description' => _i("Share admin PayPal account with selected client"),
                        'field' => "share_banqu",
                        'core_module_section_id' => 602,
                    ],
                    [
                        'name' => _i("Share Viva Wallet account"),
                        'description' => _i("Share admin Viva Wallet account with selected client"),
                        'field' => "share_banqu",
                        'core_module_section_id' => 603,
                    ],
                    [
                        'name' => _i("Share ViaCash account"),
                        'description' => _i("Share admin ViaCash account with selected client"),
                        'field' => "share_banqu",
                        'core_module_section_id' => 604,
                    ],
                    [
                        'name' => _i("Custom client accounts"),
                        'description' => _i("Add custom client accounts, if the client has his own PayPal, Viva etc."),
                        'field' => "own_banqu",
                        'core_module_section_id' => 10,
                    ],

                ];
            case 6:
                return [
                    [
                        'name' => _i("Enable semiPAY"),
                        'description' => _i("Make semiPAY included in client account"),
                        'field' => "include_semipay",
                        'core_module_section_id' => 13,
                    ],


                ];
            case 7:
                return [
                    [
                        'name' => _i("EMS reports"),
                        'description' => _i("Import EMS reports from admin"),
                        'field' => "import_reports",
                        'core_module_section_id' => 502,
                    ],
                    [
                        'name' => _i("Clearing reports"),
                        'description' => _i("Import Clearing reports reports from admin"),
                        'field' => "import_reports",
                        'core_module_section_id' => 503,
                    ],


                ];

            default:
                return _i("Basic");
        }
    }


    public static function isModuleSectionConfiguredForUserId($core_module_section_id, $user_id)
    {

        if($core_module_section_id == 1001)
        {
            $template_utils = new CoreMessageTemplateUtilities();

            $copied = $template_utils->getOneFromParams([
                'filter__user_id__equal' => $user_id,
                'filter__copy_of_id__greater_equal' => 1
            ]);

            if(!isset($copied->id))
                return false;

            $template_utils = new CoreMessageTemplateUtilities();

            $imported_templates = $template_utils->getAllFromParams([
                'filter__copy_of_id__greater_equal' => 1,
                'filter__user_id__equal' => $user_id,
                'filter__core_message_template_category_id__in' => [CoreMessageTemplateCategoryUtilities::DTICKET_PAYMENT,CoreMessageTemplateCategoryUtilities::SIGNATURE]
            ]);


            //dd($imported_templates);

            foreach ($imported_templates as $imported_template)
            {
                if($imported_template->created_at == $imported_template->updated_at)
                {
                    return false;
                }
            }


            return true;
        }

        if($core_module_section_id == 12)
        {
            $model = PostboxUserSettingsRow::where('user_id', $user_id)->first();

            return isset($model->id);
        }

        if($core_module_section_id == 10)
        {

            $existing = BanquAccountUser::where('user_id', $user_id)->where('core_access_role_id', 1)->first();

            return isset($existing->id);

        }





        return true;



    }
}
