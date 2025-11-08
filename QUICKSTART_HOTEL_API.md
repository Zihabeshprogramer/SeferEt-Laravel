# Amadeus Hotel API - Quick Start Guide

## 🚀 Getting Started

### Step 1: Configure Environment
Add your Amadeus credentials to `.env`:
```env
AMADEUS_API_KEY=your_test_api_key
AMADEUS_API_SECRET=your_test_api_secret
AMADEUS_ENV=test
```

### Step 2: Verify Setup
The migrations have already been run. Verify the tables exist:
```bash
php artisan db:show
```

## 📍 API Endpoints

### 1. Search Hotels (Public)
**Endpoint:** `GET /api/hotels/search`

**Example Request:**
```bash
curl "http://localhost:8000/api/hotels/search?location=Mecca&check_in=2025-12-01&check_out=2025-12-05&guests=2&rooms=1"
```

**Query Parameters:**
- `location` (required): City name or IATA code (e.g., "Mecca", "MKK")
- `check_in` (required): YYYY-MM-DD format
- `check_out` (required): YYYY-MM-DD format
- `guests` (optional): Number of guests (default: 1)
- `rooms` (optional): Number of rooms (default: 1)
- `star_rating` (optional): 1-5
- `max_price` (optional): Maximum price per night

**Response:**
```json
{
  "success": true,
  "data": {
    "hotels": [
      {
        "id": "123",
        "source": "local",
        "name": "Grand Makkah Hotel",
        "star_rating": 5,
        "city": "Mecca",
        "price": {
          "amount": 250.00,
          "currency": "USD"
        }
      },
      {
        "id": "amadeus_BGMAKKK1",
        "source": "amadeus",
        "name": "Hilton Suites Mecca",
        "star_rating": 5,
        "city": "Mecca",
        "price": {
          "amount": 300.00,
          "currency": "USD"
        }
      }
    ],
    "total": 25,
    "local_count": 10,
    "amadeus_count": 15
  }
}
```

### 2. Get Hotel Details (Public)
**Endpoint:** `GET /api/hotels/{id}`

**Example Requests:**
```bash
# Local hotel
curl "http://localhost:8000/api/hotels/1"

# Amadeus hotel (note the amadeus_ prefix)
curl "http://localhost:8000/api/hotels/amadeus_BGMAKKK1"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "1",
    "source": "local",
    "name": "Grand Makkah Hotel",
    "description": "Luxury hotel near Haram",
    "star_rating": 5,
    "address": "King Abdul Aziz Road",
    "city": "Mecca",
    "country": "Saudi Arabia",
    "amenities": ["wifi", "parking", "pool"],
    "images": [...],
    "check_in_time": "15:00",
    "check_out_time": "12:00",
    "average_rating": 4.5,
    "total_reviews": 120
  }
}
```

### 3. Book Hotel (Requires Authentication)
**Endpoint:** `POST /api/hotels/book`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
```

**Request Body (Local Hotel):**
```json
{
  "hotel_id": "1",
  "source": "local",
  "room_id": "5",
  "check_in": "2025-12-01",
  "check_out": "2025-12-05",
  "adults": 2,
  "children": 1,
  "guest_name": "Ahmed Mohammed",
  "guest_email": "ahmed@example.com",
  "guest_phone": "+966501234567",
  "special_requests": "Late check-in please"
}
```

**Request Body (Amadeus Hotel):**
```json
{
  "hotel_id": "amadeus_BGMAKKK1",
  "source": "amadeus",
  "offer_id": "OFFER_ID_FROM_SEARCH",
  "check_in": "2025-12-01",
  "check_out": "2025-12-05",
  "adults": 2,
  "children": 0,
  "guest_name": "Ahmed Mohammed",
  "guest_email": "ahmed@example.com",
  "guest_phone": "+966501234567"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking_id": 789,
    "booking_reference": "HB-ABC123DEF",
    "hotel_name": "Grand Makkah Hotel",
    "check_in": "2025-12-01",
    "check_out": "2025-12-05",
    "total_amount": 1000.00,
    "currency": "USD",
    "status": "pending",
    "source": "local"
  }
}
```

## 🧪 Testing with Postman

### Import Collection
Create a new Postman collection with these endpoints:

**1. Search Hotels**
- Method: GET
- URL: `{{base_url}}/api/hotels/search`
- Params: location, check_in, check_out, guests, rooms

**2. Get Hotel Details**
- Method: GET
- URL: `{{base_url}}/api/hotels/{{hotel_id}}`

**3. Book Hotel**
- Method: POST
- URL: `{{base_url}}/api/hotels/book`
- Headers: Authorization: Bearer {{token}}
- Body: JSON (see examples above)

### Environment Variables
```json
{
  "base_url": "http://localhost:8000",
  "token": "your_auth_token_here"
}
```

## 🔍 Common City Codes (for Amadeus)

| City | IATA Code |
|------|-----------|
| Mecca | MKK |
| Medina | MED |
| Jeddah | JED |
| Riyadh | RUH |
| Dubai | DXB |
| Paris | PAR |
| London | LON |
| New York | NYC |

## ❌ Error Responses

### 422 Validation Error
```json
{
  "success": false,
  "errors": {
    "check_in": ["The check in must be a date after or equal to today."],
    "guest_email": ["The guest email must be a valid email address."]
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Hotel not found"
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to search hotels. Please try again."
}
```

## 💡 Tips

1. **Local vs Amadeus Hotels**
   - Local hotels have numeric IDs: `"1"`, `"2"`, `"123"`
   - Amadeus hotels have prefixed IDs: `"amadeus_BGMAKKK1"`

2. **Booking Requirements**
   - Must be authenticated to book
   - For local hotels: need `room_id`
   - For Amadeus hotels: need `offer_id` from search results

3. **Date Formats**
   - Always use YYYY-MM-DD format
   - Check-in must be today or future date
   - Check-out must be after check-in

4. **Performance**
   - Search results are cached for 10 minutes
   - Amadeus token cached for 30 minutes
   - First search might be slower due to API calls

5. **Testing in Development**
   - Use test credentials from Amadeus
   - Test environment has limited hotel inventory
   - Switch to production credentials for live data

## 🐛 Troubleshooting

### "No hotels found"
- Try using IATA city codes instead of city names
- Check if dates are in correct format
- Verify location has hotels in Amadeus test environment

### "Amadeus search failed"
- Check API credentials in `.env`
- Verify internet connection
- System will fallback to local hotels only

### "Booking failed"
- Ensure user is authenticated
- Verify all required fields are provided
- Check that offer_id hasn't expired (10 min TTL)

### Check Logs
```bash
tail -f storage/logs/laravel.log | grep Amadeus
```

## 📚 More Documentation

For complete documentation, see:
- `AMADEUS_HOTEL_INTEGRATION.md` - Full integration guide
- `IMPLEMENTATION_SUMMARY.md` - Implementation details

## 🆘 Support

If you encounter issues:
1. Check the logs: `storage/logs/laravel.log`
2. Verify your `.env` configuration
3. Ensure migrations have been run
4. Contact the development team
