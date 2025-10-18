# Service Request System Setup (Without NPM)

## Overview

This guide explains how to set up the Service Request Management System without using NPM or build processes. All JavaScript files are included directly in the browser.

## Files Structure

```
public/js/
├── service-request-helpers.js      # Core helper functions
├── service-request-manager.js      # Service request management UI
├── service-request-notifications.js # Real-time notifications
└── echo-init.js                    # Laravel Echo initialization
```

## Setup Instructions

### 1. Environment Configuration

Update your `.env` file with broadcasting settings:

```env
# Broadcasting Configuration
BROADCAST_DRIVER=pusher

# Pusher Settings (get from pusher.com)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=api-your_cluster.pusherrapp.com
PUSHER_PORT=80
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=your_cluster
```

### 2. Laravel Broadcasting Configuration

Update `config/broadcasting.php`:

```php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'host' => env('PUSHER_HOST'),
            'port' => env('PUSHER_PORT', 80),
            'scheme' => env('PUSHER_SCHEME', 'https'),
            'encrypted' => true,
            'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
        ],
    ],
],
```

### 3. Database Setup

Run the migrations:

```bash
php artisan migrate
```

### 4. Queue Configuration

Set up queue processing:

```bash
# For development
php artisan queue:work

# For production (add to crontab)
* * * * * cd /path/to/your/project && php artisan queue:work --stop-when-empty
```

### 5. Scheduled Tasks

Add to your crontab for production:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually for development:

```bash
php artisan schedule:work
```

### 6. Test the Setup

1. **Check Database**: Ensure migrations ran successfully
2. **Check Queue**: Run `php artisan queue:work` in terminal
3. **Check Broadcasting**: Test Pusher connection in browser console
4. **Check Assets**: Verify all JS files load without errors

## JavaScript Dependencies

The system uses these external libraries via CDN:

- **Pusher JS**: `https://js.pusher.com/8.2.0/pusher.min.js`
- **Laravel Echo**: `https://unpkg.com/laravel-echo@1.15.0/dist/echo.iife.js`

### Required Dependencies Already in AdminLTE

- jQuery
- Bootstrap 4
- SweetAlert2 (optional, for better modals)
- Toastr (optional, for notifications)

## Usage

### For Agents (Travel Agents)

1. Navigate to package creation/editing page
2. Select providers in provider selection step
3. Click "Request Service" for any provider
4. Fill out the service request form
5. Watch for real-time status updates

### For Providers (Hotels, Transport, etc.)

1. Navigate to provider dashboard
2. View pending service requests
3. Click "Approve" or "Reject" for any request
4. Fill out approval/rejection details
5. Submit response

### Real-time Features

- Instant notifications when requests are created/updated
- Automatic UI updates without page refresh
- Email notifications for important events
- Sound notifications (if enabled in browser)

## Troubleshooting

### JavaScript Errors

1. **Check Console**: Open browser developer tools → Console tab
2. **Check Network**: Ensure all JS files are loading (Network tab)
3. **Check Echo**: Look for Echo initialization messages in console

### Broadcasting Issues

1. **Check Pusher Config**: Verify `.env` values are correct
2. **Check Network**: Ensure WebSocket connection is established
3. **Check Channels**: Verify user can access private channels

### API Issues

1. **Check Authentication**: Ensure user is logged in with Sanctum
2. **Check Permissions**: Verify user has correct role and permissions
3. **Check Routes**: Ensure API routes are registered

## Common Error Messages

### "Laravel Echo not available"
- Check that Pusher and Echo scripts are loaded
- Verify Pusher configuration in `.env`
- Check browser console for JavaScript errors

### "Failed to fetch service requests"
- Check API authentication
- Verify database connection
- Check Laravel logs for errors

### "WebSocket connection failed"
- Verify Pusher configuration
- Check firewall/network settings
- Test Pusher connection on pusher.com

## Testing

### Manual Testing

1. **Create Service Request**:
   - Login as agent
   - Create service request
   - Verify database record created
   - Check provider receives notification

2. **Process Service Request**:
   - Login as provider
   - Approve/reject request
   - Verify agent receives notification
   - Check database status updated

3. **Real-time Updates**:
   - Open two browser windows
   - Login as agent in one, provider in other
   - Create/process requests
   - Verify real-time updates

### API Testing

Use tools like Postman or curl to test API endpoints:

```bash
# Get service requests (as agent)
curl -H "Authorization: Bearer {token}" \
     -H "Accept: application/json" \
     http://your-domain/api/v1/service-requests/agent

# Create service request
curl -X POST \
     -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"package_id":1,"provider_id":2,"provider_type":"hotel","requested_quantity":2}' \
     http://your-domain/api/v1/service-requests
```

## Production Deployment

### Performance Optimization

1. **Enable OPcache**: For PHP performance
2. **Use Redis**: For queues and caching
3. **Optimize Database**: Add proper indexes
4. **Monitor Logs**: Set up log monitoring

### Security

1. **HTTPS Only**: Force HTTPS for all requests
2. **Rate Limiting**: Enable API rate limiting
3. **CORS**: Configure proper CORS settings
4. **Firewall**: Secure WebSocket connections

### Monitoring

1. **Queue Health**: Monitor queue worker status
2. **Database Performance**: Monitor slow queries
3. **Broadcasting Health**: Monitor WebSocket connections
4. **Error Logging**: Set up error alerting

## Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify all configuration values in `.env`
4. Test individual components separately

The system is designed to work gracefully even if some features fail (e.g., if WebSocket fails, the system still works with manual refresh).

## File Permissions

Ensure proper file permissions:

```bash
# Storage and cache directories
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Make sure web server can read public assets
chmod -R 755 public/js/
```

This completes the no-NPM setup for the Service Request Management System!