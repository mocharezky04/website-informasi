<?php

return [
    'ai' => [
        'provider' => env('AI_PROVIDER', 'gemini'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.2-3b-instruct:free'),
        'site_url' => env('APP_URL', 'http://localhost'),
        'app_name' => env('APP_NAME', 'Website Informasi'),
    ],

    'pixabay' => [
        'api_key' => env('PIXABAY_API_KEY'),
    ],
];
