<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HealthCheckService
{
    private array $endpoints;
    private int $timeout;
    private int $cacheTtl;

    public function __construct()
    {
        $this->endpoints = config('healthcheck.endpoints', [
            'database' => 'http://localhost:3306',
            'redis' => 'http://localhost:6379',
            'api' => 'http://localhost:8000/api/status'
        ]);
        $this->timeout = config('healthcheck.timeout', 5);
        $this->cacheTtl = config('healthcheck.cache_ttl', 30);
    }

    /**
     * Check the health of all configured endpoints
     */
    public function checkAllEndpoints(): array
    {
        $results = [];
        $overallStatus = 'healthy';
        $totalResponseTime = 0;
        $failedServices = [];

        foreach ($this->endpoints as $serviceName => $endpoint) {
            $result = $this->checkEndpoint($serviceName, $endpoint);
            $results[$serviceName] = $result;
            $totalResponseTime += $result['response_time'];

            if (!$result['healthy']) {
                $overallStatus = 'unhealthy';
                $failedServices[] = $serviceName;
            }
        }

        return [
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'total_response_time' => $totalResponseTime,
            'failed_services' => $failedServices,
            'services' => $results,
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage()
        ];
    }

    /**
     * Check a single endpoint
     */
    public function checkEndpoint(string $serviceName, string $endpoint): array
    {
        $cacheKey = "health_check_{$serviceName}";
        
        // Return cached result if available and not expired
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if ($cached['timestamp'] > now()->subSeconds($this->cacheTtl)) {
                return $cached;
            }
        }

        $startTime = microtime(true);
        $healthy = false;
        $error = null;
        $statusCode = null;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'HealthCheckService/1.0',
                    'Accept' => 'application/json'
                ])
                ->get($endpoint);

            $statusCode = $response->status();
            $healthy = $response->successful();
            
            if (!$healthy) {
                $error = "HTTP {$statusCode}: " . $response->body();
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
            $healthy = false;
        }

        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        $result = [
            'service' => $serviceName,
            'endpoint' => $endpoint,
            'healthy' => $healthy,
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'error' => $error,
            'timestamp' => now()->toISOString()
        ];

        // Cache the result
        Cache::put($cacheKey, $result, $this->cacheTtl);

        // Log failures
        if (!$healthy) {
            Log::warning("Health check failed for {$serviceName}", [
                'endpoint' => $endpoint,
                'error' => $error,
                'response_time' => $responseTime
            ]);
        }

        return $result;
    }

    /**
     * Get system uptime
     */
    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $uptime = shell_exec('wmic os get lastbootuptime /value');
            if ($uptime) {
                preg_match('/LastBootUpTime=(\d{14})/', $uptime, $matches);
                if (isset($matches[1])) {
                    $bootTime = \DateTime::createFromFormat('YmdHis', $matches[1]);
                    return $bootTime ? now()->diffForHumans($bootTime) : 'Unknown';
                }
            }
        } else {
            $uptime = shell_exec('uptime -p');
            return $uptime ? trim($uptime) : 'Unknown';
        }
        
        return 'Unknown';
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');

        return [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'limit' => $memoryLimit,
            'percentage' => round(($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100, 2)
        ];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }

        return $memoryLimit;
    }

    /**
     * Get health check metrics for monitoring
     */
    public function getMetrics(): array
    {
        $healthData = $this->checkAllEndpoints();
        
        return [
            'health_status' => $healthData['status'] === 'healthy' ? 1 : 0,
            'total_services' => count($this->endpoints),
            'healthy_services' => count($this->endpoints) - count($healthData['failed_services']),
            'unhealthy_services' => count($healthData['failed_services']),
            'average_response_time' => count($this->endpoints) > 0 ? 
                round($healthData['total_response_time'] / count($this->endpoints), 2) : 0,
            'memory_usage_percentage' => $healthData['memory_usage']['percentage'],
            'disk_usage_percentage' => $healthData['disk_usage']['percentage']
        ];
    }
}
