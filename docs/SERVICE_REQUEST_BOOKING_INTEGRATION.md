# Service Request to Booking Integration

## Overview

This integration automatically converts approved service requests into actual bookings in the respective booking management modules. When a hotel provider (or other service provider) approves a service request from a travel agent, the system can automatically create a booking entry in the booking module.

## How It Works

### 1. Service Request Approval Process

When a provider approves a service request through the `ProviderRequestController::approve()` method:

1. **Standard Approval**: The request is approved using the existing `ApprovalService`
2. **Booking Creation**: If enabled, the system automatically creates a booking using `BookingIntegrationService`
3. **Metadata Update**: The service request is updated with booking information
4. **Response**: The API response includes both approval and booking creation results

### 2. Automatic Booking Creation

The booking creation is controlled by several parameters:

- **`create_booking`**: Boolean parameter in the approval request (defaults to `true` for hotel requests)
- **`auto_confirm_booking`**: Boolean parameter to auto-confirm created bookings
- **Provider Type**: Different logic for hotel, flight, and transport bookings

### 3. Booking Integration Service

The `BookingIntegrationService` handles:

- **Validation**: Ensures the service request can be converted to a booking
- **Room Assignment**: Intelligent room selection based on request metadata or availability
- **Guest Information**: Extracts guest details from service request metadata
- **Pricing Calculation**: Calculates taxes, fees, and final pricing
- **Booking Creation**: Creates the actual booking record
- **Linking**: Links the service request to the created booking

## API Changes

### Approval Request

**Endpoint**: `POST /b2b/{provider-type}/requests/{id}/approve`

**New Parameters**:
```json
{
    "provider_notes": "Required notes from provider",
    "confirmed_price": 150.00,
    "currency": "USD",
    "terms_conditions": "Optional terms",
    "notify_agent": true,
    "create_booking": true,        // NEW: Control booking creation
    "auto_confirm_booking": true   // NEW: Auto-confirm created booking
}
```

**Enhanced Response**:
```json
{
    "success": true,
    "message": "Request approved successfully and booking created automatically",
    "allocation": { ... },
    "booking": {                   // NEW: Booking information
        "created": true,
        "id": 123,
        "type": "hotel",
        "reference": "HB-ABC123-241205"
    }
}
```

### Get Booking Details

**Endpoint**: `GET /b2b/{provider-type}/requests/{id}/booking`

**Response**:
```json
{
    "success": true,
    "data": {
        "booking": {
            "id": 123,
            "reference": "HB-ABC123-241205",
            "status": "confirmed",
            "payment_status": "pending",
            "hotel_name": "Grand Hotel",
            "room_number": "101",
            "guest_name": "John Doe",
            "check_in_date": "2024-12-10",
            "check_out_date": "2024-12-15",
            "nights": 5,
            "total_amount": 750.00,
            "currency": "USD",
            "view_url": "/b2b/hotel-provider/bookings/123"
        },
        "service_request": {
            "id": 456,
            "uuid": "srv-req-789",
            "status": "approved"
        }
    }
}
```

### Service Request List Data

The `getData()` method now includes booking status:

```json
{
    "success": true,
    "data": [
        {
            "id": 456,
            "uuid": "srv-req-789",
            // ... other fields ...
            "booking": {               // NEW: Booking status
                "has_booking": true,
                "id": 123,
                "type": "hotel",
                "reference": "HB-ABC123-241205",
                "created_at": "2024-12-05 10:30:00",
                "status": "created",
                "message": "Booking created successfully"
            }
        }
    ]
}
```

## Hotel Booking Creation Details

### Room Selection Logic

1. **Specific Room**: If `metadata['room_id']` is provided, try to book that room
2. **Room Type**: If `metadata['room_type']` is provided, find a room of that category
3. **Auto-Assignment**: Find the cheapest available room that can accommodate the guests

### Guest Information Extraction

The system extracts guest information from service request metadata:

- `guest_name` → `booking.guest_name`
- `guest_email` → `booking.guest_email`
- `guest_phone` → `booking.guest_phone`
- `adults` → `booking.adults`
- `children` → `booking.children`

### Pricing Calculation

- **Base Rate**: Uses `offered_price` from service request or room `base_price`
- **Tax**: 10% of subtotal (configurable)
- **Service Fee**: 5% of subtotal (configurable)
- **Discounts**: Applied if specified in metadata
- **Total**: Subtotal + Tax + Service Fee - Discounts

### Booking Notes

Automatically generated notes include:
- Reference to source service request
- Agent notes from the service request
- Provider notes from the approval
- Special requirements from metadata

## Database Changes

### Service Request Metadata

When a booking is created, the service request metadata is updated with:

```json
{
    "booking_created": true,
    "booking_id": 123,
    "booking_type": "hotel",
    "booking_reference": "HB-ABC123-241205",
    "booking_created_at": "2024-12-05 10:30:00"
}
```

### Hotel Booking Source

Created bookings have:
- `source = 'service_request'`
- `status = 'confirmed'` (auto-confirmed)
- `payment_status = 'pending'`

## Error Handling

### Booking Creation Failures

If booking creation fails:

1. **Service Request Approval**: Still succeeds (not rolled back)
2. **Error Logging**: Detailed error logs are created
3. **API Response**: Includes booking error information
4. **User Notification**: Provider is informed of the booking creation failure

### Example Error Response

```json
{
    "success": true,
    "message": "Request approved successfully (booking creation failed)",
    "allocation": { ... },
    "booking": {
        "created": false,
        "error": "No suitable room found for the booking",
        "error_code": "ROOM_NOT_FOUND"
    }
}
```

## Frontend Integration

### Service Request List

- Show booking status badges next to approved requests
- Add "View Booking" links for requests with bookings
- Display booking references and statuses

### Approval Modal

- Add checkbox to control booking creation
- Show booking creation results in success messages
- Provide links to view created bookings

### Request Details

- Show booking information panel for approved requests
- Add button to view booking details
- Display booking status and key information

## Testing

### Unit Tests

Test the `BookingIntegrationService`:

```php
// Test successful hotel booking creation
$result = $bookingService->convertToBooking($approvedServiceRequest);
$this->assertTrue($result['success']);
$this->assertInstanceOf(HotelBooking::class, $result['booking']);

// Test validation failures
$result = $bookingService->convertToBooking($pendingServiceRequest);
$this->assertFalse($result['success']);
$this->assertEquals('INVALID_STATE', $result['error_code']);
```

### Integration Tests

Test the approval process with booking creation:

```php
// Test approval with booking creation
$response = $this->postJson('/b2b/hotel-provider/requests/1/approve', [
    'provider_notes' => 'Test approval',
    'confirmed_price' => 100.00,
    'create_booking' => true
]);

$response->assertSuccessful();
$response->assertJsonPath('booking.created', true);
```

## Configuration

### Default Behavior

- **Hotel Requests**: Automatically create bookings (default: `true`)
- **Flight Requests**: Don't create bookings (default: `false`, not implemented)
- **Transport Requests**: Don't create bookings (default: `false`, not implemented)

### Customization

You can customize the booking creation behavior by:

1. Modifying default values in `ProviderRequestController`
2. Updating tax and fee rates in `BookingIntegrationService`
3. Changing room selection logic
4. Customizing booking notes format

## Future Enhancements

1. **Flight Booking Integration**: Implement flight booking creation
2. **Transport Booking Integration**: Implement transport booking creation
3. **Payment Integration**: Automatically process payments for bookings
4. **Notification System**: Send booking confirmations to guests
5. **Calendar Integration**: Update availability calendars
6. **Inventory Management**: Update room/seat availability
7. **Commission Tracking**: Track and calculate commissions
8. **Reporting**: Analytics on service request to booking conversion rates