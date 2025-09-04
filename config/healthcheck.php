<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the health check service.
    | You can configure the endpoints to monitor, timeout settings, and caching.
    |
    */

    'endpoints' => [
        'database' => env('HEALTH_DATABASE_URL', 'http://localhost:3306'),
        'redis' => env('HEALTH_REDIS_URL', 'http://localhost:6379'),
        'api' => env('HEALTH_API_URL', 'http://localhost:8000/api/status'),
    ],

    'timeout' => env('HEALTH_TIMEOUT', 5), // seconds

    'cache_ttl' => env('HEALTH_CACHE_TTL', 30), // seconds

    'retry_attempts' => env('HEALTH_RETRY_ATTEMPTS', 3),

    'alert_thresholds' => [
        'response_time' => 1000, // milliseconds
        'memory_usage' => 80, // percentage
        'disk_usage' => 90, // percentage
    ],

    'notifications' => [
        'enabled' => env('HEALTH_NOTIFICATIONS_ENABLED', false),
        'channels' => ['mail', 'slack'], // Add your notification channels
    ],
];
