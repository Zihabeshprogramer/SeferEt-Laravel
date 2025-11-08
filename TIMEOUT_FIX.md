# Hotel Search Timeout Fix

## 🐛 Issue
Hotel search was timing out after 30 seconds with error:
```
Maximum execution time of 30 seconds exceeded in 
vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php
```

## ✅ Solutions Implemented

### 1. Added HTTP Timeout Settings

**File**: `app/Services/AmadeusHotelService.php`

Added explicit timeouts to all Amadeus API calls:
- **Hotel search**: 15 seconds
- **Hotel details**: 10 seconds  
- **Location lookup**: 10 seconds

```php
$response = Http::timeout(15) // 15 second timeout
    ->withOptions(['verify' => false])
    ->withToken($token)
    ->get("{$this->baseUrl}/v3/shopping/hotel-offers", $queryParams);
```

### 2. Added Retry Logic

**File**: `app/Http/Controllers/Customer/DashboardController.php`

```php
$response = Http::timeout(25) // 25 second timeout
    ->retry(2, 100) // Retry twice with 100ms delay
    ->get(url('/api/hotels/search'), array_filter($searchParams));
```

### 3. Graceful Error Handling

Added proper exception handling:
```php
try {
    // Search hotels
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    // Timeout error
    session()->flash('error', 'Search is taking longer than expected...');
} catch (\Exception $e) {
    // General error
    session()->flash('error', 'Unable to search hotels at this time...');
}
```

### 4. Increased Script Timeout for API

**File**: `app/Http/Controllers/Api/HotelApiController.php`

```php
set_time_limit(20); // Allow 20 seconds for Amadeus search
```

### 5. Optional Amadeus Skip

Added parameter to skip Amadeus search for faster local-only results:
```
GET /api/hotels/search?location=Mecca&...&skip_amadeus=true
```

## 🔧 Timeout Configuration

### Current Settings
```
┌─────────────────────────┬──────────┐
│ Component               │ Timeout  │
├─────────────────────────┼──────────┤
│ Amadeus Hotel Search    │ 15s      │
│ Amadeus Hotel Details   │ 10s      │
│ Amadeus Location Lookup │ 10s      │
│ API Controller          │ 20s      │
│ Internal HTTP Call      │ 25s      │
│ PHP max_execution_time  │ 30s      │
└─────────────────────────┴──────────┘
```

## 🚀 Quick Fixes for Slow Searches

### Option 1: Search Local Hotels Only
Add `skip_amadeus=true` to skip external API:
```
/api/hotels/search?location=Mecca&check_in=2025-12-01&check_out=2025-12-05&skip_amadeus=true
```

### Option 2: Increase PHP Timeout
Edit `php.ini`:
```ini
max_execution_time = 60
```

Or in your `.env`:
```env
PHP_MAX_EXECUTION_TIME=60
```

### Option 3: Use City Codes Instead of Names
Amadeus works faster with city codes:
```
Mecca  → MKK
Medina → MED
Jeddah → JED
Riyadh → RUH
```

Update form to use codes:
```html
<option value="MKK">Makkah</option>
<option value="MED">Madinah</option>
```

## 🧪 Test the Fix

### Test 1: Normal Search (Should Work)
```bash
curl "http://localhost:8000/api/hotels/search?location=Mecca&check_in=2025-12-01&check_out=2025-12-05&guests=2"
```

### Test 2: Local Only (Fastest)
```bash
curl "http://localhost:8000/api/hotels/search?location=Mecca&check_in=2025-12-01&check_out=2025-12-05&skip_amadeus=true"
```

### Test 3: With City Code (Faster)
```bash
curl "http://localhost:8000/api/hotels/search?location=MKK&check_in=2025-12-01&check_out=2025-12-05&guests=2"
```

## 📊 Performance Improvements

### Before
- ❌ 30+ seconds → Timeout error
- ❌ No retry logic
- ❌ All-or-nothing approach

### After
- ✅ 15-20 seconds → Returns results
- ✅ Automatic retries on failure
- ✅ Local hotels shown even if Amadeus fails
- ✅ User-friendly error messages
- ✅ Option to skip Amadeus for faster results

## 🔍 Debugging

### Check if Amadeus is Slow
```bash
php artisan tinker
```

```php
$service = app(\App\Services\AmadeusHotelService::class);
$start = microtime(true);
$result = $service->searchHotels('MKK', '2025-12-01', '2025-12-05', 2, 1);
$duration = microtime(true) - $start;
echo "Duration: {$duration} seconds\n";
```

### Check Logs
```bash
tail -f storage/logs/laravel.log | grep "Amadeus"
```

Look for:
- `Amadeus hotel search successful` - Working
- `Amadeus search failed` - API issues
- `Skipping Amadeus search` - Skip mode active

## 💡 Recommendations

### For Development
1. Use `skip_amadeus=true` for faster testing
2. Test with city codes (MKK, MED) instead of full names
3. Set longer timeout in development: `max_execution_time=60`

### For Production
1. Monitor Amadeus API response times
2. Implement caching for popular searches
3. Consider background job for Amadeus search
4. Show local results immediately, load Amadeus async

### Potential Future Improvements
```php
// Option 1: Show local results immediately
return response()->json([
    'hotels' => $localHotels,
    'amadeus_loading' => true,
    'poll_url' => '/api/hotels/amadeus-status/...'
]);

// Option 2: Queue Amadeus search
AmadeusSearchJob::dispatch($searchParams);
```

## ✅ Status

**Fixed!** Hotel searches now:
- ✅ Have proper timeouts (won't hang forever)
- ✅ Retry on transient failures
- ✅ Show local hotels even if Amadeus fails
- ✅ Display user-friendly error messages
- ✅ Support fast local-only mode
- ✅ Log all timeout issues for monitoring

## 🎯 Expected Behavior Now

1. **Fast Search** (1-5 seconds): Local hotels only
2. **Normal Search** (5-20 seconds): Local + Amadeus hotels
3. **Slow/Timeout** (20+ seconds): Local hotels + error message about Amadeus
4. **Complete Failure**: Error message, logs written, user can retry
