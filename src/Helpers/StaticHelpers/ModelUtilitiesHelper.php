<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Modules\Banqu\Utilities\Models\Standard\BanquTransactionUtilities;
use Modules\Core\Utilities\Models\AbstractModelUtilities;
use Modules\Core\Utilities\Models\CoreCountryModelUtilities;
use Modules\Core\Utilities\Models\CoreCurrencyModelUtilities;
use Modules\Core\Utilities\Models\CoreRoleModelUtilities;
use Modules\Core\Utilities\Models\NoModelUtilities;
use Modules\Core\Utilities\Models\RoleModelUtilities;
use Modules\Core\Utilities\Models\Standard\CoreUserClientUtilities;
use Modules\Core\Utilities\Models\Standard\CoreUserSystemActionUtilities;
use Modules\Core\Utilities\Models\UserModelUtilities;
use Modules\Core\Utilities\Models\UserTemplateModelUtilities;
use Modules\Membership\Entities\MembershipProduct;
use Modules\Membership\Entities\MembershipProductEticketRow;
use Modules\Membership\Entities\MembershipSubscription;
use Modules\Membership\Utilities\Models\Standard\MembershipProductEticketRowUtilities;
use Modules\Membership\Utilities\Models\Standard\MembershipProductUtilities;

class ModelUtilitiesHelper
{

    public static function getModelUtilities($table_name) : \Modules\Core\Utilities\Models\Standard\AbstractModelUtilities
    {
        switch ($table_name)
        {
            case 'core_countries':
                return new \Modules\Core\Utilities\Models\Standard\CoreCountryModelUtilities();
            case 'core_currencies':
                return new \Modules\Core\Utilities\Models\Standard\CoreCurrencyModelUtilities();
            case 'roles':
                return new \Modules\Core\Utilities\Models\Standard\RoleModelUtilities();
            case 'core_roles':
                return new \Modules\Core\Utilities\Models\Standard\CoreRoleModelUtilities();

            case 'core_user_contact_persons':
                return new \Modules\Core\Utilities\Models\Standard\CoreUserContactPersonModelUtilities();

            case 'membership_products':
                return new MembershipProductUtilities();

            case 'membership_subscriptions':
                return new \Modules\Membership\Utilities\Models\Standard\MembershipSubscriptionUtilities();
            case 'user_templates':
                return new UserTemplateModelUtilities();
            case 'users':
                return new \Modules\Core\Utilities\Models\Standard\UserModelUtilities();

            case 'core_user_clients':
                return new CoreUserClientUtilities();


            case 'banqu_transactions':
                return new BanquTransactionUtilities();

            case 'membership_product_eticket_rows':
                return new MembershipProductEticketRowUtilities();

            case 'statistics_dtickets':
                return new MembershipProductEticketRowUtilities();

            case 'core_user_system_actions':
                return new CoreUserSystemActionUtilities();


            default:
                return new \Modules\Core\Utilities\Models\Standard\NoModelUtilities();
        }

    }

    public static function isInstanceEditable($instance)
    {
        if($instance instanceof MembershipSubscription)
            return false;

        if($instance instanceof MembershipProduct)
            return false;


        if($instance instanceof MembershipProductEticketRow)
            return false;

        return true;
    }

    public static function isInstanceDeletable($instance)
    {
        if($instance instanceof MembershipSubscription)
            return false;

        if($instance instanceof MembershipProduct)
            return false;


        if($instance instanceof MembershipProductEticketRow)
            return false;

        return true;
    }

}
