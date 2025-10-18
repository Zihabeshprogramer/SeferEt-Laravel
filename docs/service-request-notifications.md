# Service Request Notification System

This document describes the complete service request notification system implementation, including real-time broadcasting, email notifications, and frontend integration.

## Overview

The service request notification system provides:

- **Real-time notifications** via WebSocket broadcasting
- **Email notifications** for important status changes
- **Database notifications** for persistent message storage
- **Frontend integration** with automatic UI updates
- **Scheduled tasks** for handling expired requests

## Architecture

### Backend Components

#### 1. Event Classes (`app/Events/`)

- `ServiceRequestCreated.php` - Broadcasted when a service request is created
- `ServiceRequestApproved.php` - Broadcasted when a request is approved
- `ServiceRequestRejected.php` - Broadcasted when a request is rejected
- `ServiceRequestExpired.php` - Broadcasted when a request expires

#### 2. Notification Classes (`app/Notifications/`)

- `ServiceRequestCreatedNotification.php` - Sent to providers when new requests are created
- `ServiceRequestApprovedNotification.php` - Sent to agents when requests are approved
- `ServiceRequestRejectedNotification.php` - Sent to agents when requests are rejected
- `ServiceRequestExpiredNotification.php` - Sent to both parties when requests expire

#### 3. Controller (`app/Http/Controllers/Api/ServiceRequestController.php`)

Enhanced with real-time event broadcasting and notification dispatching:
- Creates and dispatches events after database operations
- Sends appropriate notifications to users
- Handles all CRUD operations for service requests

#### 4. Console Command (`app/Console/Commands/ExpireServiceRequests.php`)

Scheduled command that:
- Finds expired service requests
- Updates their status to 'expired'
- Sends notifications to affected users
- Broadcasts real-time events

### Frontend Components

#### 1. Real-time Notification Handler (`public/js/service-request-notifications.js`)

JavaScript class that:
- Connects to WebSocket channels via Laravel Echo
- Handles incoming real-time events
- Updates UI elements automatically
- Shows notifications to users
- Dispatches custom events for other components

#### 2. Layout Integration (`resources/views/layouts/adminlte.blade.php`)

Includes:
- Meta tags for user and package information
- Laravel Echo and Pusher.js libraries
- Service request notification script

## Setup Instructions

### 1. Broadcasting Configuration

Ensure your `.env` file includes broadcasting configuration:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=your_host
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=your_cluster
```

### 2. Queue Configuration

Configure queues for handling notifications:

```env
QUEUE_CONNECTION=database
```

Run the queue worker:
```bash
php artisan queue:work
```

### 3. Scheduled Tasks

Add to your crontab (for production):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run the scheduler manually (for development):
```bash
php artisan schedule:work
```

### 4. Asset Compilation

Ensure Laravel Mix is configured to compile the Echo integration:

```javascript
// webpack.mix.js
mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/bootstrap.js', 'public/js');
```

Run:
```bash
npm run dev
```

## Usage

### Creating Service Requests

```javascript
// Frontend JavaScript
fetch('/api/v1/service-requests', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        package_id: 1,
        provider_id: 2,
        provider_type: 'hotel',
        // ... other fields
    })
})
.then(response => response.json())
.then(data => {
    console.log('Service request created:', data);
});
```

### Handling Real-time Events

The notification system automatically handles real-time events, but you can also listen for custom events:

```javascript
// Listen for service request events
document.addEventListener('serviceRequestCreated', function(event) {
    console.log('New service request:', event.detail);
    // Update your UI
});

document.addEventListener('serviceRequestApproved', function(event) {
    console.log('Service request approved:', event.detail);
    // Update your UI
});
```

### Broadcasting Channels

The system uses the following private channels:

- `user.{userId}` - User-specific notifications
- `package.{packageId}` - Package-specific notifications
- `service-requests` - Admin-level notifications

### Notification Types

#### Email Notifications

All notifications include:
- Subject line
- Email content (HTML and text)
- Action URLs
- Relevant data

#### Real-time Notifications

All events include:
- Service request data
- Related user/package information
- Notification message and type
- UI update instructions

## API Endpoints

### Service Request Management

```
POST   /api/v1/service-requests           - Create new request
GET    /api/v1/service-requests/agent     - Get agent's requests
GET    /api/v1/service-requests/provider  - Get provider's requests
GET    /api/v1/service-requests/{id}      - Get specific request
PUT    /api/v1/service-requests/{id}/approve - Approve request
PUT    /api/v1/service-requests/{id}/reject  - Reject request
PUT    /api/v1/service-requests/{id}/cancel  - Cancel request
```

### Package Approval Status

```
GET    /api/v1/packages/{id}/approval-status - Get package approval status
```

## Customization

### Adding New Notification Types

1. Create a new notification class:
```bash
php artisan make:notification YourCustomNotification
```

2. Create a new event class:
```bash
php artisan make:event YourCustomEvent
```

3. Dispatch the event and notification in your controller:
```php
// Send notification
$user->notify(new YourCustomNotification($data));

// Broadcast event
broadcast(new YourCustomEvent($data));
```

4. Update the frontend handler to listen for the new event:
```javascript
// In service-request-notifications.js
.listen('.your-custom-event', this.handleYourCustomEvent)
```

### Customizing Notification Templates

Email templates are located in:
- `resources/views/emails/service-requests/`

You can customize the HTML and text versions of each notification type.

### Customizing Frontend Behavior

The `ServiceRequestNotificationHandler` class can be extended or modified to:
- Change notification display methods
- Add custom UI update logic
- Integrate with different notification libraries

## Troubleshooting

### Common Issues

1. **Events not broadcasting**
   - Check broadcasting configuration
   - Ensure queue workers are running
   - Verify Pusher/WebSocket connection

2. **Notifications not being sent**
   - Check queue configuration
   - Verify email settings
   - Check notification channel settings

3. **Frontend not receiving events**
   - Ensure Laravel Echo is properly configured
   - Check browser console for WebSocket errors
   - Verify channel authentication

### Debugging

Enable debugging in your `.env`:
```env
LOG_LEVEL=debug
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Monitor queue jobs:
```bash
php artisan queue:work --verbose
```

## Performance Considerations

1. **Queue Processing**: Use Redis or database queues for better performance
2. **Broadcasting**: Consider using Redis for broadcasting instead of Pusher for high-volume applications
3. **Notification Throttling**: Implement rate limiting for notifications
4. **Database Cleanup**: Regularly clean up old database notifications

## Security

1. **Channel Authorization**: Private channels are properly authorized
2. **CSRF Protection**: All API endpoints use CSRF protection
3. **User Permissions**: Notifications are only sent to authorized users
4. **Data Sanitization**: All user input is validated and sanitized

## Testing

Run the notification system tests:
```bash
php artisan test --filter ServiceRequest
```

Test real-time events:
```bash
php artisan tinker
>>> broadcast(new App\Events\ServiceRequestCreated($serviceRequest));
```

## Future Enhancements

Potential improvements:
- Push notifications for mobile devices
- SMS notifications for critical updates
- Notification preferences per user
- Bulk operations with batch notifications
- Advanced analytics and reporting