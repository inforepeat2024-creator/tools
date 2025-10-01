<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class MacAddressHelper
{


    public static function getMacAddress()
    {

        if(UrlHelper::isLocalhost())
        {
            try {
                return substr(shell_exec('getmac'), 159,20);

            }
            catch (\Exception $e)
            {
                return substr(exec('getmac'), 0, 17);
            }

        }
        else
        {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/php74', 'getmac']);
            $process->setWorkingDirectory(base_path());
            $process->run();

            echo $process->getOutput();
        }



        return "";
    }

}
