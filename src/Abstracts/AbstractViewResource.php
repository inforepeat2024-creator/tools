<?php

namespace RepeatToolkit\Abstracts;

use Illuminate\Http\Resources\Json\JsonResource;

class AbstractViewResource extends JsonResource
{



    protected $actions;


    public function __construct($resource)
    {
        parent::__construct($resource);


    }


    public function getColumns($columns)
    {
        $columns['actions'] =  $this->resource->modelActions();

        return $columns;
    }

}