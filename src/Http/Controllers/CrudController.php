<?php

namespace RepeatToolkit\Http\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RepeatToolkit\Abstracts\AbstractModelUtilities;
use RepeatToolkit\Helpers\StaticHelpers\DbHelper;


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
                $input['filters'],
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
        $view_obj = new \stdClass();

        $view_obj->route = route($this->model_utils->getTableName() . '.datatable');
        $view_obj->table_name = $this->model_utils->getTableName();

        return view('crud.view', compact('view_obj'));

    }

    public function authorizeCreatePartial($slug, $model)
    {
        $this->authorize('edit-' . $model->getTable(), $model);
    }

    public function createPartial($slug = 'basic', $id = null)
    {

        $view_obj = new \stdClass();


        $model = $this->model_utils->findById($id);

        if(isset($model->id))
            $this->authorizeCreatePartial($slug, $model);

        $view_obj->slugs = $this->getPartials();

        $view_obj->model = $model;
        $view_obj->slug = $slug;

        $view_obj->table_name = $this->model_utils->getTableName();

        $view_obj = $this->addDataToPartial($view_obj, $slug, $id);

        return view('crud.create_partial', compact('view_obj'));

    }

    public function addDataToPartial($view_obj, $slug, $id = null)
    {
        return $view_obj;
    }

    public function storePartial(Request $request, $id = null)
    {

        try
        {
            $input = $request->all();

            $processed_input = DbHelper::processTableInput($this->model_utils->getTableName(), $input);



            if($id == null)
            {
                $new_model = $this->model_utils->createFromParams($processed_input);

                $this->afterStore($request, $new_model->id);

                return $this->redirectWithSuccess(__i("Uspešna akcija"), route($this->model_utils->getTableName() . '.create_partial', ['basic', $new_model->id]));
            }
            else
            {
                $this->model_utils->updateFromParams(['id' => $id], $processed_input);
                $this->afterStore($request, $id);
                return $this->redirectWithSuccess();
            }
        }
        catch (\Exception $e)
        {
            return $this->redirectWithError($e);
        }




    }


    public function afterStore(Request $request, $id)
    {
        $model = $this->model_utils->findById($id);

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
    }


    public function destroy($id)
    {

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