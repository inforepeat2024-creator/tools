<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Modules\Banqu\Entities\BanquTransaction;

class ModelHelper
{

    //ovde imamo naziv kolone ali kodiran ovako: name__membership_product.banqu_transaction.banqu_account.banqu_account_type.translation
    public static function getModelPropertyValue($model, $property_with_dots)
    {

        try
        {
            $split_on_column_relation = explode('__', $property_with_dots);

            $final_column = $split_on_column_relation[0];


            $start_model = $model;

            if(isset($split_on_column_relation[1]))
            {
                $split = explode('.', $split_on_column_relation[1]);



                foreach ($split as $property)
                {
                    if(isset($start_model->{$property}))
                    {



                            $start_model = $start_model->{$property};
                    }
                }
            }


            if(in_array($final_column, ['net_amount', 'gross_amount', 'fee_amount', 'vat_amount']))
            {
                return  /*FormattedNumberHelper::maskNumber*/($start_model->{$final_column} ?? 0);
            }

            if(in_array($final_column, ['start_date', 'end_date', 'date', 'paid_datetime', 'birth_date']))
            {


                return DateTimeHelper::getUserPreferredDate($start_model->{$final_column} ?? "");
            }

            if(in_array($final_column, [/* 'paid_datetime',*/ 'created_at', 'start_datetime', 'end_datetime']))
            {
                if($final_column == 'end_datetime')
                {
                    if($start_model->always_valid == 1)
                        return _i("Always valid");
                }

                return DateTimeHelper::getUserPreferredDatetime($start_model->{$final_column} ?? "");
            }



           /* if($final_column == 'full_name')
                return utf8_encode($start_model->{$final_column});*/

            if($final_column == 'full_name' && $start_model instanceof BanquTransaction)
                return $start_model->{$final_column} ??  "Online";
            else
            return $start_model->{$final_column} ??  "";
        }
        catch (\Exception $e)
        {
            return "";
        }



    }

}
