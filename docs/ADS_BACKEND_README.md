# Ads Backend System - Implementation Documentation

## Overview

A comprehensive advertisement management system for the SeferEt platform, supporting polymorphic relationships, approval workflows, image processing, and audit logging.

## Features

- **Polymorphic Relationships**: Ads can be owned by any user type and promote any product (hotels, packages, flights, offers, vehicles)
- **Approval Workflow**: Draft → Pending → Approved/Rejected states with admin oversight
- **Image Management**: Multi-variant responsive image generation with aspect ratio enforcement
- **Audit Logging**: Complete audit trail of all ad lifecycle events
- **Scheduling**: Start/end dates for ad campaigns
- **Priority System**: Configurable priority for ad display ordering
- **CTA Configuration**: Customizable call-to-action buttons with position and styling
- **Analytics Integration**: Support for UTM parameters and tracking IDs

## Database Structure

### Tables

#### `ads`
Main table for advertisements with the following key fields:

- **Polymorphic Relationships**:
  - `owner_id`, `owner_type`: Who created the ad (User)
  - `product_id`, `product_type`: What the ad promotes (Hotel, Package, Flight, etc.)

- **Content**:
  - `title`, `description`: Ad content
  - `image_path`: Original image path
  - `image_variants` (JSON): Responsive image variants

- **Call-to-Action**:
  - `cta_text`: Button text
  - `cta_action`: Action URL or deep link
  - `cta_position`: Normalized position (0-1)
  - `cta_style`: Button style (primary, secondary, etc.)

- **Workflow**:
  - `status`: draft, pending, approved, rejected
  - `approved_by`, `approved_at`: Approval information
  - `rejection_reason`: Admin rejection reason

- **Scheduling**:
  - `start_at`, `end_at`: Campaign date range
  - `priority`: Display priority (0-100)
  - `is_active`: Manual toggle

- **Analytics**:
  - `analytics_meta` (JSON): UTM parameters, tracking IDs

#### `ad_audit_logs`
Audit trail for all ad events:

- `ad_id`: Foreign key to ads table
- `event_type`: created, submitted, approved, rejected, updated, deleted
- `user_id`: User who performed the action
- `changes` (JSON): Before/after changes
- `metadata` (JSON): Additional context (IP, user agent)

## Models

### Ad (`App\Models\Ad`)

**Key Methods**:
- `submitForApproval()`: Submit draft ad for approval
- `approve(User $approver)`: Approve pending ad
- `reject(User $approver, string $reason)`: Reject pending ad
- `withdraw()`: Withdraw pending ad back to draft
- `activate()` / `deactivate()`: Toggle active status
- `logAudit()`: Log audit event

**Scopes**:
- `draft()`, `pending()`, `approved()`, `rejected()`: Filter by status
- `active()`: Filter active ads (approved + active toggle + date range)
- `byOwner()`, `byProduct()`, `ofProductType()`: Filter by relationships
- `expired()`: Filter expired ads
- `byPriority()`: Order by priority

**Status Checks**:
- `isDraft()`, `isPending()`, `isApproved()`, `isRejected()`
- `isCurrentlyActive()`: Check if ad is currently displayable
- `isExpired()`: Check if ad campaign has ended

### AdAuditLog (`App\Models\AdAuditLog`)

Simple model for tracking ad lifecycle events with relationships to Ad and User.

## Services

### AdImageService (`App\Services\AdImageService`)

Handles image validation, upload, and variant generation using Intervention Image library.

**Key Features**:
- Dimension validation (800x400 minimum, 4000x4000 maximum)
- Aspect ratio enforcement (4:3, 16:9, or banner formats)
- File size validation (10MB maximum)
- Format validation (JPEG, PNG, WebP)
- Responsive variant generation (thumbnail, small, medium, large)
- Image optimization and compression
- Storage path organization by owner

**Methods**:
- `validateImage(UploadedFile $file)`: Validate uploaded image
- `uploadImage(UploadedFile $file, int $ownerId)`: Upload and process image
- `deleteImage(string $path, array $variants)`: Delete image and variants
- `cropImage(string $path, array $cropData)`: Server-side image cropping

## API Endpoints

### Public Endpoints
```
GET /api/v1/ads/active
```
Get active ads for public display (no authentication required)

### Authenticated Endpoints (B2B Users)

**Ad Management**:
```
GET    /api/v1/ads              - List user's ads
POST   /api/v1/ads              - Create new ad
GET    /api/v1/ads/{ad}         - Get ad details
PUT    /api/v1/ads/{ad}         - Update ad (draft/rejected only)
DELETE /api/v1/ads/{ad}         - Delete ad (draft/rejected only)
```

**Image Management**:
```
POST   /api/v1/ads/{ad}/upload-image  - Upload ad image
```

**Workflow Actions**:
```
POST   /api/v1/ads/{ad}/submit         - Submit for approval
POST   /api/v1/ads/{ad}/withdraw       - Withdraw from approval
POST   /api/v1/ads/{ad}/toggle-active  - Toggle active status
```

**Audit**:
```
GET    /api/v1/ads/{ad}/audit-logs    - Get audit history
```

### Admin Endpoints
```
POST   /api/v1/ads/{ad}/approve  - Approve pending ad
POST   /api/v1/ads/{ad}/reject   - Reject pending ad (requires reason)
```

## Authorization

### AdPolicy (`App\Policies\AdPolicy`)

**Permissions**:
- `viewAny`: Admin or B2B users can view ads
- `view`: Admin or owner can view specific ad
- `create`: Active B2B users can create ads
- `update`: Admin or owner can update draft/rejected ads
- `delete`: Admin or owner can delete draft/rejected ads
- `submit`: Owner can submit draft ads
- `withdraw`: Owner can withdraw pending ads
- `approve/reject`: Admin only for pending ads
- `toggleActive`: Admin or owner can toggle approved ads
- `uploadImage`: Owner can upload images for draft/rejected ads
- `viewAuditLogs`: Admin or owner can view audit logs

## Validation

### CreateAdRequest
- `title`: Required, 5-255 characters
- `description`: Optional, max 1000 characters
- `product_id`, `product_type`: Optional polymorphic product reference
- `cta_text`: Optional, 2-100 characters
- `cta_action`: Optional, max 500 characters
- `cta_position`: Optional, 0-1 (normalized)
- `cta_style`: Optional, predefined styles
- `start_at`, `end_at`: Optional scheduling
- `priority`: Optional, 0-100
- `analytics_meta`: Optional JSON for tracking

### UpdateAdRequest
Similar to CreateAdRequest but with `sometimes` rules for partial updates.

### UploadAdImageRequest
- `image`: Required image file
- MIME types: jpeg, png, jpg, webp
- Max size: 10MB
- Dimensions: 800x400 to 4000x4000 pixels

## Workflow

```
Draft → (submit) → Pending → (approve/reject) → Approved/Rejected
         ↑                        ↓
         └──────(withdraw)────────┘
```

**States**:
1. **Draft**: Initial state, can be edited
2. **Pending**: Submitted for admin review
3. **Approved**: Approved by admin, can be activated
4. **Rejected**: Rejected by admin with reason, can be edited

**Requirements**:
- Must have an image to submit for approval
- Only draft ads can be submitted
- Only pending ads can be approved/rejected/withdrawn
- Only approved ads can be toggled active/inactive

## Image Variants

The system generates 4 responsive variants:

1. **thumbnail**: 300x200px - For list views
2. **small**: 600x400px - For mobile displays
3. **medium**: 1200x800px - For tablet displays
4. **large**: 1920x1280px - For desktop displays

All variants maintain the original aspect ratio and prevent upscaling.

## Storage Structure

```
storage/app/public/
└── ads/
    └── {owner_id}/
        ├── original/
        │   └── {hash}.{ext}
        ├── cropped/
        │   └── {hash}_cropped_{timestamp}.{ext}
        └── variants/
            ├── {hash}_thumbnail.{ext}
            ├── {hash}_small.{ext}
            ├── {hash}_medium.{ext}
            └── {hash}_large.{ext}
```

## Running Migrations

```bash
php artisan migrate
```

This will create:
- `ads` table
- `ad_audit_logs` table

## Running Tests

```bash
# Run Ad model tests
php artisan test --filter=AdTest

# Run all tests
php artisan test
```

## Usage Examples

### Creating an Ad (B2B User)

```php
POST /api/v1/ads
{
    "title": "Luxury Hotel in Mecca",
    "description": "5-star accommodation near Haram",
    "product_id": 123,
    "product_type": "hotel",
    "cta_text": "Book Now",
    "cta_action": "/hotels/123",
    "cta_position": 0.8,
    "cta_style": "primary",
    "priority": 10,
    "analytics_meta": {
        "utm_source": "app",
        "utm_campaign": "ramadan_2024"
    }
}
```

### Uploading Image

```php
POST /api/v1/ads/{ad}/upload-image
Content-Type: multipart/form-data

image: [file]
```

### Submitting for Approval

```php
POST /api/v1/ads/{ad}/submit
```

### Approving Ad (Admin)

```php
POST /api/v1/ads/{ad}/approve
{
    "priority": 50  // Optional: Set priority
}
```

### Rejecting Ad (Admin)

```php
POST /api/v1/ads/{ad}/reject
{
    "reason": "Image quality does not meet standards"
}
```

## Future Enhancements

1. **Analytics Dashboard**: Track ad impressions, clicks, and conversions
2. **A/B Testing**: Support multiple ad variants for testing
3. **Targeting**: Audience targeting based on user preferences
4. **Budget & Billing**: Payment integration for paid ad placements
5. **Performance Metrics**: CTR, conversion rate tracking
6. **Automated Scheduling**: Bulk upload and scheduling
7. **Template System**: Pre-designed ad templates
8. **Video Ads**: Support for video content

## Best Practices

1. **Image Optimization**: Always provide high-quality images that meet dimension requirements
2. **Clear CTAs**: Use action-oriented, concise CTA text
3. **Scheduling**: Set appropriate start/end dates for campaigns
4. **Analytics**: Always include UTM parameters for tracking
5. **Priority**: Use priority system judiciously to ensure fair ad distribution
6. **Audit Trail**: Review audit logs for compliance and debugging
7. **Testing**: Test ads thoroughly before submission

## Troubleshooting

### Image Upload Fails
- Check file size (max 10MB)
- Verify dimensions (800x400 to 4000x4000)
- Ensure correct MIME type (jpeg, png, webp)
- Check aspect ratio (must be 4:3, 16:9, or banner)

### Cannot Submit Ad
- Ensure ad has an image uploaded
- Verify ad is in draft status
- Check that all required fields are filled

### Ad Not Displaying
- Verify status is "approved"
- Check `is_active` is true
- Ensure current date is within `start_at` and `end_at` range
- Verify priority is set appropriately

## Security Considerations

1. **Authorization**: All actions are protected by policies
2. **Validation**: Strict input validation on all endpoints
3. **Audit Logging**: Complete audit trail for accountability
4. **Image Sanitization**: Images are processed and re-encoded
5. **XSS Prevention**: CTA text is sanitized and validated
6. **Rate Limiting**: Consider implementing rate limits on API endpoints

## Support

For issues or questions, please contact the development team or create an issue in the repository.
