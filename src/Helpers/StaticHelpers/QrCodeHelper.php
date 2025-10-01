<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Modules\Core\Utilities\Api\QRCode\Factory\QRCodeApiFactory;

class QrCodeHelper
{


    public static function generateQrCode($data, $style = null)
    {


        $qr = QRCodeApiFactory::make();

        return $qr->getCode($data, $style);
    }

}
