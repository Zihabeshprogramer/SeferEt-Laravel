# Advertisement Moderation and Serving System

## Overview
This document describes the comprehensive advertisement moderation and serving system implemented for the SeferEt platform. The system provides full lifecycle management of advertisements from creation through approval, scheduling, serving, and analytics tracking.

## Architecture

### Database Schema

#### Tables Created

1. **`ads`** - Main advertisement table
   - Owner information (polymorphic)
   - Product relationship (polymorphic)
   - Content (title, description, images)
   - CTA configuration
   - Approval workflow (status, approver, dates)
   - Scheduling (start_at, end_at)
   - Priority and targeting (device_type, placement, regions)
   - Tracking (impressions_count, clicks_count, CTR)
   - Limits (max_impressions, max_clicks, budget)

2. **`ad_impressions`** - Impression tracking
   - Ad relationship
   - User tracking (optional)
   - Session tracking
   - Context (page_url, referrer, device_type, placement)
   - IP and user agent

3. **`ad_clicks`** - Click tracking
   - Ad relationship
   - User tracking (optional)
   - Session tracking
   - Context (destination_url, device_type)
   - Conversion tracking

4. **`ad_audit_logs`** - Administrative audit trail
   - Event tracking (created, approved, rejected, etc.)
   - User who performed action
   - Changes (before/after)
   - Metadata (IP, user agent)

### Models

#### Ad Model (`App\Models\Ad`)
**Constants:**
- Status: DRAFT, PENDING, APPROVED, REJECTED
- Device types: ALL, MOBILE, TABLET, DESKTOP
- Placements: HOME_TOP, HOME_MIDDLE, PACKAGE_LIST, etc.

**Relationships:**
- `owner()` - Morphable to User or other entities
- `product()` - Morphable to Package, Hotel, Flight, etc.
- `approver()` - Belongs to User (admin who approved/rejected)
- `impressions()` - Has many AdImpression
- `clicks()` - Has many AdClick
- `auditLogs()` - Has many AdAuditLog

**Key Scopes:**
- `active()` - Approved, within schedule, limits not reached
- `pending()` - Awaiting approval
- `forDevice($type)` - Filter by device type
- `forPlacement($placement)` - Filter by placement
- `forRegion($region)` - Filter by region (supports JSON contains)
- `prioritized()` - Order by local owner, priority, then date

**Key Methods:**
- `submitForApproval()` - Move from draft to pending
- `approve(User $admin)` - Approve ad
- `reject(User $admin, $reason)` - Reject ad with reason
- `withdraw()` - Owner withdraws pending ad
- `activate() / deactivate()` - Toggle active status
- `recordImpression() / recordClick()` - Update counters and CTR
- `isCurrentlyActive()` - Check if ad is live
- `hasReachedImpressionLimit() / hasReachedClickLimit()`

#### AdImpression Model (`App\Models\AdImpression`)
- `record()` - Static method to create impression and update ad counter
- `recentlyTracked()` - Prevent duplicate impressions

#### AdClick Model (`App\Models\AdClick`)
- `record()` - Static method to create click and update ad counter
- `markConverted()` - Mark click as converted
- `recentlyTracked()` - Prevent duplicate clicks

#### AdAuditLog Model (`App\Models\AdAuditLog`)
- Immutable audit trail
- Tracks all administrative actions

### Policies

#### AdPolicy (`App\Policies\AdPolicy`)
**Key Rules:**
- **viewAny**: Admins can view all, partners can view their own
- **create**: Only partners and admins
- **update**: Admins can update any; owners can update draft/rejected only
- **approve**: Only admins; cannot approve own ads
- **reject**: Only admins
- **setPriority**: Only admins
- **toggleActive**: Admins for any; owners for their own approved ads
- **viewAuditLogs**: Only admins

### Controllers

#### Admin Controllers

**AdManagementController** (`App\Http\Controllers\Admin\AdManagementController`)

**Endpoints:**
- `GET /admin/ads` - List all ads with filtering
- `GET /admin/ads/pending` - List pending ads for approval
- `GET /admin/ads/{id}` - View ad details with audit logs
- `POST /admin/ads/{id}/approve` - Approve ad (can set schedule/priority)
- `POST /admin/ads/{id}/reject` - Reject ad with reason
- `PUT /admin/ads/{id}/scheduling` - Update start_at/end_at
- `PUT /admin/ads/{id}/priority` - Set priority and local owner flag
- `POST /admin/ads/{id}/toggle-active` - Activate/deactivate
- `POST /admin/ads/bulk-approve` - Approve multiple ads
- `POST /admin/ads/bulk-reject` - Reject multiple ads
- `DELETE /admin/ads/{id}` - Soft delete ad
- `GET /admin/ads/{id}/analytics` - View detailed analytics

**Features:**
- Comprehensive filtering (status, placement, device, search)
- Transaction-wrapped approval/rejection
- Automatic notification to owners
- Audit logging on all actions
- Statistics dashboard

#### API Controllers

**AdServingController** (`App\Http\Controllers\Api\AdServingController`)

**Endpoint:** `GET /api/v1/ads/serve`

**Query Parameters:**
- `placement` - Target placement (e.g., home_top)
- `device_type` - mobile, tablet, desktop (auto-detected)
- `region` - Target region (auto-detected from IP/headers)
- `limit` - Number of ads to return (max 10)
- `product_type` / `product_id` - Filter by promoted product

**Features:**
- Active ad filtering (approved, scheduled, limits)
- Device type detection from user agent
- Region detection from headers (CF-IPCountry, X-Country-Code)
- Priority sorting (local owners first, then by priority)
- Randomization within priority groups
- **2-minute cache** with cache key per context
- Returns tracking URLs for impressions/clicks

**Response Format:**
```json
{
  "success": true,
  "ads": [
    {
      "id": 1,
      "title": "Ad Title",
      "description": "Ad description",
      "image_url": "https://...",
      "image_variants": {...},
      "cta_text": "Book Now",
      "cta_action": "https://...",
      "cta_position": 0.5,
      "cta_style": "primary",
      "placement": "home_top",
      "priority": 10,
      "is_local": true,
      "tracking": {
        "impression_url": "/api/v1/ads/1/track/impression",
        "click_url": "/api/v1/ads/1/track/click"
      },
      "analytics_meta": {...}
    }
  ],
  "meta": {
    "placement": "home_top",
    "device_type": "mobile",
    "region": "SA",
    "count": 3,
    "cached_until": "2025-11-08T20:15:00Z"
  }
}
```

**AdTrackingController** (`App\Http\Controllers\Api\AdTrackingController`)

**Endpoints:**
- `POST /api/v1/ads/{id}/track/impression` - Track ad impression
- `POST /api/v1/ads/{id}/track/click` - Track ad click
- `POST /api/v1/ads/{adId}/track/conversion/{clickId}` - Track conversion
- `POST /api/v1/ads/track/impressions/batch` - Batch track impressions

**Features:**
- Rate limiting (60 impressions/min, 30 clicks/min per IP)
- Duplicate prevention (5-minute window per session)
- Session tracking
- Device type and placement tracking
- IP and user agent logging
- Optional user tracking (if authenticated)

**Rate Limiting:**
```php
// Impressions: max 60 per minute per IP
RateLimiter::hit('ad-impression:' . $ip, 60);

// Clicks: max 30 per minute per IP
RateLimiter::hit('ad-click:' . $ip, 60);
```

### Jobs

**ProcessAdScheduling** (`App\Jobs\ProcessAdScheduling`)

**Runs:** Every minute via Laravel scheduler

**Functions:**
1. **activateScheduledAds()** - Activates ads when start_at is reached
2. **expireScheduledAds()** - Deactivates ads when end_at is reached
3. **deactivateAdsReachedLimits()** - Deactivates ads that reached impression or click limits

**Audit Logging:**
- Logs all automatic actions with reason
- Provides audit trail for automated changes

**Registered in:** `app/Console/Kernel.php`
```php
$schedule->job(new \App\Jobs\ProcessAdScheduling)
         ->everyMinute()
         ->withoutOverlapping()
         ->runInBackground();
```

### Notifications

**AdSubmittedNotification** - Sent to all admins when ad is submitted
**AdApprovedNotification** - Sent to owner when ad is approved
**AdRejectedNotification** - Sent to owner when ad is rejected

All notifications:
- Support database and email channels
- Respect user notification preferences
- Include action URLs
- Contain relevant ad details

### Routes

#### Admin Web Routes (`routes/web.php`)
```php
Route::prefix('admin/ads')->name('admin.ads.')->group(function () {
    Route::get('/', 'index');
    Route::get('/pending', 'pending');
    Route::get('/{id}', 'show');
    Route::post('/{id}/approve', 'approve');
    Route::post('/{id}/reject', 'reject');
    Route::put('/{id}/scheduling', 'updateScheduling');
    Route::put('/{id}/priority', 'updatePriority');
    Route::post('/{id}/toggle-active', 'toggleActive');
    Route::post('/bulk-approve', 'bulkApprove');
    Route::post('/bulk-reject', 'bulkReject');
    Route::delete('/{id}', 'destroy');
    Route::get('/{id}/analytics', 'analytics');
});
```

#### Public API Routes (`routes/api.php`)
```php
// No authentication required for serving and tracking
Route::prefix('v1/ads')->group(function () {
    Route::get('/serve', [AdServingController::class, 'serveAds']);
    Route::get('/serve/{id}', [AdServingController::class, 'getAd']);
    
    Route::post('/{id}/track/impression', [AdTrackingController::class, 'trackImpression']);
    Route::post('/{id}/track/click', [AdTrackingController::class, 'trackClick']);
    Route::post('/{adId}/track/conversion/{clickId}', [AdTrackingController::class, 'trackConversion']);
    
    Route::post('/track/impressions/batch', [AdTrackingController::class, 'batchTrackImpressions']);
});
```

## Usage Flow

### 1. Partner Creates Ad
```php
$ad = Ad::create([
    'owner_id' => auth()->id(),
    'owner_type' => 'App\Models\User',
    'product_id' => $package->id,
    'product_type' => 'package',
    'title' => 'Special Umrah Package',
    'description' => 'Limited time offer...',
    'image_path' => 'ads/image.jpg',
    'cta_text' => 'Book Now',
    'cta_action' => 'https://example.com/packages/123',
    'device_type' => 'all',
    'placement' => 'home_top',
    'priority' => 5,
    'status' => Ad::STATUS_DRAFT,
]);
```

### 2. Partner Submits for Approval
```php
$ad->submitForApproval();
// Notifies all admins via AdSubmittedNotification
```

### 3. Admin Reviews and Approves
```php
$ad->approve(auth()->user());
$ad->start_at = now();
$ad->end_at = now()->addDays(30);
$ad->priority = 10;
$ad->is_local_owner = true;
$ad->save();
// Notifies owner via AdApprovedNotification
```

### 4. Ad Goes Live
- Scheduler activates ad when start_at is reached
- Ad appears in serving API results based on targeting
- Impressions and clicks are tracked

### 5. Analytics
```php
$analytics = [
    'impressions' => $ad->impressions_count,
    'clicks' => $ad->clicks_count,
    'ctr' => $ad->ctr,
    'by_device' => $ad->impressions()->groupBy('device_type')->get(),
    'by_day' => $ad->impressions()->groupByDay()->get(),
];
```

## Configuration

### Cache Configuration
- **TTL:** 120 seconds (2 minutes)
- **Driver:** Uses default Laravel cache driver
- **Keys:** `ads_serve_{placement}_{device}_{region}_{limit}`

### Rate Limiting
- **Impressions:** 60 per minute per IP
- **Clicks:** 30 per minute per IP
- **Window:** 60 seconds

### Scheduling
- **Frequency:** Every minute
- **Overlap Prevention:** Enabled
- **Background:** Yes

## Security

### Authorization
- All admin actions protected by AdPolicy
- Owner cannot approve their own ads
- Owners can only edit draft/rejected ads
- Audit log for accountability

### Input Validation
- All requests validated
- SQL injection protected (Eloquent ORM)
- XSS protected (Laravel escaping)
- Rate limiting on tracking endpoints

### Privacy
- IP addresses hashed in production (optional)
- User agent truncated
- GDPR-compliant data retention policies

## Performance Optimizations

1. **Caching:** 2-minute cache on ad serving
2. **Indexes:** Strategic database indexes on frequently queried columns
3. **Eager Loading:** Prevents N+1 queries
4. **Batch Tracking:** Supports batch impression tracking
5. **Queue Jobs:** Background processing for notifications and scheduling
6. **Denormalized Counts:** impression_count, clicks_count stored on ads table

## Monitoring

### Logs
- All automatic actions logged via Laravel Log facade
- Audit logs stored in database
- Event types: created, submitted, approved, rejected, auto_activated, auto_expired, auto_deactivated

### Metrics to Monitor
- Ad approval time (pending to approved)
- CTR by placement and device
- Cache hit rate
- Tracking API response time
- Rate limit hits

## Future Enhancements

1. **A/B Testing** - Serve different ad variants
2. **Budget Management** - CPC/CPM pricing models
3. **Advanced Analytics** - Heatmaps, conversion funnels
4. **Automated Rules** - Auto-pause low-performing ads
5. **Real-time Bidding** - Auction-based ad placement
6. **Content Filtering** - AI-powered inappropriate content detection
7. **Frequency Capping** - Limit impressions per user
8. **Retargeting** - Show ads to users based on behavior

## Migration Guide

### Running Migrations
```bash
php artisan migrate
```

Migrations will create:
- `ads`
- `ad_impressions`
- `ad_clicks`
- `ad_audit_logs`

### Setting Up Scheduler
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Policy Registration
Add to `AuthServiceProvider`:
```php
protected $policies = [
    Ad::class => AdPolicy::class,
];
```

## Testing

### Example Tests

```php
// Test ad approval workflow
public function test_admin_can_approve_ad()
{
    $admin = User::factory()->admin()->create();
    $ad = Ad::factory()->pending()->create();
    
    $this->actingAs($admin)
         ->post(route('admin.ads.approve', $ad))
         ->assertRedirect();
    
    $this->assertEquals('approved', $ad->fresh()->status);
}

// Test ad serving
public function test_serve_ads_returns_active_ads()
{
    Ad::factory()->active()->count(5)->create([
        'placement' => 'home_top',
        'device_type' => 'all',
    ]);
    
    $response = $this->get('/api/v1/ads/serve?placement=home_top')
                     ->assertOk()
                     ->assertJsonStructure(['success', 'ads', 'meta']);
    
    $this->assertCount(3, $response['ads']);
}

// Test impression tracking
public function test_track_impression_increments_counter()
{
    $ad = Ad::factory()->active()->create();
    
    $this->post("/api/v1/ads/{$ad->id}/track/impression")
         ->assertOk();
    
    $this->assertEquals(1, $ad->fresh()->impressions_count);
}
```

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Review audit logs in database
- Monitor queue jobs: `php artisan queue:work`
- Check scheduler: `php artisan schedule:list`

## Conclusion

This system provides a complete, production-ready advertisement management solution with:
- ✅ Admin moderation workflow
- ✅ Automated scheduling and expiration
- ✅ Intelligent ad serving with caching
- ✅ Comprehensive tracking and analytics
- ✅ Role-based access control
- ✅ Audit trails
- ✅ Notification system integration
- ✅ Rate limiting and security
- ✅ Performance optimizations
