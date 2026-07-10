<?php
return [
    'default' => env('CACHE_STORE','file'),
    'stores' => [
        'array' => ['driver'=>'array','serialize'=>false],
        'file' => ['driver'=>'file','path'=>storage_path('framework/cache/data')],
        'database' => ['driver'=>'database','table'=>'cache','connection'=>null,'lock_connection'=>null],
        'redis' => ['driver'=>'redis','connection'=>'cache','lock_connection'=>'default'],
    ],
    'prefix' => env('CACHE_PREFIX','warqna_cache'),
];
