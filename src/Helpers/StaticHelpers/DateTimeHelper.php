<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Modules\Core\Utilities\Models\Standard\UserModelUtilities;
use Modules\Membership\Utilities\Models\Standard\MembershipProductUtilities;
use phpDocumentor\Reflection\Types\Self_;

class DateTimeHelper
{

    public static function getUserPreferredDate($date, $user_id = null)
    {
        if($date == "")
            return "";

        return date('d.m.Y', strtotime($date));
    }


    public static function getTimeFormatted($time)
    {
        $first_part_of_string = substr($time,0,5);
        return $first_part_of_string;
    }

    public static function formatDateSql($date)
    {
        return date('Y-m-d', strtotime($date));
    }

    public static function formatDateTimeSql($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public static function getUserPreferredDatetime($date, $user_id = null)
    {
        return date('d.m.Y - H:i', strtotime($date)) . ' h';
    }

    public static function getUserPreferredDatetimeSimple($date, $user_id = null)
    {
        return date('d.m.Y H:i', strtotime($date)) ;
    }

    public static function getUserPreferredDatetimeWithSeconds($date, $user_id = null)
    {
        return date('d.m.Y - H:i:s', strtotime($date)) . ' h';
    }

    public static function getUserPreferredDatetimeTwoDigitY($date, $user_id = null)
    {
        return date('d.m.y - H:i', strtotime($date)) . ' h';
    }

    public static function getUserPreferredDateTwoDigitY($date, $user_id = null)
    {
        return date('d.m.y', strtotime($date));
    }


    public static function getMicrotimeString()
    {
        $string =  (string) microtime(true);

        $string = str_replace('.', "", $string);

        return $string;
    }

    public static function getAboStartDatesFromNow()
    {


        $start_date = date('01.m.Y');
        $result =  [];

        for($i = 0; $i < 13; $i++)
        {
            if($i == 0)
            {
                if(date('j') > 30)
                {
                    continue;
                }
            }



            $date = date('Y-m-d 00:00:00', strtotime("+$i months", strtotime($start_date)));

            if($date <= '2025-08-01 00:00:00')
                $result[$date] = self::getUserPreferredDatetime($date);

            // $result[$date] = self::getUserPreferredDatetime($date);


        }




        if(isset($result['2023-08-01 00:00:00']))
            unset($result['2023-08-01 00:00:00']);


        /* if(isset($result['01.08.2023 00:00 h']))
             unset($result['01.08.2023 00:00 h']);*/

        return $result;
    }

    public static function getMaxStartDateDtickets()
    {
        return '2025-08-01';
    }

    public static function getAboStartDates($limit_month_day)
    {
        if($limit_month_day < 10)
            $limit_month_day = "0" . $limit_month_day;

        $current_date = date('Y-m-d');
        $limit_date = date("Y-m-" . $limit_month_day);

        if($current_date > date("Y-m-$limit_month_day"))
        {
            $start_date = date("Y-m-01 00:00:00", strtotime('+2 month', time()));
        }

        else if($current_date >= date("Y-m-01"))
        {
            $start_date = date("Y-m-01 00:00:00", strtotime('+1 month', time()));
        }


        else
        {
            $start_date = date("Y-m-01 00:00:00", strtotime('+0 month', time()));
        }


        //dd($start_date);

        $result =  [];

        for($i = 0; $i < 5; $i++)
        {

            $date = date('Y-m-d 00:00:00', strtotime("+$i months", strtotime($start_date)));

            if($date < '2025-05-01 00:00:00')
                $result[$date] = self::getUserPreferredDatetime($date);

           // $result[$date] = self::getUserPreferredDatetime($date);


        }

        if(isset($result['2023-08-01 00:00:00']))
            unset($result['2023-08-01 00:00:00']);

        return $result;

    }

    public static function getAboStartDatesZeroZero($limit_month_day)
    {
        if($limit_month_day < 10)
            $limit_month_day = "0" . $limit_month_day;

        $current_date = date('Y-m-d');
        $limit_date = date("Y-m-" . $limit_month_day);

        if($current_date > date("Y-m-$limit_month_day"))
        {
            $start_date = date("Y-m-01 03:00:00", strtotime('+2 month', time()));
        }

        else if($current_date >= date("Y-m-30"))
        {
            $start_date = date("Y-m-01 03:00:00", strtotime('+1 month', time()));
        }


        else
        {
            $start_date = date("Y-m-01 03:00:00", strtotime('+0 month', time()));
        }


        //dd($start_date);

        $result =  [];

        for($i = 0; $i < 14; $i++)
        {

            $date = date('Y-m-d 03:00:00', strtotime("+$i months", strtotime($start_date)));

            if($date <= '2026-01-01 04:00:00')
                $result[$date] = self::getUserPreferredDatetime($date);

            // $result[$date] = self::getUserPreferredDatetime($date);


        }

        return $result;

    }

    public static function getAboEndDates()
    {
        $current_date = date('Y-m-d');


        if($current_date > date("Y-m-01"))
            $start_date = date("Y-m-01 03:00:00", strtotime('+1 month', time()));
        else
            $start_date = date("Y-m-01 03:00:00", strtotime('+0 month', time()));



        $result =  [];

        for($i = 0; $i < 24; $i++)
        {

            $date = date('Y-m-d 03:00:00', strtotime("+$i months", strtotime($start_date)));

            if($date < '2025-06-01 04:00:00')
                $result[$date] = self::getUserPreferredDatetime($date);


        }

        return $result;
    }

    public static function getMonthsArray1()
    {
        return [
            1 => _i("January"),
            2 => _i("February"),
            3 => _i("March"),
            4 => _i("April"),
            5 => _i("May"),
            6 => _i("Jun"),
            7 => _i("July"),
            8 => _i("August"),
            9 => _i("September"),
            10 => _i("October"),
            11 => _i("November"),
            12 => _i("December"),
        ];
    }

    public static function getMonthsArray()
    {
        return [
            1 => "01.2023",
            2 => "02.2023",
            3 => "03.2023",
            4 => "04.2023",
            5 => "05.2023",
            6 => "06.2023",
            7 => "07.2023",
            8 => "08.2023",
            9 => "09.2023",
            10 => "10.2023",
            11 => "11.2023",
            12 => "12.2023",
        ];
    }

    public static function getNextMonthsArray()
    {
        $current_month = intval(date('n'));

        $months = [];
        for($i = $current_month + 1; $i <= $current_month + 12; $i++)
        {
            $months[$i] = date('m.Y', strtotime('+' . ($i - $current_month) . " months", time()));
        }

        return $months;
    }



    public static function getCurrentDatetimeSql()
    {
        return date('Y-m-d H:i:s');
    }

    public static function getCurrentDateSql()
    {
        return date('Y-m-d');
    }

    public static function getAgeFromBirthDate($birth_date)
    {
        $birth_date = self::formatDateSql($birth_date);

// Create a datetime object using date of birth
        $dob = new \DateTime($birth_date);

// Get current date
        $now = new \DateTime();

// Calculate the time difference between the two dates
        $diff = $now->diff($dob);

// Get the age in years, months and days

        return $diff->y;
    }

    public static function getWeekdaysSelect()
    {
        return [
            1 => _i("Monday"),
            2 => _i("Tuesday"),
            3 => _i("Wednesday"),
            4 => _i("Thursday"),
            5 => _i("Friday"),
            6 => _i("Saturday"),
            7 => _i("Sunday"),
        ];
    }


    public static function needNextTicket($user_id)
    {

        $utils = new UserModelUtilities();

        $user = $utils->findById($user_id);


        if(DateTimeHelper::getCurrentDatetimeSql() < date("Y-m-25 00:00:00"))
            return false;

        return true;





        $current_date = date("Y-m-d");




        $low_limit = date("Y-m-01", strtotime($current_date));


        $product_utils = new MembershipProductUtilities();


        $product = $product_utils->getOneFromParams(['filter__start_date__equal' => $low_limit . " 00:00:00", 'filter__user_id__equal' => $user_id]);



        if(isset($product->id))
            return false;

        if(DateTimeHelper::getCurrentDatetimeSql() < date("Y-m-25 00:00:00"))
            return false;


        return true;




    }


    public static function isADate($value)
    {

        if (!$value) {
            return false;
        }

        if(is_array($value))
            return false;

        if(strlen($value) == 10)
        {
            if(substr_count($value, '.') == 2 || substr_count($value, '-') == 2)
                return true;
        }

        return false;

    }

    public static function getHoursFromTime($time)
    {

        $hours = substr($time, 0, 2);

        return intval($hours);

    }

    public static function getMinutesFromTime($time)
    {
        $hours = substr($time, 3, 2);

        return intval($hours);

    }


    public static function getTotalMinutesFromTime($time)
    {
        $hours = self::getHoursFromTime($time);

        $minutes = $hours * 60;

        $add_minutes = self::getMinutesFromTime($time);

        $minutes += $add_minutes;

        return $minutes;
    }
}
