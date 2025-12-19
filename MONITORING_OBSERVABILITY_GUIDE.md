## 📊 MONITORING & OBSERVABILITY GUIDE
**Telemedicine System Production Monitoring**

---

## 1. REAL-TIME MONITORING DASHBOARD

### Key Metrics to Monitor

#### Application Health
```
✅ Response Time (Target: < 500ms)
✅ Error Rate (Target: < 0.5%)
✅ CPU Usage (Threshold: > 80% = Alert)
✅ Memory Usage (Threshold: > 85% = Alert)
✅ Disk Space (Threshold: > 90% = Alert)
✅ Active Connections (Info only)
```

#### API Performance
```
GET /api/health - Should return 200 OK
Response: {
    "status": "ok",
    "database": "connected",
    "redis": "connected",
    "queue": "running",
    "timestamp": "2025-12-19T10:30:00Z"
}
```

---

## 2. LOG MONITORING

### Log Files to Monitor

```bash
# Application Logs
/var/log/telemedicine/laravel.log
- Monitor for: ERROR, CRITICAL, WARNING
- Rotation: Daily, keep 30 days
- Alert trigger: > 10 errors/hour

# Queue Logs
/var/log/supervisor/telemedicine-queue.log
- Monitor for: Failed jobs, timeouts
- Alert trigger: > 5 failed/hour

# Web Server Logs
/var/log/nginx/error.log
/var/log/apache2/error.log
- Monitor for: 5xx errors
- Alert trigger: > 10 5xx/hour

# Database Logs
/var/log/mysql/error.log
- Monitor for: Connection errors, slow queries
- Alert trigger: > 3 connection errors/hour

# Redis Logs
/var/log/redis/redis-server.log
- Monitor for: Connection/memory issues
- Alert trigger: Eviction warnings
```

### Log Analysis Commands

```bash
# Count errors in last hour
grep "ERROR\|CRITICAL" /var/log/telemedicine/laravel.log | \
  grep "$(date -d '1 hour ago' '+%Y-%m-%d %H')" | wc -l

# Find slow queries
grep "slow query" /var/log/mysql/error.log | tail -20

# Check queue failure rate
grep "failed" /var/log/supervisor/telemedicine-queue.log | \
  wc -l

# Monitor real-time errors
tail -f /var/log/telemedicine/laravel.log | grep -i "error\|exception"

# Check disk usage by date
du -sh /var/log/* | sort -h
```

---

## 3. DATABASE MONITORING

### Query Performance

```sql
-- Find slow queries
SELECT * FROM mysql.slow_log 
ORDER BY query_time DESC 
LIMIT 10;

-- Check active connections
SHOW PROCESSLIST;

-- Monitor query count
SHOW STATUS WHERE variable_name IN (
    'Com_select', 'Com_insert', 'Com_update', 
    'Com_delete', 'Questions'
);

-- Check table sizes
SELECT 
    table_schema,
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'telemedicine_prod'
ORDER BY size_mb DESC;

-- Monitor replication lag (if replication enabled)
SHOW SLAVE STATUS\G
```

### Database Indexes

```sql
-- Verify indexes are being used
SELECT 
    object_schema,
    object_name,
    count_read,
    count_write
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema = 'telemedicine_prod'
ORDER BY count_read DESC;

-- Find missing indexes
SELECT object_schema, object_name, count_star
FROM performance_schema.table_io_waits_summary_by_table
WHERE object_schema = 'telemedicine_prod'
AND count_star > 0
ORDER BY count_star DESC;
```

---

## 4. REDIS MONITORING

### Redis Commands

```bash
# Check Redis info
redis-cli INFO

# Monitor memory usage
redis-cli INFO memory

# Get connection count
redis-cli INFO clients

# Monitor real-time commands
redis-cli MONITOR

# Check keyspace
redis-cli INFO keyspace

# Analyze memory by key type
redis-cli --pipe < analysis.txt

# Clear cache if needed
redis-cli FLUSHDB

# Backup Redis
redis-cli --rdb /backup/redis-dump.rdb
```

### Redis Memory Alert

```bash
#!/bin/bash
# /usr/local/bin/check_redis_memory.sh

REDIS_HOST="localhost"
REDIS_PORT="6379"
MAX_MEMORY_MB=1024

USED_MEMORY=$(redis-cli -h $REDIS_HOST -p $REDIS_PORT INFO memory | \
  grep used_memory_human | cut -d'M' -f1 | tail -c 4)

if [ $USED_MEMORY -gt $MAX_MEMORY_MB ]; then
    echo "ALERT: Redis memory usage at ${USED_MEMORY}MB (max: ${MAX_MEMORY_MB}MB)"
    # Send alert email
fi
```

---

## 5. QUEUE MONITORING

### Check Queue Status

```bash
# Monitor queue in real-time
php artisan queue:monitor redis::default

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# Monitor queue performance
php artisan tinker
> \Queue::push(new TestJob);
> Queue::size('default');  // Should return number of jobs
```

### Queue Performance Metrics

```bash
# Monitor queue depth over time
watch -n 5 'redis-cli LLEN "queues:default"'

# Check failed jobs count
redis-cli LLEN "queues:failed"

# Monitor job processing rate
redis-cli LRANGE "queues:default" 0 -1 | wc -l
```

---

## 6. REAL-TIME (PUSHER) MONITORING

### Pusher Dashboard Metrics

```
✅ Messages per minute
✅ Active connections
✅ Peak concurrent users
✅ Message latency (p95, p99)
✅ Failed authentications
✅ Channel subscription errors
```

### Test Pusher Connectivity

```php
<?php
// test_pusher.php
require_once('vendor/autoload.php');

$pusher = new Pusher\Pusher(
    env('PUSHER_APP_KEY'),
    env('PUSHER_APP_SECRET'),
    env('PUSHER_APP_ID'),
    ['cluster' => env('PUSHER_APP_CLUSTER')]
);

// Test connection
$result = $pusher->trigger('test-channel', 'test-event', ['message' => 'Hello']);
echo $result ? "✅ Connected to Pusher\n" : "❌ Pusher connection failed\n";

// Get channel info
$info = $pusher->get_channel_info('test-channel');
print_r($info);
?>
```

---

## 7. API MONITORING

### Health Check Endpoint

```bash
# Implement health check
GET /api/health

Response:
{
    "status": "ok",
    "version": "1.0.0",
    "database": "connected",
    "redis": "connected", 
    "queue": "running",
    "storage": "accessible",
    "timestamp": "2025-12-19T10:30:00Z",
    "response_time_ms": 45
}
```

### Monitor Critical Endpoints

```bash
# Login endpoint
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"test"}' \
  -w "\nResponse Time: %{time_total}s\n"

# Doctor list endpoint
curl -X GET https://your-domain.com/api/dokter \
  -H "Authorization: Bearer TOKEN" \
  -w "\nResponse Time: %{time_total}s\n"

# Consultation list endpoint
curl -X GET https://your-domain.com/api/consultations \
  -H "Authorization: Bearer TOKEN" \
  -w "\nResponse Time: %{time_total}s\n"
```

### Rate Limiting Monitoring

```bash
# Check current rate limit status
curl -X GET https://your-domain.com/api/rate-limit-status \
  -H "Authorization: Bearer TOKEN" \
  -I

# Response headers to monitor:
# X-RateLimit-Limit: 100
# X-RateLimit-Remaining: 45
# X-RateLimit-Reset: 1639960800
```

---

## 8. SECURITY MONITORING

### SSL Certificate Monitoring

```bash
# Check certificate expiry
openssl x509 -enddate -noout -in /etc/ssl/certs/your-domain.com.crt

# Days remaining
echo $(($(date -d "$(openssl x509 -enddate -noout -in /etc/ssl/certs/your-domain.com.crt|cut -d= -f 2)" +%s) - $(date +%s))) / 86400 | bc)

# Set alert if < 30 days
if [ $DAYS_LEFT -lt 30 ]; then
    echo "ALERT: SSL certificate expires in $DAYS_LEFT days"
fi
```

### Security Headers Check

```bash
# Verify security headers
curl -I https://your-domain.com | grep -E "Strict-Transport|X-Content-Type|X-Frame|CSP"

# Expected output:
# Strict-Transport-Security: max-age=31536000
# X-Content-Type-Options: nosniff
# X-Frame-Options: SAMEORIGIN
# Content-Security-Policy: ...
```

### Failed Authentication Attempts

```bash
# Monitor failed logins
grep "failed\|401\|unauthorized" /var/log/telemedicine/laravel.log | \
  grep "$(date '+%Y-%m-%d')" | wc -l

# Alert if > 20 failed attempts/hour
if [ $FAILED_ATTEMPTS -gt 20 ]; then
    echo "ALERT: High failed login attempts: $FAILED_ATTEMPTS"
fi
```

---

## 9. SETUP MONITORING TOOLS

### Option 1: Using ELK Stack (Elasticsearch, Logstash, Kibana)

```bash
# Install Elasticsearch
docker run -d --name elasticsearch -e "discovery.type=single-node" \
  -p 9200:9200 docker.elastic.co/elasticsearch/elasticsearch:8.0.0

# Install Logstash
docker run -d --name logstash -p 5000:5000 \
  -v $(pwd)/logstash.conf:/usr/share/logstash/pipeline/logstash.conf \
  docker.elastic.co/logstash/logstash:8.0.0

# Install Kibana
docker run -d --name kibana -p 5601:5601 \
  -e ELASTICSEARCH_HOSTS=http://elasticsearch:9200 \
  docker.elastic.co/kibana/kibana:8.0.0
```

### Option 2: Using Datadog

```bash
# Install Datadog agent
DD_AGENT_MAJOR_VERSION=7 \
DD_API_KEY=<your_api_key> \
DD_SITE="datadoghq.com" \
bash -c "$(curl -L https://s3.amazonaws.com/dd-agent/scripts/install_agent.sh)"

# Configure for Laravel
# /etc/datadog-agent/conf.d/laravel.d/conf.yaml
```

### Option 3: Using New Relic

```bash
# Install New Relic
sudo apt-get install newrelic-php5

# Configure
newrelic-install install

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

---

## 10. ALERTING SETUP

### Email Alerts (using cron + mail)

```bash
#!/bin/bash
# /usr/local/bin/health_check.sh

TO_EMAIL="admin@telemedicine.com"
SUBJECT_PREFIX="⚠️ ALERT: Telemedicine"

# Check CPU usage
CPU=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
if (( $(echo "$CPU > 80" | bc -l) )); then
    echo "CPU usage at ${CPU}%" | mail -s "$SUBJECT_PREFIX - High CPU" $TO_EMAIL
fi

# Check memory
MEM=$(free | grep Mem | awk '{print ($3/$2) * 100}' | cut -d. -f1)
if [ $MEM -gt 85 ]; then
    echo "Memory usage at ${MEM}%" | mail -s "$SUBJECT_PREFIX - High Memory" $TO_EMAIL
fi

# Check disk
DISK=$(df / | tail -1 | awk '{print $5}' | cut -d% -f1)
if [ $DISK -gt 90 ]; then
    echo "Disk usage at ${DISK}%" | mail -s "$SUBJECT_PREFIX - High Disk" $TO_EMAIL
fi

# Check error rate
ERRORS=$(grep ERROR /var/log/telemedicine/laravel.log | \
  grep "$(date '+%Y-%m-%d %H')" | wc -l)
if [ $ERRORS -gt 10 ]; then
    echo "High error count: $ERRORS errors in last hour" | \
    mail -s "$SUBJECT_PREFIX - High Error Rate" $TO_EMAIL
fi
```

Add to crontab:
```bash
# Run health check every 5 minutes
*/5 * * * * /usr/local/bin/health_check.sh
```

---

## 11. PERFORMANCE OPTIMIZATION

### Database Query Optimization

```bash
# Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
SET GLOBAL log_queries_not_using_indexes = 'ON';

# Analyze query performance
EXPLAIN SELECT * FROM konsultasi WHERE dokter_id = 1;

# Expected output should show:
# type: ref (or better)
# key: dokter_id_index
# rows: < 1000
```

### PHP-FPM Tuning

```ini
# /etc/php/8.2/fpm/pool.d/www.conf

; Process manager
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000

; Performance monitoring
pm.status_path = /fpm-status
request_slowlog_timeout = 10s
slowlog = /var/log/php-fpm-slow.log
```

### Redis Performance

```bash
# Monitor Redis memory
redis-cli INFO memory | grep used_memory

# Check keyspace size
redis-cli DBSIZE

# Monitor eviction policy
redis-cli INFO stats | grep evicted

# Optimize memory: Clear old sessions
redis-cli EVAL "return redis.call('del', unpack(redis.call('keys', ARGV[1])))" 0 "PHPSESSID:*"
```

---

## 12. DASHBOARD SETUP

### Grafana Dashboard

```json
{
  "dashboard": {
    "title": "Telemedicine Monitoring",
    "panels": [
      {
        "title": "API Response Time",
        "targets": [
          {"expr": "rate(http_request_duration_seconds_sum[5m])"}
        ]
      },
      {
        "title": "Error Rate",
        "targets": [
          {"expr": "rate(http_requests_total{status=~'5..'}[5m])"}
        ]
      },
      {
        "title": "Active Connections",
        "targets": [
          {"expr": "nginx_connections_active"}
        ]
      },
      {
        "title": "Queue Depth",
        "targets": [
          {"expr": "redis_llen{key='queues:default'}"}
        ]
      }
    ]
  }
}
```

---

## MONITORING CHECKLIST

### Daily
- [ ] Check error logs for patterns
- [ ] Monitor CPU/Memory/Disk usage
- [ ] Verify queue is processing
- [ ] Check failed jobs count

### Weekly  
- [ ] Review slow query logs
- [ ] Check certificate expiry
- [ ] Analyze API response times
- [ ] Review user feedback

### Monthly
- [ ] Rotate old logs
- [ ] Update monitoring thresholds
- [ ] Review security logs
- [ ] Plan capacity upgrades

---

**Last Updated:** 19 Desember 2025
**Status:** ✅ Ready for Production Deployment
