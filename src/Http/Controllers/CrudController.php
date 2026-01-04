<?php

namespace RepeatToolkit\Http\Controllers;


use App\Utilities\Models\FileUtilities;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RepeatToolkit\Abstracts\AbstractModelUtilities;
use RepeatToolkit\Helpers\StaticHelpers\DbHelper;
use RepeatToolkit\Helpers\StaticHelpers\TextHelper;


class CrudController extends AbstractController
{
    use AuthorizesRequests;


    /**
     * @var AbstractModelUtilities
     */
    protected $model_utils;


    public function __construct($model_utilities)
    {
        $this->model_utils = $model_utilities;
    }

    public  function getPartials()
    {
        return array_merge(['basic' => __i("Osnovni podaci")], $this->getAdditionalPartials());
    }

    public function getAdditionalPartials()
    {
        return $this->model_utils->getAdditionalPartials();
    }

    public function getAllFromParams(Request  $request)
    {
        $input = $request->all();




        try {



            if(isset($input['autocomplete']))
            {
                $_GET['datatable_search_input'] = $input['autocomplete'];
                $_REQUEST['datatable_search_input'] = $input['autocomplete'];

                $input['filters']['filter__user_id__equal'] = Auth::user()->id;
            }




            if(!isset($collection))
            {

                $collection = $this->model_utils->getAllFromParams(
                    $input['filters'],
                    $input['order_by'] ?? [],
                    $input['aggregates'] ?? [],
                    $input['limit'] ?? null,
                    $input['offset'] ?? null,
                );

                if(isset($cache_key))
                    Cache::put($cache_key, $collection);
            }



            if(isset($input['autocomplete']))
            {
                unset($_GET['datatable_search_input']);
                unset($_REQUEST['datatable_search_input']);
            }

            if($this->model_utils->getApiResource() != null)
                $resource = $this->model_utils->getApiResource()::collection($collection);

            else $resource = $collection;


            return $this->respondWithData($resource);
        }
        catch (\Exception $e)
        {
            return $this->respondWithError($e->getMessage());
        }
    }



    public function getAllPaginate(Request  $request)
    {
        try
        {
            $input = $request->all();



            if(isset($input['autocomplete']))
            {
                $_GET['datatable_search_input'] = $input['autocomplete'];
                $_REQUEST['datatable_search_input'] = $input['autocomplete'];

            }



            $collection = $this->model_utils->getAllPaginate(
                $input['filters'],
                $input['limit'] ?? null,
                $input['order_by'] ?? [],
            );


            if(isset($input['autocomplete']))
            {
                unset($_GET['datatable_search_input']);
                unset($_REQUEST['datatable_search_input']);
            }

            $resource = $this->model_utils->getApiResource()::collection($collection);

            return $resource;
        }
        catch (\Exception $e)
        {
            return $this->respondWithError($e);
        }



    }

    public function getAllForSelect(Request  $request)
    {

        $input = $request->all();




        try {



            if(isset($input['autocomplete']))
            {
                $_GET['datatable_search_input'] = $input['autocomplete'];
                $_REQUEST['datatable_search_input'] = $input['autocomplete'];

                $input['filters']['filter__user_id__equal'] = Auth::user()->id;
            }

            $collection = $this->model_utils->getAllForSelect(
                $input['filters'] ?? [],
                $input['order_by'] ?? [],
                $input['aggregates'] ?? [],
                $input['limit'] ?? null,
                $input['offset'] ?? null,
            );

            if(isset($input['autocomplete']))
            {
                unset($_GET['datatable_search_input']);
                unset($_REQUEST['datatable_search_input']);
            }



            return $this->respondWithData($collection);
        }
        catch (\Exception $e)
        {
            return $this->respondWithError($e->getMessage());
        }

    }

    public function updateColumn(Request $request,  $model_id, $column_name, $value)
    {
        try
        {
            $this->model_utils->updateFromParams(['id' => $model_id], [$column_name => $value]);

            if($request->ajax()) return $this->respondSuccess();

            return redirect()->back();
        }
        catch (\Exception $e)
        {
            if($request->ajax()) return $this->respondWithError($e);

            return $this->redirectWithError($e);
        }


    }

    public function getDatatableRequiredParams(Request  $request)
    {
        return [];
    }

    public function datatable(Request$request)
    {


        try
        {
            $input = $request->all();





            $columns = $this->model_utils->getViewColumns();


            if(isset($input['autocomplete']))
            {
                $_GET['datatable_search_input'] = $input['autocomplete'];
                $_REQUEST['datatable_search_input'] = $input['autocomplete'];

            }



            $collection = $this->model_utils->getAllPaginate(
                array_merge($input['filters'] ?? [], $this->getDatatableRequiredParams($request)),
                $input['limit'] ?? null,
                $input['order_by'] ?? [],
            );


            if(isset($input['autocomplete']))
            {
                unset($_GET['datatable_search_input']);
                unset($_REQUEST['datatable_search_input']);
            }



            $resource = $this->model_utils->getViewResource()::collection($collection)->additional(['columns' => $columns]);



            return $resource;
        }
        catch (\Exception $e)
        {
            return $this->respondWithError($e);
        }




    }

    public function view(Request $request)
    {
        $this->authorizeView();

        $view_obj = new \stdClass();

        $view_obj->route = route($this->model_utils->getTableName() . '.datatable');
        $view_obj->table_name = $this->model_utils->getTableName();

        return view('crud.view', compact('view_obj'));

    }

    public function authorizeCreatePartial($slug, $model)
    {
        $this->authorize('edit-' . $this->model_utils->getTableName(), [$model, $slug]);
    }

    public function authorizeView()
    {
        $this->authorize('view-' . $this->model_utils->getTableName());
    }

    public function createPartial($slug = 'basic', $id = null)
    {

        $view_obj = new \stdClass();


        $model = $this->model_utils->findById($id);

        $this->authorizeCreatePartial($slug, $model);

        $view_obj->slugs = $this->getPartials();

        $view_obj->model = $model;
        $view_obj->slug = $slug;

        $view_obj->table_name = $this->model_utils->getTableName();

        $view_obj = $this->addDataToPartial($view_obj, $slug, $id);

        return view('crud.create_partial', compact('view_obj'));

    }


    /**
     * Ovde se dodaje dodatni podaci za određeni partial, npr json_data stavlja key i val u komponentu i prosledjuje u create_partial
     * @param $view_obj
     * @param $slug
     * @param $id
     * @return mixed
     */
    public function addDataToPartial($view_obj, $slug, $id = null)
    {
        return $view_obj;
    }




    public function beforeStore(Request $request, $id)
    {



    }


    public function storePartial(Request $request, $id = null)
    {

        try
        {

            $this->beforeStore($request, $id);

            $input = $request->all();




            $processed_input = DbHelper::processTableInput($this->model_utils->getTableName(), $input);






            foreach ($processed_input as $key => $value) {

                if($value instanceof UploadedFile) unset($processed_input[$key]);


                if(TextHelper::stringContains($key, ['remove_'])) unset($processed_input[$key]);


                if(is_array($value)) unset($processed_input[$key]);

            }

            //dd($processed_input);

            if($id == null)
            {
                $new_model = $this->model_utils->createFromParams($processed_input);


                try
                {
                    $this->afterStore($request, $new_model->id);
                }
                catch (\Exception $e)
                {


                    return $this->redirectWithError($e, route($this->model_utils->getTableName() . '.create_partial', ['basic', $new_model->id]));
                }



                return $this->redirectAfterStore($request, $new_model->id);

            }
            else
            {
                $this->model_utils->updateFromParams(['id' => $id], $processed_input);
                $this->afterStore($request, $id);
                return $this->redirectAfterStore($request, $id);
            }
        }
        catch (\Exception $e)
        {
            return $this->redirectWithError($e);
        }




    }

    public function redirectAfterStore(Request $request, $id = null)
    {
        return $this->redirectWithSuccess(__i("Uspešna akcija"), route($this->model_utils->getTableName() . '.create_partial', ['basic', $id]));

    }



    public function afterStore(Request $request, $id)
    {
        $model = $this->model_utils->findById($id);

        $input = $request->all();




        //Translations
        $this->saveTranslations($model, $input);





        // --- LOGO (single) ---
        if ($request->hasFile('logo')) {


            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $request->file('logo');

            if (!$file->isValid()) {
                abort(400, 'Upload nije validan: ' . $file->getErrorMessage());
            }

            $filename = Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'bin');
            $target   = $this->model_utils->getTableName() . "/logos/{$filename}";




            $stream = fopen($file->getPathname(), 'r');
            Storage::disk('public')->put($target, $stream);
            if (is_resource($stream)) fclose($stream);

            $model->logo()->create([
                'collection'    => 'logo',
                'disk'          => 'public',
                'path'          => $target,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'title'         => $request->input('logo_title'),
                'alt'           => $request->input('logo_alt'),
            ]);
        }
        else
        {


            if(isset($input['remove_logo']) && $input['remove_logo'] == 1)
            {
                $file_utils = new FileUtilities();

                $file_utils->deleteFromParams(['id' => $model->logo->id ?? -1]);
            }
        }


        if ($request->hasFile('cover')) {



            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $request->file('cover');

            if (!$file->isValid()) {
                abort(400, 'Upload nije validan: ' . $file->getErrorMessage());
            }



            $filename = Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'bin');
            $target   = $this->model_utils->getTableName() . "/covers/{$filename}";

            $stream = fopen($file->getPathname(), 'r');
            Storage::disk('public')->put($target, $stream);
            if (is_resource($stream)) fclose($stream);

            $model->cover()->create([
                'collection'    => 'cover',
                'disk'          => 'public',
                'path'          => $target,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'title'         => $request->input('cover_title'),
                'alt'           => $request->input('cover_alt'),
                'is_cover' => 1
            ]);
        }
        else
        {
            if(isset($input['remove_cover']) && $input['remove_cover'] == 1)
            {
                $file_utils = new FileUtilities();

                $file_utils->deleteFromParams(['id' => $model->cover->id ?? -1]);
            }
        }





        if ($request->hasFile('presentation'))
        {


            foreach ($request->file('presentation') as $file)
            {
                /** @var \Illuminate\Http\UploadedFile $file */
                //$file = $request->file('presentation');

                if (!$file->isValid()) {
                    abort(400, 'Upload nije validan: ' . $file->getErrorMessage());
                }



                $filename = Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'bin');
                $target   = $this->model_utils->getTableName() . "/videos/{$filename}";

                $stream = fopen($file->getPathname(), 'r');
                Storage::disk('public')->put($target, $stream);
                if (is_resource($stream)) fclose($stream);

                $model->video()->create([
                    'collection'    => 'video',
                    'disk'          => 'public',
                    'path'          => $target,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),

                ]);
            }


        }
        else
        {
            if(isset($input['remove_presentation']) && $input['remove_presentation'] == 1)
            {
                $file_utils = new FileUtilities();

                $file_utils->deleteFromParams(['id' => $model->video->id ?? -1]);
            }
        }

        // --- PHOTOS (multiple) ---
        // Očekuje <input type="file" name="photos[]" multiple>
        if ($request->hasFile('photos')) {

            $files = $request->file('photos');
            $titles = (array) $request->input('photos_title', []); // npr. photos_title[0], photos_title[1]...
            $alts   = (array) $request->input('photos_alt',   []); // npr. photos_alt[0], photos_alt[1]...

            foreach ((array) $files as $idx => $photo) {
                if (!$photo instanceof \Illuminate\Http\UploadedFile) {
                    continue;
                }
                if (!$photo->isValid()) {
                    // Preskoči nevalidan fajl umesto da prekine ceo proces
                    // (može i abort ako hoćeš strože)
                    continue;
                }

                $filename = Str::uuid().'.'.($photo->getClientOriginalExtension() ?: 'bin');
                $target   = $this->model_utils->getTableName() . "/photos/{$filename}";

                $stream = fopen($photo->getPathname(), 'r');
                Storage::disk('public')->put($target, $stream);
                if (is_resource($stream)) fclose($stream);

                // Ako nemaš eksplicitnu relaciju photos(), zameni sa generičkom npr. $model->media()
                $model->photos()->create([
                    'collection'    => 'photos',
                    'disk'          => 'public',
                    'path'          => $target,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type'     => $photo->getMimeType(),
                    'size'          => $photo->getSize(),
                    'title'         => $titles[$idx] ?? null,
                    'alt'           => $alts[$idx] ?? null,
                ]);
            }
        }


        if($request->has('photos_removed_ids'))
        {
            $model->photos()->whereIn('id', $request->input('photos_removed_ids'))->delete();

        }

    }

    protected function saveTranslations($model, array $input): void
    {
        $table     = $this->model_utils->getTranslationsTableName();
        $fk        = $this->model_utils->getTranslationsForeignKey();
        $langs     = (array) config('languages'); // ['sr' => 1, 'en' => 2, ...]
        //$fields    = $this->model_utils->getTranslatableFields();
        $fields = [];
        $now       = now();

        // Skupljamo vrednosti po jeziku: rows[$langId] = ['field1' => ..., 'field2' => ...]
        $rowsByLang = [];




        foreach ($input as $col_name => $value) {

            if(!is_array($value))
                continue;

            $field = $col_name;


            // $input['description'] = ['sr'=>'...', 'en'=>'...']
            $found_lang_value = null;
            foreach ($input[$field] as $code => $value) {
                if (!array_key_exists($code, $langs)) {
                    continue; // jezik nije u configu
                }
                $langId = $langs[$code];

                $fields[] = $field;

                //set for other languages not to be empty
                if($value != null && $found_lang_value == null)
                    $found_lang_value = $value[$langId];

                // inicijalizuj strukturu reda za dati jezik
                $rowsByLang[$langId] ??= [];
                // prazne stringove tretiraj kao NULL (po želji)
                $rowsByLang[$langId][$field] = ($value === '') ? $found_lang_value : $value;
            }
        }

        if (empty($rowsByLang)) {
            return; // nema prevoda u inputu
        }



        // Pretvori u niz redova za upsert
        $rows = [];
        foreach ($rowsByLang as $langId => $payload) {

            if($payload != null)

                // dodaj FK + language_id + timestamps
                $rows[] = array_merge(
                    [
                        $fk           => $model->getKey(),
                        'language_id' => $langId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ],
                    $payload
                );
        }



        $result = DB::table($table)->upsert(
            $rows,
            [$fk, 'language_id'],
            array_merge($fields, ['updated_at'])
        );



        // UPSERT: unique ključ je [$fk, 'language_id'], update-ujemo samo prevodiva polja + updated_at



    }

    public function authorizeDelete($model)
    {
        $this->authorize('delete-' . $this->model_utils->getTableName(), $model);
    }

    public function destroy($id)
    {
        $model = $this->model_utils->findById($id);
        $this->authorizeDelete($model);

        $this->model_utils->deleteFromParams(['id' => $id]);

        return $this->redirectWithSuccess();

    }

    public function show($id)
    {


        $view_obj = new \stdClass();

        $view_obj->model = $this->model_utils->findById($id);

        return view('models.' . $this->model_utils->getTableName() . '.show', compact('view_obj'));




    }
}
