<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\File;

class JsonLogger
{

    public static function logInoplaApi($message)
    {
        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/inopla.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }

    public static function logVivaPayments($message)
    {
        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/viva_log.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }

    public static function logHellocash($message)
    {
        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/hellocash_log.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }

    public static function logDimoco($message)
    {



        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/dimoco_log.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }


    public static function logPaypal($message)
    {



        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/paypal_log.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }

    public static function logStripe($message)
    {



        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/stripe_log.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }

    public static function logChatBot($message)
    {



        FileHelper::createFolderIfNotExist(public_path() . '/logs');


        $path = public_path() . '/logs/chat_bot_log_'.date("Ymd").'.json';

        if(!file_exists($path))
        {
            File::put($path, json_encode([]));
        }

        $file = File::get($path);

        $content = [];

        if($file != null)
        {
            $content = json_decode($file, true  );
        }


        $content [] = [
            'datetime' => DateTimeHelper::getCurrentDatetimeSql(),
            'message' => $message
        ];


        File::put($path, json_encode($content, JSON_PRETTY_PRINT));
    }
}
