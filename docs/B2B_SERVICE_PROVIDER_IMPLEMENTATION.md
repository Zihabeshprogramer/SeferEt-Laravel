# B2B Service Provider Implementation

## Overview
This document outlines the implementation of B2B service provider functionality for the SeferEt platform, allowing hotel and transportation service providers to offer their services to package creators.

## New User Types
The system now supports three types of B2B users:
- **Partner**: Original travel package creators
- **Hotel Provider**: Hotel and accommodation service providers
- **Transport Provider**: Transportation service providers

## Database Schema Changes

### Users Table Updates
Added new fields to support service providers:
- `service_type`: Type of service offered (hotel, transport, etc.)
- `service_categories`: JSON array of service categories
- `coverage_areas`: JSON array of coverage areas
- `certification_number`: Business license/certification number
- `api_credentials`: JSON for third-party API integration
- `commission_rate`: Commission rate for services
- `is_api_enabled`: Whether API integration is enabled

### New Tables

#### 1. service_offers
Central table for all service offers with polymorphic relationships:
- `provider_id`: Foreign key to users table
- `name`: Service offer name
- `description`: Service description
- `specifications`: JSON service specifications
- `base_price`: Base price for the service
- `currency`: Price currency
- `pricing_rules`: JSON pricing rules for dynamic pricing
- `max_capacity`: Maximum capacity
- `availability`: JSON availability calendar
- `status`: Offer status (active, inactive, draft, suspended)
- `terms_conditions`: JSON terms and conditions
- `cancellation_policy`: JSON cancellation policy
- `is_api_integrated`: API integration flag
- `api_mapping`: JSON API mapping configuration
- Polymorphic fields: `service_type` and `service_id`

#### 2. hotel_services
Specific hotel service details:
- `provider_id`: Foreign key to users table
- `hotel_name`: Hotel name
- `address`, `city`, `country`: Location details
- `latitude`, `longitude`: GPS coordinates
- `star_rating`: Hotel star rating (1-5)
- `amenities`: JSON array of hotel amenities
- `room_types`: JSON array of available room types
- `check_in_time`, `check_out_time`: Check-in/out times
- `policies`: JSON hotel policies
- `contact_info`: JSON contact information
- `images`: JSON array of hotel images
- `is_active`: Active status

#### 3. transport_services
Specific transport service details:
- `provider_id`: Foreign key to users table
- `service_name`: Service name
- `transport_type`: Type (bus, car, van, taxi, shuttle, flight)
- `route_type`: Route type (airport_transfer, city_transport, intercity, pilgrimage_sites)
- `routes`: JSON available routes
- `vehicle_types`: JSON vehicle types
- `specifications`: JSON vehicle specifications
- `max_passengers`: Maximum passenger capacity
- `pickup_locations`, `dropoff_locations`: JSON location arrays
- `operating_hours`: JSON operating schedule
- `policies`: JSON service policies
- `contact_info`: JSON contact information
- `images`: JSON service/vehicle images
- `is_active`: Active status

#### 4. packages
Package management with B2B integration:
- `creator_id`: Foreign key to users (partner)
- `name`: Package name
- `description`: Package description
- `type`: Package type (economy, standard, premium, luxury)
- `duration`: Duration in days
- `base_price`: Base package price
- `currency`: Price currency
- `inclusions`, `exclusions`: JSON arrays
- `itinerary`: JSON day-by-day itinerary
- `status`: Package status
- `uses_b2b_services`: Whether package uses B2B services
- `service_preferences`: JSON service preferences

#### 5. package_service_offers
Pivot table linking packages with service offers:
- `package_id`: Foreign key to packages
- `service_offer_id`: Foreign key to service_offers
- `is_required`: Whether service is required for package
- `markup_percentage`: Markup percentage on service price
- `custom_price`: Custom price override
- `integration_config`: JSON integration configuration

## Models Created

### 1. ServiceOffer
- Central polymorphic model for all service offers
- Relationships with providers and specific service types
- Status management and pricing functionality

### 2. HotelService
- Hotel-specific service model
- Location-based queries and amenity management
- Relationship with service offers through polymorphism

### 3. TransportService
- Transport-specific service model
- Route and vehicle type management
- Capacity and scheduling functionality

### 4. Package
- Package management with B2B service integration
- Price calculation including B2B services
- Optional B2B service integration flag

### 5. PackageServiceOffer
- Pivot model for package-service relationships
- Custom pricing and markup management

## Controllers Created

### 1. HotelProviderController
- CRUD operations for hotel services
- Hotel provider dashboard and statistics
- Status management (active/inactive)

### 2. TransportProviderController ✅ COMPLETE
- **CRUD operations for transport services**: Full implementation with create, read, update, delete
- **Transport provider dashboard**: Real-time statistics and service management
- **Service type and route management**: Dynamic route creation with duration tracking
- **Vehicle management**: Vehicle types and specifications
- **Location management**: Pickup/dropoff location handling
- **Status management**: Activate/deactivate services
- **Operating hours**: Time-based availability management

### 3. ServiceOfferController
- Service offer management for all provider types
- Offer creation, editing, and status management

### 4. ServiceDiscoveryController (API)
- API endpoints for service discovery
- Filtering and search functionality
- Integration endpoints for package creators

## Authentication & Authorization Updates

### Updated Components:
1. **B2BAuthController**: Now handles all B2B user types (partners, hotel providers, transport providers)
2. **B2BRegisterController**: Extended registration for different service provider types
3. **RoleRedirect Middleware**: Updated to handle new user roles and redirect appropriately

### Registration Process:
- Users select their business type during registration
- Service providers provide additional information (service type, coverage areas, certification)
- All B2B accounts require admin approval before activation

## API Endpoints

### Service Discovery API (Protected Routes)
- `GET /api/v1/b2b/service-offers` - Get all available service offers
- `GET /api/v1/b2b/service-offers/{id}` - Get specific service offer
- `GET /api/v1/b2b/hotel-services` - Get hotel services
- `GET /api/v1/b2b/transport-services` - Get transport services  
- `GET /api/v1/b2b/service-providers` - Get service providers

### Filtering Options:
- Service type (hotel/transport)
- Location/coverage area
- Price range
- Star rating (for hotels)
- Transport type
- Route type

## Web Routes

### Hotel Provider Routes (b2b/hotel-provider/):
- `GET /` - Dashboard with hotel list and statistics
- `GET /create` - Create new hotel form
- `POST /` - Store new hotel
- `GET /{hotel}` - View hotel details
- `GET /{hotel}/edit` - Edit hotel form
- `PUT /{hotel}` - Update hotel
- `DELETE /{hotel}` - Delete hotel
- `PATCH /{hotel}/toggle-status` - Toggle hotel status

### Transport Provider Routes (b2b/transport-provider/) ✅ IMPLEMENTED:
- `GET /dashboard` - Dashboard with service list and statistics
- `GET /services/create` - Create new transport service form
- `POST /services` - Store new transport service
- `GET /services/{service}` - View transport service details
- `GET /services/{service}/edit` - Edit transport service form
- `PUT /services/{service}` - Update transport service
- `DELETE /services/{service}` - Delete transport service
- `PATCH /services/{service}/toggle-status` - Toggle service status
- Additional routes: vehicles, bookings, routes, drivers, maintenance, reports, profile

### Service Offer Routes (b2b/service-offers/):
- Manage service offers for all provider types
- Create pricing and availability rules

## Key Features

### 1. Role-Based Access Control
- Different user types have access to different functionalities
- Service providers can only manage their own services
- Partners can discover and integrate available services

### 2. Optional B2B Integration
- Package creators can choose whether to use B2B services
- Services can be marked as required or optional for packages
- Custom pricing and markup configuration

### 3. Service Discovery
- Partners can browse available services when creating packages
- Filtering by location, price, type, and other criteria
- Real-time availability checking

### 4. Flexible Pricing
- Base pricing from service providers
- Markup percentage configuration
- Custom price overrides for specific packages

### 5. API Integration Ready
- Service providers can integrate their own APIs
- Mapping configuration for external systems
- Real-time inventory and pricing updates

## Next Steps

### 1. View Templates ✅ TRANSPORT COMPLETE
**Completed for Transport Services:**
- ✅ Transport service creation form with dynamic route management
- ✅ Transport service editing form with existing data pre-population
- ✅ Transport service detailed view with action sidebar
- ✅ Transport provider dashboard with real statistics

**Still Needed:**
- Service offer management interfaces
- Package creation with service selection

### 2. Service Integration Interface
Build the interface for package creators to:
- Browse available services
- Select and configure services for packages
- Set pricing and requirements

### 3. API Enhancement
Extend API functionality for:
- Real-time booking and availability
- Webhook integrations for external systems
- Advanced filtering and search

### 4. Admin Management
Create admin interfaces to:
- Approve service providers
- Monitor service quality
- Manage commission rates

### 5. Reporting & Analytics
Implement analytics for:
- Service usage statistics
- Revenue tracking for service providers
- Performance metrics

## Usage Example

### For Hotel Providers:
1. Register as "Hotel Service Provider"
2. Add hotel details (location, amenities, etc.)
3. Create service offers with pricing
4. Package creators can discover and integrate these offers

### For Transport Providers:
1. Register as "Transport Service Provider"
2. Add transport services (vehicle types, routes)
3. Create offers with route-specific pricing
4. Package creators can add transport to their packages

### For Package Creators:
1. Create package with basic details
2. Optionally enable B2B services
3. Browse and select available hotel/transport services
4. Set markup and requirements for selected services
5. Publish complete package

This implementation provides a robust foundation for B2B service provider integration while maintaining the existing partner functionality.
