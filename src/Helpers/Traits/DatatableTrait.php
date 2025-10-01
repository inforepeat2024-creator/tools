<?php

namespace RepeatToolkit\Helpers\Traits;

trait DatatableTrait
{

    public function getTranslationArray($core_language_id)
    {

        $translation_array = [];


        $translation_array['emptyTable'] = _i("No data available in table");
        $translation_array['info'] = _i("Showing") . ' _START_ ' . _i('to') . ' _END_ '. _i('of') .' _TOTAL_ ' . _i("entries");
        $translation_array['infoEmpty'] = _i("Showing") . ' 0 ' . _i('to') . ' 0 '. _i('of') .' 0 ' . _i("entries");
        $translation_array['infoFiltered'] = '('. _i('filtered from') .' _MAX_ '. _i('total entries') .')';
        $translation_array['infoThousands'] = ',';
        $translation_array['lengthMenu'] = _i('Show') . ' _MENU_ ' . _i('entries');
        $translation_array['loadingRecords'] = _i('Loading records') . '...';
        $translation_array['processing'] = _i('Processing') . '...';
        $translation_array['search'] = _i('Search');
        $translation_array['zeroRecords'] = _i('No matching records found');
        $translation_array['thousands'] = ',';

        $translation_array['paginate'] = [
            'first' => _i("First"),
            'last' => _i("Last"),
            'next' => _i("Next"),
            'previous' => _i("Previous"),
        ];

        $translation_array['aria'] = [
            'sortAscending' => _i("Sort ascending"),
            'sortDescending' => _i("Sort descending"),
        ];


        return $translation_array;

    }

}
