<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    private HealthCheckService $healthCheckService;

    public function __construct(HealthCheckService $healthCheckService)
    {
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Get comprehensive health status
     */
    public function health(): JsonResponse
    {
        $healthData = $this->healthCheckService->checkAllEndpoints();
        
        $statusCode = $healthData['status'] === 'healthy' ? 200 : 503;
        
        return response()->json($healthData, $statusCode);
    }

    /**
     * Get health metrics for monitoring systems
     */
    public function metrics(): JsonResponse
    {
        $metrics = $this->healthCheckService->getMetrics();
        
        return response()->json($metrics);
    }

    /**
     * Get health status in a simple format
     */
    public function status(): JsonResponse
    {
        $healthData = $this->healthCheckService->checkAllEndpoints();
        
        $simpleStatus = [
            'status' => $healthData['status'],
            'timestamp' => $healthData['timestamp'],
            'services' => array_map(function($service) {
                return [
                    'name' => $service['service'],
                    'healthy' => $service['healthy'],
                    'response_time' => $service['response_time']
                ];
            }, $healthData['services'])
        ];
        
        $statusCode = $healthData['status'] === 'healthy' ? 200 : 503;
        
        return response()->json($simpleStatus, $statusCode);
    }

    /**
     * Get detailed health check for a specific service
     */
    public function service(string $serviceName): JsonResponse
    {
        $endpoints = config('healthcheck.endpoints');
        
        if (!isset($endpoints[$serviceName])) {
            return response()->json([
                'error' => 'Service not found',
                'available_services' => array_keys($endpoints)
            ], 404);
        }
        
        $result = $this->healthCheckService->checkEndpoint($serviceName, $endpoints[$serviceName]);
        
        $statusCode = $result['healthy'] ? 200 : 503;
        
        return response()->json($result, $statusCode);
    }
}
