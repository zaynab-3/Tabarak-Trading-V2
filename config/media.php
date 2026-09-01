<?php

return [
    'optimization' => [
        'enabled' => env('MEDIA_OPTIMIZATION_ENABLED', true),
        'max_dimension' => (int) env('MEDIA_IMAGE_MAX_DIMENSION', 1800),
        'webp_quality' => (int) env('MEDIA_WEBP_QUALITY', 88),
    ],
];
