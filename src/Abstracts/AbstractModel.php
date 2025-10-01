<?php

namespace RepeatToolkit\Abstracts;


use Illuminate\Database\Eloquent\Model;
use RepeatToolkit\Helpers\Traits\ModelActionsTrait;


class AbstractModel extends Model
{
    use ModelActionsTrait;
    protected $guarded =  [];


    public function getHtmlTitleAttribute()
    {
        return "";
    }

    public function getHtmlSubtitleAttribute()
    {
        return "";
    }

    public function authorizeEdit($user)
    {
        return false;
    }

    public function scopeOrderByRelations($query, array $relations, $column, $direction = 'asc')
    {
        $currentModel = $query->getModel();
        $currentTable = $currentModel->getTable();

        foreach ($relations as $relation) {
            $relationInstance = $currentModel->{$relation}();

            if (!method_exists($relationInstance, 'getRelated')) {
                throw new \Exception("Invalid relation: $relation");
            }

            $related = $relationInstance->getRelated();
            $relatedTable = $related->getTable();

            // Determine join keys based on relation type
            if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                $foreignKey = $relationInstance->getForeignKeyName();
                $ownerKey = $relationInstance->getOwnerKeyName();
                $query->join($relatedTable, "{$relatedTable}.{$ownerKey}", '=', "{$currentTable}.{$foreignKey}");
            } elseif ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasOne ||
                $relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                $foreignKey = $relationInstance->getForeignKeyName();
                $localKey = $relationInstance->getLocalKeyName();
                $query->join($relatedTable, "{$relatedTable}.{$foreignKey}", '=', "{$currentTable}.{$localKey}");
            } else {
                throw new \Exception("Unsupported relation type for $relation");
            }

            $currentModel = $related;
            $currentTable = $relatedTable;
        }

        $query->select($query->getModel()->getTable() . '.*')
            ->orderBy("{$currentTable}.{$column}", $direction);
    }


}
