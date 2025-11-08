# Featured Products - Quick Setup Guide

## Prerequisites
- Laravel 10+
- PHP 8.1+
- MySQL/PostgreSQL database
- Existing user authentication (Sanctum)

## Setup Steps

### 1. Run Database Migrations
```bash
cd C:\Users\seide\SeferEt\SeferEt-Laravel
php artisan migrate
```

This will create:
- `featured_requests` table
- Add featured columns to `flights`, `hotels`, `packages` tables

### 2. Register the Policy

Edit `app/Providers/AuthServiceProvider.php`:

```php
use App\Models\FeaturedRequest;
use App\Policies\FeaturedRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FeaturedRequest::class => FeaturedRequestPolicy::class,
    ];
}
```

### 3. Include Routes

Edit `routes/api.php` and add:

```php
// At the end of the file
require __DIR__.'/api_featured.php';
```

### 4. Verify Scheduled Task

The cleanup task is already added to `app/Console/Kernel.php`. 

Verify it's present:
```php
$schedule->command('featured:cleanup')
         ->dailyAt('03:00')
         ->withoutOverlapping()
         ->runInBackground();
```

### 5. Test the Command Manually

```bash
php artisan featured:cleanup
```

Expected output:
```
Starting featured products cleanup...
Unfeatured 0 expired flights.
Unfeatured 0 expired hotels.
Unfeatured 0 expired packages.
Cleanup complete. Total products unfeatured: 0
```

### 6. Set Up Cron Job (Production Only)

On your server, add to crontab:
```bash
crontab -e
```

Add this line:
```bash
* * * * * cd /path/to/SeferEt-Laravel && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Add Admin Menu Item

Edit your admin layout file (e.g., `resources/views/layouts/admin.blade.php`):

```html
<!-- In the sidebar navigation -->
<li class="nav-item">
    <a class="nav-link" href="/admin/featured/requests">
        <i class="fas fa-star"></i>
        <span>Featured Products</span>
    </a>
</li>
```

### 8. Create Admin Web Routes

Create `routes/admin.php` or add to existing admin routes:

```php
use App\Http\Controllers\Admin\FeaturedWebController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/featured/requests', function () {
        return view('admin.featured.requests');
    })->name('featured.requests');
    
    Route::get('/featured/featured', function () {
        return view('admin.featured.featured');
    })->name('featured.featured');
    
    Route::get('/featured/manual', function () {
        return view('admin.featured.manual');
    })->name('featured.manual');
});
```

## Testing the Implementation

### Test 1: Create a Feature Request (API)

Using Postman or similar:

```http
POST /api/feature/request
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "product_id": 1,
  "product_type": "flight",
  "notes": "Test feature request",
  "end_date": "2026-12-31"
}
```

Expected Response:
```json
{
  "success": true,
  "message": "Feature request submitted successfully.",
  "data": { ... }
}
```

### Test 2: View Requests as Admin

```http
GET /api/admin/feature/requests
Authorization: Bearer {admin_token}
```

### Test 3: Approve Request

```http
POST /api/admin/feature/approve/1
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "priority_level": 5,
  "end_date": "2026-12-31"
}
```

### Test 4: Query Featured Products

```php
// In tinker or controller
php artisan tinker

>>> App\Models\Flight::featured()->count();
>>> App\Models\Hotel::featured()->get();
>>> App\Models\Package::where('is_featured', true)->first();
```

## Verification Checklist

- [ ] Migrations ran successfully
- [ ] Policy is registered
- [ ] Routes are accessible
- [ ] Admin can view requests page
- [ ] Providers can create requests
- [ ] Admin can approve/reject
- [ ] Products marked as featured after approval
- [ ] Cleanup command runs without errors
- [ ] Featured scope returns correct products

## Common Issues & Solutions

### Issue: Policy not working
**Solution:** Clear config cache
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Routes not found
**Solution:** Clear route cache
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Migration already exists error
**Solution:** Check if tables exist
```bash
php artisan migrate:status
```

### Issue: Unauthorized errors
**Solution:** Ensure user has correct role and token is valid
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->role; // Should be 'admin' for admin endpoints
```

## Integration with Existing Code

### Add to Flight Controller
```php
use App\Models\FeaturedRequest;

public function show(Flight $flight)
{
    $featureRequest = $flight->featuredRequests()->latest()->first();
    $canRequestFeature = !$flight->is_featured && 
                         !$flight->featuredRequests()->pending()->exists();
    
    return view('flights.show', compact('flight', 'featureRequest', 'canRequestFeature'));
}
```

### Add to Hotel Dashboard
```php
public function dashboard()
{
    $hotels = auth()->user()->hotels()
        ->with(['featuredRequests' => function($q) {
            $q->latest();
        }])
        ->get();
    
    return view('provider.dashboard', compact('hotels'));
}
```

### Display Featured Badge in Blade
```blade
@if($product->is_featured)
    <span class="badge badge-warning">
        <i class="fas fa-star"></i> Featured
        @if($product->featured_expires_at)
            (Until {{ $product->featured_expires_at->format('M d, Y') }})
        @endif
    </span>
@endif
```

## Next Steps

1. **Customize Admin Views** - Adjust styling to match your theme
2. **Add Notifications** - Email users when requests are approved/rejected
3. **Add Analytics** - Track performance of featured products
4. **Create Frontend Components** - Build Vue/React components for request buttons
5. **Add Payment Integration** - If you want to charge for featuring

## Support

For detailed documentation, see: `docs/FEATURED_PRODUCTS.md`

For questions or issues:
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: Set `APP_DEBUG=true` in `.env` (development only)
- Run tests: `php artisan test` (if you create test files)

---

**Setup completed on:** ___________  
**Tested by:** ___________  
**Deployed to production:** ___________
