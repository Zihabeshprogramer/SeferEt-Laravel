# Ad Analytics Pipeline - Implementation Summary

## Overview

A complete, production-ready analytics pipeline and admin reporting system for tracking ad performance in the SeferEt platform. The system provides real-time tracking, daily aggregation, caching, and comprehensive reporting capabilities.

---

## Architecture Components

### 1. Data Collection Layer

**Tracking Endpoints** (Already Implemented)
- `POST /api/v1/ads/{id}/track/impression` - Track ad impression
- `POST /api/v1/ads/{id}/track/click` - Track ad click  
- `POST /api/v1/ads/track/impressions/batch` - Batch impression tracking
- `POST /api/v1/ads/{adId}/track/conversion/{clickId}` - Track conversion

**Controller**: `App\Http\Controllers\Api\AdTrackingController`

**Features**:
- ✅ Rate limiting (60 impressions/min, 30 clicks/min per IP)
- ✅ Duplicate prevention (5-minute window)
- ✅ Batch processing support
- ✅ Session tracking
- ✅ Device type & placement tracking
- ✅ Privacy-focused (minimal PII collection)

**Usage from Mobile App**:
```dart
// Track impression
await http.post(
  Uri.parse('$apiUrl/api/v1/ads/$adId/track/impression'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'device_type': 'mobile',
    'placement': 'home_banner',
  }),
);

// Track click
await http.post(
  Uri.parse('$apiUrl/api/v1/ads/$adId/track/click'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'device_type': 'mobile',
    'placement': 'home_banner',
  }),
);
```

---

### 2. Data Storage Layer

**Database Tables**:

#### `ad_impressions`
- Stores raw impression events
- Indexed: `ad_id + created_at`, `user_id + created_at`, `session_id`
- Fields: ad_id, user_id, session_id, ip_address, user_agent, device_type, placement, metadata

#### `ad_clicks`
- Stores raw click events
- Indexed: `ad_id + created_at`, `user_id + created_at`, `session_id`, `converted`
- Fields: ad_id, user_id, session_id, ip_address, user_agent, device_type, placement, destination_url, converted, converted_at, metadata

#### `ad_analytics_daily`
- Pre-aggregated daily statistics
- Unique index: `ad_id + date`
- Fields: impressions, clicks, ctr, conversions, conversion_rate, unique_users, unique_sessions, device_breakdown, placement_breakdown, cost, cpc, cpm

**Models**:
- `App\Models\AdImpression`
- `App\Models\AdClick`
- `App\Models\AdAnalyticsDaily`

---

### 3. Aggregation Layer

**Job**: `App\Jobs\AggregateAdAnalytics`

**Execution**:
- Runs daily at 1:00 AM via Laravel scheduler
- Processes previous day's raw data
- Updates/creates records in `ad_analytics_daily` table
- Supports both full aggregation and single-ad aggregation

**Metrics Computed**:
- Total impressions & clicks
- Click-Through Rate (CTR)
- Unique users & sessions
- Conversions & conversion rate
- Device breakdown (mobile, tablet, desktop)
- Placement breakdown (home, search, detail, etc.)
- Cost metrics (CPC, CPM) if budget tracking enabled

**Scheduler Configuration** (Already Configured):
```php
// In app/Console/Kernel.php
$schedule->job(new \App\Jobs\AggregateAdAnalytics)
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();
```

**Manual Execution**:
```bash
# Trigger aggregation for yesterday (via Tinker)
php artisan tinker
>>> dispatch(new \App\Jobs\AggregateAdAnalytics());

# Aggregate specific date
>>> dispatch(new \App\Jobs\AggregateAdAnalytics('2025-11-07'));

# Aggregate specific ad
>>> dispatch(new \App\Jobs\AggregateAdAnalytics(null, 123));
```

---

### 4. Caching Layer

**Service**: `App\Services\AdAnalyticsCacheService`

**Cache Strategy**:
- Real-time stats (today's data): **5 minutes** TTL
- Aggregated stats (historical): **30 minutes** TTL
- Top ads lists: **1 hour** TTL
- Summary stats: **30 minutes** TTL

**Cache Keys**:
- `ad_analytics_summary_{start}_{end}[_ad_{id}]`
- `ad_analytics_trends_{start}_{end}[_ad_{id}]`
- `ad_analytics_daily_{start}_{end}[_ad_{id}]`
- `ad_analytics_top_ads_{metric}_{start}_{end}_{limit}[_owner_{id}]`
- `ad_analytics_realtime_{ad_id}`

**Methods**:
```php
// Get cached summary
$summary = $cacheService->getSummary($startDate, $endDate, $adId);

// Get trends (comparison with previous period)
$trends = $cacheService->getTrends($startDate, $endDate, $adId);

// Get daily breakdown for charts
$dailyData = $cacheService->getDailyData($startDate, $endDate, $adId);

// Get top performing ads
$topAds = $cacheService->getTopAds($startDate, $endDate, $ownerId, 10);

// Get real-time stats for today
$realtimeStats = $cacheService->getRealtimeStats($adId);

// Invalidate cache
$cacheService->invalidateAdCache($adId);
$cacheService->invalidateAll();

// Warm up cache
$cacheService->warmUpCache();
```

---

### 5. Admin Reporting Layer

**Controller**: `App\Http\Controllers\Admin\AdAnalyticsController`

**Routes** (Already Registered):
- `GET /admin/ads/analytics` - Analytics dashboard
- `GET /admin/ads/analytics/{ad}` - Detailed ad analytics
- `GET /admin/ads/analytics/export` - CSV export
- `GET /admin/ads/analytics/realtime` - Real-time stats API

**Dashboard Features**:

#### Overview Dashboard (`/admin/ads/analytics`)
- Summary statistics (impressions, clicks, CTR, conversions)
- Trend comparison with previous period
- Daily performance charts (line graphs)
- Top performing ads table
- Device & placement breakdown (pie charts)
- Date range filtering
- Ad, owner, and status filtering

#### Detailed Ad View (`/admin/ads/analytics/{ad}`)
- Complete ad information
- Performance summary for date range
- Daily breakdown with charts
- Device breakdown visualization
- Placement breakdown visualization
- Real-time today's stats
- Export option for this ad

#### Filtering Options:
- **Date Range**: Custom start/end dates (default: last 30 days)
- **Ad**: Filter by specific ad ID
- **Owner**: Filter by business owner ID & type
- **Status**: Filter by ad status (active, expired, etc.)

---

### 6. Export Functionality

**CSV Export** (`GET /admin/ads/analytics/export`)

**Export Fields**:
- Date
- Ad ID & Title
- Owner ID, Name & Type
- Impressions
- Clicks
- CTR (%)
- Conversions
- Conversion Rate (%)
- Unique Users
- Unique Sessions
- Cost
- CPC (Cost Per Click)
- CPM (Cost Per Thousand Impressions)

**Request Parameters**:
```
start_date: 2025-10-01
end_date: 2025-11-08
ad_id: (optional) - specific ad
owner_id: (optional) - specific owner
owner_type: (optional) - owner type
format: csv|json (default: csv)
```

**Response**:
- Content-Type: `text/csv` or `application/json`
- Filename: `ad-analytics-{start}-to-{end}.csv`

---

## Key Features

### Performance Optimizations

1. **Batch Updates**
   - Counter increments use atomic operations
   - Batch impression tracking reduces HTTP requests

2. **Database Indexing**
   - Composite indexes on `ad_id + created_at`
   - Indexes on frequently queried columns
   - Unique index on `ad_id + date` for aggregated data

3. **Caching**
   - Multi-level cache strategy
   - Redis-based distributed caching
   - Cache invalidation on data updates
   - Cache warming for common queries

4. **Query Optimization**
   - Eager loading relationships
   - Aggregated data queries instead of raw data
   - Date range-based partitioning support

### Privacy & Security

1. **Rate Limiting**
   - 60 impressions per minute per IP
   - 30 clicks per minute per IP
   - Prevents abuse and bot traffic

2. **Duplicate Prevention**
   - 5-minute window for same session/ad
   - Prevents inflated metrics

3. **Data Privacy**
   - IP addresses hashed/anonymized (configurable)
   - Minimal PII collection
   - Session IDs for deduplication only
   - No long-term user tracking

4. **Access Control**
   - Admin-only access to analytics dashboard
   - Owner-based filtering
   - Role-based permissions

---

## API Endpoints Summary

### Public Tracking Endpoints
```
POST /api/v1/ads/{id}/track/impression
POST /api/v1/ads/{id}/track/click
POST /api/v1/ads/track/impressions/batch
POST /api/v1/ads/{adId}/track/conversion/{clickId}
```

### Admin Analytics Endpoints
```
GET  /admin/ads/analytics              - Overview dashboard
GET  /admin/ads/analytics/{ad}         - Detailed ad analytics
GET  /admin/ads/analytics/export       - CSV export
GET  /admin/ads/analytics/realtime     - Real-time stats (JSON)
```

---

## Metrics Tracked

### Core Metrics
- **Impressions**: Total views of the ad
- **Clicks**: Total clicks on the ad
- **CTR (Click-Through Rate)**: (Clicks / Impressions) × 100
- **Conversions**: Completed desired actions after click
- **Conversion Rate**: (Conversions / Clicks) × 100

### Engagement Metrics
- **Unique Users**: Distinct authenticated users
- **Unique Sessions**: Distinct anonymous sessions
- **Repeat Views**: Users seeing ad multiple times

### Cost Metrics (if budget enabled)
- **Cost**: Total spent for the period
- **CPC (Cost Per Click)**: Cost / Clicks
- **CPM (Cost Per Mille)**: (Cost / Impressions) × 1000

### Breakdown Metrics
- **Device Breakdown**: Mobile, tablet, desktop distribution
- **Placement Breakdown**: Home, search, detail page distribution

---

## Usage Scenarios

### Scenario 1: Daily Admin Check
```
1. Admin logs into /admin/ads/analytics
2. Views last 30 days summary
3. Checks top performing ads
4. Identifies underperforming ads
5. Exports data for stakeholder report
```

### Scenario 2: Business Owner Reports
```
1. Admin filters by specific owner_id
2. Views that owner's ad performance
3. Compares trends vs previous period
4. Exports CSV for owner review
5. Shares insights with business
```

### Scenario 3: Ad Performance Investigation
```
1. Admin navigates to specific ad details
2. Views daily breakdown chart
3. Identifies spike or drop in performance
4. Checks device/placement breakdown
5. Adjusts ad targeting or budget
```

### Scenario 4: Real-time Monitoring
```
1. Admin makes GET request to /realtime endpoint
2. Receives today's uncached stats
3. Monitors active campaigns
4. Makes quick decisions
```

---

## Maintenance Tasks

### Daily Automated Tasks
```
1:00 AM - Aggregate previous day's analytics
2:00 AM - Clean up old raw data (optional)
3:00 AM - Warm up analytics cache
```

### Weekly Manual Tasks
- Review top performing ads
- Check for anomalies in CTR
- Export weekly reports
- Monitor cache hit rates

### Monthly Manual Tasks
- Archive old analytics data
- Review and optimize queries
- Update business intelligence reports
- Plan ad strategy based on trends

---

## Testing

### Manual Testing Commands
```bash
# Test impression tracking
curl -X POST http://localhost/api/v1/ads/1/track/impression \
  -H "Content-Type: application/json" \
  -d '{"device_type":"mobile","placement":"home"}'

# Test click tracking  
curl -X POST http://localhost/api/v1/ads/1/track/click \
  -H "Content-Type: application/json" \
  -d '{"device_type":"mobile","placement":"home"}'

# Test aggregation
php artisan tinker
>>> dispatch(new \App\Jobs\AggregateAdAnalytics());

# Warm cache
>>> app(\App\Services\AdAnalyticsCacheService::class)->warmUpCache();

# Check cache stats
>>> app(\App\Services\AdAnalyticsCacheService::class)->getCacheStats();
```

---

## Monitoring

### Key Performance Indicators
- Aggregation job duration (should be < 5 minutes)
- Cache hit rate (target > 80%)
- API response times (< 200ms for cached)
- Rate limit violations count
- Failed tracking attempts

### Logs to Monitor
```
# Aggregation logs
tail -f storage/logs/laravel.log | grep "ad analytics"

# Cache logs
tail -f storage/logs/laravel.log | grep "cache"

# Rate limit logs
tail -f storage/logs/laravel.log | grep "rate limit"
```

---

## Troubleshooting

### Issue: Aggregation job fails
```bash
# Check job queue
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job_id}

# Check logs
tail -f storage/logs/laravel.log
```

### Issue: Cache not working
```bash
# Clear all cache
php artisan cache:clear

# Check Redis connection
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### Issue: Slow dashboard loading
```bash
# Warm up cache
php artisan tinker
>>> app(\App\Services\AdAnalyticsCacheService::class)->warmUpCache();

# Check database indexes
php artisan db:show
```

---

## Future Enhancements

### Phase 2 Features
- [ ] A/B testing support
- [ ] Automated alerts for performance drops
- [ ] Machine learning-based predictions
- [ ] Real-time dashboard with WebSockets
- [ ] Advanced segmentation (demographic, geographic)
- [ ] Funnel analysis
- [ ] Cohort analysis
- [ ] Attribution modeling

### Phase 3 Features
- [ ] API for programmatic access
- [ ] White-label reporting for business owners
- [ ] Mobile app analytics dashboard
- [ ] Integration with Google Analytics
- [ ] Automated optimization recommendations

---

## Conclusion

The Ad Analytics Pipeline is now **production-ready** with:

✅ **Collection**: Efficient tracking endpoints with rate limiting & privacy
✅ **Storage**: Optimized database schema with proper indexes
✅ **Aggregation**: Automated daily job via scheduler
✅ **Caching**: Multi-level strategy with Redis
✅ **Reporting**: Comprehensive admin dashboard with filtering
✅ **Export**: CSV download for external analysis
✅ **Performance**: Batch updates, caching, query optimization
✅ **Privacy**: Rate limiting, duplicate prevention, minimal PII

The system is designed to handle thousands of ads and millions of impressions while maintaining fast response times and data accuracy.

---

## Quick Reference

| Component | Location | Purpose |
|-----------|----------|---------|
| Tracking Controller | `App\Http\Controllers\Api\AdTrackingController` | Collect impressions/clicks |
| Analytics Controller | `App\Http\Controllers\Admin\AdAnalyticsController` | Admin reporting |
| Aggregation Job | `App\Jobs\AggregateAdAnalytics` | Daily data processing |
| Cache Service | `App\Services\AdAnalyticsCacheService` | Performance optimization |
| AdImpression Model | `App\Models\AdImpression` | Raw impression data |
| AdClick Model | `App\Models\AdClick` | Raw click data |
| AdAnalyticsDaily Model | `App\Models\AdAnalyticsDaily` | Aggregated daily stats |

---

**Last Updated**: 2025-11-08  
**Version**: 1.0  
**Status**: ✅ Production Ready
