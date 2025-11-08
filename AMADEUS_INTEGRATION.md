# Amadeus Flight Integration Module

Complete integration of the **Amadeus Self-Service GDS API** for global flight search and booking.

## Overview

This integration provides:
- Global flight search via Amadeus API
- Flight offer pricing validation
- Booking creation with PNR generation
- Canonical `offers` table for all flight sources
- Seamless integration with existing `flight_bookings` table
- Duplicate prevention via deterministic offer hashing

## Installation & Setup

### 1. Environment Configuration

Add the following to your `.env` file:

```env
AMADEUS_API_KEY=your_api_key_here
AMADEUS_API_SECRET=your_api_secret_here
AMADEUS_ENV=test
```

Get your credentials from [Amadeus for Developers](https://developers.amadeus.com/).

### 2. Run Migrations

```bash
php artisan migrate
```

This will:
- Create the `offers` table with unique `offer_hash` constraint
- Add `offer_id`, `pnr`, and `agent_id` columns to `flight_bookings`
- Add composite unique constraint on `(pnr, agent_id)`

### 3. Add Routes

Include the flight routes in your `routes/api.php`:

```php
require __DIR__.'/api_flights.php';
```

Or manually add:

```php
use App\Http\Controllers\FlightController;

Route::prefix('flights')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/search', [FlightController::class, 'search']);
    Route::post('/book', [FlightController::class, 'book']);
    Route::get('/booking/{pnr}', [FlightController::class, 'getByPnr']);
});
```

## API Endpoints

### 1. Search Flights

**Endpoint:** `GET /api/flights/search`

**Parameters:**
- `origin` (required): IATA airport code (e.g., "JFK")
- `destination` (required): IATA airport code (e.g., "LAX")
- `departure_date` (required): Date in "YYYY-MM-DD" format
- `return_date` (optional): Date in "YYYY-MM-DD" format
- `adults` (optional): Number of adults (default: 1)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/flights/search?origin=JFK&destination=LAX&departure_date=2025-12-01&return_date=2025-12-08&adults=2" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Flight offers retrieved successfully",
  "data": [...],
  "meta": {...},
  "dictionaries": {...}
}
```

### 2. Book Flight

**Endpoint:** `POST /api/flights/book`

**Body:**
```json
{
  "offer": {
    "id": "1",
    "price": {
      "total": "350.00",
      "currency": "USD"
    },
    "itineraries": [...]
  },
  "travelers": [
    {
      "id": "1",
      "dateOfBirth": "1990-01-01",
      "name": {
        "firstName": "JOHN",
        "lastName": "DOE"
      },
      "gender": "MALE",
      "contact": {
        "emailAddress": "john.doe@example.com",
        "phones": [
          {
            "deviceType": "MOBILE",
            "countryCallingCode": "1",
            "number": "1234567890"
          }
        ]
      }
    }
  ],
  "customer_id": 1,
  "agent_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Flight booking created successfully",
  "data": {
    "booking": {...},
    "offer": {...},
    "pnr": "ABC123",
    "amadeus_booking": {...}
  }
}
```

### 3. Get Booking by PNR

**Endpoint:** `GET /api/flights/booking/{pnr}`

**Example:**
```bash
curl -X GET "http://localhost:8000/api/flights/booking/ABC123" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Architecture

### Database Schema

#### `offers` Table
Canonical table for all flight offers (local/Amadeus/manual):
- `offer_source`: enum('local', 'amadeus', 'manual')
- `external_offer_id`: Amadeus offer ID
- `offer_hash`: SHA-256 hash (unique) for deduplication
- `origin`, `destination`, `departure_date`, `return_date`
- `price_amount`, `price_currency`
- `segments`: JSON array of flight segments
- `owner_agent_id`: FK to users (for group flights)

#### `flight_bookings` Updates
- Added `offer_id`: FK to offers table
- Added `pnr`: Passenger Name Record from Amadeus
- Added `agent_id`: FK to users (agent who processed booking)
- Unique constraint on `(pnr, agent_id)` to prevent duplicates

### Services

#### `AmadeusService`
Located at: `app/Services/AmadeusService.php`

**Methods:**
- `searchFlights(array $params)`: Search for flight offers
- `validatePricing(array $offerData)`: Validate offer pricing
- `createBooking(array $offerData, array $travelers)`: Create booking
- `computeOfferHash(array $offerData)`: Generate deterministic hash
- `upsertOffer(array $offerData)`: Store/update offer in DB

**Caching:**
- OAuth tokens cached for 30 minutes
- Search results cached for 10 minutes
- Uses Redis/file cache (per Laravel config)

**Logging:**
- All API calls logged to `storage/logs/laravel.log`
- Errors include full context for debugging

### Models

#### `Offer` Model
Located at: `app/Models/Offer.php`

**Relationships:**
- `bookings()`: hasMany FlightBooking
- `ownerAgent()`: belongsTo User

**Scopes:**
- `fromAmadeus()`, `local()`, `manual()`

#### `FlightBooking` Model Updates
Located at: `app/Models/FlightBooking.php`

**New Relationships:**
- `offer()`: belongsTo Offer
- `agent()`: belongsTo User

## Flutter Integration

### Service Class
Located at: `SeferEt-Flutter/lib/services/amadeus_flight_service.dart`

**Usage Example:**

```dart
final flightService = AmadeusFlightService(
  baseUrl: 'https://your-api.com',
);

// Search flights
final searchResult = await flightService.searchFlights(
  origin: 'JFK',
  destination: 'LAX',
  departureDate: '2025-12-01',
  returnDate: '2025-12-08',
  adults: 2,
  token: authToken,
);

// Book flight
final bookingResult = await flightService.bookFlight(
  offer: selectedOffer,
  travelers: travelerList,
  customerId: userId,
  token: authToken,
);
```

## Duplication Prevention

### Offer Deduplication
- `offer_hash` computed from: segments, price, currency
- Unique constraint ensures no duplicate offers stored
- `firstOrCreate` used to idempotently insert offers

### Booking Deduplication
- Unique constraint on `(pnr, agent_id)` in `flight_bookings`
- Prevents same agent from creating duplicate bookings with same PNR

## Testing

### Postman/Insomnia

1. **Search Test:**
```
GET http://localhost:8000/api/flights/search?origin=MAD&destination=LON&departure_date=2025-12-01&adults=1
Authorization: Bearer YOUR_TOKEN
```

2. **Verify Offers Table:**
```sql
SELECT * FROM offers WHERE offer_source = 'amadeus';
```

3. **Book Test:**
Use the offer from search result + valid traveler data

4. **Verify Booking:**
```sql
SELECT * FROM flight_bookings WHERE pnr IS NOT NULL;
```

### Unit Tests (Optional)
Create tests in `tests/Feature/FlightTest.php`:
```php
public function test_flight_search()
{
    $response = $this->get('/api/flights/search?origin=JFK&destination=LAX&departure_date=2025-12-01');
    $response->assertStatus(200);
}
```

## Rollout Plan

1. ✅ Run migrations
2. ✅ Configure `.env` with Amadeus credentials
3. ✅ Test search endpoint in Postman
4. ✅ Verify `offers` table populated correctly
5. ✅ Test booking endpoint with valid traveler data
6. ✅ Confirm `flight_bookings` created with `offer_id` and `pnr`
7. ✅ Deploy to staging
8. ✅ Validate with existing group flight/collaboration flows
9. ✅ Monitor logs for errors
10. ✅ Deploy to production

## Compatibility

### Existing Features
This integration:
- ✅ Preserves existing `flight_bookings` structure
- ✅ Compatible with existing group flights functionality
- ✅ Does NOT replace or duplicate any existing tables
- ✅ Adds new capabilities without breaking changes

### Future Enhancements
- Add `group_flights` support (agents can publish Amadeus offers)
- Implement offer expiration/cleanup (cron job)
- Add flight status tracking
- Integrate cancellation/refund workflows

## Troubleshooting

### Token Issues
```
Error: Failed to obtain Amadeus access token
```
- Check `AMADEUS_API_KEY` and `AMADEUS_API_SECRET` in `.env`
- Verify credentials at Amadeus developer portal

### No Search Results
```
"data": []
```
- Amadeus test environment has limited data
- Try common routes: MAD-LON, NYC-LAX, PAR-LON
- Check date is future date

### Booking Fails
```
Failed to create booking with Amadeus
```
- Offer may have expired (search again)
- Validate traveler data format (see Flutter service comments)
- Check logs in `storage/logs/laravel.log`

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review Amadeus docs: https://developers.amadeus.com/self-service
3. Check migration status: `php artisan migrate:status`

---

**Generated:** 2025-10-26  
**Laravel Version:** 10.x+  
**Amadeus API Version:** v2 (search), v1 (booking)
