<?php

namespace RepeatToolkit\Helpers\Traits;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait DbTableTrait
{

    protected function getColumnNameFromAlias($table_name, $column_alias)
    {

        $column_name = substr($column_alias, strpos($column_alias, $table_name) + strlen($table_name) + 1, strlen($column_alias));


        return $column_name;

    }

    protected function getColumnAliasFromTableAndColumnName($table_name, $column_name)
    {
        return $table_name . '_' . $column_name;
    }

    protected function getTableColumnsArray($table_name) : array
    {
        return DB::getSchemaBuilder()->getColumnListing($table_name);
    }

    protected function getTableColumnsFullInfoListing($table_name)
    {
        $tableColumnInfos = DB::select('SHOW FULL COLUMNS FROM ' . $table_name);

        return $tableColumnInfos;
    }

    protected function getColumnFullInfo($table_name, $column_name)
    {
        $all_columns = $this->getTableColumnsFullInfoListing($table_name);

        $obj = new \stdClass();

        if(($column_name == "id"))
        {
            //dd($column_name);
            $obj->Comment = _i("ID");
        }

        if(Str::contains($column_name, ['created_at']))
        {
           //dd($column_name);
            $obj->Comment = _i("Created at");
        }

        if(Str::contains($column_name, ['paid_datetime']))
        {
            //dd($column_name);
            $obj->Comment = _i("Paid");
        }

        if(Str::contains($column_name, ['updated_at']))
        {
            $obj->Comment = _i("Updated at");
        }

        if(Str::contains($column_name, ['start_datetime']))
        {
            $obj->Comment = _i("Start datetime");
        }

        if(Str::contains($column_name, ['start_date']))
        {
            $obj->Comment = _i("Start date");
        }

        if(Str::contains($column_name, ['end_date']))
        {
            $obj->Comment = _i("End date");
        }

        if(Str::contains($column_name, ['status_text']))
        {
            $obj->Comment = _i("Status text");
        }

        if(Str::contains($column_name, ['membership_subscription_id']))
        {
            $obj->Comment = _i("Subscription ID");
        }
        if(Str::contains($column_name, ['user_id']))
        {
            $obj->Comment = _i("Client ID");
        }




        if(isset($obj->Comment))
            return $obj;


        foreach ($all_columns as $column)
            if($column->Field == $column_name)
                return $column;

        return null;
    }

    protected function getTranslatableTableColumnsArray($table_name) : array
    {
        $all_columns = $this->getTableColumnsArray($table_name);

        $filtered_column_names = [];

        foreach ($all_columns as $column_name)
        {
            if(in_array($column_name, ['id', 'created_at', 'updated_at']))
                continue;

            if(Str::contains($column_name, ['_id']))
                continue;

            $filtered_column_names [] = $column_name;
        }

        return $filtered_column_names;
    }

    protected function getTableColumnAliasesArray(array $table_columns_array, $table_name): array
    {
        $aliases_array = [];

        foreach ($table_columns_array as $table_column)
        {
            $aliases_array[$table_name . '.' .$table_column] = $table_name . '_' .$table_column;
        }

        return $aliases_array;
    }




    public function isTranslationColumn($column_alias)
    {
        foreach (config('laravel-gettext')['supported-locales'] as $core_language_id => $locale)
        {
            if(Str::contains($column_alias, ['_translations_' . $core_language_id]))
                return true;
        }

        return false;
    }


    public function isTranslationColumnOfGivenLanguage($column_alias, $core_language_id)
    {

        return Str::contains($column_alias, ["translations_$core_language_id" . '_']);

    }

    public function tableHasColumn($table_name, $column_name)
    {
        $all_columns = $this->getTableColumnsArray($table_name);

        if(in_array($column_name, $all_columns))
        {
            return true;
        }

        return false;
    }
}
