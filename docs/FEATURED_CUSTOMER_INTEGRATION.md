# Featured Products - Customer Platform Integration

## Overview
The customer home page now displays **live, admin-approved featured packages** directly from the Featured Products module. No placeholder data or static content remains.

## What Changed

### 1. Service Layer Updates
**File:** `app/Services/B2CPackageService.php`

**Changed Method:** `fetchFeaturedPackages()`

**Before:**
```php
protected function fetchFeaturedPackages(int $limit): Collection
{
    $packages = $this->buildBaseQuery()
        ->where('is_featured', true) // Simple boolean check
        ->orderByDesc('average_rating')
        ->orderByDesc('bookings_count')
        ->limit($limit)
        ->get();

    return $packages->map(function ($package) {
        return $this->transformPackageForB2C($package);
    });
}
```

**After:**
```php
protected function fetchFeaturedPackages(int $limit): Collection
{
    $packages = $this->buildBaseQuery()
        ->featured() // Uses Package model scope (checks is_featured + expiry)
        ->orderByDesc('featured_at') // Most recently featured first
        ->orderByDesc('average_rating')
        ->orderByDesc('bookings_count')
        ->limit($limit)
        ->get();

    return $packages->map(function ($package) {
        return $this->transformPackageForB2C($package);
    });
}
```

**What This Does:**
- Uses the `featured()` scope from Package model
- Automatically checks `is_featured = true` AND `featured_expires_at > now()` OR is null
- Only shows packages approved by admin via Featured Products workflow
- Respects expiry dates set by admin
- Orders by most recently featured first

### 2. Controller Updates
**File:** `app/Http/Controllers/Customer/DashboardController.php`

**Changed Method:** `home()`

**Improvements:**
- Added try-catch error handling
- Added detailed logging
- Provides graceful fallback if featured packages fail to load
- Updated comments to reflect Featured Products integration

**Error Handling:**
```php
try {
    $featuredPackages = $this->packageService->getFeaturedPackages(4);
} catch (\Exception $e) {
    Log::error('Error loading home page data: ' . $e->getMessage());
    $featuredPackages = collect([]); // Empty state handled by view
}
```

### 3. Cache Management
**File:** `app/Http/Controllers/Api/Admin/AdminFeatureController.php`

**New Method:** `clearFeaturedCache()`

**Integration Points:**
- Cache cleared when admin approves a feature request
- Cache cleared when admin rejects a feature request
- Cache cleared when admin manually features a product
- Cache cleared when admin unfeatures a product

**Cache Keys Cleared:**
```php
'b2c_featured_packages_4',
'b2c_featured_packages_8',
'b2c_featured_packages_12',
'b2c_package_statistics',
```

**Why This Matters:**
- Home page shows updated featured products within cache TTL (2 hours by default)
- When admin makes changes, cache is immediately cleared
- Next visitor sees fresh featured products list

### 4. View Layer
**File:** `resources/views/customer/home.blade.php`

**No Changes Required** ✅
- Already has empty state handling (`@if(count($featuredPackages) > 0)`)
- Gracefully shows "Featured Packages Coming Soon" message when empty
- Responsive design maintained
- All existing styling preserved

## Workflow

### Admin Perspective

1. **Agent/Provider requests feature:**
   ```
   POST /api/feature/request
   {
     "product_id": 5,
     "product_type": "package",
     "notes": "Special Ramadan package"
   }
   ```

2. **Admin reviews in dashboard:**
   - Navigate to `/admin/featured/requests`
   - View pending requests
   - Click "Approve" or "Reject"

3. **Admin approves:**
   ```
   POST /api/admin/feature/approve/5
   {
     "priority_level": 10,
     "end_date": "2026-03-31"
   }
   ```

4. **System actions:**
   - Updates `packages.is_featured = true`
   - Sets `packages.featured_at = now()`
   - Sets `packages.featured_expires_at = '2026-03-31'`
   - **Clears featured packages cache**
   - Creates approved featured_request record

5. **Customer sees it:**
   - Next visit to home page (within cache TTL or after cache clear)
   - Featured package appears in "Featured Umrah Packages" section
   - Badge shows "Featured" with star icon

### Customer Perspective

**What They See:**
1. Home page loads
2. "Featured Umrah Packages" section populated with up to 4 packages
3. Each card shows:
   - Package image
   - "Featured" badge
   - Package name
   - Rating
   - Description
   - Duration, capacity, destinations
   - Price
   - "View Details" and "Book Now" buttons

**Empty State:**
If no featured packages exist:
```
✨ Featured Packages Coming Soon
Our partners are preparing amazing Umrah packages for you.
[Browse Available Packages]
```

## Data Flow

```
┌─────────────────────────────────────────────────────────┐
│  Admin approves featured request                        │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│  Database Updates:                                       │
│  - featured_requests.status = 'approved'                │
│  - packages.is_featured = true                          │
│  - packages.featured_at = now()                         │
│  - packages.featured_expires_at = '2026-03-31'          │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│  Cache::forget('b2c_featured_packages_4')               │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│  Customer visits home page                              │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│  DashboardController::home()                            │
│    └─> packageService->getFeaturedPackages(4)          │
│         └─> Cache miss, fetch from DB                   │
│              └─> Package::featured()->get()             │
│                   └─> WHERE is_featured = 1             │
│                       AND (featured_expires_at IS NULL  │
│                            OR featured_expires_at > NOW)│
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│  home.blade.php renders featured packages               │
└─────────────────────────────────────────────────────────┘
```

## Performance Considerations

### Caching Strategy
- **Default TTL:** 2 hours (7200 seconds)
- **Cache Key Format:** `b2c_featured_packages_{limit}`
- **Cache Provider:** Laravel cache (Redis/File/Database as configured)

### Query Optimization
```php
Package::query()
    ->select([...]) // Only needed columns
    ->active()
    ->approved()
    ->featured() // Adds is_featured check + expiry check
    ->orderByDesc('featured_at')
    ->limit(4)
    ->get();
```

**Indexes Used:**
- `packages.is_featured` (added by migration)
- `packages.featured_expires_at` (added by migration)
- `packages.status`
- `packages.approval_status`

### Database Queries
- **First Request:** 1 query (with eager loading if configured)
- **Cached Requests:** 0 queries
- **After Admin Change:** Cache cleared, next request = 1 query

## Testing

### Manual Testing Steps

1. **Verify Empty State:**
   ```bash
   # Clear all featured packages
   php artisan tinker
   >>> DB::table('packages')->update(['is_featured' => false]);
   >>> Cache::flush();
   ```
   Visit home page → Should show "Featured Packages Coming Soon"

2. **Create Featured Package (Admin Way):**
   ```bash
   # As admin user via API
   POST /api/admin/feature/manual
   {
     "product_id": 1,
     "product_type": "package",
     "priority_level": 5,
     "end_date": "2026-12-31"
   }
   ```
   Visit home page → Should show 1 featured package

3. **Test Expiry:**
   ```bash
   php artisan tinker
   >>> $pkg = Package::first();
   >>> $pkg->update([
         'is_featured' => true, 
         'featured_at' => now(), 
         'featured_expires_at' => now()->subDay()
       ]);
   >>> Cache::flush();
   ```
   Visit home page → Package should NOT appear (expired)

4. **Test Cache Clearing:**
   - Approve a featured request via admin panel
   - Immediately visit home page
   - Should see new featured package (cache was cleared)

5. **Test Error Handling:**
   ```bash
   # Simulate database error
   php artisan down
   ```
   Visit home page → Should show graceful fallback (empty state)

### Automated Testing

Create `tests/Feature/FeaturedPackagesHomeTest.php`:
```php
public function test_home_page_shows_featured_packages()
{
    $package = Package::factory()->create([
        'is_featured' => true,
        'featured_at' => now(),
        'featured_expires_at' => now()->addDays(30),
        'status' => 'active',
        'approval_status' => 'approved',
    ]);

    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee($package->name);
    $response->assertSee('Featured');
}

public function test_home_page_hides_expired_featured_packages()
{
    $package = Package::factory()->create([
        'is_featured' => true,
        'featured_at' => now()->subDays(10),
        'featured_expires_at' => now()->subDay(),
        'status' => 'active',
        'approval_status' => 'approved',
    ]);

    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertDontSee($package->name);
}
```

## Configuration

**File:** `config/b2c.php`

Relevant settings:
```php
'cache' => [
    'enabled' => env('B2C_CACHE_ENABLED', true),
    'ttl' => [
        'featured_packages' => env('B2C_CACHE_FEATURED_TTL', 7200), // 2 hours
        'package_statistics' => env('B2C_CACHE_STATS_TTL', 14400), // 4 hours
    ],
],
```

**Environment Variables:**
```env
# Enable/disable caching (useful for development)
B2C_CACHE_ENABLED=true

# Featured packages cache duration (seconds)
B2C_CACHE_FEATURED_TTL=7200

# Statistics cache duration (seconds)
B2C_CACHE_STATS_TTL=14400
```

## Troubleshooting

### Featured packages not showing

**Check 1: Database**
```sql
SELECT id, name, is_featured, featured_at, featured_expires_at, status, approval_status
FROM packages 
WHERE is_featured = 1;
```

**Check 2: Cache**
```bash
php artisan cache:clear
# Or specific key
php artisan tinker
>>> Cache::forget('b2c_featured_packages_4');
```

**Check 3: Logs**
```bash
tail -f storage/logs/laravel.log | grep featured
```

**Check 4: Query**
```bash
php artisan tinker
>>> use App\Models\Package;
>>> Package::featured()->get();
```

### Cache not clearing after admin action

**Solution 1: Check cache driver**
```bash
# .env file
CACHE_DRIVER=redis # or file, database
```

**Solution 2: Manual clear**
```bash
php artisan cache:clear
```

**Solution 3: Verify method calls**
Check that `clearFeaturedCache()` is being called in AdminFeatureController

### Performance issues

**Solution 1: Enable caching**
```env
B2C_CACHE_ENABLED=true
```

**Solution 2: Use Redis**
```env
CACHE_DRIVER=redis
```

**Solution 3: Optimize eager loading**
Edit `config/b2c.php`:
```php
'performance' => [
    'eager_load_relations' => ['creator'],
],
```

## Security Considerations

✅ **Authorization:** Only admin can approve/feature packages
✅ **Validation:** All inputs validated in controllers
✅ **SQL Injection:** Using Eloquent ORM and query builder
✅ **XSS Protection:** Blade auto-escaping enabled
✅ **Cache Poisoning:** Cache keys are predictable but safe (read-only public data)

## Maintenance

### Daily Tasks
- Monitor featured packages expiry
- Automated cleanup runs daily at 3 AM (`featured:cleanup` command)

### Weekly Tasks
- Review featured packages performance
- Check cache hit rates
- Review error logs for feature-related issues

### Monthly Tasks
- Analyze featured packages conversion rates
- Review and optimize cache TTL settings
- Database index performance review

---

**Status:** ✅ Production Ready
**Last Updated:** November 5, 2025
**Version:** 1.0
