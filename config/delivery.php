<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minimum Delivery Lead Time
    |--------------------------------------------------------------------------
    |
    | The minimum lead time in hours required before a direct delivery order
    | can be fulfilled. For example, 24 hours.
    |
    */
    'minimum_lead_hours' => 1,

    /*
    |--------------------------------------------------------------------------
    | Courier Flat Rate Options
    |--------------------------------------------------------------------------
    |
    | Default flat rate shipping fee and free shipping threshold for courier
    | delivery options.
    |
    */
    'courier' => [
        'fee' => 4.99,
        'free_threshold' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Store/Warehouse Coordinates (Default origin for delivery route optimization)
    |--------------------------------------------------------------------------
    |*/
    'store_coordinates' => [
        'latitude' => env('STORE_LATITUDE', -35.39926983575099),
        'longitude' => env('STORE_LONGITUDE', 149.1039048482348),
    ],
];
