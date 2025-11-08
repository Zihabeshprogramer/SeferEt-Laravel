# Amadeus Hotel Integration Documentation

## Overview

This integration extends the existing hotel module to include Amadeus Hotel Self-Service API, providing access to a vast global hotel inventory alongside local provider hotels.

## Features

- **Unified Hotel Search**: Combines local provider hotels with Amadeus hotel inventory
- **Prioritization**: Local hotels always appear first in search results
- **Unified Booking Flow**: Single booking endpoint handles both local and Amadeus hotels
- **Caching**: Amadeus hotel offers are cached for performance
- **Error Handling**: Graceful fallback if Amadeus API is unavailable

## Architecture

### Components

1. **AmadeusHotelService** (`app/Services/AmadeusHotelService.php`)
   - Handles OAuth2 authentication with Amadeus API
   - Methods:
     - `searchHotels()`: Search hotels by location and dates
     - `getHotelDetails()`: Get detailed hotel information
     - `bookHotel()`: Create hotel booking through Amadeus
     - `transformToUnifiedFormat()`: Convert Amadeus data to unified format

2. **HotelApiController** (`app/Http/Controllers/Api/HotelApiController.php`)
   - Unified API endpoints for hotel operations
   - Combines local and Amadeus results
   - Handles both local and Amadeus bookings

3. **Database**
   - `amadeus_hotels_cache`: Stores temporary Amadeus hotel offers
   - `hotel_bookings`: Extended with `source` column to track booking origin

## API Endpoints

### 1. Search Hotels
```
GET /api/hotels/search
```

**Parameters:**
- `location` (required): City name or code
- `check_in` (required): Check-in date (YYYY-MM-DD)
- `check_out` (required): Check-out date (YYYY-MM-DD)
- `guests` (optional): Number of guests (default: 1)
- `rooms` (optional): Number of rooms (default: 1)
- `star_rating` (optional): Filter by star rating (1-5)
- `max_price` (optional): Maximum price filter

**Response:**
```json
{
  "success": true,
  "data": {
    "hotels": [
      {
        "id": "123",
        "source": "local",
        "name": "Grand Hotel",
        "city": "Mecca",
        "star_rating": 5,
        "price": {
          "amount": 250.00,
          "currency": "USD"
        },
        ...
      },
      {
        "id": "amadeus_BGMAKKK1",
        "source": "amadeus",
        "name": "Hilton Mecca",
        ...
      }
    ],
    "total": 25,
    "local_count": 10,
    "amadeus_count": 15
  },
  "search_params": { ... }
}
```

### 2. Get Hotel Details
```
GET /api/hotels/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "123",
    "source": "local",
    "name": "Grand Hotel",
    "description": "...",
    "amenities": [...],
    "images": [...],
    ...
  }
}
```

### 3. Book Hotel
```
POST /api/hotels/book
```

**Headers:**
- `Authorization: Bearer {token}` (required)

**Request Body:**
```json
{
  "hotel_id": "123",
  "source": "local",
  "check_in": "2025-12-01",
  "check_out": "2025-12-05",
  "adults": 2,
  "children": 0,
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "guest_phone": "+1234567890",
  "special_requests": "Late check-in",
  "room_id": "456",
  "offer_id": "OFFER123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking_id": 789,
    "booking_reference": "HB-ABC123",
    "hotel_name": "Grand Hotel",
    "check_in": "2025-12-01",
    "check_out": "2025-12-05",
    "total_amount": 1000.00,
    "currency": "USD",
    "status": "pending",
    "source": "local"
  }
}
```

## Setup Instructions

### 1. Environment Configuration

Add Amadeus credentials to `.env`:
```env
AMADEUS_API_KEY=your_api_key_here
AMADEUS_API_SECRET=your_api_secret_here
AMADEUS_ENV=test
```

### 2. Run Migrations

```bash
php artisan migrate
```

This will create:
- `amadeus_hotels_cache` table
- Add `source` column to `hotel_bookings` table

### 3. Clear Cache (if needed)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Data Flow

### Search Flow
1. User sends search request to `/api/hotels/search`
2. Controller searches local hotels from database
3. Controller searches Amadeus hotels via API
4. Results are merged (local first, then Amadeus)
5. Combined results returned to user

### Booking Flow - Local Hotel
1. User submits booking with `source: "local"`
2. Controller validates hotel and room availability
3. Booking created in `hotel_bookings` table
4. Booking reference generated and returned

### Booking Flow - Amadeus Hotel
1. User submits booking with `source: "amadeus"`
2. Controller calls `AmadeusHotelService::bookHotel()`
3. Amadeus API processes booking
4. Booking stored in `hotel_bookings` table with Amadeus details
5. Confirmation returned to user

## Database Schema

### amadeus_hotels_cache
```sql
- id: bigint (primary key)
- hotel_id: string (unique, indexed)
- name: string
- city_code: string
- price: decimal
- currency: string
- rating: integer
- offer_id: string (indexed)
- offer_data: json
- expires_at: timestamp (indexed)
- created_at, updated_at: timestamps
```

### hotel_bookings (extended)
```sql
...existing columns...
- source: string (default: 'local')
```

## Error Handling

### Amadeus API Failures
- If Amadeus API is unreachable, system continues with local hotels only
- All Amadeus API errors are logged to `storage/logs/laravel.log`
- Users receive combined results without interruption

### Booking Failures
- Local booking failures rollback database transaction
- Amadeus booking failures are caught and reported with clear error messages
- All booking attempts are logged

## Caching Strategy

### Token Cache
- Amadeus access tokens cached for 30 minutes
- Automatically refreshed on expiration

### Search Cache
- Search results cached for 10 minutes
- Cache key includes all search parameters

### Offer Cache
- Hotel offers stored in database
- Expires after search TTL (10 minutes)
- Used for booking validation

## Testing

### Test Amadeus Connection
```php
use App\Services\AmadeusHotelService;

$service = app(AmadeusHotelService::class);
$results = $service->searchHotels('MKK', '2025-12-01', '2025-12-05', 2, 1);
dd($results);
```

### Test Unified Search
```bash
curl -X GET "http://localhost:8000/api/hotels/search?location=Mecca&check_in=2025-12-01&check_out=2025-12-05&guests=2"
```

## Monitoring & Logs

All Amadeus API interactions are logged with:
- Request parameters
- Response status
- Execution time
- Error details (if any)

Check logs:
```bash
tail -f storage/logs/laravel.log | grep Amadeus
```

## Future Enhancements

- [ ] Add webhook support for booking updates
- [ ] Implement hotel reviews aggregation
- [ ] Add advanced filtering (amenities, distance, etc.)
- [ ] Implement price comparison and best deal detection
- [ ] Add support for multi-room bookings
- [ ] Implement cancellation flow for Amadeus bookings

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review Amadeus API documentation: https://developers.amadeus.com/
3. Contact development team

## License

This integration is part of the SeferEt platform.
