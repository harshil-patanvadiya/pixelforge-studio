<?php

return [

    'markup_percent' => 25,

    'starting_credits' => '5.00',

    'credit_packages' => ['5.00', '10.00', '25.00'],

    'pixelforge' => [
        'base_url' => env('PIXELFORGE_BASE_URL', 'https://pixelforge.promptedit.dev/v1'),
        'api_key' => env('PIXELFORGE_API_KEY'),
        'webhook_secret' => env('PIXELFORGE_WEBHOOK_SECRET'),
    ],

];
