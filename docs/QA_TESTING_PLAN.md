# SeferEt Advertising System - QA Testing Plan

## Overview
This document provides comprehensive testing guidelines for the SeferEt advertising system, covering unit tests, integration tests, end-to-end tests, security tests, performance tests, and manual testing procedures.

## Table of Contents
1. [Test Environment Setup](#test-environment-setup)
2. [Unit Testing](#unit-testing)
3. [Integration Testing](#integration-testing)
4. [End-to-End Testing](#end-to-end-testing)
5. [Security Testing](#security-testing)
6. [Performance Testing](#performance-testing)
7. [Manual Testing Checklist](#manual-testing-checklist)
8. [Test Data](#test-data)
9. [Recovery Procedures](#recovery-procedures)

---

## Test Environment Setup

### Laravel Backend Setup

```bash
# Navigate to Laravel project
cd C:\Users\seide\SeferEt\SeferEt-Laravel

# Install dependencies
composer install

# Copy environment file
cp .env.example .env.testing

# Configure testing database in .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Run migrations
php artisan migrate --env=testing

# Seed test data
php artisan db:seed --env=testing
```

### Flutter App Setup

```bash
# Navigate to Flutter project
cd C:\Users\seide\SeferEt\SeferEt-Flutter

# Get dependencies
flutter pub get

# Run tests
flutter test
```

---

## Unit Testing

### Running Unit Tests

```bash
# Run all unit tests
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Unit/AdModelTest.php

# Run with coverage
php artisan test --coverage
```

### Coverage Targets
- **Ad Model**: 95%+ coverage
- **Policies**: 100% coverage
- **Services**: 90%+ coverage
- **Jobs**: 90%+ coverage

### Key Unit Test Files

| Test File | Purpose | Priority |
|-----------|---------|----------|
| `AdModelTest.php` | Ad model logic, scopes, relationships | HIGH |
| `AdPolicyTest.php` | Authorization rules | HIGH |
| `AdImageServiceTest.php` | Image upload, validation, processing | HIGH |
| `ProcessAdSchedulingTest.php` | Scheduling job logic | MEDIUM |
| `AdImpressionTest.php` | Impression tracking logic | MEDIUM |
| `AdClickTest.php` | Click tracking logic | MEDIUM |

---

## Integration Testing

### Running Integration Tests

```bash
# Run all feature tests
php artisan test --testsuite=Feature

# Run ad-specific tests
php artisan test tests/Feature/AdApiTest.php
php artisan test tests/Feature/AdWorkflowTest.php
php artisan test tests/Feature/AdSecurityTest.php
```

### API Endpoint Tests

#### 1. Ad Serving Endpoint Tests
```bash
GET /api/v1/ads/serve
```

**Test Cases:**
- ✅ Serves active ads without authentication
- ✅ Filters by placement (home_top, package_details, etc.)
- ✅ Filters by device type (mobile, tablet, desktop)
- ✅ Respects region targeting
- ✅ Prioritizes local owners first
- ✅ Returns cached results (2-minute TTL)
- ✅ Excludes expired ads
- ✅ Excludes future-scheduled ads
- ✅ Respects impression/click limits
- ✅ Returns proper JSON structure

#### 2. Impression Tracking Tests
```bash
POST /api/v1/ads/{id}/track/impression
```

**Test Cases:**
- ✅ Tracks impression without authentication
- ✅ Increments ad impression counter
- ✅ Prevents duplicate tracking (5-minute window)
- ✅ Rate limiting (60 per minute per IP)
- ✅ Records device_type and placement
- ✅ Captures IP, user agent, page URL
- ✅ Handles invalid ad ID gracefully

#### 3. Click Tracking Tests
```bash
POST /api/v1/ads/{id}/track/click
```

**Test Cases:**
- ✅ Tracks click without authentication
- ✅ Increments ad click counter
- ✅ Updates CTR calculation
- ✅ Prevents duplicate tracking (5-minute window)
- ✅ Rate limiting (30 per minute per IP)
- ✅ Returns redirect URL
- ✅ Records conversion data

#### 4. B2B CRUD Operations
```bash
GET    /api/v1/ads          # List ads
POST   /api/v1/ads          # Create ad
GET    /api/v1/ads/{id}     # View ad
PUT    /api/v1/ads/{id}     # Update ad
DELETE /api/v1/ads/{id}     # Delete ad
```

**Test Cases:**
- ✅ B2B users can create ads
- ✅ Customers cannot create ads
- ✅ Owners can only see their own ads
- ✅ Owners can edit draft/rejected ads only
- ✅ Owners cannot edit approved ads
- ✅ Owners can delete draft/rejected ads
- ✅ Authorization checks prevent cross-user access

#### 5. Admin Approval Workflow
```bash
POST /admin/ads/{id}/approve
POST /admin/ads/{id}/reject
```

**Test Cases:**
- ✅ Admin can approve pending ads
- ✅ Admin can reject with reason
- ✅ Status updates correctly
- ✅ Notifications sent to owner
- ✅ Audit log created
- ✅ Non-admins blocked from approval actions

---

## End-to-End Testing

### Complete Flow Test

```gherkin
Feature: Advertisement Complete Lifecycle

Scenario: B2B user creates and publishes advertisement
  Given I am a logged-in travel agent
  When I create a new advertisement
  And I upload an image
  And I configure CTA button
  And I submit for approval
  Then the ad status should be "pending"
  
  When an admin approves my ad
  Then the ad status should be "approved"
  And I should receive an approval notification
  
  When a customer views the home screen
  Then my ad should be visible
  
  When the customer views the ad
  Then an impression should be tracked
  
  When the customer clicks the ad
  Then a click should be tracked
  And the CTR should be calculated
  
  When the customer completes a booking
  Then a conversion should be tracked
```

### Testing the Complete Flow

```bash
# 1. Create test users
php artisan tinker
$agent = User::factory()->create(['role' => 'travel_agent', 'email' => 'agent@test.com']);
$admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);
$customer = User::factory()->create(['role' => 'customer', 'email' => 'customer@test.com']);

# 2. Create ad as agent (via API or web interface)
# POST /api/v1/ads with auth token

# 3. Submit for approval
# PUT /api/v1/ads/{id}/submit

# 4. Approve as admin
# POST /admin/ads/{id}/approve

# 5. Test mobile app
# Run Flutter app and verify ad appears

# 6. Track impression and click
# Interact with ad in Flutter app

# 7. Verify analytics
# Check admin dashboard for stats
```

---

## Security Testing

### 1. Upload Sanitization Tests

**Test Cases:**
- ✅ Rejects non-image files (PDF, EXE, ZIP)
- ✅ Validates file size (max 5MB)
- ✅ Validates image dimensions (800x600 to 4000x3000)
- ✅ Validates aspect ratio
- ✅ Sanitizes filenames
- ✅ Prevents path traversal attacks
- ✅ Validates MIME types
- ✅ Scans for malicious content

**Manual Test:**
```bash
# Try uploading various malicious files
curl -X POST http://localhost:8000/api/v1/ads/1/upload-image \
  -H "Authorization: Bearer TOKEN" \
  -F "image=@malicious.php"
  
# Expected: 422 Unprocessable Entity
```

### 2. XSS Prevention Tests

**Test Cases:**
- ✅ CTA text sanitized (strips HTML)
- ✅ Title and description sanitized
- ✅ No JavaScript execution in ad content
- ✅ Image alt text sanitized

**Manual Test:**
```bash
# Try creating ad with XSS payload
curl -X POST http://localhost:8000/api/v1/ads \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "<script>alert(1)</script>Test",
    "cta_text": "<img src=x onerror=alert(1)>",
    "description": "<iframe src=evil.com></iframe>"
  }'
  
# Expected: Content is sanitized
```

### 3. SQL Injection Tests

**Test Cases:**
- ✅ All inputs properly escaped
- ✅ Query builder used (no raw SQL)
- ✅ Prepared statements for all queries

**Manual Test:**
```bash
# Try SQL injection in search
GET /api/v1/ads?search='; DROP TABLE ads; --

# Expected: Safely escaped, no database damage
```

### 4. Authorization Bypass Tests

**Test Cases:**
- ✅ Cannot view other users' draft ads
- ✅ Cannot edit other users' ads
- ✅ Cannot delete other users' ads
- ✅ Cannot approve ads without admin role
- ✅ Cannot bypass status restrictions
- ✅ Cannot access admin endpoints as B2B user

**Manual Test:**
```bash
# Try to access another user's ad
curl -X GET http://localhost:8000/api/v1/ads/999 \
  -H "Authorization: Bearer USER_TOKEN"
  
# Expected: 403 Forbidden or 404 Not Found
```

### 5. Deep Link Safety Tests

**Test Cases:**
- ✅ CTA action URL validated
- ✅ Only HTTPS URLs allowed (or app deep links)
- ✅ No javascript: protocol
- ✅ No data: protocol
- ✅ Whitelist of allowed domains (optional)

**Manual Test:**
```bash
# Try malicious deep link
curl -X POST http://localhost:8000/api/v1/ads \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "title": "Test",
    "cta_action": "javascript:alert(1)"
  }'
  
# Expected: 422 Validation Error
```

### 6. Rate Limiting Tests

**Test Cases:**
- ✅ Impression tracking limited (60/min)
- ✅ Click tracking limited (30/min)
- ✅ API requests limited (100/min)
- ✅ Returns 429 when exceeded

**Load Test Script:**
```bash
# Install artillery
npm install -g artillery

# Run load test
artillery quick --count 100 --num 1 http://localhost:8000/api/v1/ads/1/track/impression
```

---

## Performance Testing

### 1. Ad Serving Performance

**Requirements:**
- Response time < 200ms (with cache)
- Response time < 500ms (without cache)
- Supports 1000 requests/second

**Test Script:**
```bash
# Install Apache Bench
# On Windows: Download from Apache website

# Load test ad serving
ab -n 10000 -c 100 http://localhost:8000/api/v1/ads/serve?placement=home_top

# Expected results:
# - Time per request: < 200ms (50th percentile)
# - Time per request: < 500ms (95th percentile)
# - Failed requests: 0
```

### 2. Cache Validation

**Test Cases:**
- ✅ First request hits database
- ✅ Second request hits cache
- ✅ Cache expires after 2 minutes
- ✅ Cache invalidated on ad update

**Manual Test:**
```bash
# Enable query logging
DB_LOG_QUERIES=true php artisan serve

# Make first request
curl http://localhost:8000/api/v1/ads/serve

# Make second request
curl http://localhost:8000/api/v1/ads/serve

# Check logs - second request should have fewer queries
```

### 3. Database Query Optimization

**Requirements:**
- Max 5 queries per ad serving request
- Max 3 queries per tracking request
- Eager loading for relationships

**Test:**
```php
// Enable query logging in test
DB::enableQueryLog();

$response = $this->get('/api/v1/ads/serve');

$queries = DB::getQueryLog();
$this->assertLessThanOrEqual(5, count($queries));
```

### 4. Impression/Click Write Performance

**Requirements:**
- Handle 10,000 impressions/minute
- Handle 1,000 clicks/minute
- No blocking operations

**Test:**
```bash
# Use artillery for load testing
artillery run impression-load-test.yml

# impression-load-test.yml
config:
  target: 'http://localhost:8000'
  phases:
    - duration: 60
      arrivalRate: 200  # 200 requests/second
scenarios:
  - name: Track Impressions
    flow:
      - post:
          url: '/api/v1/ads/{{ adId }}/track/impression'
          json:
            device_type: 'mobile'
```

---

## Manual Testing Checklist

### Pre-Testing Setup
- [ ] Test environment configured
- [ ] Test database seeded
- [ ] Test users created
- [ ] Test images prepared
- [ ] Browser DevTools open
- [ ] Network throttling configured (for mobile testing)

### 1. B2B Ad Creation Flow

| Test Case | Steps | Expected Result | Status | Notes |
|-----------|-------|-----------------|--------|-------|
| **Create Draft Ad** | 1. Login as travel agent<br>2. Navigate to Ads<br>3. Click "Create Ad"<br>4. Fill form<br>5. Save as draft | Ad created with status "draft" | ⬜ | |
| **Upload Image** | 1. Click "Upload Image"<br>2. Select valid image<br>3. Crop if needed<br>4. Save | Image uploaded and displayed | ⬜ | |
| **Invalid Image** | 1. Try upload PDF<br>2. Try upload oversized image<br>3. Try upload tiny image | Proper validation errors shown | ⬜ | |
| **CTA Configuration** | 1. Enter CTA text<br>2. Enter CTA URL<br>3. Select CTA style<br>4. Adjust position | CTA configured correctly | ⬜ | |
| **Submit for Approval** | 1. Click "Submit"<br>2. Confirm | Status changes to "pending" | ⬜ | |
| **Edit Draft** | 1. Edit draft ad<br>2. Make changes<br>3. Save | Changes saved | ⬜ | |
| **Cannot Edit Approved** | 1. Try to edit approved ad | Edit button disabled/blocked | ⬜ | |
| **Delete Draft** | 1. Delete draft ad<br>2. Confirm | Ad deleted (soft delete) | ⬜ | |

### 2. Admin Approval Flow

| Test Case | Steps | Expected Result | Status | Notes |
|-----------|-------|-----------------|--------|-------|
| **View Pending Ads** | 1. Login as admin<br>2. Navigate to Ads<br>3. Click "Pending" | List of pending ads shown | ⬜ | |
| **Review Ad Details** | 1. Click on pending ad<br>2. Review image<br>3. Review content | All details visible | ⬜ | |
| **Approve Ad** | 1. Click "Approve"<br>2. Set scheduling<br>3. Set priority<br>4. Confirm | Ad approved, owner notified | ⬜ | |
| **Reject Ad** | 1. Click "Reject"<br>2. Enter reason<br>3. Confirm | Ad rejected, owner notified | ⬜ | |
| **Bulk Approve** | 1. Select multiple ads<br>2. Bulk approve | All approved | ⬜ | |

### 3. Flutter App Ad Display

| Test Case | Steps | Expected Result | Status | Notes |
|-----------|-------|-----------------|--------|-------|
| **View Home Screen** | 1. Open app<br>2. Wait for loading | Ads displayed in carousel | ⬜ | |
| **Ad Image Loads** | 1. Observe ad images | Images load correctly | ⬜ | |
| **Responsive Images** | 1. Test on various devices | Correct image variant loads | ⬜ | |
| **CTA Visible** | 1. Look for CTA button | Button visible, correctly styled | ⬜ | |
| **Impression Tracked** | 1. Ad becomes visible<br>2. Wait 1 second | Impression API called | ⬜ | Check DevTools |
| **Click Ad** | 1. Tap on ad | Deep link opens, click tracked | ⬜ | |
| **Multiple Ads** | 1. Scroll through ads | Multiple ads shown | ⬜ | |
| **No Active Ads** | 1. Deactivate all ads<br>2. Refresh app | Placeholder or no ads shown | ⬜ | |

### 4. Analytics and Reporting

| Test Case | Steps | Expected Result | Status | Notes |
|-----------|-------|-----------------|--------|-------|
| **View Ad Stats** | 1. Navigate to ad details<br>2. Check stats | Impressions, clicks, CTR shown | ⬜ | |
| **Real-time Updates** | 1. Track impression<br>2. Refresh stats | Count incremented | ⬜ | |
| **CTR Calculation** | 1. Check CTR formula<br>2. Verify manually | Correct calculation | ⬜ | |
| **Date Range Filter** | 1. Filter by date range | Correct data shown | ⬜ | |
| **Export Analytics** | 1. Click "Export"<br>2. Download CSV | Data exported correctly | ⬜ | |

### 5. Scheduling Tests

| Test Case | Steps | Expected Result | Status | Notes |
|-----------|-------|-----------------|--------|-------|
| **Schedule Future Ad** | 1. Set start_at to tomorrow<br>2. Approve ad | Ad not served until tomorrow | ⬜ | |
| **Schedule End Date** | 1. Set end_at to yesterday<br>2. Check serving | Ad not served | ⬜ | |
| **Auto Activation** | 1. Schedule starts<br>2. Wait for job | Ad auto-activated | ⬜ | Run: `php artisan schedule:run` |
| **Auto Expiration** | 1. Schedule ends<br>2. Wait for job | Ad auto-deactivated | ⬜ | |
| **Impression Limit** | 1. Set max_impressions<br>2. Reach limit | Ad auto-deactivated | ⬜ | |
| **Click Limit** | 1. Set max_clicks<br>2. Reach limit | Ad auto-deactivated | ⬜ | |

### 6. Edge Cases

| Test Case | Steps | Expected Result | Status | Notes |
|-----------|-------|-----------------|--------|-------|
| **Concurrent Edits** | 1. Two users edit same ad<br>2. Both save | Last save wins or conflict detected | ⬜ | |
| **Network Offline** | 1. Disable network<br>2. Try operations | Proper error messages | ⬜ | |
| **Large Image** | 1. Upload 10MB image | Rejected with message | ⬜ | |
| **Special Characters** | 1. Use emoji in title<br>2. Use Arabic text | Properly displayed | ⬜ | |
| **Very Long Title** | 1. Enter 500 char title | Truncated or validated | ⬜ | |

---

## Test Data

### Sample Test Ads

```php
// Draft Ad
[
    'title' => 'Amazing Umrah Package',
    'description' => '5-star hotels, direct flights, visa included',
    'cta_text' => 'Book Now',
    'cta_action' => 'https://example.com/packages/123',
    'status' => 'draft',
]

// Approved Active Ad
[
    'title' => 'Last Minute Deal',
    'description' => 'Save 30% on Ramadan packages',
    'status' => 'approved',
    'is_active' => true,
    'placement' => 'home_top',
    'device_type' => 'all',
]

// Scheduled Ad
[
    'title' => 'Hajj 2025 Booking Open',
    'status' => 'approved',
    'is_active' => false,
    'start_at' => '2025-01-01 00:00:00',
    'end_at' => '2025-03-31 23:59:59',
]
```

### Test Images

Prepare test images:
- Valid image: 1200x800 JPEG (< 5MB)
- Too small: 600x400 JPEG
- Too large: 5000x3000 JPEG
- Wrong format: test.pdf
- Malicious: test.php.jpg

---

## Recovery Procedures

### 1. Test Database Corruption

**Problem:** Test database becomes corrupted

**Solution:**
```bash
# Reset test database
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

### 2. Cache Issues

**Problem:** Stale cache causing test failures

**Solution:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### 3. Failed Migrations

**Problem:** Migration fails mid-way

**Solution:**
```bash
# Rollback all migrations
php artisan migrate:reset --env=testing

# Re-run migrations
php artisan migrate --env=testing
```

### 4. Stuck Jobs

**Problem:** Queue jobs stuck in processing

**Solution:**
```bash
# Clear failed jobs
php artisan queue:flush

# Restart queue worker
php artisan queue:restart
```

### 5. Storage Issues

**Problem:** Test images filling up storage

**Solution:**
```bash
# Clean test storage
rm -rf storage/app/public/ads/test_*

# Regenerate storage link
php artisan storage:link
```

---

## Continuous Integration

### GitHub Actions Workflow

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
          extensions: mbstring, pdo_sqlite, gd
          
      - name: Install Dependencies
        run: composer install
        
      - name: Run Tests
        run: php artisan test --coverage --min=80
        
      - name: Upload Coverage
        uses: codecov/codecov-action@v2
```

---

## Test Execution Schedule

### Daily Tests
- All unit tests
- Critical integration tests
- Security scans

### Weekly Tests  
- Full integration test suite
- Performance tests
- Load tests

### Before Release
- Complete end-to-end tests
- Manual testing checklist
- Security audit
- Performance baseline

---

## Reporting

### Test Results Template

```markdown
## Test Execution Report

**Date:** 2025-01-08
**Environment:** Staging
**Tester:** [Name]

### Summary
- Total Tests: 150
- Passed: 145
- Failed: 3
- Skipped: 2
- Coverage: 87%

### Failed Tests
1. **test_ad_serving_with_expired_ads**
   - Expected: 0 ads
   - Actual: 1 ad
   - Root Cause: Cache not cleared
   - Fix: Added cache clear in test

### Performance Metrics
- Ad Serving: 180ms avg
- Impression Tracking: 45ms avg
- Click Tracking: 50ms avg

### Issues Found
- [BUG-123] Image upload fails for WebP format
- [BUG-124] CTR calculation incorrect when impressions = 0

### Recommendations
- Increase cache TTL to 5 minutes
- Add retry logic for failed tracking
```

---

## Contact

For questions about testing:
- **QA Lead:** qa@seferet.com
- **Dev Lead:** dev@seferet.com
- **Documentation:** https://docs.seferet.com/qa

---

**Last Updated:** 2025-01-08
**Version:** 1.0
