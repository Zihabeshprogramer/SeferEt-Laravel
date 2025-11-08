# Advertisement System - Production Rollout Plan
**SeferEt Umrah Booking System**

---

## 📋 Executive Summary

This document outlines the comprehensive deployment strategy for the Advertisement Management System, including migrations, worker setup, cache configuration, cron scheduling, feature-flag rollout, backout procedures, and monitoring plans.

**Rollout Date:** TBD  
**Rollout Type:** Phased (gradual with feature flags)  
**Expected Duration:** 3-4 weeks  
**Risk Level:** Medium  

---

## 🎯 Deployment Objectives

1. ✅ Zero-downtime deployment of ads feature
2. ✅ Gradual rollout with ability to disable instantly
3. ✅ Comprehensive monitoring and alerting
4. ✅ Safe rollback procedures
5. ✅ Performance optimization (cache, CDN)

---

## 📦 Pre-Deployment Checklist

### 1. Environment Verification

**Production Server Requirements:**
- [ ] PHP 8.1+ installed and configured
- [ ] MySQL 5.7+ or 8.0+
- [ ] Redis server installed and running
- [ ] Supervisor installed for queue workers
- [ ] GD or Imagick PHP extension enabled
- [ ] Storage directory writable (chmod 775)
- [ ] Minimum 2GB free disk space
- [ ] CDN/S3 configuration (optional but recommended)

**Environment Variables:**
```bash
# Add to .env file
QUEUE_CONNECTION=redis          # Or database for smaller deployments
CACHE_DRIVER=redis              # Or file for smaller deployments
FILESYSTEM_DISK=s3              # Or local for testing

# Image Processing Configuration
ADS_MAX_FILE_SIZE=5120          # 5MB in KB
ADS_IMAGE_QUALITY=85            # JPEG quality 0-100
ADS_CACHE_TTL=120               # Cache TTL in seconds (2 minutes)

# Feature Flag (for gradual rollout)
FEATURE_ADS_ENABLED=false       # Start disabled, enable later
FEATURE_ADS_ADMIN_ONLY=true     # Admin-only testing first
FEATURE_ADS_B2B_ENABLED=false   # B2B users
FEATURE_ADS_PUBLIC_ENABLED=false # Public ad serving

# Performance Tuning
ADS_WORKER_PROCESSES=2          # Number of image processing workers
ADS_MAX_VARIANTS=4              # Keep at 4 (thumbnail, small, medium, large)
```

### 2. Code Review Checklist

- [ ] All migrations tested in staging environment
- [ ] Image processing service unit tests passing
- [ ] API endpoints secured with proper authorization
- [ ] XSS/injection vulnerabilities addressed
- [ ] Rate limiting configured on public endpoints
- [ ] SQL queries optimized with proper indexes
- [ ] N+1 query issues resolved
- [ ] Dead code removed

### 3. Database Backup

**CRITICAL: Backup database before migration**

```bash
# Production database backup
php artisan backup:run --only-db

# Or manual MySQL backup
mysqldump -u root -p seferet_db > backup_pre_ads_$(date +%Y%m%d_%H%M%S).sql

# Verify backup integrity
mysql -u root -p seferet_db_test < backup_pre_ads_YYYYMMDD_HHMMSS.sql
```

**Backup Retention:**
- Keep backup for minimum 30 days
- Store off-site (S3, separate server)
- Document restore procedure

---

## 🚀 Step-by-Step Deployment

### Phase 1: Infrastructure Setup (Day 1)

#### Step 1.1: Install Dependencies

```bash
# On production server
cd /var/www/SeferEt-Laravel

# Update composer dependencies (if needed)
composer install --no-dev --optimize-autoloader

# Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 1.2: Verify Intervention Image Library

```bash
# Check if GD or Imagick is installed
php -m | grep -i gd
php -m | grep -i imagick

# If missing, install:
# For Ubuntu/Debian:
sudo apt-get install php-gd php-imagick
sudo systemctl restart php8.1-fpm
```

#### Step 1.3: Storage Setup

```bash
# Create storage directories
mkdir -p storage/app/public/ads/{original,cropped,variants}

# Set permissions
sudo chown -R www-data:www-data storage/app/public/ads
sudo chmod -R 775 storage/app/public/ads

# Create symbolic link (if not exists)
php artisan storage:link

# Verify storage is writable
touch storage/app/public/ads/test.txt && rm storage/app/public/ads/test.txt
```

#### Step 1.4: Redis Setup (Recommended)

```bash
# Install Redis (Ubuntu/Debian)
sudo apt-get install redis-server

# Start Redis
sudo systemctl start redis
sudo systemctl enable redis

# Test connection
redis-cli ping  # Should return PONG

# Update .env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Phase 2: Database Migration (Day 1-2)

#### Step 2.1: Pre-Migration Checks

```bash
# Check current migration status
php artisan migrate:status

# Verify no pending migrations will break
php artisan migrate --pretend

# Check database connection
php artisan db:show
```

#### Step 2.2: Run Migrations (PRODUCTION)

```bash
# Enable maintenance mode
php artisan down --message="System upgrade in progress. Back in 10 minutes."

# Run migrations
php artisan migrate --force

# Expected migrations:
# ✓ 2025_11_08_000001_create_ads_table
# ✓ 2025_11_08_000002_create_ad_audit_logs_table
# ✓ 2025_11_08_000003_create_ad_impressions_table
# ✓ 2025_11_08_000004_create_ad_clicks_table
# ✓ 2025_11_08_000005_enhance_ads_table
# ✓ 2025_11_08_000006_create_ad_analytics_daily_table

# Verify migrations succeeded
php artisan migrate:status | grep ads

# Disable maintenance mode
php artisan up
```

**Expected Downtime:** 2-5 minutes

#### Step 2.3: Verify Database Schema

```sql
-- Connect to MySQL and verify tables
USE seferet_db;

SHOW TABLES LIKE 'ads%';
-- Expected:
-- ads
-- ad_audit_logs
-- ad_clicks
-- ad_impressions
-- ad_analytics_daily

-- Verify indexes
SHOW INDEX FROM ads;
-- Should see indexes on:
-- owner_id, owner_type
-- product_id, product_type
-- status, priority, is_active
-- start_at, end_at
-- device_type, placement
```

### Phase 3: Queue Worker Setup (Day 2)

#### Step 3.1: Create Supervisor Configuration

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/seferet-queue-ads.conf
```

**Configuration Content:**
```ini
[program:seferet-queue-ads]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/SeferEt-Laravel/artisan queue:work redis --queue=ads,default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/SeferEt-Laravel/storage/logs/queue-ads.log
stopwaitsecs=3600
```

#### Step 3.2: Start Queue Workers

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start seferet-queue-ads:*

# Verify workers are running
sudo supervisorctl status seferet-queue-ads:*

# Monitor queue in real-time (optional)
php artisan queue:listen --queue=ads
```

#### Step 3.3: Test Queue Processing

```bash
# Dispatch test job
php artisan tinker
>>> dispatch(new \App\Jobs\ProcessAdScheduling());
>>> exit

# Check queue status
php artisan queue:work --once

# Monitor logs
tail -f storage/logs/queue-ads.log
```

### Phase 4: Cron Job Configuration (Day 2)

#### Step 4.1: Configure Laravel Scheduler

```bash
# Edit crontab
crontab -e
```

**Add this single line:**
```cron
* * * * * cd /var/www/SeferEt-Laravel && php artisan schedule:run >> /dev/null 2>&1
```

#### Step 4.2: Verify Scheduled Jobs

```bash
# List scheduled tasks
php artisan schedule:list

# Expected scheduled jobs for ads:
# ✓ App\Jobs\ProcessAdScheduling      Every minute
# ✓ App\Jobs\AggregateAdAnalytics     Daily at 01:00
```

**Scheduled Tasks Overview:**

| Task | Frequency | Purpose | Priority |
|------|-----------|---------|----------|
| `ProcessAdScheduling` | Every 1 min | Auto-activate/expire ads, check limits | HIGH |
| `AggregateAdAnalytics` | Daily 1 AM | Aggregate daily analytics | MEDIUM |
| `featured:cleanup` | Daily 3 AM | Clean expired featured items | LOW |
| `drafts:cleanup-packages` | Daily 2 AM | Clean old draft packages | LOW |

#### Step 4.3: Test Cron Jobs

```bash
# Manually trigger scheduled jobs
php artisan schedule:run

# Check logs
tail -f storage/logs/laravel.log | grep -i "schedule\|ad"

# Test specific job
php artisan schedule:test ProcessAdScheduling
```

### Phase 5: Cache Warm-Up (Day 2-3)

#### Step 5.1: Pre-Cache Configuration

```bash
# Clear existing cache
php artisan cache:clear

# Warm up configuration cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 5.2: Ad Cache Strategy

The system uses **short-lived cache (2 minutes TTL)** for ad serving to balance:
- ✅ Fast response times
- ✅ Fresh content
- ✅ Low database load

**Cache Keys Pattern:**
```
ads_serve_{placement}_{device}_{region}_{limit}
```

**Example:**
```
ads_serve_home_top_mobile_US_3
ads_serve_package_list_desktop_UK_5
```

#### Step 5.3: CDN Configuration (Optional but Recommended)

```bash
# If using S3 for storage
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=seferet-ads
AWS_URL=https://cdn.seferet.com
FILESYSTEM_DISK=s3

# Update storage config
php artisan storage:link
php artisan config:cache
```

**CDN Benefits:**
- 🚀 Faster image loading (geographic distribution)
- 📉 Reduced server bandwidth
- 💾 Better cache control

### Phase 6: Admin Notifications Setup (Day 3)

#### Step 6.1: Configure Mail Settings

```bash
# Update .env for production mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com      # Or your mail server
MAIL_PORT=587
MAIL_USERNAME=noreply@seferet.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seferet.com
MAIL_FROM_NAME="SeferEt System"
```

#### Step 6.2: Create Admin Notification System

**Events to Notify Admins:**
1. ✅ New ad submitted for approval
2. ✅ Ad reached impression/click limits
3. ✅ Ad budget exhausted
4. ✅ Multiple ad rejections by same user
5. ✅ Unusual click patterns (fraud detection)

**Create notification command:**
```bash
php artisan make:notification AdPendingApprovalNotification
php artisan make:notification AdLimitReachedNotification
```

#### Step 6.3: Test Notifications

```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test email from SeferEt', function($msg) {
...     $msg->to('admin@seferet.com')->subject('Test');
... });
>>> exit

# Verify email sent
tail -f storage/logs/laravel.log | grep -i mail
```

---

## 🎚️ Feature Flag Strategy (Gradual Rollout)

### Feature Flag Implementation

**Config File:** `config/features.php`

```php
<?php

return [
    'ads' => [
        'enabled' => env('FEATURE_ADS_ENABLED', false),
        'admin_only' => env('FEATURE_ADS_ADMIN_ONLY', true),
        'b2b_enabled' => env('FEATURE_ADS_B2B_ENABLED', false),
        'public_enabled' => env('FEATURE_ADS_PUBLIC_ENABLED', false),
        
        // Rollout percentages (0-100)
        'b2b_rollout_percentage' => env('FEATURE_ADS_B2B_PERCENTAGE', 0),
        'public_rollout_percentage' => env('FEATURE_ADS_PUBLIC_PERCENTAGE', 0),
    ],
];
```

**Helper Service:** `app/Services/FeatureFlagService.php`

```php
<?php

namespace App\Services;

class FeatureFlagService
{
    public static function isAdSystemEnabled(): bool
    {
        return config('features.ads.enabled', false);
    }
    
    public static function canUserAccessAds($user = null): bool
    {
        if (!self::isAdSystemEnabled()) {
            return false;
        }
        
        $user = $user ?? auth()->user();
        
        // Admin always has access
        if ($user && $user->role === 'admin') {
            return true;
        }
        
        // Check B2B access
        if ($user && in_array($user->role, ['travel_agent', 'hotel_provider', 'transport_provider'])) {
            if (!config('features.ads.b2b_enabled')) {
                return false;
            }
            
            // Percentage rollout
            $percentage = config('features.ads.b2b_rollout_percentage', 0);
            return $this->isInRolloutPercentage($user->id, $percentage);
        }
        
        return false;
    }
    
    public static function isPublicAdsEnabled(): bool
    {
        return config('features.ads.public_enabled', false);
    }
    
    protected function isInRolloutPercentage(int $userId, int $percentage): bool
    {
        if ($percentage >= 100) {
            return true;
        }
        if ($percentage <= 0) {
            return false;
        }
        
        // Consistent hashing for same user
        return ($userId % 100) < $percentage;
    }
}
```

### Rollout Timeline (4 Weeks)

#### **Week 1: Internal Testing**
```bash
# Day 1-2: Admin-only access
FEATURE_ADS_ENABLED=true
FEATURE_ADS_ADMIN_ONLY=true
FEATURE_ADS_B2B_ENABLED=false
FEATURE_ADS_PUBLIC_ENABLED=false
```

**Actions:**
- ✅ Admin creates test ads
- ✅ Test approval workflow
- ✅ Verify image processing
- ✅ Test audit logs
- ✅ Monitor performance

#### **Week 2: B2B Beta (10%)**
```bash
# Day 8-10: 10% of B2B users
FEATURE_ADS_ENABLED=true
FEATURE_ADS_ADMIN_ONLY=false
FEATURE_ADS_B2B_ENABLED=true
FEATURE_ADS_B2B_PERCENTAGE=10
FEATURE_ADS_PUBLIC_ENABLED=false
```

**Actions:**
- ✅ Monitor B2B user feedback
- ✅ Track error rates
- ✅ Measure ad creation times
- ✅ Check image processing queue

**Metrics to Monitor:**
- Ad creation success rate
- Image upload failures
- Queue processing time
- User feedback

#### **Week 2-3: B2B Expansion (50%)**
```bash
# Day 11-17: 50% of B2B users
FEATURE_ADS_B2B_PERCENTAGE=50
```

**Decision Point:**
- ✅ If errors < 1%, proceed
- ❌ If errors > 2%, rollback to 10%

#### **Week 3: B2B Full Rollout (100%)**
```bash
# Day 18-21: All B2B users
FEATURE_ADS_B2B_PERCENTAGE=100
```

#### **Week 4: Public Ad Serving (Gradual)**

**Day 22-23: Public Beta (5%)**
```bash
FEATURE_ADS_PUBLIC_ENABLED=true
FEATURE_ADS_PUBLIC_PERCENTAGE=5
```

**Day 24-26: Public Expansion (50%)**
```bash
FEATURE_ADS_PUBLIC_PERCENTAGE=50
```

**Day 27-28: Full Public Rollout**
```bash
FEATURE_ADS_PUBLIC_PERCENTAGE=100
```

---

## ⚠️ Backout Plan (Emergency Rollback)

### Level 1: Feature Flag Disable (Instant - 0 downtime)

**Fastest option - Disable via feature flags**

```bash
# Update .env immediately
FEATURE_ADS_ENABLED=false

# Or more granular:
FEATURE_ADS_B2B_ENABLED=false
FEATURE_ADS_PUBLIC_ENABLED=false

# Clear config cache
php artisan config:clear
php artisan config:cache
```

**Recovery Time:** < 30 seconds  
**Impact:** Ads disappear from UI, B2B users can't create new ads  
**Data Loss:** None

---

### Level 2: Disable Routes (Quick - minimal downtime)

**Add middleware to block ad routes**

```php
// routes/api.php - Comment out or wrap in condition
if (config('features.ads.enabled')) {
    Route::prefix('ads')->group(function () {
        // ... ad routes
    });
}
```

```bash
php artisan route:clear
php artisan route:cache
```

**Recovery Time:** 1-2 minutes  
**Impact:** 404 on ad endpoints  
**Data Loss:** None

---

### Level 3: Database Rollback (Last Resort - 5-15 min downtime)

**Only if database corruption or critical issues**

```bash
# Enable maintenance mode
php artisan down

# Restore database backup
mysql -u root -p seferet_db < backup_pre_ads_YYYYMMDD_HHMMSS.sql

# Rollback migrations
php artisan migrate:rollback --step=6

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Disable maintenance mode
php artisan up
```

**Recovery Time:** 5-15 minutes  
**Impact:** All ad data lost, system downtime  
**Data Loss:** All ads, analytics, impressions, clicks

---

### Backout Decision Matrix

| Severity | Trigger | Action | Expected Recovery Time |
|----------|---------|--------|------------------------|
| **Low** | Minor bugs, UI issues | Bug fix deployment | 1-2 hours |
| **Medium** | Error rate 2-5%, performance issues | Rollback to previous rollout stage | 5-10 minutes |
| **High** | Error rate > 5%, major bugs | Disable feature flags | < 1 minute |
| **Critical** | Data corruption, security breach | Full database rollback | 5-15 minutes |

---

## 📊 Monitoring & Alerting Plan

### 1. Application Performance Monitoring (APM)

**Metrics to Track:**

#### Ad Serving Performance
```bash
# Key metrics
ads.serve.latency            # Target: < 100ms (p95)
ads.serve.cache_hit_rate     # Target: > 80%
ads.serve.errors             # Target: < 1%
ads.serve.requests_per_sec   # Baseline: TBD
```

#### Image Processing
```bash
ads.image.upload_latency     # Target: < 3s
ads.image.processing_time    # Target: < 10s
ads.image.failures           # Target: < 0.5%
ads.queue.depth              # Target: < 100 jobs
ads.queue.wait_time          # Target: < 5s
```

#### Database Performance
```bash
ads.db.query_time            # Target: < 50ms
ads.db.connection_pool       # Monitor for exhaustion
ads.db.slow_queries          # Alert if > 1s
```

#### Analytics Tracking
```bash
ads.impressions.total        # Daily trend
ads.clicks.total             # Daily trend
ads.ctr.average              # Target: > 2%
ads.fraud.detected           # Alert threshold
```

---

### 2. Error Monitoring

**Laravel Log Integration:**

```php
// config/logging.php - Add dedicated ads channel
'channels' => [
    'ads' => [
        'driver' => 'daily',
        'path' => storage_path('logs/ads.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

**Critical Errors to Alert On:**
1. ❌ Image upload failures
2. ❌ Migration errors
3. ❌ Queue processing failures
4. ❌ Cache connection failures
5. ❌ Database deadlocks
6. ❌ Approval workflow errors

---

### 3. Monitoring Tools Setup

#### Option A: Self-Hosted (Laravel Telescope + Horizon)

```bash
# Install Telescope for debugging
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Install Horizon for queue monitoring
composer require laravel/horizon
php artisan horizon:install
php artisan migrate

# Start Horizon dashboard
php artisan horizon

# Access dashboards:
# http://your-domain.com/telescope
# http://your-domain.com/horizon
```

#### Option B: Cloud Monitoring (Recommended for Production)

**Recommended Services:**
1. **New Relic APM** - Full application monitoring
2. **Sentry** - Error tracking
3. **Datadog** - Infrastructure + APM
4. **CloudWatch** (if using AWS) - Logs and metrics

**Example: Sentry Setup**
```bash
composer require sentry/sentry-laravel

# Add to .env
SENTRY_LARAVEL_DSN=https://your-key@sentry.io/your-project

# Configure in config/sentry.php
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

---

### 4. Alert Configuration

**Critical Alerts (Page immediately):**
```yaml
Alerts:
  - name: "High Error Rate"
    condition: error_rate > 5%
    severity: critical
    action: Page DevOps + Disable feature flag
    
  - name: "Database Down"
    condition: db_connection_failed
    severity: critical
    action: Page DevOps + Enable maintenance mode
    
  - name: "Queue Backlog"
    condition: queue_depth > 1000
    severity: critical
    action: Page DevOps + Scale workers
```

**Warning Alerts (Investigate within 1 hour):**
```yaml
  - name: "Elevated Error Rate"
    condition: error_rate > 2%
    severity: warning
    action: Notify DevOps via Slack
    
  - name: "Slow Response Time"
    condition: p95_latency > 500ms
    severity: warning
    action: Investigate performance
    
  - name: "Cache Miss Rate High"
    condition: cache_miss_rate > 30%
    severity: warning
    action: Check cache server health
```

**Info Alerts (Daily reports):**
```yaml
  - name: "Daily Ad Stats"
    schedule: daily_at_9am
    metrics:
      - total_ads_created
      - total_impressions
      - total_clicks
      - average_ctr
      - top_performing_ads
```

---

### 5. Analytics Dashboard

**Create admin analytics dashboard:**

**Metrics to Display:**
1. **Ad Performance**
   - Total active ads
   - Total impressions (today, week, month)
   - Total clicks (today, week, month)
   - Average CTR by placement
   - Top performing ads

2. **System Health**
   - API response time (p50, p95, p99)
   - Error rate
   - Queue depth
   - Cache hit rate

3. **User Engagement**
   - Ads created per day
   - Pending approvals count
   - Rejection rate
   - Active B2B advertisers

**Implementation:**
```bash
# Create admin analytics route
Route::get('/admin/ads/analytics', [AdAnalyticsController::class, 'dashboard']);
```

---

### 6. Daily Health Check Script

**Create automated health check:**

```bash
#!/bin/bash
# File: /usr/local/bin/check-ads-health.sh

echo "=== SeferEt Ads System Health Check ==="
echo "Time: $(date)"
echo ""

# Check queue workers
echo "1. Queue Workers:"
sudo supervisorctl status seferet-queue-ads:*
echo ""

# Check database
echo "2. Database Connection:"
cd /var/www/SeferEt-Laravel && php artisan db:show
echo ""

# Check scheduled jobs
echo "3. Scheduled Jobs:"
cd /var/www/SeferEt-Laravel && php artisan schedule:list | grep -i ad
echo ""

# Check cache
echo "4. Cache Connection:"
redis-cli ping
echo ""

# Check storage permissions
echo "5. Storage Permissions:"
ls -la /var/www/SeferEt-Laravel/storage/app/public/ads
echo ""

# Recent errors
echo "6. Recent Errors (last 10):"
tail -n 10 /var/www/SeferEt-Laravel/storage/logs/laravel.log | grep -i error
echo ""

echo "=== Health Check Complete ==="
```

```bash
# Make executable
chmod +x /usr/local/bin/check-ads-health.sh

# Add to cron (run every 6 hours)
0 */6 * * * /usr/local/bin/check-ads-health.sh > /var/log/seferet-ads-health.log
```

---

## 🔍 Post-Deployment Validation

### Day 1-3: Intensive Monitoring

**Hourly Checks:**
- [ ] Error rate < 1%
- [ ] Response time < 200ms
- [ ] Queue depth < 50 jobs
- [ ] Cache hit rate > 70%
- [ ] No database deadlocks
- [ ] Worker processes running
- [ ] Cron jobs executing

### Week 1: Daily Review

**Daily Checks:**
- [ ] Review error logs
- [ ] Analyze slow queries
- [ ] Check user feedback
- [ ] Monitor resource usage (CPU, memory, disk)
- [ ] Verify backups are running
- [ ] Review security logs

### Week 2-4: Business Metrics

**Weekly Reports:**
- Total ads created
- Approval rate (approved / submitted)
- Rejection reasons analysis
- Average CTR by placement
- Top performing advertisers
- Revenue impact (if applicable)

---

## 📝 Testing Checklist

### Functional Tests

- [ ] **Ad Creation**
  - Create ad as B2B user
  - Upload image (various formats: JPEG, PNG, WebP)
  - Set CTA button
  - Schedule start/end dates
  - Submit for approval

- [ ] **Admin Approval**
  - Approve ad
  - Reject ad with reason
  - Edit ad priority

- [ ] **Ad Serving**
  - Fetch ads by placement
  - Verify cache is working
  - Test device targeting (mobile, tablet, desktop)
  - Test region targeting
  - Verify CTR tracking

- [ ] **Image Processing**
  - Upload large image (near max size)
  - Verify variants generated
  - Test crop functionality
  - Verify storage cleanup on delete

- [ ] **Workflow**
  - Draft → Pending → Approved
  - Draft → Pending → Rejected → Edit → Re-submit
  - Withdraw pending ad
  - Deactivate approved ad

### Performance Tests

```bash
# Load test ad serving endpoint
ab -n 1000 -c 10 https://api.seferet.com/api/v1/ads/serve?placement=home_top

# Expected results:
# - Requests per second: > 100
# - Time per request: < 100ms (mean)
# - Failed requests: 0
```

### Security Tests

- [ ] Authorization policies enforced
- [ ] Image XSS sanitization
- [ ] SQL injection prevention
- [ ] Rate limiting on public endpoints
- [ ] CSRF protection on web forms
- [ ] File upload restrictions (type, size)

---

## 📞 Rollout Support Plan

### Dedicated Support Team

**Roles and Responsibilities:**

| Role | Responsibility | Availability |
|------|---------------|-------------|
| **DevOps Engineer** | Server health, monitoring, deployments | 24/7 during rollout |
| **Backend Developer** | Bug fixes, API issues, database | On-call |
| **QA Engineer** | Testing, validation, user feedback | Business hours |
| **Product Manager** | Business decisions, rollout pace | Business hours |

### Communication Channels

1. **Slack Channel:** `#rollout-ads-system`
2. **Emergency Hotline:** +XX-XXX-XXX-XXXX
3. **Email:** devops@seferet.com
4. **Status Page:** status.seferet.com

### Escalation Path

```
Level 1: DevOps Engineer (15 min response)
    ↓ (if unresolved in 30 min)
Level 2: Backend Lead + CTO (immediate)
    ↓ (if critical)
Level 3: Full Engineering Team + CEO
```

---

## ✅ Success Criteria

The rollout is considered successful when:

1. ✅ **Zero Critical Bugs** - No P0/P1 bugs for 7 consecutive days
2. ✅ **Performance Targets Met**
   - API response time < 200ms (p95)
   - Image processing < 10s
   - Error rate < 1%
3. ✅ **User Adoption**
   - > 50% of B2B users create at least 1 ad
   - > 80% approval rate
4. ✅ **System Stability**
   - 99.9% uptime
   - No emergency rollbacks
5. ✅ **Business Metrics**
   - Positive user feedback
   - Increased engagement

---

## 📚 Reference Documentation

- [Ads Backend Documentation](./ADS_BACKEND_README.md)
- [API Endpoints](./ADS_BACKEND_README.md#api-endpoints)
- [Image Processing Service](../app/Services/AdImageService.php)
- [Ad Model](../app/Models/Ad.php)
- [Laravel Queues](https://laravel.com/docs/10.x/queues)
- [Laravel Scheduler](https://laravel.com/docs/10.x/scheduling)

---

## 🔐 Security Considerations

1. **Access Control**
   - Only admins can approve/reject ads
   - B2B users can only manage their own ads
   - Public endpoints rate-limited

2. **Data Validation**
   - Strict file upload validation
   - XSS prevention in CTA text
   - SQL injection prevention

3. **Audit Trail**
   - All ad changes logged
   - Admin actions tracked
   - IP and user agent recorded

4. **Privacy**
   - Analytics anonymized
   - GDPR compliance (if applicable)
   - Data retention policies

---

## 📅 Rollout Schedule Summary

| Phase | Duration | % Exposed | Key Milestone |
|-------|----------|-----------|---------------|
| **Infrastructure Setup** | Day 1 | 0% | Redis, workers, storage ready |
| **Database Migration** | Day 1-2 | 0% | Tables created |
| **Admin Testing** | Week 1 | Admin only | Workflow validated |
| **B2B Beta** | Week 2 | 10% B2B | Initial user feedback |
| **B2B Expansion** | Week 2-3 | 50% → 100% B2B | Full B2B rollout |
| **Public Beta** | Week 4 | 5% → 50% public | Ad serving tested |
| **Full Launch** | Week 4 end | 100% | Complete rollout |

---

## 🎉 Post-Rollout Actions

After successful rollout (Week 5+):

1. **Optimize Performance**
   - Analyze slow queries
   - Implement additional caching
   - CDN optimization

2. **Feature Enhancements**
   - A/B testing
   - Advanced targeting
   - Budget management
   - Video ads

3. **Documentation**
   - Update user guides
   - Create video tutorials
   - FAQ section

4. **Feedback Loop**
   - Monthly user surveys
   - Regular analytics review
   - Continuous improvement

---

**Document Version:** 1.0  
**Last Updated:** 2025-11-08  
**Next Review:** Post-rollout + 30 days  
**Owner:** DevOps & Backend Team
