<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use Google\Service\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;
use Modules\Banqu\Utilities\Models\Standard\BanquAccountModelUtilities;
use Modules\Core\Entities\CoreStorageItem;
use Modules\Core\Utilities\Models\Standard\CoreElementFileModelUtilities;
use Modules\Core\Utilities\Models\Standard\CoreElementStorageItemUtilities;
use Modules\Core\Utilities\Models\Standard\CoreStorageItemUtilities;
use Modules\Faktura\Utilities\Models\Standard\FakturaInvoiceModelUtilities;
use Modules\Projects\Utilities\Models\Standard\ProjectsPlanUtilities;
use Modules\Projects\Utilities\Models\Standard\ProjectsTaskUtilities;
use phpDocumentor\Reflection\Types\Self_;

class FilemanagerHelper
{


    public static function getBasePathForUser($user_id = null)
    {
        if($user_id == null)
            $user_id = Auth::user()->id;


        FileHelper::createFolderIfNotExist(storage_path('app/public/user_' . $user_id . '/file_manager'));


        return storage_path('app/public/user_' . $user_id . '/file_manager');

    }

    public static function getSessionItems()
    {
        $items = Session::get("core_storage_items");

        if($items == null)
            $items =  [];

        return $items;
    }

    public static function getDticketsImportFolder($user_id)
    {
        $utils = new CoreStorageItemUtilities();

        return $utils->getOneFromParams(['system_id' => 100, 'user_id' => $user_id]);
    }

    public static function initFilemanager($user_id)
    {

        $base_path = self::getBasePathForUser($user_id);


        $system_folders = [
            1 => 'billoTAX',
            2 => 'postBOX',
            3 => 'ScanSnap',
            4 => 'Documents',
            5 => "Projects",
            6 => "Import",
            7 => "Configuration",
            8 => "Web-shops",
            9 => "Products",
            10 => "Kassomat",
        ];


        $system_folders_level_2 = [
            100 => ['name' => "D-TICKETS", 'parent_id' => 6],
            150 => ['name' => "System-files", 'parent_id' => 7],
            250 => ['name' => "Invoices", 'parent_id' => 1],
            256 => ['name' => "Z-Bon", 'parent_id' => 10],
            350 => ['name' => "Exports", 'parent_id' => 1],
          //  200  => ['name' => "Tasks", 'parent_id' => 5],
        ];



        foreach ($system_folders as $id => $system_folder)
        {

            FileHelper::createFolderIfNotExist($base_path . '/' . $system_folder);

            $utils = new CoreStorageItemUtilities();
            $existing = $utils->getOneFromParams(['user_id' => $user_id, 'system_id' => $id]);



            if(!isset($existing->id))
            {


                $existing = $utils->createFromParams([
                    'user_id' => $user_id,
                    'system_id' => $id,
                    'type' => 'folder',
                    'name' => $system_folder,
                    'path' => $base_path . '/' . $system_folder,

                ]);
            }


        }


        foreach ($system_folders_level_2 as $id => $folder_data)
        {

            $utils = new CoreStorageItemUtilities();

            $parent_folder = $utils->getOneFromParams([
                'user_id' => $user_id,
                'system_id' => $folder_data['parent_id'],
            ]);

            $existing = $utils->getOneFromParams(['user_id' => $user_id, 'system_id' => $id]);
            FileHelper::createFolderIfNotExist($parent_folder->path . '/' . $folder_data['name']);
            if(!isset($existing->id))
            {


                $existing = $utils->createFromParams([
                    'user_id' => $user_id,
                    'system_id' => $id,
                    'parent_id' => $parent_folder->id,
                    'type' => 'folder',
                    'name' => $folder_data['name'],
                    'path' => $parent_folder->path . '/' . $folder_data['name'],

                ]);
            }


        }

    }

    /**
     * @param $parent_id
     * @param UploadedFile|null $uploaded_file
     * @return CoreStorageItem|null
     */
    public static function storeUploadedFile($parent_id, ?UploadedFile $uploaded_file)
    {





        if (is_null($uploaded_file)) {
            return null;
        }

        $storage_item_utils = new CoreStorageItemUtilities();
        $parent_folder = $storage_item_utils->findById($parent_id);

        if (!isset($parent_folder->id)) {
            return null;
        }

        $storage_path = $parent_folder->path;
        $original_name = $uploaded_file->getClientOriginalName();
        $new_filename = $original_name;

        $base_filename = pathinfo($original_name, PATHINFO_FILENAME); // Filename without extension


        // Try to open as image
        try {
            $img = Image::make($uploaded_file->getRealPath());
            $mime = $img->mime();

            if (str_starts_with($mime, 'imagesss/')) {

                // Store the compressed image
                $relative_storage_path = str_replace(storage_path('app') . '/', '', $storage_path);


                // Get the size of the uploaded image
                $fileSizeKB = filesize($uploaded_file->getRealPath()) / 1024; // File size in KB

              //  dd($fileSizeKB);s

                // Determine the compression quality based on file size
                $compressionQuality = 80; // Default quality for files smaller than 1MB

                // Adjust quality based on file size
                if ($fileSizeKB >= 10000) {
                    // If file is 4MB or more, compress more aggressively (e.g., 20%)
                    $compressionQuality = 10;
                }
                else  if ($fileSizeKB >= 4000) {
                    // If file is 4MB or more, compress more aggressively (e.g., 20%)
                    $compressionQuality = 15;
                } elseif ($fileSizeKB >= 2000) {
                    // If file is between 2MB and 4MB, use 30% quality
                    $compressionQuality = 20;
                } elseif ($fileSizeKB >= 1000) {
                    // If file is between 1MB and 2MB, use 50% quality
                    $compressionQuality = 50;
                }


                $jpg_filename = $base_filename . '.jpg';
                $compressedImage = (string) $img->encode('jpg', $compressionQuality); // 75% quality JPG

                $new_filename = $jpg_filename; // Save new name for database
                \Illuminate\Support\Facades\Storage::put($relative_storage_path . '/' . $new_filename, $compressedImage);




            } else {
                // Not an image -> store normally
                $relative_storage_path = str_replace(storage_path('app'), "", $storage_path);
                $uploaded_file->storeAs($relative_storage_path, $new_filename);
            }
        } catch (\Exception $e) {
            // Failed to read as image -> store normally
            $relative_storage_path = str_replace(storage_path('app'), "", $storage_path);
            $uploaded_file->storeAs($relative_storage_path, $new_filename);
        }

        $full_path = $storage_path . '/' . $new_filename;

        $storage_item_utils = new CoreStorageItemUtilities();
        $existing_item = $storage_item_utils->getOneFromParams([
            'path' => $full_path,
            'user_id' => $parent_folder->user_id,
        ]);

        if (!isset($existing_item->id)) {
            $existing_item = $storage_item_utils->createFromParams([
                'parent_id' => $parent_folder->id,
                'path' => $full_path,
                'user_id' => $parent_folder->user_id,
                'type' => "file",
                'name' => $new_filename,
            ]);
        }

        return $existing_item;
    }

    public static function createFolderForWebshop($user_id, $web_shop_id)
    {
        self::initFilemanager($user_id);


        $storage_item_utils = new CoreStorageItemUtilities();

        $parent_folder = $storage_item_utils->getOneFromParams(['filter__system_id__equal' => 8, 'filter__user_id__equal' => $user_id]);




        $folder_name = 'web_shop_' . $web_shop_id;

        $path = $parent_folder->path . '/' . $folder_name;



        FileHelper::createFolderIfNotExist($path);

        $core_storage_utils = new CoreStorageItemUtilities();


        $existing_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $parent_folder->id,
            'path' => $path,
            'type' => 'folder',
            'name' => $folder_name
        ]);


        if(!isset($existing_folder->id))
        {
            $existing_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $parent_folder->id,
                'path' => $path,
                'type' => 'folder',
                'name' => $folder_name
            ]);

        }


        return $existing_folder;
    }

    public static function createFolderForZbon($banqu_account_id, $date)
    {
        $utils = new BanquAccountModelUtilities();

        $banqu_account = $utils->findById($banqu_account_id);



        self::initFilemanager($banqu_account->owner->user_id);




        $storage_item_utils = new CoreStorageItemUtilities();

        $parent_folder = $storage_item_utils->getOneFromParams(['filter__system_id__equal' => 256, 'filter__user_id__equal' => $banqu_account->owner->user_id]);




        $folder_name = 'hellocash_' . $banqu_account_id;

        $path = $parent_folder->path . '/' . $folder_name;



        FileHelper::createFolderIfNotExist($path);

        $core_storage_utils = new CoreStorageItemUtilities();

        $user_id = $banqu_account->owner->user_id;

        $existing_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $parent_folder->id,
            'path' => $path,
            'type' => 'folder',
            'name' => $folder_name
        ]);


        if(!isset($existing_folder->id))
        {
            $existing_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $parent_folder->id,
                'path' => $path,
                'type' => 'folder',
                'name' => $folder_name
            ]);

        }


        $year = date("Y", strtotime($date));

        FileHelper::createFolderIfNotExist( $existing_folder->path . '/' . $year);

        $existing_year_folder  = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $existing_folder->id,
            'path' => $existing_folder->path . '/' . $year,
            'type' => 'folder',
            'name' => $year
        ]);

        if(!isset($existing_year_folder->id))
        {
            $existing_year_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $existing_folder->id,
                'path' => $existing_folder->path . '/' . $year,
                'type' => 'folder',
                'name' => $year
            ]);
        }


        $month = date("m-Y", strtotime($date));
        FileHelper::createFolderIfNotExist( $existing_year_folder->path . '/' . $month);
        $existing_month_folder  = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $existing_year_folder->id,
            'path' => $existing_year_folder->path . '/' . $month,
            'type' => 'folder',
            'name' => $month
        ]);

        if(!isset($existing_month_folder->id))
        {
            $existing_month_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $existing_year_folder->id,
                'path' => $existing_year_folder->path . '/' . $month,
                'type' => 'folder',
                'name' => $month
            ]);
        }



        return $existing_month_folder;
    }

    public static function createFolderForProduct($user_id, $model_id)
    {
        self::initFilemanager($user_id);


        $storage_item_utils = new CoreStorageItemUtilities();

        $parent_folder = $storage_item_utils->getOneFromParams(['filter__system_id__equal' => 9, 'filter__user_id__equal' => $user_id]);




        $folder_name = 'product_' . $model_id;

        $path = $parent_folder->path . '/' . $folder_name;



        FileHelper::createFolderIfNotExist($path);

        $core_storage_utils = new CoreStorageItemUtilities();


        $existing_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $parent_folder->id,
            'path' => $path,
            'type' => 'folder',
            'name' => $folder_name
        ]);


        if(!isset($existing_folder->id))
        {
            $existing_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $parent_folder->id,
                'path' => $path,
                'type' => 'folder',
                'name' => $folder_name
            ]);

        }


        return $existing_folder;
    }

    public static function createFolderForProject($user_id, $project_id)
    {

        self::initFilemanager($user_id);


        $storage_item_utils = new CoreStorageItemUtilities();

        $parent_folder = $storage_item_utils->getOneFromParams(['filter__system_id__equal' => 5, 'filter__user_id__equal' => $user_id]);


        $plan_utils = new ProjectsPlanUtilities();

        $plan = $plan_utils->findById($project_id);


        $path = $parent_folder->path . '/' . $plan->id . '-' . (str_replace(" ", "-", $plan->name));



        FileHelper::createFolderIfNotExist($path);

        $core_storage_utils = new CoreStorageItemUtilities();


        $existing_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $parent_folder->id,
            'path' => $path,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $plan->name))
        ]);


        if(!isset($existing_folder->id))
        {
            $existing_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $parent_folder->id,
                'path' => $path,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $plan->name))
            ]);

        }


        $core_element_file_utils = new CoreElementStorageItemUtilities();

        $existing_elem_file = $core_element_file_utils->getOneFromParams([
            'core_storage_item_id' => $existing_folder->id,
            'projects_plan_id' => $project_id,
        ]);

        if(!isset($existing_elem_file))
        {
            $existing_elem_file = $core_element_file_utils->createFromParams([
                'core_storage_item_id' => $existing_folder->id,
                'projects_plan_id' => $project_id,
            ]);
        }


        return $existing_folder;

    }

    public static function createDateFolderUnderPostbox($user_id, $date)
    {
        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        self::initFilemanager($user_id);

        $postbox_folder = self::getPostboxFolder($user_id);


        $core_storage_utils = new CoreStorageItemUtilities();


        //YEAR

        $year_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $postbox_folder->id,
            'path' => $postbox_folder->path . '/' . $year,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $year))
        ]);


        FileHelper::createFolderIfNotExist($postbox_folder->path . '/' . $year);
        if(!isset($year_folder->id))
        {
            $year_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $postbox_folder->id,
                'path' => $postbox_folder->path . '/' . $year,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $year))
            ]);



        }



        FileHelper::createFolderIfNotExist($year_folder->path . '/' . $month);
        $month_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $year_folder->id,
            'path' => $year_folder->path . '/' . $month,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $month))
        ]);

        if(!isset($month_folder->id))
        {
            $month_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $year_folder->id,
                'path' => $year_folder->path . '/' . $month,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $month))
            ]);



        }


        //DAY

        FileHelper::createFolderIfNotExist($month_folder->path . '/' . $day);

        $day_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $month_folder->id,
            'path' => $month_folder->path . '/' . $day,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $day))
        ]);

        if(!isset($day_folder->id))
        {
            $day_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $month_folder->id,
                'path' => $month_folder->path . '/' . $day,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $day))
            ]);



        }

        return $day_folder;

    }

    public static function createFolderForTask($user_id, $task_id)
    {



        self::initFilemanager($user_id);


        $task_utils = new ProjectsTaskUtilities();

        $task = $task_utils->findById($task_id);

        $core_element_file_utils = new CoreElementStorageItemUtilities();

       /* $existing_elem_file = $core_element_file_utils->getOneFromParams([
            'core_storage_item_id' => $existing_folder->id,
            'projects_task_id' => $task->id,
        ]);*/




        $storage_item_utils = new CoreStorageItemUtilities();

        $parent_folder = $storage_item_utils->getOneFromParams(['filter__system_id__equal' => 200, 'filter__user_id__equal' => $user_id]);





        $path = $parent_folder->path . '/' . $task_id . '-' . (str_replace(" ", "-", $task->name));



        FileHelper::createFolderIfNotExist($path);

        $core_storage_utils = new CoreStorageItemUtilities();


        $existing_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $parent_folder->id,
            'path' => $path,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $task->name))
        ]);


        if(!isset($existing_folder->id))
        {
            $existing_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $parent_folder->id,
                'path' => $path,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $task->name))
            ]);

        }


        $core_element_file_utils = new CoreElementStorageItemUtilities();

        $existing_elem_file = $core_element_file_utils->getOneFromParams([
            'core_storage_item_id' => $existing_folder->id,
            'projects_task_id' => $task_id,
        ]);

        if(!isset($existing_elem_file))
        {
            $existing_elem_file = $core_element_file_utils->createFromParams([
                'core_storage_item_id' => $existing_folder->id,
                'projects_task_id' => $task_id,
            ]);
        }


        return $existing_folder;

    }


    public static function getPostboxFolder($user_id)
    {
        self::initFilemanager($user_id);

        $utils = new CoreStorageItemUtilities();

        return $utils->getOneFromParams(['system_id' => 2, 'user_id' => $user_id]);
    }

    public static function getInvoicesFolder($user_id)
    {
        self::initFilemanager($user_id);

        $utils = new CoreStorageItemUtilities();

        return $utils->getOneFromParams(['system_id' => 250, 'user_id' => $user_id]);
    }

    public static function getExportsFolder($user_id)
    {
        self::initFilemanager($user_id);

        $utils = new CoreStorageItemUtilities();

        return $utils->getOneFromParams(['system_id' => 350, 'user_id' => $user_id]);
    }

    public static function getExportsOnDateFolder($user_id, $date = null)
    {

        if($date == null)
            $date = date("Y-m-d");

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));



        $postbox_folder = self::getExportsFolder($user_id);



        $core_storage_utils = new CoreStorageItemUtilities();


        //YEAR

        $year_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $postbox_folder->id,
            'path' => $postbox_folder->path . '/' . $year,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $year))
        ]);


        FileHelper::createFolderIfNotExist($postbox_folder->path . '/' . $year);
        if(!isset($year_folder->id))
        {
            $year_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $postbox_folder->id,
                'path' => $postbox_folder->path . '/' . $year,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $year))
            ]);



        }



        FileHelper::createFolderIfNotExist($year_folder->path . '/' . $month);
        $month_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $year_folder->id,
            'path' => $year_folder->path . '/' . $month,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $month))
        ]);

        if(!isset($month_folder->id))
        {
            $month_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $year_folder->id,
                'path' => $year_folder->path . '/' . $month,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $month))
            ]);



        }


        //DAY

        FileHelper::createFolderIfNotExist($month_folder->path . '/' . $day);

        $day_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $month_folder->id,
            'path' => $month_folder->path . '/' . $day,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $day))
        ]);

        if(!isset($day_folder->id))
        {
            $day_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $month_folder->id,
                'path' => $month_folder->path . '/' . $day,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $day))
            ]);



        }

        return $day_folder;


    }

    public static function getGivenInvoiceFolder($invoice_id)
    {
        $utils = new FakturaInvoiceModelUtilities();
        $invoice = $utils->findById($invoice_id);

        $date = $invoice->date;

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));



        $postbox_folder = self::getInvoicesFolder($invoice->user_id);

        $user_id = $invoice->user_id;

        $core_storage_utils = new CoreStorageItemUtilities();


        //YEAR

        $year_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $postbox_folder->id,
            'path' => $postbox_folder->path . '/' . $year,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $year))
        ]);


        FileHelper::createFolderIfNotExist($postbox_folder->path . '/' . $year);
        if(!isset($year_folder->id))
        {
            $year_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $postbox_folder->id,
                'path' => $postbox_folder->path . '/' . $year,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $year))
            ]);



        }



        FileHelper::createFolderIfNotExist($year_folder->path . '/' . $month);
        $month_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $year_folder->id,
            'path' => $year_folder->path . '/' . $month,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $month))
        ]);

        if(!isset($month_folder->id))
        {
            $month_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $year_folder->id,
                'path' => $year_folder->path . '/' . $month,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $month))
            ]);



        }


        //DAY

        FileHelper::createFolderIfNotExist($month_folder->path . '/' . $day);

        $day_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $month_folder->id,
            'path' => $month_folder->path . '/' . $day,
            'type' => 'folder',
            'name' => (str_replace(" ", "-", $day))
        ]);

        if(!isset($day_folder->id))
        {
            $day_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $month_folder->id,
                'path' => $month_folder->path . '/' . $day,
                'type' => 'folder',
                'name' => (str_replace(" ", "-", $day))
            ]);



        }

        return $day_folder;


    }

    public static function getPostboxPostFolder($user_id, $post_id)
    {
        self::initFilemanager($user_id);

        $utils = new CoreStorageItemUtilities();

        $postbox_folder = $utils->getOneFromParams(['system_id' => 2, 'user_id' => $user_id]);

        $core_storage_utils = new CoreStorageItemUtilities();

        FileHelper::createFolderIfNotExist($postbox_folder->path . '/' . $post_id);

        $existing_folder = $core_storage_utils->getOneFromParams([
            'user_id' => $user_id,
            'parent_id' => $postbox_folder->id,
            'path' => $postbox_folder->path . '/' . $post_id,
            'type' => 'folder',
            'name' => $post_id
        ]);


        if(!isset($existing_folder->id))
        {
            $existing_folder = $core_storage_utils->createFromParams([
                'user_id' => $user_id,
                'parent_id' => $postbox_folder->id,
                'path' =>  $postbox_folder->path . '/' . $post_id,
                'type' => 'folder',
              //  'postbox_received_post_id' => $post_id,
                'name' => $post_id
            ]);

        }

        return $existing_folder;
    }

}
