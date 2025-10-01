<?php

namespace RepeatToolkit\Helpers\StaticHelpers;



use Modules\Core\Utilities\Models\Standard\UserModelUtilities;
use Modules\Membership\Utilities\Models\Standard\MembershipProductEticketRowUtilities;
use Modules\Membership\Utilities\Models\Standard\MembershipProductUtilities;
use Modules\Postbox\Entities\PostboxMailbox;
use Modules\Postbox\Utilities\Models\Standard\PostboxMailboxUtilities;

class EmailHelper
{

    const DTICKET = 1;
    const BANQU_TRANSACTION = 5;
    const D_TICKET_PAGE_URL = 10;


    public static function getSenderEmailBasedOnProduct($product_id)
    {
        $product_utils = new MembershipProductUtilities();

        $model = $product_utils->findById($product_id);

        $sender_user = null;


        if(isset($model->operator->id))
        {
            $sender_user = $model->operator;

            if(isset($sender_user->no_reply_email) && $sender_user->no_reply_email != null)
                return $sender_user->no_reply_email;
        }

        $user_utils = new UserModelUtilities();
        $sender_user = $user_utils->findById(UserModelUtilities::ADMIN_ID);

        return $sender_user->no_reply_email;
    }

    public static function getSenderEmailForDticketPurposes($client_id)
    {
        $user_utils = new UserModelUtilities();

        $client = $user_utils->findById($client_id);


        $sender_user = null;

        if(isset($client->id))
        {
            $eticket_row_utils = new MembershipProductEticketRowUtilities();
            $eticket = $eticket_row_utils->getOneFromParams(['filter__user_id__equal__membership_product' => $client->id]);

            if(isset($eticket->id))
                $sender_user = $eticket->membership_product->operator;

            if($sender_user == null)
            {
                $sender_user = $user_utils->findById($client->parent_id);
            }
        }



        if(isset($sender_user->no_reply_email) && $sender_user->no_reply_email != null)
            return $sender_user->no_reply_email;

        $sender_user = $user_utils->findById(UserModelUtilities::ADMIN_ID);

        return $sender_user->no_reply_email;
    }


    /**
     * @param $user_id
     * @return PostboxMailboxUtilities|null
     */
    public static function getDefaultMailbox($user_id)
    {
        $utils = new PostboxMailboxUtilities();

        return $utils->getOneFromParams([
            'is_default' => 1,
            'user_id' => $user_id
        ]);
    }
}
