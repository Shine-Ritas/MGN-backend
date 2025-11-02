<?php

return [

    'client_app_url' => env('CLIENT_APP_URL'),
    'test' => [
        'users_count' => 180,
        'mogous_count' => 50,
        'categories_count' => 22,
        'chapter_analysis_count' => 120,
    ],
    'mongou_storage' => 'bunnycdn',

    'cacheMode' => false,

    'mogou' => [
        'cover' => [
            'path' => 'mogou/cover',
            'disk' => 'public',
            'is_public' => true,
        ],
        'chapter' => [
            'path' => 'mogou/chapter',
            'disk' => 'public',
            'is_public' => true,
        ],
        'sub_mogou' => [
            'cover' => [
                'path' => 'mogou/sub_mogou/cover',
                'disk' => 'public',
                'is_public' => true,
            ],
            'chapter' => [
                'path' => 'mogou/sub_mogou/chapter',
                'disk' => 'public',
                'is_public' => true,
            ],
        ],
    ],

    'cache_key' => [
        'homepage' => [
            'tags' => 'homepage',
            'most_viewed' => 'homepage_most_viewed',
            'last_uploaded' => 'homepage_last_uploaded',
            'carousel' => 'hero_highlight_slider',
            'recommend' => 'main_page_recommended',
        ],
    ],

    'bunnycdn' => [
        'storage_zone' => env('BUNNYCDN_STORAGE_ZONE'),
        'pull_zone' => env('BUNNYCDN_PULL_ZONE'),
        'api_key' => env('BUNNYCDN_API_KEY'),
        'region' => env('BUNNYCDN_REGION', \PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion::DEFAULT),
        'papi' => env('BUNNYCDN_PAPI_KEY'),
        'storage_zone_id' => env('BUNNYCDN_STORAGE_ZONE_ID'),
        'api' => [
            'getZone' => 'https://api.bunny.net/storagezone/',
        ],
    ],
];
