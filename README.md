# SeferEt Umrah Booking System - Laravel Backend

A comprehensive multi-platform Umrah travel booking system built with Laravel 10+. This monolithic application serves as both the API backend and hosts the web interfaces for customers (B2C), partners (B2B), and administrators.

## Architecture Overview

### Backend Components
- **API Backend**: Laravel 10+ with Sanctum authentication
- **B2C Customer Website**: Laravel Blade/Inertia.js frontend
- **B2B Partner Portal**: Dedicated partner management interface
- **Admin Dashboard**: System administration interface

### User Roles
- **Customer**: End users who book Umrah packages
- **Partner**: Travel agencies/operators who create and manage packages
- **Admin**: System administrators with full access

## Project Structure

```
SeferEt-Laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/          # API Controllers (v1)
│   │   │   │   ├── AuthController.php
│   │   │   │   └── BaseApiController.php
│   │   │   └── Web/             # Web Controllers
│   │   │       ├── B2C/         # Customer website controllers
│   │   │       ├── B2B/         # Partner portal controllers
│   │   │       └── Admin/       # Admin dashboard controllers
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   ├── Requests/Api/V1/     # API Form Requests
│   │   └── Resources/Api/V1/    # API Resources
│   ├── Models/
│   │   └── User.php
│   └── Services/                # Business Logic Services
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000000_create_users_table.php
│   └── seeders/
├── resources/
│   ├── js/
│   │   └── Pages/               # Inertia.js pages
│   │       ├── B2C/             # Customer pages
│   │       ├── B2B/             # Partner pages
│   │       └── Admin/           # Admin pages
│   └── views/                   # Blade templates
│       ├── b2c/
│       ├── b2b/
│       └── admin/
├── routes/
│   ├── api.php                  # API routes with versioning
│   └── web.php                  # Web routes for all interfaces
└── tests/
    ├── Feature/Api/V1/          # API feature tests
    └── Unit/                    # Unit tests
```

## API Endpoints

### Public Endpoints
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User login
- `GET /api/v1/health` - Health check

### Protected Endpoints
- `POST /api/v1/auth/logout` - User logout
- `POST /api/v1/auth/refresh` - Token refresh
- `GET /api/v1/auth/me` - Get current user
- `GET /api/v1/profile` - Get user profile

### Role-Specific Endpoints
- `GET /api/v1/customer/dashboard` - Customer dashboard data
- `GET /api/v1/partner/dashboard` - Partner dashboard data
- `GET /api/v1/admin/dashboard` - Admin dashboard data

## Installation & Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url> SeferEt-Laravel
   cd SeferEt-Laravel
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Install Sanctum**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Web Interface URLs

- **B2C Customer Website**: `http://localhost:8000/`
- **B2B Partner Portal**: `http://localhost:8000/partner/`
- **Admin Dashboard**: `http://localhost:8000/admin/`

## API Testing

You can test the API endpoints using tools like Postman or curl:

```bash
# Health check
curl http://localhost:8000/api/v1/health

# Register a new customer
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "customer"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

## Authentication

This system uses Laravel Sanctum for API authentication:
- **Token-based authentication** for API requests (mobile app, external integrations)
- **Session-based authentication** for web interfaces
- **Role-based access control** with middleware protection

## Development Guidelines

1. **API Versioning**: All API endpoints are versioned (currently v1)
2. **Consistent Responses**: All API responses follow the same JSON structure
3. **Role-based Access**: Use middleware to protect routes based on user roles
4. **Single Source of Truth**: The Laravel backend is the authoritative data source

## Next Steps

1. Implement package and booking models
2. Add payment gateway integration
3. Implement email notifications
4. Add comprehensive testing
5. Set up CI/CD pipeline
