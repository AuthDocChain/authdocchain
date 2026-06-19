<?php

return [

    'mailgun'  => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET'), 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'), 'scheme' => 'https'],
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses'      => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],

    /*
    |--------------------------------------------------------------------------
    | AuthDocChain SDK
    |--------------------------------------------------------------------------
    | Add AUTHDOCCHAIN_API_KEY to your .env file.
    | Requires a Pro plan or higher.
    */
    'authdocchain' => [
        'api_key' => env('AUTHDOCCHAIN_API_KEY'),
    ],

];
