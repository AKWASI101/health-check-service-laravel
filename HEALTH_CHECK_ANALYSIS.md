# Health Check Service Analysis

## Overview

This health check service monitors 3 endpoints and exposes comprehensive metrics via `/health`. The service is designed to detect service failures and provide detailed monitoring information for production environments.

## Architecture

### Components

1. **HealthCheckService** (`app/Services/HealthCheckService.php`)
   - Core service that performs health checks
   - Monitors multiple endpoints with configurable timeouts
   - Implements caching to reduce load on monitored services
   - Provides system metrics (memory, disk, uptime)

2. **HealthCheckController** (`app/Http/Controllers/HealthCheckController.php`)
   - RESTful API endpoints for health status
   - Multiple response formats (detailed, simple, metrics)
   - Individual service health checks

3. **Configuration** (`config/healthcheck.php`)
   - Configurable endpoints, timeouts, and thresholds
   - Environment-based configuration support

## Endpoints

### Health Check Endpoints

- `GET /health` - Comprehensive health status
- `GET /health/status` - Simple health status
- `GET /health/metrics` - Monitoring metrics
- `GET /health/service/{name}` - Individual service check

### Test Endpoints (for simulation)

- `GET /api/status` - Healthy service simulation
- `GET /api/unreliable` - Service with random failures
- `GET /api/slow` - Service with slow responses

## What Happens When a Service Goes Down

### 1. Detection Process

When a monitored service goes down, the health check service will:

1. **Attempt Connection**: Make an HTTP request to the service endpoint
2. **Timeout Handling**: Wait for the configured timeout period (default: 5 seconds)
3. **Error Classification**: Categorize the failure type (timeout, connection refused, HTTP error)
4. **Status Update**: Mark the service as unhealthy
5. **Logging**: Log the failure with details for debugging
6. **Caching**: Cache the failure result to avoid repeated checks

### 2. Response Behavior

```json
{
  "status": "unhealthy",
  "timestamp": "2024-01-15T10:30:00.000Z",
  "total_response_time": 5000,
  "failed_services": ["database"],
  "services": {
    "database": {
      "service": "database",
      "endpoint": "http://localhost:3306",
      "healthy": false,
      "status_code": null,
      "response_time": 5000,
      "error": "Connection timed out after 5 seconds",
      "timestamp": "2024-01-15T10:30:00.000Z"
    },
    "redis": {
      "service": "redis",
      "endpoint": "http://localhost:6379",
      "healthy": true,
      "status_code": 200,
      "response_time": 45.2,
      "error": null,
      "timestamp": "2024-01-15T10:30:00.000Z"
    },
    "api": {
      "service": "api",
      "endpoint": "http://localhost:8000/api/status",
      "healthy": true,
      "status_code": 200,
      "response_time": 123.5,
      "error": null,
      "timestamp": "2024-01-15T10:30:00.000Z"
    }
  },
  "uptime": "2 days, 5 hours",
  "memory_usage": {
    "current": "45.2 MB",
    "peak": "67.8 MB",
    "limit": "128M",
    "percentage": 35.3
  },
  "disk_usage": {
    "total": "500 GB",
    "used": "250 GB",
    "free": "250 GB",
    "percentage": 50.0
  }
}
```

### 3. HTTP Status Codes

- **200 OK**: All services healthy
- **503 Service Unavailable**: One or more services unhealthy

## Production Detection Strategies

### 1. Monitoring Integration

**Prometheus/Grafana Setup:**
```yaml
# prometheus.yml
scrape_configs:
  - job_name: 'health-check'
    static_configs:
      - targets: ['localhost:8000']
    metrics_path: '/health/metrics'
    scrape_interval: 30s
```

**Key Metrics to Monitor:**
- `health_status` (0 = unhealthy, 1 = healthy)
- `healthy_services` / `unhealthy_services`
- `average_response_time`
- `memory_usage_percentage`
- `disk_usage_percentage`

### 2. Alerting Rules

**Critical Alerts:**
- Any service down for > 2 minutes
- Response time > 1 second
- Memory usage > 80%
- Disk usage > 90%

**Warning Alerts:**
- Service down for > 30 seconds
- Response time > 500ms
- Memory usage > 70%
- Disk usage > 80%

### 3. Log Analysis

**Structured Logging:**
```json
{
  "level": "warning",
  "message": "Health check failed for database",
  "context": {
    "endpoint": "http://localhost:3306",
    "error": "Connection timed out after 5 seconds",
    "response_time": 5000,
    "service": "database"
  },
  "timestamp": "2024-01-15T10:30:00.000Z"
}
```

**Log Queries:**
- `grep "Health check failed" /var/log/laravel.log`
- `grep "database" /var/log/laravel.log | grep "failed"`
- Monitor error rates and patterns

### 4. Automated Response

**Immediate Actions:**
1. **Alert Notification**: Send alerts to on-call engineers
2. **Service Restart**: Automatically restart failed services
3. **Traffic Routing**: Route traffic away from failed services
4. **Scaling**: Scale up healthy services to handle load

**Example Alert Script:**
```bash
#!/bin/bash
# check_health.sh
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/health)
if [ $response -ne 200 ]; then
    # Send alert
    curl -X POST -H 'Content-type: application/json' \
        --data '{"text":"Health check failed! Status: '$response'"}' \
        $SLACK_WEBHOOK_URL
fi
```

### 5. Dashboard Monitoring

**Key Dashboard Widgets:**
- Service status overview (green/red indicators)
- Response time trends
- Error rate over time
- System resource usage
- Service dependency map

### 6. Health Check Best Practices

**Service Design:**
- Implement dedicated health check endpoints
- Avoid heavy operations in health checks
- Return consistent response formats
- Include service version and build info

**Monitoring Strategy:**
- Check frequently (every 30 seconds)
- Use multiple monitoring locations
- Implement circuit breakers
- Set up redundancy for critical services

**Response Planning:**
- Document runbooks for common failures
- Practice incident response procedures
- Maintain service dependency documentation
- Implement automated recovery where possible

## Configuration

The service can be configured via environment variables:

```bash
HEALTH_DATABASE_URL=http://localhost:3306
HEALTH_REDIS_URL=http://localhost:6379
HEALTH_API_URL=http://localhost:8000/api/status
HEALTH_TIMEOUT=5
HEALTH_CACHE_TTL=30
HEALTH_RETRY_ATTEMPTS=3
HEALTH_NOTIFICATIONS_ENABLED=false
```

## Testing the Service

1. **Start the Laravel server:**
   ```bash
   php artisan serve
   ```

2. **Test health check endpoints:**
   ```bash
   curl http://localhost:8000/health
   curl http://localhost:8000/health/status
   curl http://localhost:8000/health/metrics
   ```

3. **Test individual services:**
   ```bash
   curl http://localhost:8000/health/service/database
   curl http://localhost:8000/health/service/api
   ```

4. **Simulate service failures:**
   ```bash
   # Stop a service and observe health check response
   # Check logs for failure detection
   ```

This health check service provides comprehensive monitoring capabilities and follows production-ready patterns for service reliability and observability.
