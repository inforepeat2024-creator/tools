<?php

namespace RepeatToolkit\Abstracts;

use RepeatToolkit\Abstracts\AbstractModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RepeatToolkit\Helpers\StaticHelpers\DateTimeHelper;
use RepeatToolkit\Helpers\StaticHelpers\TextHelper;
use RepeatToolkit\Helpers\Traits\DbTableTrait;
use RepeatToolkit\Helpers\Traits\LocaleTrait;
use RepeatToolkit\Helpers\Traits\ModuleTrait;


abstract class AbstractModelUtilities
{

    use DbTableTrait;
    use ModuleTrait;

    use LocaleTrait;




    /**
     * @var AbstractModel
     */
    protected $model;

    protected $import_from_admin_flag = false;

    protected $translations_table_name;

    protected $files_parent_folder_id = null;

    protected $default_order_bys = ['id' => 'desc'];


    public $add_user_id_filter = true;


    protected $api_resource;

    protected $view_resource;


    /**
     * @var string[]
     */
    protected $relations = [];


    /**
     * @var AbstractModelUtilities
     */
    protected $translation_model_utils;


    protected $aggregates = [];



    protected $search_relations = [];



    public function __construct()
    {


    }

    public function getViewColumns()
    {
        $columns = [

            ['label' => 'Motor', 'key' => 'name'],
            ['label' => 'Dodatno', 'key' => 'add'],
            ['label' => 'Akcije', 'key' => 'actions'],

        ];

        return $columns;
    }

    public function getAdditionalPartials()
    {
        return [];
    }

    /**
     * @return AbstractModelUtilities
     */
    public function getTranslationModelUtils(): AbstractModelUtilities
    {
        return $this->translation_model_utils;
    }

    /**
     * @param AbstractModelUtilities $translation_model_utils
     */
    public function setTranslationModelUtils(AbstractModelUtilities $translation_model_utils): void
    {
        $this->translation_model_utils = $translation_model_utils;
    }



    /**
     * @return mixed
     */
    public function getTranslationsTableName()
    {
        return $this->translations_table_name;
    }

    /**
     * @param mixed $translations_table_name
     */
    public function setTranslationsTableName($translations_table_name): void
    {
        $this->translations_table_name = $translations_table_name;
    }










    public function getTableName()
    {
        $model = new $this->model();

        return $model->getTable();
    }

    private function alterQueryWithAggregates($query, $aggregates = [])
    {
        foreach ($this->aggregates as $aggregate)
        {

            $query = $query->withAggregate($aggregate->getRelationName(), $aggregate->getColumnName());

        }

        /*  foreach ($aggregates as $aggregate)
          {
              $query = $query->withAggregate($aggregate->getRelationName(), $aggregate->getColumnName());
          }*/




        return $query;
    }


    private function alterQueryWithTableSearchParams($query)
    {
        try {
            $search_term = null;

            if(isset($_POST['datatable_search_input']) && $_POST['datatable_search_input'] != "")
            {
                $search_term = $_POST['datatable_search_input'];
            }

            if(isset($_REQUEST['datatable_search_input']) && $_REQUEST['datatable_search_input'] != "")
            {
                $search_term = $_REQUEST['datatable_search_input'];
            }





            if($search_term != null)
            {



                $query = $query->where(function ($query) use ($search_term) {

                    /* try {
                         $query->whereHas("translations", function ($query){
                             $query->where('name', 'like', "%". $_REQUEST['datatable_search_input'] ."%");
                         });
                     }
                     catch (\Exception $e)
                     {

                     }*/

                    $query->where(function ($query) use ($search_term){

                        $total_columns = $this->getTableColumnsArray($this->getTableName());

                        foreach ($total_columns as $key => $column)
                        {
                            if($column == 'imported_data')
                            {
                                unset($total_columns[$key]);
                            }
                        }


                        foreach ($total_columns as $key => $column)
                        {
                            if($key == 0)
                                $query
                                    ->where($column, 'like', "%". $search_term ."%");
                            else
                                $query
                                    ->orWhere($column, 'like', "%". $search_term ."%");

                        }
                    });




                    $query->orWhere(function ($query) use ($search_term){




                        $relations = $this->search_relations;












                        if(!isset($columns_to_search))
                            $columns_to_search = [];





                        foreach ($relations as $key => $relation)
                        {



                            //Log::info("datatable search: " . $relation);






                            try {



                                if($key == 0)
                                {
                                    $query->whereHas($relation, function ($query) use ($search_term, $columns_to_search, $relation){

                                        if(count($columns_to_search) > 0)
                                        {
                                            $iterate_columns = array_intersect($this->getTableColumnsArray($query->getModel()->getTable()), $columns_to_search);
                                        }
                                        else
                                            $iterate_columns = $this->getTableColumnsArray($query->getModel()->getTable());


                                        //  dd($iterate_columns);


                                        foreach ($iterate_columns as $key1 => $column)
                                        {
                                            //ime i prezime zajedno
                                            if($column == 'first_name' || $column == 'last_name')
                                            {



                                                if($key1 == 0)
                                                    $query
                                                        ->where([[DB::raw('CONCAT(first_name ," ",last_name)'), 'like', "%". $search_term ."%"], /*[$column, "!=", ""]*/]);
                                                else
                                                    $query
                                                        ->orWhere([[DB::raw('CONCAT(first_name ," ",last_name)'), 'like', "%". $search_term ."%"], /*[$column, "!=", ""]*/]);
                                            }
                                            else
                                            {
                                                if($key1 == 0)
                                                    $query
                                                        ->where([[$column, 'like', "%". $search_term ."%"]/*, [$column, "!=", ""]*/]);
                                                else
                                                    $query
                                                        ->orWhere([[$column, 'like', "%". $search_term ."%"]/*, [$column, "!=", ""]*/]);
                                            }


                                        }

                                    });
                                }
                                else
                                {
                                    $query->orWhereHas($relation, function ($query) use ($search_term, $columns_to_search, $relation){




                                        if(count($columns_to_search) > 0)
                                        {
                                            $iterate_columns = array_intersect($this->getTableColumnsArray($query->getModel()->getTable()), $columns_to_search);
                                        }
                                        else
                                            $iterate_columns = $this->getTableColumnsArray($query->getModel()->getTable());

                                        foreach ($iterate_columns as $key1 => $column)
                                        {

                                            /*  if($column == 'id' && !in_array($query->getModel()->getTable(), ['users']))
                                              {
                                                  continue;
                                              }*/


                                            if($column == 'first_name' || $column == 'last_name')
                                            {



                                                if($key1 == 0)
                                                    $query
                                                        ->where([[DB::raw('CONCAT(first_name ," ",last_name)'), 'like', "%". $search_term ."%"]/*, [$column, "!=", ""]*/]);
                                                else
                                                    $query
                                                        ->orWhere([[DB::raw('CONCAT(first_name ," ",last_name)'), 'like', "%". $search_term ."%"]/*, [$column, "!=", ""]*/]);
                                            }
                                            else
                                            {
                                                if($key1 == 0)
                                                    $query
                                                        ->where([[$column, 'like', "%". $search_term ."%"]/*, [$column, "!=", ""]*/]);
                                                else
                                                    $query
                                                        ->orWhere([[$column, 'like', "%". $search_term ."%"]/*, [$column, "!=", ""]*/]);
                                            }
                                        }

                                    });
                                }



                            }
                            catch (\Exception $e)
                            {
                                throw $e;
                            }
                        }
                    });




                });
            }




            return $query;
        }
        catch (\Exception $e)
        {
            throw $e;
        }


    }

    private function alterQueryWithOrderBys($query, array $order_by = [])
    {






        if(count($order_by) > 0)
        {
            foreach ($order_by as $column => $direction)
            {

                if(!TextHelper::stringContains($column, ['.']))
                    $query = $query->orderBy($column, $direction);
                else
                {
                    try
                    {
                        $split = explode('.', $column);

                        $relations = [];
                        $column = null;
                        foreach ($split as $key => $part)
                        {
                            if($key != (count($split) - 1))
                                $relations [] = $part;
                            else
                                $column = $part;
                        }

                        $query = $query->OrderByRelations($relations, $column, $direction);
                    }
                    catch (\Exception $e)
                    {

                    }

                }
            }
        }
        else
        {
            foreach ($this->default_order_bys as $key => $val)
            {

                if(!TextHelper::stringContains($key, ['.']))
                    $query->orderBy($key, $val);
                else
                {
                    try {
                        $split = explode('.', $key);
                        $query = $query->orderByRelated($split[0], $split[1], $val);
                    }
                    catch (\Exception $e)
                    {

                    }

                }
            }

        }

        return $query;
    }

    private function alterQueryWithLimitOffset($query, $limit = null, $offset = null)
    {

        if($limit != null)
            $query = $query->limit($limit);

        if($offset != null)
            $query = $query->offset($offset);

        return $query;
    }


    private function convertFilterValueBasedOnOperator($operator_string, $value, $column)
    {
        $converted_value = $value;

        switch ($operator_string)
        {
            case "like":
            case "not_like":
                $converted_value = "%" . $value . "%";
                break;

            case "is_null":
            case "not_null":



                $converted_value = "";
                break;

            case "in":

                if(!is_array($value))
                    $converted_value = json_decode($value);
                else
                    $converted_value = $value;

                break;



            default:
                if($this->isDate($value))
                {
                    $value = date('Y-m-d', strtotime($value));

                    if(Str::contains($column, ['datetime']))
                    {

                        if(Str::contains($operator_string, ['greater']))
                            $value .= " 00:00:00";

                        if(Str::contains($operator_string, ['less']))
                            $value .= " 23:59:59";

                        //dd($operator_string, $value, $column);
                    }
                }
                $converted_value =  $value;
                break;


        }



        return $converted_value;
    }

    private function isDate($value)
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

    private function isDatetime($value)
    {
        if (!$value) {
            return false;
        }

        if(is_array($value))
            return false;

        if(strlen($value) == 19)
        {
            if(substr_count($value, '.') == 2 && substr_count($value, ':') == 2)
                return true;

            if(substr_count($value, '-') == 2 && substr_count($value, ':') == 2)
                return true;
        }

        return false;

    }

    private function convertOperatorStringToSqlOperator($operator_string)
    {
        $converted_operator = "=";

        switch ($operator_string)
        {
            case "equal":
                $converted_operator = "=";
                break;
            case "like":
                $converted_operator = "like";
                break;

            case "not_like":
                $converted_operator = "not like";
                break;

            case "is_null":
                $converted_operator = "IS NULL";
                break;

            case "not_null":



                $converted_operator = "IS NOT NULL";
                break;

            case "greater":
                $converted_operator = ">";
                break;
            case "greater_equal":
                $converted_operator = ">=";
                break;
            case "less":
                $converted_operator = "<";
                break;
            case "less_equal":
                $converted_operator = "<=";
                break;
            case 'not_equal':
                $converted_operator =  '!=';
                break;
            case 'in':
                $converted_operator =   'in';
                break;
        }

        return $converted_operator;
    }
    private function alterQueryWithFilterParams($query, array $params)
    {


        foreach ($params as $key => $value)
        {
            if($value === "")
                continue;

            if(Str::contains($key, ['filter__']))
            {

                if(Str::contains($key, ['custom_filter__']))
                {


                    if($key == 'filter__custom_filter__owner_shared')
                    {


                        $query = $query->where(function ($query) use ($key, $value){
                            $query->whereHas("postbox_received_post_users", function ($query) use ($key, $value){
                                $query->whereIn('core_access_role_id', json_decode($value, true));
                            })->whereHas('postbox_mailbox', function ($query){
                                $query->where("user_id", Auth::user()->id);
                            });

                        });
                    }

                    if($key == 'filter__custom_filter__client_shared')
                    {


                        $query = $query->where(function ($query) use ($key, $value){
                            $query->whereHas("postbox_received_post_users", function ($query) use ($key, $value){
                                $query->whereIn('core_access_role_id', json_decode($value, true));
                            })->whereHas('postbox_mailbox', function ($query){
                                $query->where("user_id", "!=" , Auth::user()->id);
                            });

                        });
                    }

                    if($key == 'filter__custom_filter__has_active_dticket')
                    {
                        if($value == 2)
                        {
                            $query = $query->where(function ($query){
                                $query->whereDoesntHave('client.membership_products', function ($query){
                                    $query->where('end_date', '>', /*DateTimeHelper::getCurrentDatetimeSql()*/ '2023-07-02 00:00:00')->whereHas('membership_product_eticket_row', function ($query){
                                        $query->where("id", ">", 0);
                                    });
                                })
                                    /*   ->orWhereHas("client.membership_products", function ($query) {
                                           $query->where('end_date', '>',   '2023-07-02 00:00:00')->whereDoesntHave("membership_product_eticket_row", function ($query){
                                               $query->where("id", '>=', 1);
                                           });
                                       })*/

                                ;
                            })

                                // ->withCount(['client.membership_subscriptions' => function($query){

                                //  $query->whereHas("banqu_transaction", function ($query){
                                //    $query->where("gross_amount", ">", 0);
                                //});*/

                                //    }])
                                // ->having("client.membership_subscriptions_count", '>', 0)


                            ;

                        }
                        else if($value == 1)
                        {
                            $query = $query->whereHas('client.dticket_transaction', function ($query){

                                //dd('dsf');

                                $query->where('id', '>', 0);


                                /*$query->whereHas('membership_product_eticket_row', function ($query){
                                    $query->where('id', '>', 0);
                                });*/

                                /* if(\Modules\Core\Utilities\Models\Standard\UserModelUtilities::getUserIdFromUrl() != 2)
                                 {
                                     $query->where('operator_id', \Modules\Core\Utilities\Models\Standard\UserModelUtilities::getUserIdFromUrl());
                                 }*/
                            });
                        }

                        else if($value == 3)
                        {
                            $query = $query->withCount(['client.membership_products' => function($query){

                                $query->whereHas("banqu_transaction", function ($query){
                                    $query->where("gross_amount", ">", 0);
                                });

                            }])->having("client.membership_products_count", '>=', 3);
                        }


                        else if($value == 4)
                        {

                            $query->whereDoesntHave("client.banqu_transactions", function ($query){
                                $query->where("id", ">", 0);
                            });

                            /* $query = $query->withCount(['client.membership_subscriptions' => function($query){



                             }])->having("client.membership_subscriptions_count", '=', 0);*/
                        }

                        else if($value == 5)
                        {
                            $query = $query->whereHas('client', function ($query){
                                $query->whereNotNull('dticket_box_number') ;

                            });
                        }


                        /*   else if($value == 2)
                           {
                               $query = $query->whereHas('membership_products', function ($query){
                                   $query->where('start_date', '<', DateTimeHelper::getCurrentDatetimeSql());
                               });
                           }*/


                    }

                    if($key == 'filter__custom_filter__has_at_least_one_dticket')
                    {


                        try {
                            $query = $query->whereHas('client.membership_products.membership_product_eticket_row', function ($query){
                                $query->where('id', '>', 1/*DateTimeHelper::getCurrentDatetimeSql()*/)
                                ;
                            });
                        }
                        catch (\Exception $e)
                        {

                        }



                    }

                    if($key == 'filter__custom_filter__my_contacts')
                    {


                        //dd('dsf');


                        $query = $query->whereHas('core_user_contact_person', function ($query){
                            $query->where('contact_person_id', Auth::user()->id);
                            ;
                        });

                    }


                    if($key == 'filter__custom_filter___eticket_date__greater_equal')
                    {
                        if(isset($params['filter__custom_filter__eticket_date_type']) && $params['filter__custom_filter__eticket_date_type'] == 2)
                        {
                            $query = $query->whereHas('membership_product.banqu_transactions', function ($query) use ($value){



                                $query
                                    ->where('paid_datetime', '>=', DateTimeHelper::formatDateSql($value) . " 00:00:00" )
                                ;
                            });
                        }
                        else
                        {
                            $query = $query->whereHas('membership_product', function ($query) use ($value){




                                $query
                                    ->where('start_date', '>=', DateTimeHelper::formatDateTimeSql($value))
                                    ->where('end_date', '>=', DateTimeHelper::formatDateTimeSql($value))
                                ;
                            });
                        }


                    }

                    if($key == 'filter__custom_filter___eticket_date__less_equal')
                    {
                        if(isset($params['filter__custom_filter__eticket_date_type']) && $params['filter__custom_filter__eticket_date_type'] == 2)
                        {
                            $query = $query->whereHas('membership_product.banqu_transactions', function ($query) use ($value){

                                //dd( DateTimeHelper::formatDateSql($value) . " 23:59:59");
                                $query
                                    ->where('paid_datetime', '<=', DateTimeHelper::formatDateSql($value) . " 23:59:59")
                                ;
                            });
                        }
                        else
                        {
                            $query = $query->whereHas('membership_product', function ($query) use ($value){
                                $query
                                    //->where('end_date', '>=', DateTimeHelper::formatDateTimeSql($value))
                                    ->where('end_date', '<=', str_replace("00:00:00", "03:00:00", DateTimeHelper::formatDateTimeSql($value)))
                                ;
                            });
                        }


                    }



                    if($key == 'filter__user_sub_valid_date__greater_equal')
                    {

                        $query = $query->whereHas('membership_subscriptions', function ($query) use ($value){
                            $query
                                ->where('start_datetime', '<=', DateTimeHelper::formatDateTimeSql($value))
                                ->where('end_datetime', '>', DateTimeHelper::formatDateTimeSql($value))

                            ;
                        });

                    }

                    if($key == 'filter__user_sub_valid_date__less_equal')
                    {
                        //$get_params['filter__end_datetime__greater_equal'] = $input['filter__valid_date__less_equal'];

                        $query = $query->whereHas('membership_subscriptions', function ($query) use ($value){
                            $query
                                ->where('end_datetime', '<=', DateTimeHelper::formatDateTimeSql($value))

                            ;
                        });

                    }










                }
                else
                {
                    $split = explode("__", $key);

                    $column = $split[1];
                    $operator = $split[2];



                    $relation = $split[3] ?? null;




                    if($relation != null)
                    {

                        if($operator == 'doesnt_have')
                        {
                            $query = $query->whereDoesntHave($relation, function ($query){
                                $query->where("id", ">=", 1);
                            });
                        }
                        else
                        {
                            $query = $query->whereHas($relation, function ($query) use ($column, $operator, $value){

                                if($operator == "in")
                                {


                                    $query->whereIn($column, $this->convertFilterValueBasedOnOperator($operator, $value, $column));
                                }


                                else if($operator == 'not_null')
                                {
                                    $query->whereNotNull($column);
                                }

                                else if($operator == 'is_null')
                                {
                                    $query->whereNull($column);
                                }

                                else
                                {
                                    if($column == 'first_name')
                                    {
                                        $explode = explode(" ", $value);

                                        $first_name = $explode[0];

                                        $last_name = "";

                                        foreach ($explode as $key => $val)
                                        {
                                            if($key > 0)
                                                $last_name .= $val;
                                        }


                                        $query->where(function ($query) use($value, $first_name, $last_name){

                                            if($last_name != "")
                                            {
                                                $query->where('first_name', 'like', "%$first_name%")->where('last_name', 'like', "%$last_name%");
                                            }
                                            else
                                            {
                                                if($last_name != "")
                                                {
                                                    $query
                                                        ->where('first_name', 'like', "%$first_name%")
                                                        ->orWhere('last_name', 'like', "%$last_name%")
                                                        ->orWhere('first_name', 'like', "%$last_name%")
                                                        ->orWhere('last_name', 'like', "%$first_name%")
                                                        ->orWhere('company_name', 'like', "%$first_name%")
                                                        ->orWhere('company_name', 'like', "%$last_name%")
                                                    ;
                                                }
                                                else
                                                {
                                                    $query
                                                        ->where('first_name', 'like', "%$first_name%")
                                                        ->orWhere('last_name', 'like', "%$first_name%")
                                                        ->orWhere('company_name', 'like', "%$first_name%")


                                                    ;
                                                }
                                            }

                                        });
                                    }
                                    else
                                    {

                                        $query->where($column, $this->convertOperatorStringToSqlOperator($operator), $this->convertFilterValueBasedOnOperator($operator, $value, $column));
                                    }

                                }


                            });
                        }


                    }
                    else
                    {

                        if($operator == "in")
                        {


                            $query->whereIn($column, $this->convertFilterValueBasedOnOperator($operator, $value, $column));

                        }

                        else if($operator == 'not_null')
                        {
                            $query->whereNotNull($column);
                        }

                        else if($operator == 'is_null')
                        {
                            $query->whereNull($column);
                        }

                        else
                        {
                            if($key == 'filter__end_datetime__greater_equal')
                            {
                                $query->where(function ($query) use ($column, $operator, $value){
                                    $query = $query->where($column, $this->convertOperatorStringToSqlOperator($operator), $this->convertFilterValueBasedOnOperator($operator, $value, $column))
                                        ->orWhere("always_valid", 1);
                                    ;
                                });

                            }

                            else if($column == 'first_name')
                            {
                                $explode = explode(" ", $value);

                                $first_name = $explode[0];

                                $last_name = "";

                                foreach ($explode as $key1 => $val)
                                {
                                    if($key1 > 0)
                                        $last_name .= $val;
                                }


                                $query->where(function ($query) use($value, $first_name, $last_name){

                                    if($last_name != "")
                                    {
                                        $query->where('first_name', 'like', "%$first_name%")->where('last_name', 'like', "%$last_name%");
                                    }
                                    else
                                    {
                                        if($last_name != "")
                                        {
                                            $query
                                                ->where('first_name', 'like', "%$first_name%")
                                                ->orWhere('last_name', 'like', "%$last_name%")
                                                ->orWhere('first_name', 'like', "%$last_name%")
                                                ->orWhere('last_name', 'like', "%$first_name%")
                                            ;
                                        }
                                        else
                                        {
                                            $query
                                                ->where('first_name', 'like', "%$first_name%")
                                                ->orWhere('last_name', 'like', "%$first_name%")


                                            ;
                                        }
                                    }

                                });
                            }

                            else
                            {
                                $query = $query->where($column, $this->convertOperatorStringToSqlOperator($operator), $this->convertFilterValueBasedOnOperator($operator, $value, $column));
                            }


                        }


                    }
                }


            }
            else
            {
                $query = $query->where($key, '=', $value);
            }
        }


        return $query;
    }

    public function getBaseQuery(array $params = [], array $order_by = [], array $aggregates = [],$limit = null, $offset = null)
    {
        $query = $this->model::where($this->getTableName() . '.id', '>=', 0)->with($this->relations);




        $query = $this->alterQueryWithAggregates($query, $aggregates);

        $query = $this->alterQueryWithFilterParams($query, $params);

        $query = $this->alterQueryWithTableSearchParams($query);

        $query = $this->alterQueryWithOrderBys($query, $order_by);


        $query = $this->alterQueryWithLimitOffset($query, $limit, $offset);

        return $query;
    }

    public function getColumnsFromParams(array $columns, array $params = [], array $order_by = [], array $aggregates = [],$limit = null, $offset = null)
    {
        $query = $this->getBaseQuery($params, $order_by, $aggregates, $limit, $offset);


        //dd($query->toSql());
        return $query->select($columns)->get();
    }

    public function getAllFromParams(array $params = [], array $order_by = [], array $aggregates = [],$limit = null, $offset = null)
    {

        $query = $this->getBaseQuery($params, $order_by, $aggregates, $limit, $offset);


        return $query->get();
    }

    public function orderByRelationships($result, $order_by)
    {
        foreach ($order_by as $column => $order) {
            if(TextHelper::stringContains($column, ['.']))
            {
                $split = explode('.', $column);

                if($order == 'desc')
                {
                    $result = $result->sortByDesc(function ($item) use ($split) {
                        return $item->{$split[0]}->{$split[1]};
                    });
                }
                else
                {
                    $result = $result->sortBy(function ($item) use ($split) {
                        return $item->{$split[0]}->{$split[1]};
                    });
                }

            }
        }

        return $result;
    }

    public function getAllPaginate(array $params, $limit, array $order_by = [], array $aggregates = [], $group_by = null)
    {



        $query = $this->getBaseQuery($params, $order_by, $aggregates);

        // dd($query->toRawSql());

        $result = $query->paginate($limit);


        //$result = $this->orderByRelationships($result, $order_by);


        return $result;
    }



    public function getCountFromParams(array $params = [], array $order_by = [], array $aggregates = [],$limit = null, $offset = null)
    {

        $query = $this->getBaseQuery($params, $order_by, $aggregates, $limit, $offset);


        return $query->count();
    }

    public function getAllForSelect(array $params = [], array $order_by = [], array $aggregates = [],$limit = null, $offset = null)
    {

        $query = $this->getBaseQuery($params, $order_by, $aggregates, $limit, $offset);


        return $this->formatSelectRows($query->get());
    }

    protected function formatSelectRows($rows)
    {
        $array = [];
        foreach ($rows as $row)
        {
            try
            {
                $array[$row->id] = $row->translation->name ?? $row->full_name ?? $row->name ?? "";



            }
            catch (\Exception $e)
            {
                $array[$row->id] = $row->name;
            }


        }

        return $array;
    }


    public function pluckColumnFromParams($column, array $params = [], array $order_by = [], array $aggregates = [],$limit = null, $offset = null)
    {
        $query = $this->getBaseQuery($params, $order_by, $aggregates, $limit, $offset);

        return $query->pluck($column)->toArray();
    }

    public function getOneFromParams(array $params = [], array $order_by = [], array $aggregates = [])
    {
        $query = $this->getBaseQuery($params, $order_by, $aggregates);


        return $query->first();
    }


    public function findById($id)
    {
        $query = $this->getBaseQuery(['id' => $id]);


        return $query->first();
    }


    public function createFromParams(array $params)
    {

        $params = $this->beforeModelCreate($params);






        $model = $this->model::create($params);

        /* if($this instanceof \Modules\Core\Utilities\Models\Standard\UserModelUtilities)
         {
             dd($model);
         }*/

        $this->afterModelCreate($model);

        return $model;

    }

    public function beforeModelCreate($params)
    {


        return $params;
    }

    public function getTableActions()
    {
        return [];
    }





    public function getTableActionsSimple()
    {
        $actions =  [];
        foreach ($this->getTableActions() as $action)
        {

            foreach ($action['actions'] as $key => $val)
                $actions[$key]   = $val;

        }

        return $actions;
    }

    public function afterModelCreate($model)
    {
        $this->dealWithDefaultRow($model);
    }

    public function updateFromParams(array $search_params, array $update_params)
    {
        $models = $this->getAllFromParams($search_params);

        foreach ($models as $model)
        {
            $update_params = $this->beforeModelUpdate($model, $update_params);
            $model->update($update_params);
            $this->afterModelUpdate($model->fresh());
        }

    }

    public function beforeModelUpdate($model, $params)
    {
        return $params;
    }



    public function afterModelUpdate($model)
    {

        $this->dealWithDefaultRow($model);

    }


    public function deleteFromParams(array $search_params)
    {


        $models = $this->getAllFromParams($search_params);

        foreach ($models as $model)
        {
            $this->beforeModelDelete($model);
            $model->delete();
            $this->afterModelDelete($model);
        }
    }

    public function beforeModelDelete($model)
    {

    }

    public function afterModelDelete($model)
    {

        $this->dealWithDefaultRow($model);

    }



    public function getDefaultUserColumnsCollection()
    {
        return $this->getTotalColumnsCollection();
    }




    public function getTotalColumnsCollection()
    {

        return [];


    }

    public function getModelShowColumnsCollection()
    {
        return $this->getTotalColumnsCollection();
    }

    public function getTwoColumnAsideViewObj()
    {
        return new \stdClass();
    }

    public function getTwoColumnAsideView()
    {

        $view_path = $this->getModuleName() . "::" . $this->getTableName() . '.two_columns_aside';

        if(!UrlHelper::currentUrlContains('two-column'))
            $view_path = $this->getModuleName() . "::" . $this->getTableName() . '.two_columns_aside_view';

        if(view()->exists($view_path))
            return view($view_path, ['view_obj' => $this->getTwoColumnAsideViewObj()])->render();

        return "";
    }


    public function getFormInputs($model_id = null): array
    {
        return [];
    }

    public function getValidationRules()
    {
        $validation_rules = [];

        foreach ($this->getFormInputs() as $form_input)
        {
            if(Str::contains(strtolower(get_class($form_input)), ['translatable']))
            {
                foreach ($form_input->getValidatorRules() as $validator)
                {
                    $validation_rules[$form_input->getGivenLanguageColumnAlias($this->getCurrentLanguageId())] [] = $validator->getServerSideSlug();
                }
            }
            else
            {
                foreach ($form_input->getValidatorRules() as $validator)
                {
                    $validation_rules[$form_input->getName()] [] = $validator->getServerSideSlug();
                }
            }


        }



        return $validation_rules;
    }




    public function copyModel($id, array $change_fields)
    {


        $source_model = $this->model::find($id);

        $new_model = $source_model->replicate();

        foreach ($change_fields as $key => $value)
        {
            $new_model->{$key} = $value;
        }


        $new_model->save();

        try {
            if(isset($source_model->translations))
            {
                foreach ($source_model->translations as $translation)
                {
                    $new_translation = $translation->replicate();

                    if(isset($translation->core_article_id))
                        $new_translation->core_article_id = $new_model->id;

                    $new_translation->save();
                }
            }
        }
        catch (\Exception $e)
        {

        }


        try {
            $this->copyRelatedModels($source_model, $new_model);
        }
        catch (\Exception $e)
        {

        }

        return $new_model;
    }

    public function copyRelatedModels($source_model, $new_model = null)
    {

    }

    public static function getModelIcon($id, $class = null)
    {
        return "";
    }

    public function autocomplete($term, $user_id = null, $params = [])
    {


        return [];

    }


    public function autocompleteTags($term, $user_id = null, $params = [])
    {


        return [];

    }

    public function toggleActive($id)
    {
        $model = $this->findById($id);


        $this->updateFromParams(['id' => $id], ['is_active' => ($model->is_active + 1) % 2]);
    }

    public function toggleLock($id)
    {

        return $this->findById($id);

    }

    public function toggleFavorite($id)
    {
        $model = $this->findById($id);


        $this->updateFromParams(['id' => $id], ['is_favorite' => ($model->is_favorite + 1) % 2]);

        return $this->findById($id);
    }

    public function toggleColumn($id, $column)
    {
        $model = $this->findById($id);


        $this->updateFromParams(['id' => $id], [$column => ($model->{$column} + 1) % 2]);

        return $this->findById($id);
    }

    public function getReceipt($id)
    {
        return "";
    }

    public function storno($id)
    {
        return 1;
    }



    /**
     * @return bool
     */
    public function isImportFromAdminFlag(): bool
    {
        return $this->import_from_admin_flag;
    }

    /**
     * @param bool $import_from_admin_flag
     */
    public function setImportFromAdminFlag(bool $import_from_admin_flag): void
    {
        $this->import_from_admin_flag = $import_from_admin_flag;
    }

    /**
     * @return string[]
     */
    public function getDefaultOrderBys(): array
    {
        return $this->default_order_bys;
    }


    public function getApiResource()
    {
        return $this->api_resource;
    }


    public function setApiResource($api_resource): void
    {
        $this->api_resource = $api_resource;
    }

    /**
     * @return mixed
     */
    public function getViewResource()
    {
        return $this->view_resource;
    }

    /**
     * @param mixed $view_resource
     */
    public function setViewResource($view_resource): void
    {
        $this->view_resource = $view_resource;
    }





    public function getRelations()
    {
        return $this->relations ;
    }

    public function setRelations(array $relations = [])
    {
        $this->relations = $relations   ;
    }

    /**
     * @return int|null
     */
    public function getFilesParentFolderId($user_id, $model_id = null)
    {
        return null;
    }





}