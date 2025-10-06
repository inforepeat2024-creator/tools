<?php

namespace RepeatToolkit\Abstracts;


use Illuminate\Database\Eloquent\Model;
use RepeatToolkit\Helpers\Traits\ModelActionsTrait;


class AbstractModel extends Model
{
    use ModelActionsTrait;
    protected $guarded =  [];

    protected $translation_model;

    /**
     * Sve prevode (svi jezici)
     */
    public function translations(): HasMany
    {
        // Ako FK nije konvencionalan, dodaj ga kao 2. argument, npr. ->hasMany($this->translation_model, 'clinic_id');
        return $this->hasMany($this->translation_model);
    }

    /**
     * Prevodi za trenutni locale (kao hasOne sa WHERE uslovom) — mozes eager-load:
     * Model::with('currentTranslation')->get();
     */
    public function translation(?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        $language_id = config('languages')[$locale];

        return $this->hasOne($this->translation_model)
            ->where('language_id', $language_id)->first();
        // Ako koristiš language_id umesto locale:
        // ->where('language_id', $this->mapLocaleToLanguageId($locale));
    }





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
