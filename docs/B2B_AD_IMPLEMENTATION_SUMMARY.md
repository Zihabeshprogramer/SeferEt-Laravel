# B2B Ad Creation/Edit Workflow Implementation

## Implementation Status

### ✅ Completed Components

1. **Database Migrations** (Already existed)
   - `2025_11_08_000001_create_ads_table.php`
   - `2025_11_08_000002_create_ad_audit_logs_table.php`

2. **Models**
   - ✅ `app/Models/Ad.php` - Full ad model with polymorphic relationships, status management, ownership validation
   - ✅ `app/Models/AdAuditLog.php` - Audit logging model

3. **Services**
   - ✅ `app/Services/AdImageService.php` - Server-side image cropping, validation, variant generation

4. **Form Requests**
   - ✅ `app/Http/Requests/StoreAdRequest.php` - Create ad validation with ownership checks
   - ✅ `app/Http/Requests/UpdateAdRequest.php` - Update ad validation

### 🚧 Remaining Components

5. **Controllers**
   - ⏳ `app/Http/Controllers/B2B/AdController.php` - B2B user ad management
   - ⏳ `app/Http/Controllers/Admin/AdController.php` - Admin approval workflow

6. **Notifications**
   - ⏳ `app/Notifications/AdSubmittedNotification.php` - For admins
   - ⏳ `app/Notifications/AdStatusChangedNotification.php` - For owners

7. **Policy**
   - ⏳ `app/Policies/AdPolicy.php` - Authorization checks

8. **Routes**
   - ⏳ Add to `routes/b2b.php` - B2B ad management routes
   - ⏳ Add to `routes/admin.php` - Admin approval routes

9. **Tests**
   - ⏳ Feature tests for ownership validation and workflows

## Key Features Implemented

### Ad Model Features
- **Polymorphic Relationships**: Owner and product relationships supporting multiple entity types
- **Status Management**: Draft, Pending, Approved, Rejected states
- **Workflow Methods**:
  - `submitForApproval()` - Move from draft to pending
  - `withdraw()` - Move from pending back to draft
  - `approve()` - Admin approval
  - `reject()` - Admin rejection with reason
- **Ownership Validation**: `isOwnedBy()` method ensures cross-owner protection
- **Image Management**: Automatic cleanup on deletion
- **Audit Logging**: Automatic logging of all changes

### AdImageService Features
- **Image Validation**:
  - Dimensions: 800x600 minimum, 4000x3000 maximum
  - File size: Maximum 5MB
  - Formats: JPEG, PNG, WebP
  - Aspect ratio validation (0.5 to 3.0)
- **Server-Side Cropping**: Crop with dimensions and position
- **Variant Generation**: Automatic responsive image variants (thumbnail, small, medium, large)
- **CTA Validation**: Safe text validation and sanitization
- **Position Validation**: Normalized 0-1 position values

### Form Request Validation
- **Ownership Validation**: Verifies user owns the product before creating ad
- **Product Type Validation**: Supports packages, hotels, flights, transport services
- **Image Validation**: Comprehensive file and dimension checks
- **CTA Validation**: Text length, content safety, position constraints
- **Schedule Validation**: Start/end date logic
- **Automatic Sanitization**: CTA text sanitized on input

## API Endpoints (To Be Implemented)

### B2B User Endpoints
```
GET    /b2b/ads                    - List user's ads
GET    /b2b/ads/create             - Show create form
POST   /b2b/ads                    - Store new ad
GET    /b2b/ads/{ad}               - Show ad details
GET    /b2b/ads/{ad}/edit          - Show edit form
PUT    /b2b/ads/{ad}               - Update ad
DELETE /b2b/ads/{ad}               - Delete ad

POST   /b2b/ads/upload-image       - Upload ad image
POST   /b2b/ads/{ad}/crop-image    - Crop uploaded image
GET    /b2b/ads/{ad}/preview       - Preview ad
POST   /b2b/ads/{ad}/submit        - Submit for approval
POST   /b2b/ads/{ad}/withdraw      - Withdraw from approval
```

### Admin Endpoints
```
GET    /admin/ads                  - List all ads
GET    /admin/ads/pending          - List pending ads
GET    /admin/ads/{ad}             - Show ad details
POST   /admin/ads/{ad}/approve     - Approve ad
POST   /admin/ads/{ad}/reject      - Reject ad with reason
```

## Workflow

### For B2B Users

1. **Create Ad**
   - Select owned product (package, hotel, flight, or transport)
   - Upload image (validated dimensions/format/size)
   - Set title, description
   - Configure CTA (text, action URL, position, style)
   - Save as draft

2. **Edit Draft**
   - Update any field
   - Re-upload/crop image
   - Adjust CTA placement
   - Save changes

3. **Crop Image** (Optional)
   - Server-side cropping with position and dimensions
   - Automatic variant regeneration
   - Preview before saving

4. **Submit for Approval**
   - Move from draft to pending
   - Admin notification sent
   - Cannot edit while pending

5. **Handle Approval Response**
   - If approved: Ad becomes active (based on schedule)
   - If rejected: Returns to draft, reason provided, can edit and resubmit

6. **Withdraw** (if pending)
   - Move back to draft status
   - Can edit and resubmit

### For Admins

1. **View Pending Ads**
   - List of all pending submissions
   - Filter by product type, submitter, date

2. **Review Ad**
   - View all details
   - Check image quality
   - Verify CTA appropriateness
   - Review product ownership (automatic)

3. **Approve or Reject**
   - Approve: Ad becomes active
   - Reject: Provide rejection reason
   - Owner notified of decision

## Security Features

1. **Ownership Validation**
   - Product ownership verified at form request level
   - Additional check in policy
   - Prevents cross-owner ad creation

2. **CTA Safety**
   - XSS prevention (script tags blocked)
   - HTML stripping
   - Pattern-based disallowed content detection

3. **Image Validation**
   - File type restrictions
   - Dimension constraints
   - Size limits
   - Aspect ratio validation

4. **Authorization Policy**
   - Owner can only manage their own ads
   - Admin can approve/reject any ad
   - Status-based edit restrictions

5. **Audit Trail**
   - All changes logged
   - User tracking
   - Before/after state capture

## Database Schema

### ads table
- **Ownership**: `owner_id`, `owner_type` (polymorphic)
- **Product**: `product_id`, `product_type` (polymorphic)
- **Content**: `title`, `description`
- **Image**: `image_path`, `image_variants` (JSON)
- **CTA**: `cta_text`, `cta_action`, `cta_position`, `cta_style`
- **Status**: `status` (draft|pending|approved|rejected)
- **Approval**: `approved_by`, `approved_at`, `rejection_reason`
- **Schedule**: `start_at`, `end_at`
- **Settings**: `priority`, `is_active`
- **Analytics**: `analytics_meta` (JSON for UTM params)

### ad_audit_logs table
- `ad_id` - Foreign key to ads
- `event_type` - Type of event
- `user_id` - Who performed the action
- `changes` - JSON before/after state
- `metadata` - Additional context
- `created_at` - Timestamp

## Image Storage Structure

```
storage/app/public/ads/
├── {owner_id}/
│   ├── original/
│   │   └── {hash}.jpg
│   ├── cropped/
│   │   └── {hash}_cropped_{timestamp}.jpg
│   └── variants/
│       ├── {hash}_thumbnail.jpg    (300x200)
│       ├── {hash}_small.jpg        (600x400)
│       ├── {hash}_medium.jpg       (1200x800)
│       └── {hash}_large.jpg        (1920x1280)
```

## Notification Events

1. **AdSubmittedNotification** (to admins)
   - Triggered: When ad submitted for approval
   - Recipients: All admins
   - Content: Submitter name, ad title, product type, preview link

2. **AdStatusChangedNotification** (to owner)
   - Triggered: When ad approved or rejected
   - Recipients: Ad owner
   - Content: Status change, admin name, rejection reason (if rejected)

## Testing Requirements

### Unit Tests
- Ad model state transitions
- Ownership validation logic
- CTA validation and sanitization
- Image dimension validation

### Feature Tests
1. **Ownership Tests**
   - User can only create ads for owned products
   - User cannot create ad for other user's product
   - Admin cannot bypass ownership checks

2. **Workflow Tests**
   - Draft → Submit → Pending
   - Pending → Approve → Approved
   - Pending → Reject → Rejected
   - Pending → Withdraw → Draft
   - Rejected → Edit → Resubmit → Pending

3. **Image Tests**
   - Valid image upload
   - Invalid format rejection
   - Size limit enforcement
   - Dimension validation
   - Cropping functionality
   - Variant generation

4. **CTA Tests**
   - Valid CTA text
   - XSS prevention
   - Position constraints
   - URL validation

## Next Steps to Complete Implementation

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Create Controllers**
   - Implement B2B AdController with all CRUD operations
   - Implement Admin AdController for approval workflow

3. **Create Notifications**
   - AdSubmittedNotification for admins
   - AdStatusChangedNotification for owners

4. **Create Policy**
   - AdPolicy with authorization logic

5. **Add Routes**
   - B2B routes in routes/b2b.php
   - Admin routes (create routes/admin.php if needed)

6. **Create Views** (Optional for API-first approach)
   - B2B ad management interface
   - Admin approval interface

7. **Write Tests**
   - Feature tests for workflows
   - Unit tests for validation

8. **Update AuthServiceProvider**
   - Register AdPolicy

## Usage Example

### Creating an Ad (B2B User)

```php
POST /b2b/ads
{
    "product_id": 123,
    "product_type": "package",
    "title": "Special Umrah Package 2025",
    "description": "Limited time offer for premium Umrah experience",
    "image": <file upload>,
    "cta_text": "Book Now",
    "cta_action": "https://example.com/packages/123/book",
    "cta_position": 0.75,
    "cta_style": "primary",
    "start_at": "2025-01-01",
    "end_at": "2025-03-31"
}
```

### Approving an Ad (Admin)

```php
POST /admin/ads/456/approve
{
    "notes": "Approved - meets all guidelines"
}
```

### Rejecting an Ad (Admin)

```php
POST /admin/ads/456/reject
{
    "rejection_reason": "Image quality does not meet minimum standards. Please upload a higher resolution image."
}
```

## Configuration

### Image Settings (AdImageService)
- MIN_WIDTH: 800px
- MIN_HEIGHT: 600px
- MAX_WIDTH: 4000px
- MAX_HEIGHT: 3000px
- MAX_FILE_SIZE: 5MB
- ALLOWED_TYPES: JPEG, PNG, WebP
- ASPECT_RATIO: 0.5 to 3.0

### Variants Generated
- thumbnail: 300x200
- small: 600x400
- medium: 1200x800
- large: 1920x1280

All variants maintain aspect ratio and never upscale.

## Monitoring and Analytics

The `analytics_meta` JSON field supports:
- utm_source
- utm_medium
- utm_campaign
- Custom tracking parameters

This enables tracking ad performance across different channels and campaigns.
