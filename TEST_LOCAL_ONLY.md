# Test Local Hotels Only (Skip Amadeus)

## 🧪 Quick Test to Verify Local Hotels Work

If you want to test that everything works without Amadeus, use the `skip_amadeus` parameter.

### Via Browser
```
http://localhost:8000/api/hotels/search?location=MKK&check_in=2025-12-01&check_out=2025-12-05&skip_amadeus=true
```

### Via Command Line
```bash
curl "http://localhost:8000/api/hotels/search?location=MKK&check_in=2025-12-01&check_out=2025-12-05&skip_amadeus=true"
```

### Expected Response (if you have local hotels)
```json
{
  "success": true,
  "data": {
    "hotels": [
      {
        "id": "1",
        "source": "local",
        "name": "Your Local Hotel",
        ...
      }
    ],
    "total": 1,
    "local_count": 1,
    "amadeus_count": 0
  }
}
```

### Expected Response (if no local hotels)
```json
{
  "success": true,
  "data": {
    "hotels": [],
    "total": 0,
    "local_count": 0,
    "amadeus_count": 0
  }
}
```

## ✅ This Proves

If this works instantly:
- ✅ Your Laravel app is working
- ✅ API routing is correct
- ✅ Controllers are functioning
- ✅ Database connection works
- ❌ **The issue is specifically with Amadeus API**

## 🔍 If This Test Works But Full Search Times Out

The problem is definitely with Amadeus. Options:

1. **Check Amadeus credentials**
2. **Test Amadeus API directly** (see below)
3. **Disable Amadeus temporarily** (see below)

## 🧪 Test Amadeus API Directly

```bash
php artisan tinker
```

```php
// Test if Amadeus credentials work
$service = app(\App\Services\AmadeusHotelService::class);

// Try to get auth token
try {
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getAccessToken');
    $method->setAccessible(true);
    $token = $method->invoke($service);
    echo "✅ Auth successful! Token: " . substr($token, 0, 20) . "...\n";
} catch (\Exception $e) {
    echo "❌ Auth failed: " . $e->getMessage() . "\n";
}
```

## 🚫 Temporarily Disable Amadeus in Code

If you want to completely disable Amadeus while you debug:

**Edit**: `app/Http/Controllers/Api/HotelApiController.php`

Find line ~63:
```php
$skipAmadeus = $request->boolean('skip_amadeus', false);
```

Change to:
```php
$skipAmadeus = true; // Force skip Amadeus for now
```

This will make ALL searches skip Amadeus automatically.

## 📊 Comparison

### Local Only (skip_amadeus=true)
- ⚡ Response time: < 1 second
- ✅ No external API calls
- ✅ Shows only your database hotels

### With Amadeus (skip_amadeus=false or omitted)
- 🐌 Response time: 30-120 seconds
- 🌐 Calls external Amadeus API
- ✅ Shows local + Amadeus hotels

## 💡 Recommendation

1. **Test local-only first** to verify base functionality
2. **Then test Amadeus separately** to isolate the issue
3. **Fix Amadeus or keep it disabled** until resolved

Try the local-only test now!
