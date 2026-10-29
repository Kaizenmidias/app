<?php

return [
    'registration_enabled' => filter_var(env('REGISTRATION_ENABLED', true), FILTER_VALIDATE_BOOL),
    'scraper' => [
        'base_url' => rtrim(env('SCRAPER_BASE_URL', 'http://127.0.0.1:8080'), '/'),
        'api_key' => env('SCRAPER_API_KEY'),
   'timeout' => (int) env('SCRAPER_TIMEOUT', 60),
   'poll_seconds' => (int) env('SCRAPER_POLL_SECONDS', 10),
   'max_duration' => (int) env('SCRAPER_MAX_DURATION', 3600),
   'max_attempts' => (int) env('SCRAPER_MAX_ATTEMPTS', 3),
   'concurrency' => (int) env('SCRAPER_CONCURRENCY', 1),
   'import_max_bytes' => (int) env('SCRAPER_IMPORT_MAX_BYTES', 25 * 1024 * 1024),
   'import_max_rows' => (int) env('SCRAPER_IMPORT_MAX_ROWS', 10000),
    ],
    'apify' => [
        'base_url' => rtrim(env('APIFY_BASE_URL', 'https://api.apify.com/v2'), '/'),
        'actor' => env('APIFY_ACTOR', 'compass~crawler-google-places'),
        'timeout' => (int) env('APIFY_TIMEOUT', 90),
        'concurrency' => (int) env('APIFY_CONCURRENCY', 4),
    ],
];
