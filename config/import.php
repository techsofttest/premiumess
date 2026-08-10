<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product Image Search Directory
    |--------------------------------------------------------------------------
    |
    | The directory where imported product images are stored.
    | The ImageResolver will search this directory for image files
    | matching the names provided in the Excel import.
    |
    */
    'image_directory' => storage_path('app/public/import_images'),

    /*
    |--------------------------------------------------------------------------
    | Product Image Storage Path
    |--------------------------------------------------------------------------
    |
    | The relative path (within the 'public' disk) where resolved
    | product images will be copied to.
    |
    */
    'image_storage_path' => 'products',
];
