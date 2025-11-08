# Ad System Quick Start Guide

## Files Created

### Database Migrations
- `2025_11_08_000001_create_ads_table.php` - Main ads table (already existed)
- `2025_11_08_000002_create_ad_audit_logs_table.php` - Audit logs (already existed)
- `2025_11_08_000003_create_ad_impressions_table.php` - Impression tracking
- `2025_11_08_000004_create_ad_clicks_table.php` - Click tracking
- `2025_11_08_000005_enhance_ads_table.php` - Additional fields

### Models
- `app/Models/Ad.php` - Enhanced with tracking methods and scopes
- `app/Models/AdImpression.php` - Impression tracking
- `app/Models/AdClick.php` - Click tracking
- `app/Models/AdAuditLog.php` - Already existed

### Policies
- `app/Policies/AdPolicy.php` - Already existed

### Controllers
- `app/Http/Controllers/Admin/AdManagementController.php` - Admin moderation
- `app/Http/Controllers/Api/AdServingController.php` - Ad serving API
- `app/Http/Controllers/Api/AdTrackingController.php` - Impression/click tracking

### Jobs
- `app/Jobs/ProcessAdScheduling.php` - Scheduled ad management

### Notifications
- `app/Notifications/AdSubmittedNotification.php` - New ad submission alert
- `app/Notifications/AdApprovedNotification.php` - Approval notification
- `app/Notifications/AdRejectedNotification.php` - Rejection notification

### Routes
- Added admin routes in `routes/web.php` under `/admin/ads`
- Added API routes in `routes/api.php` under `/api/v1/ads`

### Scheduler
- Registered in `app/Console/Kernel.php` - Runs every minute

## Setup Steps

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Register Policy (if not auto-discovered)
In `app/Providers/AuthServiceProvider.php`:
```php
use App\Models\Ad;
use App\Policies\AdPolicy;

protected $policies = [
    Ad::class => AdPolicy::class,
];
```

### 3. Start Scheduler (Production)
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run locally for testing:
```bash
php artisan schedule:work
```

### 4. Queue Worker (If using queues for notifications)
```bash
php artisan queue:work
```

## Key API Endpoints

### Ad Serving (Public)
```http
GET /api/v1/ads/serve?placement=home_top&device_type=mobile&limit=3

Response:
{
  "success": true,
  "ads": [...],
  "meta": {
    "placement": "home_top",
    "device_type": "mobile",
    "count": 3,
    "cached_until": "2025-11-08T20:15:00Z"
  }
}
```

### Track Impression (Public)
```http
POST /api/v1/ads/{id}/track/impression
Content-Type: application/json

{
  "device_type": "mobile",
  "placement": "home_top"
}
```

### Track Click (Public)
```http
POST /api/v1/ads/{id}/track/click
Content-Type: application/json

{
  "device_type": "mobile",
  "placement": "home_top",
  "destination_url": "https://example.com"
}
```

### Admin Endpoints (Requires Admin Auth)
```http
# List pending ads
GET /admin/ads/pending

# Approve ad
POST /admin/ads/{id}/approve
{
  "start_at": "2025-11-10 00:00:00",
  "end_at": "2025-12-10 23:59:59",
  "priority": 10,
  "admin_notes": "Looks good"
}

# Reject ad
POST /admin/ads/{id}/reject
{
  "reason": "Does not meet quality standards"
}

# Update priority
PUT /admin/ads/{id}/priority
{
  "priority": 15,
  "is_local_owner": true
}

# View analytics
GET /admin/ads/{id}/analytics
```

## Common Usage Patterns

### Create and Submit Ad (Partner)
```php
use App\Models\Ad;

$ad = Ad::create([
    'owner_id' => auth()->id(),
    'owner_type' => 'App\Models\User',
    'title' => 'My Ad Title',
    'description' => 'Ad description',
    'image_path' => 'path/to/image.jpg',
    'cta_text' => 'Click Here',
    'cta_action' => 'https://example.com',
    'placement' => 'home_top',
    'device_type' => 'all',
    'status' => Ad::STATUS_DRAFT,
]);

$ad->submitForApproval();
```

### Admin Approval Flow
```php
use App\Models\Ad;

$ad = Ad::pending()->findOrFail($id);

// Approve
$ad->approve(auth()->user());
$ad->start_at = now();
$ad->end_at = now()->addDays(30);
$ad->priority = 10;
$ad->save();

// Or reject
$ad->reject(auth()->user(), 'Reason for rejection');
```

### Check Ad Status
```php
$ad->isCurrentlyActive(); // Is live right now?
$ad->hasReachedImpressionLimit(); // Hit impression limit?
$ad->hasReachedClickLimit(); // Hit click limit?
```

### Get Active Ads
```php
$ads = Ad::active()
    ->forPlacement('home_top')
    ->forDevice('mobile')
    ->forRegion('SA')
    ->prioritized()
    ->limit(3)
    ->get();
```

## Environment Variables

Optional configuration in `.env`:
```env
# Cache driver for ad serving
CACHE_DRIVER=redis

# Queue connection for notifications
QUEUE_CONNECTION=redis

# Ad serving cache TTL (seconds)
AD_CACHE_TTL=120

# Rate limiting
AD_IMPRESSION_RATE_LIMIT=60
AD_CLICK_RATE_LIMIT=30
```

## Troubleshooting

### Ads not showing?
1. Check ad status: `SELECT * FROM ads WHERE status = 'approved' AND is_active = 1`
2. Check scheduling: Ensure `start_at <= NOW()` and `end_at >= NOW()`
3. Check limits: Ensure `impressions_count < max_impressions`
4. Clear cache: `php artisan cache:clear`

### Tracking not working?
1. Check rate limits in logs
2. Verify session is working
3. Check database for recent impressions/clicks
4. Check route definitions

### Scheduler not running?
1. Verify cron is configured: `crontab -l`
2. Test manually: `php artisan schedule:run`
3. Check logs: `storage/logs/laravel.log`

### Notifications not sent?
1. Check queue is running: `php artisan queue:work`
2. Verify notification settings on user model
3. Check `notifications` table in database

## Performance Tips

1. **Use caching appropriately** - Ad serving is cached for 2 minutes
2. **Batch tracking** - Use batch endpoint for multiple impressions
3. **Index optimization** - Migrations include strategic indexes
4. **Queue jobs** - Use queue for notification sending
5. **Monitor queries** - Use Laravel Debugbar in development

## Security Checklist

- ✅ Authorization via AdPolicy
- ✅ No self-approval for owners
- ✅ Rate limiting on tracking endpoints
- ✅ Input validation on all requests
- ✅ Audit logging for accountability
- ✅ Duplicate prevention (5-minute window)
- ✅ SQL injection protection (Eloquent)
- ✅ XSS protection (Blade escaping)

## Next Steps

1. Create admin UI views in `resources/views/admin/ads/`
2. Add frontend integration for ad display
3. Set up monitoring and alerts
4. Configure backup strategy for tracking data
5. Implement additional analytics dashboards

## Support

See full documentation in `docs/AD_MODERATION_SYSTEM.md` for detailed information.
