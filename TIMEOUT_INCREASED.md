# Timeout Increased to 2 Minutes

## ⏱️ All Timeouts Updated

### Current Configuration (Temporary for Debugging)

```
┌─────────────────────────────┬──────────┐
│ Component                   │ Timeout  │
├─────────────────────────────┼──────────┤
│ Dashboard HTTP Call         │ 120s     │
│ API Controller Script       │ 120s     │
│ Amadeus Hotel Search        │ 90s      │
│ Amadeus Hotel Details       │ 60s      │
│ Amadeus Location Lookup     │ 30s      │
└─────────────────────────────┴──────────┘
```

## 📝 Files Modified

### 1. `app/Http/Controllers/Customer/DashboardController.php`
```php
Http::timeout(120) // 2 minute timeout
```

### 2. `app/Http/Controllers/Api/HotelApiController.php`
```php
set_time_limit(120); // 2 minutes for Amadeus
```

### 3. `app/Services/AmadeusHotelService.php`
- Hotel search: 90 seconds
- Hotel details: 60 seconds
- Location lookup: 30 seconds

## 🧪 Test Now

Try searching for hotels again:

```bash
# Via web form
http://localhost:8000/
# Select: Makkah (Mecca)
# Fill dates and click "Search Hotels"
```

OR direct API test:

```bash
curl -X GET "http://localhost:8000/api/hotels/search?location=MKK&check_in=2025-12-01&check_out=2025-12-05&guests=2&rooms=1"
```

## 🔍 If Still Timing Out

### Check PHP Configuration

1. **Check current PHP timeout:**
```bash
php -i | grep max_execution_time
```

2. **If timeout is less than 120s, increase it:**

**Option A: Edit php.ini**
```ini
max_execution_time = 120
```

**Option B: Set in .env**
```env
PHP_MAX_EXECUTION_TIME=120
```

3. **Restart server:**
```bash
php artisan serve
```

### Check Amadeus API Credentials

If you're still getting timeouts, the issue might be:

1. **Invalid API credentials** - Amadeus API hanging on auth
2. **Network issues** - Connection to Amadeus is slow/blocked
3. **Test environment down** - Amadeus test API might be slow

**Quick Test:**
```bash
php artisan tinker
```

```php
// Test Amadeus connection
$service = app(\App\Services\AmadeusHotelService::class);
$start = microtime(true);
try {
    $result = $service->searchHotels('MKK', '2025-12-01', '2025-12-05', 2, 1);
    $duration = microtime(true) - $start;
    echo "Duration: {$duration}s\n";
    echo "Results: " . count($result['data'] ?? []) . " hotels\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Temporarily Skip Amadeus

If Amadeus is the problem, you can skip it temporarily:

```bash
# Add skip_amadeus=true parameter
curl "http://localhost:8000/api/hotels/search?location=MKK&check_in=2025-12-01&check_out=2025-12-05&skip_amadeus=true"
```

Or update the controller to always skip Amadeus:
```php
$skipAmadeus = true; // Force skip Amadeus
```

## 📊 What to Look For

### If it works now:
✅ Amadeus API is just slow - consider:
- Caching results
- Background jobs
- Async loading
- Showing local results first

### If it still times out:
❌ Check:
1. Amadeus API credentials are valid
2. Network can reach Amadeus servers
3. PHP timeout is actually increased
4. Check error logs: `tail -f storage/logs/laravel.log`

## 🔧 Recommended Next Steps

Once you identify the issue:

### If Amadeus is Just Slow
1. Implement aggressive caching
2. Show local hotels immediately
3. Load Amadeus results via AJAX
4. Add loading spinner

### If Amadeus Credentials Issue
1. Verify API key and secret in `.env`
2. Check Amadeus dashboard for API status
3. Regenerate credentials if needed

### If Network Issue
1. Check firewall settings
2. Try from different network
3. Check if Amadeus test API is up

## 📝 Logging

Check what's happening:

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Filter for Amadeus
tail -f storage/logs/laravel.log | grep Amadeus

# Filter for errors
tail -f storage/logs/laravel.log | grep -i error
```

## ✅ Expected Output

With 2-minute timeout, you should see:
1. Search starts
2. Local hotels found (if any)
3. Amadeus search begins
4. Either:
   - Results returned (success!)
   - Timeout after 90-120 seconds (still an issue)
   - Error with details in logs

## 🎯 Summary

All timeouts increased to 2 minutes to allow Amadeus API more time to respond. This is temporary for debugging.

**If it works:** Great! Amadeus is just slow, we can optimize.
**If it doesn't:** Likely a configuration or credential issue to fix.

Try the search now and let me know what happens!
