# Service Request Management API Documentation

## Overview

The Service Request Management API provides comprehensive endpoints for managing service requests between travel agents and service providers in the SeferEt platform. This system enables automated approval workflows, real-time notifications, and atomic transaction handling.

## Base URL

```
/api/v1
```

## Authentication

All endpoints require authentication using Laravel Sanctum tokens.

```
Authorization: Bearer {your-token-here}
```

## Content Type

All requests and responses use JSON format:

```
Content-Type: application/json
Accept: application/json
```

## Response Format

All API responses follow a consistent structure:

```json
{
  "success": boolean,
  "message": string,
  "data": object|array,
  "errors": object (only on validation errors)
}
```

## Status Codes

- `200` - OK: Successful request
- `201` - Created: Resource created successfully
- `400` - Bad Request: Invalid request parameters
- `401` - Unauthorized: Authentication required
- `403` - Forbidden: Insufficient permissions
- `404` - Not Found: Resource not found
- `422` - Unprocessable Entity: Validation errors
- `500` - Internal Server Error: Server error

---

## Service Request Endpoints

### 1. Create Service Request

**POST** `/service-requests`

Creates a new service request from agent to provider.

#### Request Body

```json
{
  "package_id": integer (required) - Package ID (can be draft or published package),
  "provider_id": integer (required),
  "provider_type": string (required) - enum: "hotel", "flight", "transport",
  "item_id": integer (required),
  "requested_quantity": integer (optional) - Will be auto-calculated from package data if not provided,
  "start_date": string (optional) - Will be taken from package/draft data if not provided,
  "end_date": string (optional) - Will be taken from package/draft data if not provided,
  "guest_count": integer (optional) - Will be taken from package data if not provided,
  "special_requirements": string (optional, max: 1000),
  "expires_in_hours": integer (optional, min: 1, max: 168)
}
```

#### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "status": "pending",
    "provider_type": "hotel",
    "requested_quantity": 2,
    "start_date": "2024-01-15",
    "end_date": "2024-01-18",
    "guest_count": 4,
    "special_requirements": "Twin beds required",
    "expires_at": "2024-01-14T15:00:00.000000Z",
    "created_at": "2024-01-13T15:00:00.000000Z",
    "package": {
      "id": 1,
      "name": "Umrah Package Premium"
    },
    "agent": {
      "id": 1,
      "name": "John Agent",
      "email": "agent@example.com"
    },
    "provider": {
      "id": 2,
      "name": "Grand Hotel",
      "email": "provider@example.com"
    }
  },
  "message": "Service request created successfully"
}
```

#### Permissions

- Only travel agents can create service requests
- Agents can only create requests for their own packages

---

### 2. Get Agent's Service Requests

**GET** `/service-requests/agent`

Retrieves all service requests created by the authenticated agent.

#### Query Parameters

```
package_id: integer (optional) - Filter by package ID
status: string (optional) - enum: "pending", "approved", "rejected", "expired", "cancelled"
provider_type: string (optional) - enum: "hotel", "flight", "transport"
```

#### Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "status": "pending",
      "provider_type": "hotel",
      "requested_quantity": 2,
      "start_date": "2024-01-15",
      "end_date": "2024-01-18",
      "created_at": "2024-01-13T15:00:00.000000Z",
      "package": {
        "id": 1,
        "name": "Umrah Package Premium"
      },
      "provider": {
        "id": 2,
        "name": "Grand Hotel",
        "email": "provider@example.com"
      },
      "allocations": []
    }
  ]
}
```

---

### 3. Get Provider's Service Requests

**GET** `/service-requests/provider`

Retrieves all service requests assigned to the authenticated provider.

#### Query Parameters

```
status: string (optional) - enum: "pending", "approved", "rejected", "expired", "cancelled"
provider_type: string (optional) - enum: "hotel", "flight", "transport"
sort_by: string (optional) - enum: "created_at", "expires_at", "status"
sort_order: string (optional) - enum: "asc", "desc"
per_page: integer (optional, min: 1, max: 100)
```

#### Response

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "status": "pending",
        "provider_type": "hotel",
        "requested_quantity": 2,
        "start_date": "2024-01-15",
        "end_date": "2024-01-18",
        "expires_at": "2024-01-14T15:00:00.000000Z",
        "created_at": "2024-01-13T15:00:00.000000Z",
        "package": {
          "id": 1,
          "name": "Umrah Package Premium"
        },
        "agent": {
          "id": 1,
          "name": "John Agent",
          "email": "agent@example.com"
        },
        "allocations": []
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  },
  "summary": {
    "pending_count": 5,
    "expiring_soon_count": 3
  }
}
```

---

### 4. Get Specific Service Request

**GET** `/service-requests/{id}`

Retrieves detailed information about a specific service request.

#### Parameters

- `id`: Service request ID

#### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "status": "pending",
    "provider_type": "hotel",
    "requested_quantity": 2,
    "start_date": "2024-01-15",
    "end_date": "2024-01-18",
    "guest_count": 4,
    "special_requirements": "Twin beds required",
    "notes": "Urgent booking needed",
    "expires_at": "2024-01-14T15:00:00.000000Z",
    "created_at": "2024-01-13T15:00:00.000000Z",
    "package": {
      "id": 1,
      "name": "Umrah Package Premium"
    },
    "agent": {
      "id": 1,
      "name": "John Agent",
      "email": "agent@example.com"
    },
    "provider": {
      "id": 2,
      "name": "Grand Hotel",
      "email": "provider@example.com"
    },
    "allocations": [],
    "communications": []
  }
}
```

#### Permissions

- Agents can view their own requests
- Providers can view requests assigned to them
- Admins can view all requests

---

### 5. Approve Service Request

**PUT** `/service-requests/{id}/approve`

Approves a pending service request (Provider action).

#### Request Body

```json
{
  "notes": string (optional, max: 1000),
  "pricing": {
    "unit_price": number (optional, min: 0),
    "commission_rate": number (optional, min: 0, max: 100),
    "total_price": number (optional, min: 0)
  },
  "terms_conditions": string (optional, max: 2000)
}
```

#### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "status": "approved",
    "approved_at": "2024-01-14T10:30:00.000000Z",
    "approval_notes": "Confirmed booking with twin beds",
    "package": {
      "id": 1,
      "name": "Umrah Package Premium"
    },
    "agent": {
      "id": 1,
      "name": "John Agent"
    },
    "provider": {
      "id": 2,
      "name": "Grand Hotel"
    },
    "allocations": []
  },
  "message": "Service request approved successfully"
}
```

#### Permissions

- Only the assigned provider can approve their requests
- Request must be in "pending" status

---

### 6. Reject Service Request

**PUT** `/service-requests/{id}/reject`

Rejects a pending service request (Provider action).

#### Request Body

```json
{
  "rejection_reason": string (required, max: 1000),
  "alternative_dates": [
    {
      "start_date": string (format: Y-m-d, after: today),
      "end_date": string (format: Y-m-d, after: start_date)
    }
  ] (optional)
}
```

#### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "status": "rejected",
    "rejected_at": "2024-01-14T10:30:00.000000Z",
    "rejection_reason": "Fully booked for those dates",
    "package": {
      "id": 1,
      "name": "Umrah Package Premium"
    },
    "agent": {
      "id": 1,
      "name": "John Agent"
    },
    "provider": {
      "id": 2,
      "name": "Grand Hotel"
    }
  },
  "message": "Service request rejected"
}
```

#### Permissions

- Only the assigned provider can reject their requests
- Request must be in "pending" status

---

### 7. Cancel Service Request

**PUT** `/service-requests/{id}/cancel`

Cancels a service request (Agent action).

#### Request Body

```json
{
  "cancellation_reason": string (required, max: 1000)
}
```

#### Response

```json
{
  "success": true,
  "message": "Service request cancelled"
}
```

#### Permissions

- Only the requesting agent can cancel their requests
- Request must be in "pending" or "approved" status

---

### 8. Get Package Approval Status

**GET** `/packages/{id}/approval-status`

Gets the overall approval status for all service requests in a package.

#### Response

```json
{
  "success": true,
  "data": {
    "package_id": 1,
    "total_requests": 5,
    "approved": 3,
    "pending": 1,
    "rejected": 1,
    "expired": 0,
    "cancelled": 0,
    "can_proceed": false,
    "blocking_requests": [
      {
        "id": 1,
        "status": "pending",
        "provider": {
          "name": "Grand Hotel"
        }
      }
    ]
  }
}
```

#### Permissions

- Only the package owner (agent) can view approval status

---

## Real-time Events

The system broadcasts real-time events via WebSocket connections using Laravel Echo and Pusher.

### Event Channels

#### Private Channels

- `user.{userId}` - User-specific notifications
- `package.{packageId}` - Package-specific updates
- `service-requests` - Admin-level notifications

### Event Types

#### service-request.created

Broadcasted when a new service request is created.

```json
{
  "service_request": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "status": "pending",
    // ... other fields
  },
  "package": { /* package data */ },
  "agent": { /* agent data */ },
  "provider": { /* provider data */ },
  "notification": {
    "title": "Service Request Created",
    "message": "New service request from John Agent",
    "type": "info",
    "icon": "fas fa-plus"
  }
}
```

#### service-request.approved

Broadcasted when a service request is approved.

```json
{
  "service_request": { /* request data */ },
  "package": { /* package data */ },
  "agent": { /* agent data */ },
  "provider": { /* provider data */ },
  "allocations": [ /* allocation data */ ],
  "notification": {
    "title": "Service Request Approved",
    "message": "Your service request has been approved by Grand Hotel",
    "type": "success",
    "icon": "fas fa-check-circle"
  }
}
```

#### service-request.rejected

Broadcasted when a service request is rejected.

```json
{
  "service_request": { /* request data */ },
  "package": { /* package data */ },
  "agent": { /* agent data */ },
  "provider": { /* provider data */ },
  "notification": {
    "title": "Service Request Rejected",
    "message": "Your service request was rejected by Grand Hotel: Fully booked",
    "type": "error",
    "icon": "fas fa-times-circle"
  }
}
```

#### service-request.expired

Broadcasted when a service request expires.

```json
{
  "service_request": { /* request data */ },
  "package": { /* package data */ },
  "agent": { /* agent data */ },
  "provider": { /* provider data */ },
  "notification": {
    "title": "Service Request Expired",
    "message": "Service request from John Agent has expired due to no response",
    "type": "warning",
    "icon": "fas fa-clock"
  }
}
```

---

## Error Handling

### Validation Errors (422)

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "package_id": ["The package id field is required."],
    "provider_id": ["The provider id must be a number."],
    "start_date": ["The start date must be after today."]
  }
}
```

### Authorization Errors (403)

```json
{
  "success": false,
  "message": "You can only create requests for your own packages"
}
```

### Not Found Errors (404)

```json
{
  "success": false,
  "message": "Service request not found"
}
```

### Server Errors (500)

```json
{
  "success": false,
  "message": "An error occurred while processing your request"
}
```

---

## Rate Limiting

API endpoints are subject to rate limiting:

- **General endpoints**: 60 requests per minute per user
- **Create operations**: 10 requests per minute per user
- **Broadcasting events**: No rate limit (handled by queue system)

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1609459200
```

---

## Pagination

List endpoints support pagination using Laravel's standard pagination format:

### Query Parameters

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

### Response Format

```json
{
  "success": true,
  "data": {
    "data": [ /* items */ ],
    "current_page": 1,
    "first_page_url": "http://api.example.com/service-requests?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://api.example.com/service-requests?page=3",
    "next_page_url": "http://api.example.com/service-requests?page=2",
    "path": "http://api.example.com/service-requests",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 45
  }
}
```

---

## Status Workflow

Service requests follow a specific status workflow:

```
pending → approved (by provider)
        → rejected (by provider)
        → cancelled (by agent)
        → expired (by system)
```

### Status Descriptions

- **pending**: Request created, awaiting provider response
- **approved**: Provider has approved the request and may have created allocations
- **rejected**: Provider has declined the request with reason
- **cancelled**: Agent has cancelled the request
- **expired**: Request expired due to no provider response within time limit

### Valid Status Transitions

| From     | To        | Who Can Change | Conditions |
|----------|-----------|----------------|------------|
| pending  | approved  | Provider       | Must be assigned provider |
| pending  | rejected  | Provider       | Must be assigned provider |
| pending  | cancelled | Agent          | Must be request owner |
| pending  | expired   | System         | Automatic when expires_at passed |
| approved | cancelled | Agent          | Must be request owner |

---

## Webhooks

For integrations requiring server-to-server notifications, webhook endpoints can be configured to receive service request events.

### Webhook Payload

All webhooks send a POST request with the following structure:

```json
{
  "event": "service_request.approved",
  "data": {
    "service_request": { /* full request data */ },
    "timestamp": "2024-01-14T10:30:00.000000Z"
  },
  "signature": "sha256=abc123..." // HMAC signature for verification
}
```

### Supported Events

- `service_request.created`
- `service_request.approved`
- `service_request.rejected`
- `service_request.expired`
- `service_request.cancelled`

---

## Testing

Use the following test credentials in the staging environment:

### Test Users

**Travel Agent**
- Email: `agent@test.com`
- Password: `password`
- Role: `travel_agent`

**Hotel Provider**
- Email: `hotel@test.com`
- Password: `password`
- Role: `hotel_provider`

**Admin**
- Email: `admin@test.com`
- Password: `password`
- Role: `admin`

### Test Data

The staging environment includes sample packages and service requests for testing all scenarios.

### Postman Collection

A complete Postman collection is available at: `/docs/postman/service-requests.json`

---

## Changelog

### v1.0.0 (2024-01-15)

- Initial release
- Complete CRUD operations for service requests
- Real-time broadcasting integration
- Email notification system
- Comprehensive test coverage
- API documentation