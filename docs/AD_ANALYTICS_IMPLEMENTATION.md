# Ad Analytics Pipeline Implementation

## Overview
This document describes the lightweight analytics pipeline and admin reporting system for ads in the SeferEt platform.

## Architecture

### Components

1. **Tracking Endpoints (API)**
   - `POST /api/v1/ads/{id}/track/impression` - Track ad impression
   - `POST /api/v1/ads/{id}/track/click` - Track ad click
   - `POST /api/v1/ads/track/impressions/batch` - Batch impression tracking
   - `POST /api/v1/ads/{adId}/track/conversion/{clickId}` - Track conversion

2. **Data Storage**
   - `ad_impressions` - Raw impression events
   - `ad_clicks` - Raw click events
   - `ad_analytics_daily` - Aggregated daily statistics

3. **Aggregation Job**
   - `AggregateAdAnalytics` - Daily job that processes raw data into aggregated stats
   - Runs daily at 1:00 AM via Laravel scheduler
   - Processes previous day's data

4. **Admin Dashboard**
   - `/admin/ads/analytics` - Main analytics dashboard
   - `/admin/ads/analytics/{ad}` - Detailed ad analytics
   - `/admin/ads/analytics/export` - CSV export
   - `/admin/ads/analytics/realtime` - Real-time stats API

5. **Caching Layer**
   - `AdAnalyticsCacheService` - Redis-based caching for performance
   - TTLs: 5 min (realtime), 30 min (aggregated), 1 hour (top ads)

## Features

### Tracking
- **Privacy-focused**: Rate limiting (100 impressions/min per IP, 50 clicks/min per IP)
- **Duplicate prevention**: Prevents tracking same session within 5 minutes
- **Batch processing**: Support for batch impression tracking
- **Metadata support**: Tracks device type, placement, user agent, referrer

### Aggregation
- **Daily aggregation**: Computes stats for previous day
- **Metrics tracked**:
  - Total impressions & clicks
  - CTR (Click-Through Rate)
  - Unique users & sessions
  - Conversions & conversion rate
  - Device breakdown (mobile, tablet, desktop)
  - Placement breakdown
  - Cost metrics (CPC, CPM)

### Reporting
- **Filtering**: By date range, ad, owner, status
- **Visualizations**: Line charts for trends, doughnut charts for breakdowns
- **Top performers**: List of top ads by clicks
- **Trends**: Comparison with previous period
- **Real-time data**: Today's unaggreated stats
- **CSV export**: Full data export for external analysis

### Performance
- **Caching**: Redis caching for frequently accessed data
- **Indexes**: Database indexes on date, ad_id, created_at
- **Batch updates**: Counter increments use atomic operations
- **Queue support**: Aggregation job runs in background

## Usage

### Mobile App Integration
```dart
// Track impression
await http.post(
  Uri.parse('$apiUrl/api/v1/ads/$adId/track/impression'),
  body: {
    'device_type': 'mobile',
    'placement': 'home_banner',
  },
);

// Track click
await http.post(
  Uri.parse('$apiUrl/api/v1/ads/$adId/track/click'),
  body: {
    'device_type': 'mobile',
    'placement': 'home_banner',
    'destination_url': ad.ctaAction,
  },
);

// Batch tracking (efficient for multiple ads)
await http.post(
  Uri.parse('$apiUrl/api/v1/ads/track/impressions/batch'),
  body: {
    'impressions': [
      {'ad_id': 1, 'device_type': 'mobile', 'placement': 'home'},
      {'ad_id': 2, 'device_type': 'mobile', 'placement': 'search'},
    ],
  },
);
```

### Admin Commands
```bash
# Manually trigger aggregation for yesterday
php artisan tinker
>>> dispatch(new \App\Jobs\AggregateAdAnalytics());

# Aggregate specific date
>>> dispatch(new \App\Jobs\AggregateAdAnalytics('2025-11-07'));

# Aggregate specific ad
>>> dispatch(new \App\Jobs\AggregateAdAnalytics(null, 123));

# Warm up cache
>>> app(\App\Services\AdAnalyticsCacheService::class)->warmUpCache();

# Invalidate cache
>>> app(\App\Services\AdAnalyticsCacheService::class)->invalidateAll();

# Get cache stats
>>> app(\App\Services\AdAnalyticsCacheService::class)->getCacheStats();
```

### Scheduler Setup
The aggregation job runs automatically via Laravel scheduler. Ensure cron is configured:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Database Schema

### ad_impressions
- Stores individual impression events
- Indexed by: ad_id + created_at, user_id + created_at, session_id, created_at
- Includes: user, session, IP, device, placement, metadata

### ad_clicks
- Stores individual click events  
- Indexed by: ad_id + created_at, user_id + created_at, session_id, converted, created_at
- Includes: user, session, IP, device, placement, destination_url, conversion tracking

### ad_analytics_daily
- Pre-aggregated daily statistics
- Unique index: ad_id + date
- Contains: impressions, clicks, CTR, conversions, device/placement breakdowns, costs

## Privacy & Security

### Privacy Measures
- IP addresses hashed/anonymized (configurable)
- No PII stored beyond user_id (which is optional)
- Session IDs used for deduplication, not long-term tracking
- Rate limiting prevents abuse and data collection

### Security
- Rate limiting on all tracking endpoints
- Duplicate prevention within 5-minute windows
- Input validation and sanitization
- CORS protection on API endpoints
- Admin-only access to analytics dashboard

## Performance Optimization

### Database
- Proper indexes on all frequently queried columns
- Partitioning by date on impressions/clicks tables (optional)
- Regular cleanup of old raw data (keep aggregated)

### Caching
- Multi-level cache strategy (realtime < aggregated < top performers)
- Cache invalidation on data updates
- Cache warming for common queries
- Redis for distributed caching

### API
- Batch endpoints to reduce round-trips
- Async tracking (fire-and-forget from app perspective)
- Response compression
- CDN for static assets in dashboard

## Monitoring

### Metrics to Watch
- Aggregation job duration
- Cache hit rate
- API response times
- Rate limit violations
- Failed tracking attempts

### Logging
- All tracking errors logged
- Aggregation job logs success/failure
- Cache invalidation logged
- Rate limit exceeded logged

## Maintenance

### Daily
- Monitor aggregation job execution
- Check for tracking errors

### Weekly
- Review cache performance
- Check database table sizes

### Monthly
- Archive old raw impression/click data
- Analyze top performing ads
- Review and adjust rate limits if needed

## Future Enhancements
- Real-time analytics streaming (WebSocket)
- A/B testing support
- Geo-location tracking (privacy-aware)
- Custom event tracking
- Integration with external analytics platforms
- Machine learning for ad performance prediction
- Automated reporting via email

## Related Files
- Models: `Ad.php`, `AdImpression.php`, `AdClick.php`, `AdAnalyticsDaily.php`
- Controllers: `AdTrackingController.php`, `AdAnalyticsController.php`
- Jobs: `AggregateAdAnalytics.php`
- Services: `AdAnalyticsCacheService.php`
- Migrations: `2025_11_08_000003_create_ad_impressions_table.php`, `2025_11_08_000004_create_ad_clicks_table.php`, `2025_11_08_000006_create_ad_analytics_daily_table.php`
- Views: `resources/views/admin/ads/analytics/index.blade.php`
- Routes: `routes/api.php`, `routes/web.php`
