<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class InvoiceHelper
{


    public static function getBatlerDataTotals($batler_data): array
    {
        $gross_amount = 0;
        $net_amount = 0;
        $vat_amount = 0;

        foreach ($batler_data['item_amount'] as $key => $amount)
        {

            $item_sum = $amount * $batler_data['item_single_price'][$key];


            if($batler_data['show_prices_type'] == 'net')
            {
                $item_net = round($item_sum, 2);
                $item_vat = round(0.01 * $batler_data['item_tax_amount'][$key] * $item_net, 2);
                $item_gross = $item_net + $item_vat;
            }
            else
            {
                $item_gross = round($item_sum, 2);
                $item_net = round($item_gross / (1 + 0.01 *  $batler_data['item_tax_amount'][$key]), 2);
                $item_vat = $item_gross - $item_net;
            }

            $gross_amount += $item_gross;
            $net_amount += $item_net;
            $vat_amount += $item_vat;
        }

        return ['net_amount' => $net_amount, 'vat_amount' => $vat_amount, 'gross_amount' =>$gross_amount];
    }


}
