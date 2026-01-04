<?php

namespace RepeatToolkit\Abstracts;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use RepeatToolkit\Helpers\Traits\ModelActionsTrait;


class AbstractModel extends Model
{
    use ModelActionsTrait;
    protected $guarded =  [];

    protected $translation_model;

    public function translations(): HasMany
    {
        if (empty($this->translation_model)) {
            return $this->emptyHasMany();
        }

        // Ako FK nije konvencionalan, dodaj ga kao 2. argument, npr. ->hasMany($this->translation_model, 'clinic_id');
        return $this->hasMany($this->translation_model);
    }

    /**
     * Fabrika “prazne” HasOne relacije nad ovim modelom (uvek prazno).
     */
    protected function emptyHasOne(): HasOne
    {
        /** @var Builder $q */
        $q = $this->newQuery()->whereRaw('0=1');

        return new HasOne(
            $q,
            $this,
            $this->getKeyName(),
            $this->getKeyName()
        );
    }

    /**
     * Fabrika “prazne” HasMany relacije nad ovim modelom (bez ikakvih pogodaka).
     */
    protected function emptyHasMany(): HasMany
    {
        /** @var Builder $q */
        $q = $this->newQuery()->whereRaw('0=1'); // nikad ne vraća redove

        // foreignKey i localKey stavljamo na primarni ključ ovog modela,
        // pošto realno nećemo nići pogodak (0=1), DB upit je trivijalan i brz.
        return new HasMany(
            $q,           // query nad trenutnim modelom
            $this,        // parent
            $this->getKeyName(), // foreign key
            $this->getKeyName()  // local key
        );
    }

    /**
     * Prevodi za trenutni locale (kao hasOne sa WHERE uslovom) — mozes eager-load:
     * Model::with('currentTranslation')->get();
     */
    public function translation(?string $locale = null)
    {


        if (empty($this->translation_model)) {




            // Prazan “objekat” (ne null), da možeš `$model->translation->title`
            return $this->emptyHasOne();
        }



        $locale = $locale ?: app()->getLocale();

        $language_id = config('languages')[$locale];



        return $this->hasOne($this->translation_model)
            ->where('language_id', $language_id);
        // Ako koristiš language_id umesto locale:
        // ->where('language_id', $this->mapLocaleToLanguageId($locale));
    }



    private function getPropertyAllLangs($property)
    {
        $array = [];

        foreach ($this->translations as $translation) {

            $array[$translation->language->code] = $translation->{$property};

        }

        return $array;
    }

    public function getExcerptsAttribute()
    {
        return $this->getPropertyAllLangs('excerpt');
    }

    public function getExcerptAttribute()
    {

        return $this->translation->excerpt ?? "";
    }

    public function getDescriptionAttribute()
    {
        return $this->translation->description ?? "";
    }

    public function getTitleAttribute()
    {
        return $this->translation->title ?? "";
    }

    public function getSlugAttribute($value)
    {


        return $this->translation->slug ?? $this->slug ?? "";
    }

    public function getContentAttribute()
    {
        return $this->translation->content ?? "";
    }

    public function getDescriptionsAttribute()
    {

        return $this->getPropertyAllLangs('description');
    }

    public function getTitlesAttribute()
    {
        return $this->getPropertyAllLangs('title');
    }

    public function getSlugsAttribute()
    {
        return $this->getPropertyAllLangs('slug');
    }

    public function getContentsAttribute()
    {
        return $this->getPropertyAllLangs('content');
    }

    /**
     * Sve prevode (svi jezici)
     */





    public function getHtmlTitleAttribute()
    {
        return $this->translation->title ?? $this->title ?? $this->translation->name ?? $this->name ?? "";
    }



    public function getHtmlSubtitleAttribute()
    {
        return "";
    }

    public function authorizeEdit($user)
    {
        return false;
    }

    public function authorizeDelete($user)
    {
        return false;
    }

    public function scopeOrderByRelations($query, array $relations, $column, $direction = 'asc')
    {
        $baseModel  = $query->getModel();
        $baseTable  = $baseModel->getTable();
        $current    = $baseModel;
        $currentTbl = $baseTable;

        // 1) Nemoj resetovati selekciju ako već postoji
        if (empty($query->getQuery()->columns)) {
            $query->select("{$baseTable}.*");
        }

        // 2) Distinct postavi samo jednom
        if (!$query->getQuery()->distinct) {
            $query->distinct();
        }

        foreach ($relations as $idx => $relation) {
            $rel = $current->{$relation}();
            if (!$rel || !method_exists($rel, 'getRelated')) {
                throw new \Exception("Invalid relation: $relation");
            }

            $related      = $rel->getRelated();
            $relatedTable = $related->getTable();

            // Stabilan alias lanca relacija (npr. "clinic" ili "clinic__owner")
            $alias = implode('__', array_slice($relations, 0, $idx + 1));

            // Preskoči ako je JOIN već dodat
            $existingJoins = collect($query->getQuery()->joins ?? [])->map(fn($j) => $j->table)->all();
            if (!in_array("{$relatedTable} as {$alias}", $existingJoins, true)) {
                // Preuzmi constraints sa relacije (npr. language_id)
                $relQuery = $rel->getQuery()->getQuery();
                $wheres   = $relQuery->wheres ?? [];

                if ($rel instanceof BelongsTo) {
                    $foreignKey = $rel->getForeignKeyName();  // na parent tabeli
                    $ownerKey   = $rel->getOwnerKeyName();    // na related tabeli

                    $query->leftJoin("{$relatedTable} as {$alias}", function ($join) use ($alias, $ownerKey, $currentTbl, $foreignKey, $wheres) {
                        $join->on("{$alias}.{$ownerKey}", '=', "{$currentTbl}.{$foreignKey}");
                        foreach ($wheres as $w) {
                            if (($w['type'] ?? null) === 'Basic') {
                                $join->where($w['column'], $w['operator'], $w['value']);
                            }
                        }
                    });
                } elseif ($rel instanceof HasOne || $rel instanceof HasMany) {
                    $foreignKey = $rel->getForeignKeyName(); // na related
                    $localKey   = $rel->getLocalKeyName();   // na parent

                    $query->leftJoin("{$relatedTable} as {$alias}", function ($join) use ($alias, $foreignKey, $currentTbl, $localKey, $wheres) {
                        $join->on("{$alias}.{$foreignKey}", '=', "{$currentTbl}.{$localKey}");
                        foreach ($wheres as $w) {
                            if (($w['type'] ?? null) === 'Basic') {
                                $join->where($w['column'], $w['operator'], $w['value']);
                            }
                        }
                    });
                } else {
                    throw new \Exception("Unsupported relation type for $relation");
                }
            }

            // Pomeramo "trenutnu" tabelu na alias
            $current    = $related;
            $currentTbl = $alias;
        }

        // 3) Dodaj (a ne prepisuj) kolonu za sortiranje – unikatni alias
        $orderColAlias = '__order_col_' . substr(md5($currentTbl . '.' . $column), 0, 8);

        // Ako već nije dodat, dodaj ga
        $alreadySelected = collect($query->getQuery()->columns ?? [])
            ->contains(function ($col) use ($orderColAlias) {
                // $col može biti string ili Expression
                return is_string($col) && str_ends_with(strtolower($col), " as {$orderColAlias}");
            });

        if (!$alreadySelected) {
            $query->addSelect(DB::raw("{$currentTbl}.{$column} as {$orderColAlias}"));
        }

        // 4) Sam ORDER BY po aliasu; ne resetuje prethodne order by-jeve
        return $query->orderBy($orderColAlias, $direction);
    }

    public function isStepCompleted($slug)
    {
        return false;
    }
}
