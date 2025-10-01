<?php

namespace RepeatToolkit\Helpers\Traits;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Core\Entities\UserBank;
use Modules\Core\Utilities\Buttons\HeaderButton;
use Modules\Core\Utilities\Models\Filters\Creator\ManualQueryStringFilterCreator;
use Modules\Core\Utilities\Models\Filters\Parser\AbstractFilterParser;
use Modules\Core\Utilities\Models\UserAddressModelUtilities;
use Modules\Core\Utilities\Models\UserBankModelUtilities;
use Modules\Core\Utilities\Models\UserContactModelUtilities;
use Modules\Core\Utilities\Models\UserModelUtilities;
use Modules\Core\Utilities\Models\UserTemplateModelUtilities;

trait UserTablesTrait
{

    protected function initHeaderButtons()
    {
        $chosen_model_utils = new \Modules\Core\Utilities\Models\Standard\UserTemplateModelUtilities();


        if(isset($_REQUEST['user']) || Str::contains(url()->current(), ['core_user_roles', 'user_addresses/use']))
        {

            $chosen_model_utils = new \Modules\Core\Utilities\Models\Standard\UserModelUtilities();
        }
        else
        {

            if(Route::current() != null)
            {
                $current_params = Route::current()->parameters() ?? [];

                if(isset($current_params['user_address']) )
                {
                    $model_utils = new \Modules\Core\Utilities\Models\Standard\UserAddressModelUtilities();
                    $model = $model_utils->getOneFromParams(['id' => $current_params['user_address']]);

                    if($model->user_addresses_user_id != null)
                    {
                        $chosen_model_utils = new \Modules\Core\Utilities\Models\Standard\UserModelUtilities();
                    }

                }
                else if(Str::contains(url()->current(), ['create-custom']))
                {
                    $chosen_model_utils = new \Modules\Core\Utilities\Models\Standard\UserModelUtilities();
                }
                else if(isset($current_params['user_contact']))
                {

                    $model_utils = new \Modules\Core\Utilities\Models\Standard\UserContactModelUtilities();
                    $model = $model_utils->getOneFromParams(['id' => $current_params['user_contact']]);

                    if($model->user_contacts_user_id != null)
                    {
                        $chosen_model_utils = new \Modules\Core\Utilities\Models\Standard\UserModelUtilities();
                    }

                }

                else if(isset($current_params['user_bank']))
                {
                    $model_utils = new \Modules\Core\Utilities\Models\Standard\UserBankModelUtilities();
                    $model = $model_utils->getOneFromParams(['id' => $current_params['user_bank']]);

                    if($model->user_banks_user_id != null)
                    {
                        $chosen_model_utils = new \Modules\Core\Utilities\Models\Standard\UserModelUtilities();
                    }

                }
            }







        }




        $buttons = [
            new HeaderButton(route($chosen_model_utils->getTableName() . '.create'),("Create new") . ' ' . _i($chosen_model_utils->getTableLabels()->getLabelSingle()) , Str::contains(Route::currentRouteName(), ['.create', '.edit']) ?  'btn  btn-primary' : 'btn btn-light', ''),
            new HeaderButton(route($chosen_model_utils->getTableName() . '.index'), ('View') . " " . _i($chosen_model_utils->getTableLabels()->getLabelPlural()) , 'btn ' . (Str::contains(Route::currentRouteName(), ['.index']) ?  'btn-primary' : 'btn-light'), ''),
        ];



        return $buttons;
    }

}
