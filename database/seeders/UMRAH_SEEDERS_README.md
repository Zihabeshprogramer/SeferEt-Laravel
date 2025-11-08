# Umrah Data Seeders - Quick Start Guide

## Overview
These seeders populate your database with **realistic Umrah-specific flights and hotels** for testing and demo purposes.

---

## Prerequisites

### Required Users in Database:
1. **Travel Agents** (for flights) - `role = 'travel_agent'`
2. **Hotel Providers** (for hotels) - `role = 'hotel_provider'`

If you don't have these users, the seeders will display an error and stop.

---

## Running the Seeders

### 1. Seed Umrah Flights (20-25 flights)
```bash
php artisan db:seed --class=UmrahFlightsSeeder
```

**What it creates:**
- ✈️ Round-trip flights from major Muslim countries (Pakistan, India, Indonesia, Egypt, Turkey, Malaysia, Bangladesh)
- 🎯 Destinations: Jeddah (JED) and Madinah (MED)
- 🏷️ Airlines: Saudia, Flynas, Qatar Airways, Emirates, Turkish Airlines, PIA, etc.
- 💰 Prices: $350-$1200 (economy), with group discounts
- 📅 Departure dates: Within next 3 months
- 🧳 Includes: Zamzam baggage allowance, halal meals, Umrah-specific services

---

### 2. Seed Umrah Hotels (30 hotels)
```bash
php artisan db:seed --class=UmrahHotelsSeeder
```

**What it creates:**
- 🏨 15 hotels in **Makkah** (near Masjid al-Haram)
- 🏨 15 hotels in **Madinah** (near Masjid an-Nabawi)
- ⭐ Star ratings: 3-5 stars
- 📍 Distance to Haram: 0.1km - 1.3km
- 🛎️ Real hotel names: Swissotel, Pullman, Hilton, Raffles, Conrad, etc.
- ✨ Amenities: WiFi, Prayer areas, Shuttle to Haram, Haram view rooms (5-star)

---

## Run Both at Once
```bash
php artisan db:seed --class=UmrahFlightsSeeder
php artisan db:seed --class=UmrahHotelsSeeder
```

---

## Data Features

### Flights Include:
✅ Realistic airline codes (SV, QR, EK, TK, PK, etc.)  
✅ Group booking support (min 10-15 passengers)  
✅ Agent collaboration enabled  
✅ Round-trip with 7-14 day stays  
✅ Zamzam water allowance (5L)  
✅ Halal meal service  
✅ Prayer time announcements  
✅ Multilingual crew  

### Hotels Include:
✅ Authentic Saudi hotel names  
✅ Real addresses in Makkah/Madinah  
✅ GPS coordinates (latitude/longitude)  
✅ Distance to Haram/Masjid  
✅ Check-in/out times  
✅ Cancellation policies  
✅ Children policies  
✅ Prayer areas & Haram shuttle  
✅ Active status for immediate booking  

---

## Verification

After running seeders, verify the data:

### Check Flights:
```bash
php artisan tinker
>>> App\Models\Flight::count()
>>> App\Models\Flight::with('provider')->first()
```

### Check Hotels:
```bash
php artisan tinker
>>> App\Models\Hotel::count()
>>> App\Models\Hotel::where('city', 'Makkah')->count()
>>> App\Models\Hotel::where('city', 'Madinah')->count()
```

### API Endpoints (Test in Postman/Browser):
```
GET /api/flights
GET /api/hotels
GET /api/hotels?city=Makkah
GET /api/hotels?city=Madinah
```

---

## Troubleshooting

### Error: "No travel agents found"
**Solution:** Seed users first with travel agent role:
```php
User::create([
    'name' => 'Test Agent',
    'email' => 'agent@example.com',
    'password' => bcrypt('password'),
    'role' => 'travel_agent',
    'status' => 'active',
]);
```

### Error: "No hotel providers found"
**Solution:** Seed users first with hotel provider role:
```php
User::create([
    'name' => 'Test Hotel Provider',
    'email' => 'hotel@example.com',
    'password' => bcrypt('password'),
    'role' => 'hotel_provider',
    'status' => 'active',
]);
```

---

## Clean Up (Optional)

To remove seeded data:
```bash
php artisan tinker
>>> App\Models\Flight::truncate()
>>> App\Models\Hotel::truncate()
```

Or re-run migration:
```bash
php artisan migrate:fresh --seed
```

---

## Notes

- All flights are **scheduled** and **active**
- All hotels have **active** status
- Prices are in **USD**
- Dates are within **next 3 months**
- All data is **Umrah-specific** and realistic
- Images are **placeholder paths** (update with real images later)

---

## Next Steps

1. ✅ Run the seeders
2. ✅ Verify data via API
3. ✅ Test on mobile/web app
4. 🎨 Upload real hotel images to `/storage/app/public/hotels/`
5. 🎨 Upload flight banners to `/storage/app/public/flights/`
6. 📊 Check B2C listings for customers

---

**Happy Testing! 🕋**
