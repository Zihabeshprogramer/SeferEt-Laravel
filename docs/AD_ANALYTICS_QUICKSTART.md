# Ad Analytics Quick Start Guide

## Setup

### 1. Run Database Migration
```bash
cd C:\Users\seide\SeferEt\SeferEt-Laravel
php artisan migrate
```

This will create:
- `ad_impressions` table
- `ad_clicks` table  
- `ad_analytics_daily` table

### 2. Verify Scheduler is Running
Ensure your Laravel scheduler is configured in cron (or Windows Task Scheduler):
```bash
php artisan schedule:list
```

You should see:
- `App\Jobs\AggregateAdAnalytics` runs daily at 01:00

### 3. Test Manual Aggregation (Optional)
```bash
php artisan tinker
>>> dispatch(new \App\Jobs\AggregateAdAnalytics());
>>> exit
```

## API Endpoints

### Track Impression
```bash
POST /api/v1/ads/{id}/track/impression
Content-Type: application/json

{
  "device_type": "mobile",
  "placement": "home_banner"
}
```

### Track Click
```bash
POST /api/v1/ads/{id}/track/click
Content-Type: application/json

{
  "device_type": "mobile",
  "placement": "home_banner",
  "destination_url": "https://example.com"
}
```

### Batch Track Impressions
```bash
POST /api/v1/ads/track/impressions/batch
Content-Type: application/json

{
  "impressions": [
    {"ad_id": 1, "device_type": "mobile", "placement": "home"},
    {"ad_id": 2, "device_type": "mobile", "placement": "search"}
  ]
}
```

## Admin Dashboard

### Access the Dashboard
Navigate to: `http://your-domain/admin/ads/analytics`

### Features Available
- **Date Range Filtering**: Last 7, 30, or 90 days
- **Ad Filtering**: View stats for specific ads
- **Owner Filtering**: Filter by ad owner
- **Charts**: Timeline and device breakdown charts
- **Top Performers**: See best performing ads
- **Export**: Download CSV reports
- **Real-time Stats**: View today's unaggreated data

### Export CSV
Click "Export CSV" button or navigate to:
`http://your-domain/admin/ads/analytics/export?start_date=2025-11-01&end_date=2025-11-08`

## Testing the System

### 1. Create Test Ad (if needed)
```bash
php artisan tinker
>>> $ad = \App\Models\Ad::create([
...   'owner_id' => 1,
...   'owner_type' => 'App\Models\User',
...   'title' => 'Test Ad',
...   'status' => 'approved',
...   'is_active' => true,
... ]);
>>> exit
```

### 2. Track Test Impression
```bash
curl -X POST http://localhost/api/v1/ads/1/track/impression \
  -H "Content-Type: application/json" \
  -d '{"device_type":"mobile","placement":"test"}'
```

### 3. Track Test Click
```bash
curl -X POST http://localhost/api/v1/ads/1/track/click \
  -H "Content-Type: application/json" \
  -d '{"device_type":"mobile","placement":"test"}'
```

### 4. Run Aggregation
```bash
php artisan tinker
>>> dispatch(new \App\Jobs\AggregateAdAnalytics(now()->format('Y-m-d')));
>>> exit
```

### 5. View in Dashboard
Navigate to: `http://your-domain/admin/ads/analytics`

## Performance Tips

### Enable Redis Caching
Ensure Redis is configured in `.env`:
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Warm Up Cache
```bash
php artisan tinker
>>> app(\App\Services\AdAnalyticsCacheService::class)->warmUpCache();
>>> exit
```

### Monitor Cache Usage
```bash
php artisan tinker
>>> app(\App\Services\AdAnalyticsCacheService::class)->getCacheStats();
>>> exit
```

## Common Tasks

### View Aggregation Log
```bash
tail -f storage/logs/laravel.log | grep "ad analytics"
```

### Clear Analytics Cache
```bash
php artisan tinker
>>> app(\App\Services\AdAnalyticsCacheService::class)->invalidateAll();
>>> exit
```

### Get Real-time Stats via API
```bash
curl http://localhost/admin/ads/analytics/realtime?ad_id=1
```

## Troubleshooting

### No data showing in dashboard?
1. Check if impressions/clicks were tracked: `SELECT COUNT(*) FROM ad_impressions;`
2. Check if aggregation ran: `SELECT COUNT(*) FROM ad_analytics_daily;`
3. Run manual aggregation: `dispatch(new \App\Jobs\AggregateAdAnalytics());`
4. Check logs: `tail -f storage/logs/laravel.log`

### Rate limit errors?
- Default limits: 100 impressions/min, 50 clicks/min per IP
- Wait 1 minute or adjust limits in `AdTrackingController.php`

### Cache not working?
1. Check Redis connection: `php artisan tinker` → `Redis::ping();`
2. Verify CACHE_DRIVER in `.env`
3. Clear config cache: `php artisan config:clear`

## Next Steps

1. **Integrate with Mobile App**: Use the tracking endpoints in your Flutter app
2. **Set Up Monitoring**: Monitor aggregation job execution daily
3. **Configure Alerts**: Set up alerts for failed aggregations
4. **Optimize Performance**: Enable Redis and warm up cache
5. **Schedule Reports**: Set up automated CSV exports via email (future enhancement)

## Support

For issues or questions, refer to:
- Full documentation: `docs/AD_ANALYTICS_IMPLEMENTATION.md`
- Laravel logs: `storage/logs/laravel.log`
- Database migrations: `database/migrations/2025_11_08_000*`
