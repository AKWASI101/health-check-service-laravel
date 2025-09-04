# Health Check Service

A comprehensive health check service built with Laravel that monitors multiple endpoints and exposes detailed metrics for production monitoring.

## Features

- **Multi-Service Monitoring**: Monitor up to 3 (configurable) external endpoints
- **Comprehensive Metrics**: System uptime, memory usage, disk usage, response times
- **Caching**: Intelligent caching to reduce load on monitored services
- **Multiple Endpoints**: Various health check endpoints for different use cases
- **Error Handling**: Detailed error logging and classification
- **Production Ready**: Built with monitoring and alerting in mind

## Quick Start

1. **Install Dependencies:**
   ```bash
   composer install
   ```

2. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Start the Server:**
   ```bash
   php artisan serve
   ```

4. **Test Health Check:**
   ```bash
   curl http://localhost:8000/health
   ```

## API Endpoints

### Health Check Endpoints

- `GET /health` - Comprehensive health status with all metrics
- `GET /health/status` - Simple health status overview
- `GET /health/metrics` - Monitoring metrics for Prometheus/Grafana
- `GET /health/service/{name}` - Check individual service health

### Test Endpoints

- `GET /api/status` - Simulates a healthy service
- `GET /api/unreliable` - Simulates a service with random failures
- `GET /api/slow` - Simulates a slow-responding service

## Configuration

Configure monitored endpoints in `config/healthcheck.php` or via environment variables:

```bash
HEALTH_DATABASE_URL=http://localhost:3306
HEALTH_REDIS_URL=http://localhost:6379
HEALTH_API_URL=http://localhost:8000/api/status
HEALTH_TIMEOUT=5
HEALTH_CACHE_TTL=30
```

## Production Monitoring

See `HEALTH_CHECK_ANALYSIS.md` for detailed information about:
- Service failure detection
- Production monitoring strategies
- Alerting configuration
- Dashboard setup
- Best practices

## Example Response

```json
{
  "status": "healthy",
  "timestamp": "2024-01-15T10:30:00.000Z",
  "total_response_time": 168.7,
  "failed_services": [],
  "services": {
    "database": {
      "service": "database",
      "endpoint": "http://localhost:3306",
      "healthy": true,
      "status_code": 200,
      "response_time": 45.2,
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

## Testing

Run the test suite:
```bash
php artisan test
```
