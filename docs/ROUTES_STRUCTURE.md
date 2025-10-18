# SeferEt Route Structure Documentation

## Overview
The SeferEt Laravel application uses a clean, organized route structure that separates concerns between different user types and functionalities.

## Route Files Structure

### 1. `routes/web.php` - Main Web Routes
- **Admin routes** (`/admin/*`) - Admin panel functionality
- **Customer routes** (`/customer/*`) - Customer authentication and account management  
- **Public routes** (`/*`) - Public facing pages (home, packages, hotels, etc.)
- **Legacy redirects** - Backward compatibility routes
- **B2B routes inclusion** - Includes `routes/b2b.php`

### 2. `routes/b2b.php` - B2B Partner Routes
- **B2B Authentication** (`/b2b/login`, `/b2b/register`) - Partner login/registration
- **Role-based partner routes** - Separated by partner type
- **API routes** for AJAX functionality

### 3. `routes/api.php` - API Routes
- **V1 API endpoints** (`/api/v1/*`) - REST API for mobile/external integrations
- **Authentication endpoints** - API login/logout/refresh
- **Service discovery** - B2B service integration endpoints

## B2B Route Structure Details

### Authentication Routes (Public Access)
```
GET|POST /b2b/login          - Partner login
GET|POST /b2b/register       - Partner registration  
GET      /b2b/pending        - Registration pending page
POST     /b2b/logout         - Partner logout
```

### Protected Routes (Authentication Required)

#### Main Dashboard
```
GET /b2b/dashboard           - Role-based dashboard redirect
```

#### Hotel Provider Routes (`role:hotel_provider`)
```
GET    /b2b/hotel-provider/dashboard                    - Hotel provider dashboard
GET    /b2b/hotel-provider/hotels                      - List all hotels
POST   /b2b/hotel-provider/hotels                      - Create new hotel
GET    /b2b/hotel-provider/hotels/create               - Hotel creation form
GET    /b2b/hotel-provider/hotels/{hotel}              - View hotel details
GET    /b2b/hotel-provider/hotels/{hotel}/edit         - Edit hotel form
PUT    /b2b/hotel-provider/hotels/{hotel}              - Update hotel
DELETE /b2b/hotel-provider/hotels/{hotel}              - Delete hotel
PATCH  /b2b/hotel-provider/hotels/{hotel}/toggle-status - Toggle hotel status

# Hotel sub-features
GET    /b2b/hotel-provider/hotels/{hotel}/rooms        - Hotel room management
GET    /b2b/hotel-provider/hotels/{hotel}/bookings     - Hotel booking history
GET    /b2b/hotel-provider/hotels/{hotel}/reviews      - Hotel reviews
GET    /b2b/hotel-provider/hotels/{hotel}/analytics    - Hotel analytics

# General provider features
GET    /b2b/hotel-provider/bookings                    - All provider bookings
GET    /b2b/hotel-provider/rates                       - Rate management
GET    /b2b/hotel-provider/availability                - Availability calendar
GET    /b2b/hotel-provider/reports                     - Provider reports
GET|PUT /b2b/hotel-provider/profile                    - Provider profile
```

#### Travel Agent Routes (`role:travel_agent`) - TODO: Create Controller
```
GET /b2b/travel-agent/dashboard     - Travel agent dashboard
GET /b2b/travel-agent/bookings      - Manage customer bookings
GET /b2b/travel-agent/customers     - Customer management
GET /b2b/travel-agent/packages      - Package management
GET /b2b/travel-agent/commissions   - Commission tracking
GET /b2b/travel-agent/reports       - Agent reports
GET /b2b/travel-agent/profile       - Agent profile
```

#### Transport Provider Routes (`role:transport_provider`) - TODO: Create Controller
```
GET /b2b/transport-provider/dashboard    - Transport provider dashboard
GET /b2b/transport-provider/vehicles     - Vehicle management
GET /b2b/transport-provider/bookings     - Transport bookings
GET /b2b/transport-provider/routes       - Route management
GET /b2b/transport-provider/drivers      - Driver management
GET /b2b/transport-provider/maintenance  - Vehicle maintenance
GET /b2b/transport-provider/reports      - Provider reports
GET /b2b/transport-provider/profile      - Provider profile
```

#### Shared B2B Routes (All Partner Types)
```
GET /b2b/notifications     - Partner notifications
GET /b2b/help             - Help and documentation
GET /b2b/settings         - Account settings
GET /b2b/packages         - Legacy package routes
GET /b2b/analytics        - General analytics
```

### API Routes (AJAX/DataTables)
```
GET   /api/b2b/hotel-provider/hotels/datatable         - Hotel DataTable data
PATCH /api/b2b/hotel-provider/hotels/{hotel}/quick-toggle - Quick status toggle
```

## Route Middleware

### Authentication Middleware
- `auth` - Requires user authentication
- `verified` - Requires email verification (where applicable)

### Authorization Middleware
- `role.redirect` - Redirects users based on their role
- `role:hotel_provider` - Restricts access to hotel providers only
- `role:travel_agent` - Restricts access to travel agents only
- `role:transport_provider` - Restricts access to transport providers only
- `role:admin` - Restricts access to administrators only

### API Middleware
- `auth:sanctum` - API authentication using Laravel Sanctum
- `web` - Web session-based authentication for AJAX routes

## Controllers Status

### ✅ Implemented Controllers
- `B2B\HotelController` - Complete hotel management functionality
- `Auth\B2BAuthController` - B2B authentication
- `Auth\B2BRegisterController` - B2B registration
- `B2B\DashboardController` - B2B dashboard functionality
- `Admin\UserModerationController` - Admin user management
- Various customer and admin controllers

### 🔄 TODO: Controllers to Create
- `B2B\TravelAgentController` - Travel agent functionality
- `B2B\TransportController` - Transport provider functionality
- Additional specialized controllers as needed

## Route Security

### Role-Based Access Control
Each partner type has isolated access to their specific functionality through role-based middleware.

### Route Protection
- All B2B routes require authentication except login/register
- Sensitive operations use PATCH/PUT/DELETE methods appropriately
- API routes use appropriate authentication mechanisms

## Usage Examples

### Hotel Provider Workflow
1. Register/Login via `/b2b/register` or `/b2b/login`
2. Access dashboard via `/b2b/dashboard` (redirects to hotel provider dashboard)
3. Manage hotels via `/b2b/hotel-provider/hotels/*`
4. View reports and analytics via provider-specific routes

### API Integration
```javascript
// Get hotel data for DataTable
fetch('/api/b2b/hotel-provider/hotels/datatable')

// Quick toggle hotel status
fetch('/api/b2b/hotel-provider/hotels/123/quick-toggle', {
    method: 'PATCH'
})
```

## Best Practices Implemented

1. **Separation of Concerns** - Each partner type has isolated route groups
2. **RESTful Design** - Hotel management follows RESTful conventions
3. **Middleware Protection** - Appropriate role-based access control
4. **Nested Resources** - Hotel sub-features properly nested
5. **API Separation** - AJAX routes separated from web routes
6. **Backward Compatibility** - Legacy routes maintained where needed
7. **Clean Organization** - Routes logically grouped and documented

This structure provides a scalable foundation for adding new partner types and features while maintaining security and organization.
