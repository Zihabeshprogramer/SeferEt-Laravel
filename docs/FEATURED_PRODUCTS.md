# Featured Products Feature - Implementation Guide

## Overview
This document describes the complete implementation of the **Featured Products** feature for the SeferEt Laravel platform. This feature allows any user type with correct permissions to request a product to be featured, and allows the Admin to review, approve, reject, or manually feature/unfeature any product.

## Architecture

### Database Structure

#### 1. `featured_requests` Table
Main table for managing feature requests:
- `id` - Primary key
- `product_id` - ID of the product to be featured
- `product_type` - Type of product: 'flight', 'hotel', 'package'
- `requested_by` - User ID who requested the feature
- `status` - Request status: 'pending', 'approved', 'rejected'
- `approved_by` - Admin user ID who approved/rejected (nullable)
- `approved_at` - Timestamp of approval (nullable)
- `priority_level` - Priority ranking (1-100, higher = more prominent)
- `start_date` - When the feature should start (nullable)
- `end_date` - When the feature should expire (nullable)
- `notes` - Additional notes
- `rejection_reason` - Reason if rejected (nullable)
- `created_at`, `updated_at` - Standard timestamps

#### 2. Product Tables Updates
Added featured columns to `flights`, `hotels`, and `packages` tables:
- `is_featured` - Boolean flag
- `featured_at` - Timestamp when featured
- `featured_expires_at` - Expiry timestamp

### Models

#### FeaturedRequest Model
**Location:** `app/Models/FeaturedRequest.php`

**Key Features:**
- Polymorphic relationship to products (flights, hotels, packages)
- Relationships to requester and approver users
- Scopes for filtering (pending, approved, rejected, active, expired)
- Helper methods for status checks

**Constants:**
```php
STATUS_PENDING = 'pending'
STATUS_APPROVED = 'approved'
STATUS_REJECTED = 'rejected'

PRODUCT_TYPE_FLIGHT = 'flight'
PRODUCT_TYPE_HOTEL = 'hotel'
PRODUCT_TYPE_PACKAGE = 'package'
```

#### Product Models (Flight, Hotel, Package)
Updated to include:
- `featuredRequests()` relationship
- `activeFeaturedRequest()` method
- `featured()` scope for querying featured products
- Featured attributes in `$fillable` and `$casts`

### Authorization & Security

#### FeaturedRequestPolicy
**Location:** `app/Policies/FeaturedRequestPolicy.php`

**Permissions:**
- `viewAny` - Admin or B2B users can view requests
- `view` - Admin or request owner
- `create` - B2B users can request for their own products only
- `approve` - Admin only
- `reject` - Admin only
- `delete` - Admin or requester (pending only)
- `manualFeature` - Admin only
- `unfeature` - Admin only
- `manageFeatured` - Admin only

**Ownership Validation:**
- Flights: `provider_id` must match user
- Hotels: `provider_id` must match user
- Packages: `creator_id` must match user

### API Endpoints

#### Provider/Agent Endpoints
**Base:** `/api/feature`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/requests` | List user's feature requests |
| POST | `/request` | Create new feature request |
| GET | `/requests/{id}` | View specific request |
| DELETE | `/requests/{id}` | Cancel pending request |
| GET | `/status/{type}/{id}` | Get feature status for product |

#### Admin Endpoints
**Base:** `/api/admin/feature`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/requests` | View all feature requests |
| POST | `/approve/{id}` | Approve a request |
| POST | `/reject/{id}` | Reject a request |
| POST | `/manual` | Manually feature a product |
| POST | `/unfeature` | Unfeature a product |
| GET | `/featured` | Get all featured products |
| GET | `/statistics` | Get feature statistics |
| PATCH | `/priority/{id}` | Update priority level |

### Controllers

#### FeatureRequestController
**Location:** `app/Http/Controllers/Api/FeatureRequestController.php`

Handles provider/agent feature requests:
- List user's requests with filters
- Create new requests with ownership validation
- View request details
- Cancel pending requests
- Check feature status for products

#### AdminFeatureController
**Location:** `app/Http/Controllers/Api/Admin/AdminFeatureController.php`

Handles admin management:
- View all requests with filtering
- Approve/reject requests
- Manual feature products
- Unfeature products
- View featured products list
- Get statistics dashboard
- Update priority levels

### Scheduled Tasks

#### UnfeatureExpiredProducts Command
**Location:** `app/Console/Commands/UnfeatureExpiredProducts.php`

**Command:** `php artisan featured:cleanup`

**Schedule:** Daily at 3:00 AM

**Function:**
- Checks all products for expired `featured_expires_at` dates
- Automatically unfeatures expired products
- Logs cleanup activity
- Runs separately for flights, hotels, and packages

### Frontend Integration

#### Admin Views
**Location:** `resources/views/admin/featured/`

**Key Files:**
- `requests.blade.php` - Main featured requests management page

**Features:**
- Statistics dashboard (pending, active, total, rejected)
- Filter by status, product type, and search
- Approve/reject modals with validation
- Real-time AJAX updates
- Priority management
- Date range configuration

#### Provider/Agent Dashboard Integration
To integrate into existing dashboards, add:

```php
// Check if product can be featured
$canRequestFeature = !$product->is_featured && 
                     !$product->featuredRequests()->pending()->exists();

// Get current feature status
$featureRequest = $product->featuredRequests()->latest()->first();
```

#### UI Components Needed
1. **Request Feature Button**
   - Show only for product owners
   - Disable if already featured or pending request exists
   - Modal with date range and notes fields

2. **Feature Status Badge**
   - Display on product cards
   - Show different colors for pending, approved, rejected
   - Include expiry date if applicable

3. **Feature Request History**
   - List all requests for a product
   - Show status, dates, and admin notes

## Usage Examples

### 1. Provider Requests Feature for Their Flight

```javascript
POST /api/feature/request
{
  "product_id": 123,
  "product_type": "flight",
  "notes": "Holiday season special offer",
  "start_date": "2025-12-01",
  "end_date": "2025-12-31"
}
```

### 2. Admin Approves Request

```javascript
POST /api/admin/feature/approve/456
{
  "priority_level": 10,
  "start_date": "2025-12-01",
  "end_date": "2025-12-31",
  "notes": "Approved for holiday season"
}
```

### 3. Admin Manually Features a Product

```javascript
POST /api/admin/feature/manual
{
  "product_id": 789,
  "product_type": "hotel",
  "priority_level": 5,
  "end_date": "2026-03-31"
}
```

### 4. Query Featured Products

```php
// Get all featured flights
$featuredFlights = Flight::featured()->get();

// Get featured hotels with highest priority
$topHotels = Hotel::featured()
    ->leftJoin('featured_requests', function($join) {
        $join->on('hotels.id', '=', 'featured_requests.product_id')
             ->where('featured_requests.product_type', 'hotel')
             ->where('featured_requests.status', 'approved');
    })
    ->orderBy('featured_requests.priority_level', 'desc')
    ->get();

// Get featured packages within date range
$packages = Package::featured()
    ->whereDate('featured_at', '<=', now())
    ->whereDate('featured_expires_at', '>=', now())
    ->get();
```

## Installation Steps

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Register Policy** (Add to `AuthServiceProvider.php`)
   ```php
   protected $policies = [
       FeaturedRequest::class => FeaturedRequestPolicy::class,
   ];
   ```

3. **Include Routes** (Add to `routes/api.php`)
   ```php
   require __DIR__.'/api_featured.php';
   ```

4. **Schedule Task** (Already added to `app/Console/Kernel.php`)
   ```php
   $schedule->command('featured:cleanup')->dailyAt('03:00');
   ```

5. **Setup Cron** (On server)
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

## Testing

### Manual Testing Checklist

**Provider/Agent:**
- [ ] Can request feature for own product
- [ ] Cannot request for others' products
- [ ] Cannot create duplicate pending requests
- [ ] Can view own request history
- [ ] Can cancel pending requests
- [ ] Cannot cancel approved/rejected requests

**Admin:**
- [ ] Can view all requests
- [ ] Can filter by status and type
- [ ] Can approve requests with custom dates/priority
- [ ] Can reject requests with reason
- [ ] Can manually feature any product
- [ ] Can unfeature any product
- [ ] Can view statistics dashboard

**System:**
- [ ] Expired products auto-unfeature daily
- [ ] Featured scope works correctly
- [ ] Priority ordering works
- [ ] Polymorphic relationships work

### Test Cases

```php
// Test provider can request feature
$this->actingAs($provider)
    ->postJson('/api/feature/request', [
        'product_id' => $flight->id,
        'product_type' => 'flight'
    ])
    ->assertStatus(201);

// Test admin can approve
$this->actingAs($admin)
    ->postJson("/api/admin/feature/approve/{$request->id}", [
        'priority_level' => 5
    ])
    ->assertStatus(200);

// Test product is featured after approval
$flight->refresh();
$this->assertTrue($flight->is_featured);
```

## Security Considerations

1. **Authorization:** All endpoints protected by policies
2. **Ownership:** Strict validation of product ownership
3. **Duplicate Prevention:** Check for existing pending/approved requests
4. **Input Validation:** All dates and priority levels validated
5. **CSRF Protection:** API uses Sanctum tokens
6. **SQL Injection:** Use query builder/Eloquent
7. **XSS Protection:** Blade auto-escaping enabled

## Performance Optimization

1. **Indexes:** Added on `is_featured`, `featured_expires_at`, and `status`
2. **Eager Loading:** Use `with()` for relationships
3. **Caching:** Consider caching featured products list
4. **Pagination:** All list endpoints paginated
5. **Background Jobs:** Cleanup runs asynchronously

## Future Enhancements

1. **Notifications:** Email/SMS when request approved/rejected
2. **Analytics:** Track views/clicks on featured products
3. **Payment:** Paid featured spots
4. **A/B Testing:** Test featured vs non-featured performance
5. **Auto-renewal:** Option to auto-renew expired featured
6. **Bulk Operations:** Feature multiple products at once
7. **API Rate Limiting:** Prevent spam requests

## Support

For issues or questions, refer to:
- Code comments in controllers and models
- Laravel documentation: https://laravel.com/docs
- Policy documentation: https://laravel.com/docs/authorization

---

**Version:** 1.0  
**Last Updated:** November 5, 2025  
**Author:** SeferEt Development Team
