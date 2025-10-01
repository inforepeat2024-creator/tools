<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Self_;

class DbHelper
{


    public static function processTableInput($table, $input)
    {


        try {


            $cache_key = 17;


            //dd(Cache::get($table . '_column_types' . $cache_key));

            $table_columns = Cache::get($table . '_columns' . $cache_key) != null  ? Cache::get($table . '_columns' . $cache_key) : Schema::getColumnListing($table);




            if(Cache::get($table . '_column_types' . $cache_key) != null)
            {
                $types =    Cache::get($table . '_column_types' . $cache_key);
            }
            else
            {
                $types = [];
                foreach ($table_columns as $table_column)
                {
                    $types[$table_column] = Schema::getColumnType($table, $table_column);
                }
            }


            Cache::forever($table . '_columns'. $cache_key, $table_columns);
            Cache::forever($table . '_column_types'. $cache_key, $types);




            $input = self::addValuesToUncheckedCheckboxes($table, $input, $table_columns,$types);
            $input = self::processDoubleFields($table, $input, $table_columns,$types);
            $input = self::processDateFields($table, $input, $table_columns,$types);
            $input = self::processDatetimeFields($table, $input, $table_columns,$types);




        }
        catch (\Exception $e)
        {

        }




        return $input;
    }

    public static function removeTableNameFromInput($table_name, $input)
    {
        $new_input = [];
        foreach ($input as $key => $val)
        {
            $new_input[str_replace($table_name . "_", "", $key)] = $val;
        }

        return $new_input;
    }

    public static function processDateFields($table, $input, $table_columns = [], $types = [])
    {


        foreach ($table_columns as $column)
        {

            try {
                //dd(Schema::getColumnType($table, $column));
                if(!in_array($column, ['created_at', 'updated_at', 'id', 'order']) && in_array($types[$column], ["date"]))
                {
                    if(isset($input[$column]))
                        $input[$column] = DateTimeHelper::formatDateSql(($input[$column]));
                }
            }
            catch (\Exception $e)
            {

            }




        }

        return $input;
    }

    public static function processDatetimeFields($table, $input, $table_columns = [], $types = [])
    {
        if($table_columns == [])
            $table_columns = Schema::getColumnListing($table);


        foreach ($table_columns as $column)
        {
            //dd(Schema::getColumnType($table, $column));
            if(!in_array($column, ['created_at', 'updated_at', 'id', 'order']) && in_array($types[$column], ["datetime"]))
            {
                if(isset($input[$column]))
                    $input[$column] = DateTimeHelper::formatDateTimeSql(($input[$column]));
            }


        }

        return $input;
    }

    public static function processDoubleFields($table, $input, $table_columns = [], $types = [])
    {

        if($table_columns == [])
            $table_columns = Schema::getColumnListing($table);





        foreach ($table_columns as $column)
        {
            //dd(Schema::getColumnType($table, $column));

            try {

                if(!in_array($column, ['created_at', 'updated_at', 'id', 'order']) && in_array($types[$column], ["float", "double", 'decimal']))
                {
                    if(isset($input[$column]))
                        $input[$column] = FormattedNumberHelper::unmaskNumber($input[$column]);
                }
            }
            catch (\Exception $e)
            {

            }




        }

        return $input;
    }

    public static function addValuesToUncheckedCheckboxes($table, $input, $table_columns = [], $types = [])
    {

        if($table_columns == [])
            $table_columns = Schema::getColumnListing($table);







        foreach ($table_columns as $column)
        {
            try {
                if(  !in_array($column, ['created_at', 'updated_at', 'id', 'order']) && in_array($types[$column], ["integger", "boolean"]))
                {
                    if(!isset($input[$column]) && isset($input['include_' . $column]))
                        $input[$column] = 0;
                }
            }
            catch (\Exception $e)
            {

            }
            //dd(Schema::getColumnType($table, $column));



        }

       // dd($input);

        return $input;
    }

}
