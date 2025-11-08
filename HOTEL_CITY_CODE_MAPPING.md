# Hotel City Code Mapping - Mecca/JED Explanation

## 🎯 The Issue

When searching for hotels in **Mecca/Makkah**, the system returns **JED** (Jeddah) as the location code.

## ✅ This is CORRECT Behavior!

### Why JED for Mecca?

**Mecca does not have an international airport or IATA city code.**

- ✈️ **Nearest Airport**: King Abdulaziz International Airport in Jeddah
- 📍 **Airport Code**: JED
- 📏 **Distance**: ~80km from Mecca (approximately 1 hour drive)
- 🗺️ **Coverage Area**: Amadeus uses JED to represent the **Jeddah/Mecca region**

### Geographic Reality

```
┌─────────────────────────────────┐
│  Jeddah/Mecca Region (JED)      │
│                                  │
│  • Jeddah (الجدة)                │
│    └─ King Abdulaziz Airport    │
│       (JED) ✈️                   │
│                                  │
│  • Mecca (مكة المكرمة)          │
│    └─ Masjid al-Haram 🕋        │
│                                  │
│  Distance: ~80km                 │
└─────────────────────────────────┘
```

---

## 🔧 Solution Implemented

We've created an **automatic city code mapper** that intelligently converts city names to IATA codes.

### Features

1. **Automatic Conversion**: "Mecca" → "JED"
2. **Multiple Variations Supported**:
   - Mecca, Makkah, Makka, Makkah Al Mukarramah → JED
   - Medina, Madinah, Al Madinah → MED
   - Jeddah, Jiddah → JED

3. **User-Friendly Response**: API now includes explanatory metadata

---

## 📋 Supported Cities

### Saudi Arabia (Umrah/Hajj Cities)

| City Name | Variations | IATA Code | Airport |
|-----------|-----------|-----------|---------|
| **Mecca** | Makkah, Makka | **JED** | King Abdulaziz Int'l, Jeddah |
| **Medina** | Madinah, Al Madinah | **MED** | Prince Mohammad Bin Abdulaziz |
| **Jeddah** | Jiddah | **JED** | King Abdulaziz International |
| **Riyadh** | Riyad | **RUH** | King Khalid International |
| **Taif** | - | **TIF** | Taif Regional Airport |

### Other Cities Supported

- Dammam, Dhahran, Khobar → **DMM**
- Abha → **AHB**
- Tabuk → **TUU**
- Dubai → **DXB**
- Istanbul → **IST**
- Cairo → **CAI**
- And 20+ more cities...

---

## 🚀 How It Works

### Before (Without Mapper)
```php
// User searches: "Mecca"
// API Error: "No hotels found" ❌
```

### After (With Mapper)
```php
// User searches: "Mecca"
// Mapper converts: "Mecca" → "JED"
// Amadeus finds: 200+ hotels in Mecca/Jeddah area ✅
```

---

## 📡 API Response Format

### Hotel Search with Metadata

```json
{
  "success": true,
  "data": {
    "hotels": [...],
    "total": 25,
    "local_count": 5,
    "amadeus_count": 20
  },
  "search_params": {
    "location": "Mecca",
    "location_code": "JED",
    "location_display": "Jeddah / Mecca area",
    "check_in": "2025-12-01",
    "check_out": "2025-12-10"
  },
  "meta": {
    "is_mecca": true,
    "code_explanation": "Hotels in Mecca are searched using JED (Jeddah) code because Mecca does not have an international airport. Jeddah's King Abdulaziz International Airport (JED) is the nearest major airport, located approximately 80km from Mecca."
  }
}
```

---

## 🔌 New API Endpoints

### 1. Search Cities
```bash
GET /api/cities/search?query=mecca
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "city": "Mecca",
      "code": "JED",
      "display": "Mecca (JED)"
    }
  ]
}
```

### 2. Get City Code
```bash
GET /api/cities/code?city=Mecca
```

**Response:**
```json
{
  "success": true,
  "data": {
    "city": "Mecca",
    "code": "JED",
    "display_name": "Jeddah / Mecca area",
    "is_mecca": true,
    "explanation": "Hotels in Mecca are searched using JED..."
  }
}
```

### 3. Get Mecca Explanation
```bash
GET /api/cities/mecca-explanation
```

**Response:**
```json
{
  "success": true,
  "data": {
    "question": "Why does Mecca show JED code?",
    "answer": "Hotels in Mecca are searched using JED...",
    "mapping": {
      "input": ["Mecca", "Makkah", "Makka"],
      "code": "JED",
      "airport": "King Abdulaziz International Airport",
      "city": "Jeddah",
      "distance_from_mecca": "80km (approximately 1 hour drive)"
    }
  }
}
```

---

## 💻 Code Usage

### In Controllers
```php
use App\Helpers\CityCodeMapper;

// Convert city name to code
$iataCode = CityCodeMapper::cityToIata('Mecca');
// Returns: "JED"

// Check if it's Mecca
$isMecca = CityCodeMapper::isMecca('Makkah');
// Returns: true

// Get friendly name
$name = CityCodeMapper::iataToFriendlyName('JED');
// Returns: "Jeddah / Mecca area"

// Get explanation
$explanation = CityCodeMapper::getMeccaExplanation();
```

### In API Calls
```php
// User searches with "Mecca"
$location = "Mecca";
$iataCode = CityCodeMapper::cityToIata($location);

// Search Amadeus with correct code
$results = $amadeusService->searchHotels(
    $iataCode,  // "JED"
    $checkIn,
    $checkOut,
    $guests,
    $rooms
);
```

---

## 🎨 Frontend Implementation Suggestions

### 1. Show User-Friendly Message
```javascript
// When user searches "Mecca"
if (response.meta.is_mecca) {
  showInfoBanner({
    title: "Hotels near Mecca",
    message: "Showing hotels in the Jeddah/Mecca area (JED)",
    explanation: response.meta.code_explanation
  });
}
```

### 2. Autocomplete with Codes
```javascript
// City search autocomplete
<input 
  placeholder="Search city (e.g., Mecca, Medina)" 
  onChange={searchCities}
/>

// Show results like:
// Mecca (JED) - Jeddah/Mecca area
// Medina (MED) - Medina
```

### 3. Pre-fill Popular Cities
```javascript
const popularCities = [
  { name: 'Mecca', code: 'JED', display: 'Mecca (Hotels in Jeddah area)' },
  { name: 'Medina', code: 'MED', display: 'Medina' },
  { name: 'Jeddah', code: 'JED', display: 'Jeddah' },
  { name: 'Riyadh', code: 'RUH', display: 'Riyadh' }
];
```

---

## ✅ Testing

### Test the Mapping
```bash
# Test Mecca variations
curl "http://your-api.com/api/cities/code?city=Mecca"
curl "http://your-api.com/api/cities/code?city=Makkah"
curl "http://your-api.com/api/cities/code?city=Makka"

# All should return: JED
```

### Test Hotel Search
```bash
# Search with "Mecca" (will auto-convert to JED)
curl "http://your-api.com/api/hotels/search?location=Mecca&check_in=2025-12-01&check_out=2025-12-10"

# Should return hotels in Mecca/Jeddah area
```

---

## 📚 Key Files

1. **Helper Class**: `app/Helpers/CityCodeMapper.php`
2. **API Controller**: `app/Http/Controllers/Api/CityCodeController.php`
3. **Hotel Controller**: `app/Http/Controllers/Api/HotelApiController.php` (updated)

---

## 🌟 Benefits

✅ **No More Confusion**: Clear explanation for users  
✅ **Automatic Conversion**: Handles all city name variations  
✅ **Better UX**: Informative API responses  
✅ **Industry Standard**: Follows Amadeus/IATA conventions  
✅ **Extensible**: Easy to add more cities  

---

## 📝 Summary

**The JED code for Mecca is not an error—it's the correct behavior!**

Amadeus (and most travel APIs) use IATA airport codes as geographic references. Since Mecca has no airport, it's represented by the nearest major airport: **JED (Jeddah)**.

Our solution:
1. ✅ Automatically converts "Mecca" → "JED"
2. ✅ Provides clear explanations in API responses
3. ✅ Supports multiple city name variations
4. ✅ Helps users understand why JED is used

---

**Created**: November 3, 2025  
**Status**: ✅ Implemented & Working  
**Next Step**: Update your Flutter app to handle the metadata! 📱
