<?php

return [
    'analyzer' => env(
        'PRODUCT_IMAGE_ANALYZER',
        env('GEMINI_API_KEY') ? 'gemini' : (env('OPENAI_API_KEY') ? 'openai' : 'placeholder'),
    ),

    'upload_chunk_size' => (int) env('IMPORT_UPLOAD_CHUNK_SIZE', 10),
    'max_image_size_kb' => (int) env('IMPORT_IMAGE_MAX_SIZE_KB', 8192),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_VISION_MODEL', 'gemini-3.5-flash-lite'),
        'timeout' => (int) env('GEMINI_VISION_TIMEOUT', 90),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_VISION_MODEL', 'gpt-4o-mini'),
        'detail' => env('OPENAI_VISION_DETAIL', 'high'),
        'timeout' => (int) env('OPENAI_VISION_TIMEOUT', 90),
    ],
];
