<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestServiceController extends Controller
{
    /**
     * Simulate a healthy API service
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'test-api',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    }

    /**
     * Simulate a service with random failures
     */
    public function unreliable(): JsonResponse
    {
        $random = rand(1, 10);
        
        if ($random <= 3) { // 30% chance of failure
            return response()->json([
                'status' => 'error',
                'error' => 'Service temporarily unavailable'
            ], 503);
        }
        
        return response()->json([
            'status' => 'healthy',
            'service' => 'unreliable-api',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Simulate a slow service
     */
    public function slow(): JsonResponse
    {
        sleep(2); // Simulate slow response
        
        return response()->json([
            'status' => 'healthy',
            'service' => 'slow-api',
            'timestamp' => now()->toISOString(),
            'response_time' => '2000ms'
        ]);
    }
}
