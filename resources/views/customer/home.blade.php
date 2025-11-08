@extends('layouts.customer')

@section('title', 'SeferEt - Your Spiritual Journey Begins Here')

@section('content')
    <!-- Hero Section with Flight Search -->
    <div class="hero-section">
        <div class="hero-background">
            <div class="container-fluid">
                <div class="row min-vh-100 align-items-center">
                    <div class="col-12">
                        <!-- Hero Content -->
                        <div class="text-center text-white mb-5">
                            <h1 class="hero-title display-4 fw-bold mb-3">
                                <img src="{{ asset('images/logo/seferet-logo-white.png') }}" alt="SeferEt" style="height: 124px; width: auto; margin-right: 16px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                                Your Spiritual Journey Awaits
                            </h1>
                            <p class="hero-subtitle lead mb-4 opacity-90">
                                Discover the perfect Umrah package for your sacred journey to Makkah and Madinah
                            </p>
                        </div>

                        <!-- Flight Search Card -->
                        <div class="search-card mx-auto">
                            <x-customer.card variant="elevated" elevation="xl" padding="lg" class="search-form-card">
                                <div class="search-tabs mb-4">
                                    <ul class="nav nav-pills justify-content-center" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#packages-search">
                                                <i class="fas fa-suitcase-rolling me-2"></i>Umrah Packages
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#flights-search">
                                                <i class="fas fa-plane me-2"></i>Flights Only
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#hotels-search">
                                                <i class="fas fa-hotel me-2"></i>Hotels Only
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-content">
                                    <!-- Umrah Packages Search -->
                                    <div class="tab-pane fade show active" id="packages-search">
                                        <form class="search-form" action="{{ route('packages') }}" method="GET">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Departure From</label>
                                                    <div class="position-relative">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   id="package-departure-input"
                                                                   name="departure" 
                                                                   placeholder="Select departure city"
                                                                   autocomplete="off">
                                                        </div>
                                                        <div id="departure-city-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                                                    </div>
                                                    <small class="text-muted">Start typing to search cities</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Travel Date</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        <input type="date" class="form-control" name="departure_date">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Duration</label>
                                                    <select class="form-select" name="duration">
                                                        <option value="">Any Duration</option>
                                                        <option value="7">7 Days</option>
                                                        <option value="10">10 Days</option>
                                                        <option value="14">14 Days</option>
                                                        <option value="21">21 Days</option>
                                                        <option value="30">30+ Days</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-2">
                                                <div class="col-md-3">
                                                    <label class="form-label">Travelers</label>
                                                    <select class="form-select" name="travelers">
                                                        <option value="1">1 Person</option>
                                                        <option value="2">2 People</option>
                                                        <option value="3">3 People</option>
                                                        <option value="4">4 People</option>
                                                        <option value="5+">5+ People</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Budget Range</label>
                                                    <select class="form-select" name="budget">
                                                        <option value="">Any Budget</option>
                                                        <option value="0-1000">$0 - $1,000</option>
                                                        <option value="1000-2500">$1,000 - $2,500</option>
                                                        <option value="2500-5000">$2,500 - $5,000</option>
                                                        <option value="5000+">$5,000+</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 d-flex align-items-end">
                                                    <x-customer.button type="submit" variant="primary" size="lg" fullWidth="true" class="search-btn">
                                                        <i class="fas fa-search me-2"></i>Search Umrah Packages
                                                    </x-customer.button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Flights Only Search -->
                                    <div class="tab-pane fade" id="flights-search">
                                        <form class="search-form" action="{{ route('flights') }}" method="GET">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">From</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-plane-departure"></i></span>
                                                        <input type="text" class="form-control" name="origin" placeholder="Airport code (e.g., JFK)" maxlength="3" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">To</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-plane-arrival"></i></span>
                                                        <input type="text" class="form-control" name="destination" placeholder="Airport code" maxlength="3" value="JED" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Departure</label>
                                                    <input type="date" class="form-control" name="departure_date" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Return</label>
                                                    <input type="date" class="form-control" name="return_date">
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-2">
                                                <div class="col-md-4">
                                                    <label class="form-label">Passengers</label>
                                                    <select class="form-select" name="adults">
                                                        <option value="1">1 Passenger</option>
                                                        <option value="2">2 Passengers</option>
                                                        <option value="3">3 Passengers</option>
                                                        <option value="4">4 Passengers</option>
                                                        <option value="5">5 Passengers</option>
                                                        <option value="6">6 Passengers</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Class</label>
                                                    <select class="form-select" name="travel_class">
                                                        <option value="ECONOMY">Economy</option>
                                                        <option value="PREMIUM_ECONOMY">Premium Economy</option>
                                                        <option value="BUSINESS">Business</option>
                                                        <option value="FIRST">First Class</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end">
                                                    <x-customer.button type="submit" variant="primary" size="lg" fullWidth="true">
                                                        <i class="fas fa-search me-2"></i>Search Flights
                                                    </x-customer.button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Hotels Only Search -->
                                    <div class="tab-pane fade" id="hotels-search">
                                        <form class="search-form" action="{{ route('hotels') }}" method="GET" id="hotel-search-form">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Destination <span class="text-danger">*</span></label>
                                                    <div class="position-relative">
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="hotel-destination-input"
                                                               name="location_display" 
                                                               placeholder="Type city name (e.g., Makkah, Dubai)..."
                                                               autocomplete="off"
                                                               required>
                                                        <input type="hidden" name="location" id="hotel-location-code" required>
                                                        <div id="city-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                                                    </div>
                                                    <small class="text-muted">Start typing to search cities</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Check-in <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="check_in" required min="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Check-out <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="check_out" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Rooms</label>
                                                    <select class="form-select" name="rooms">
                                                        <option value="1">1 Room</option>
                                                        <option value="2">2 Rooms</option>
                                                        <option value="3">3 Rooms</option>
                                                        <option value="4">4 Rooms</option>
                                                        <option value="5">5+ Rooms</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-2">
                                                <div class="col-md-4">
                                                    <label class="form-label">Guests</label>
                                                    <select class="form-select" name="guests">
                                                        <option value="1">1 Guest</option>
                                                        <option value="2">2 Guests</option>
                                                        <option value="3">3 Guests</option>
                                                        <option value="4">4 Guests</option>
                                                        <option value="5">5+ Guests</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Star Rating</label>
                                                    <select class="form-select" name="stars">
                                                        <option value="">Any Rating</option>
                                                        <option value="3">3 Stars+</option>
                                                        <option value="4">4 Stars+</option>
                                                        <option value="5">5 Stars Only</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end">
                                                    <x-customer.button type="submit" variant="primary" size="lg" fullWidth="true">
                                                        <i class="fas fa-search me-2"></i>Search Hotels
                                                    </x-customer.button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="stats-section py-5">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" class="stat-card text-center">
                        <div class="stat-icon text-success mb-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <h3 class="stat-number text-primary">{{ number_format($stats['happyCustomers']) }}+</h3>
                        <p class="stat-label text-muted mb-0">Happy Pilgrims</p>
                    </x-customer.card>
                </div>
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" class="stat-card text-center">
                        <div class="stat-icon text-info mb-3">
                            <i class="fas fa-plane fa-2x"></i>
                        </div>
                        <h3 class="stat-number text-primary">{{ number_format($stats['successfulTrips']) }}+</h3>
                        <p class="stat-label text-muted mb-0">Successful Journeys</p>
                    </x-customer.card>
                </div>
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" class="stat-card text-center">
                        <div class="stat-icon text-warning mb-3">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                        <h3 class="stat-number text-primary">{{ $stats['partnerCompanies'] }}+</h3>
                        <p class="stat-label text-muted mb-0">Trusted Partners</p>
                    </x-customer.card>
                </div>
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" class="stat-card text-center">
                        <div class="stat-icon text-primary mb-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                        <h3 class="stat-number text-primary">{{ $stats['yearsOfService'] }}+</h3>
                        <p class="stat-label text-muted mb-0">Years of Excellence</p>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Packages Section -->
    <div class="featured-packages-section py-5">
        <div class="container-fluid">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-star text-warning me-3"></i>
                    Featured Umrah Packages
                </h2>
                <p class="section-subtitle text-muted">Handpicked packages for your spiritual journey</p>
            </div>

            @if(count($featuredPackages) > 0)
                <div class="row g-4">
                    @foreach($featuredPackages as $package)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <x-customer.card variant="elevated" elevation="md" hover="true" class="package-card h-100">
                            <div class="package-image">
                                @if(isset($package->main_image) && $package->main_image)
                                    <img src="{{ $package->main_image }}" alt="{{ $package->name }}" class="card-img-top">
                                @else
                                    <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop" alt="{{ $package->name }}" class="card-img-top">
                                @endif
                                <div class="package-badges">
                                    <span class="badge bg-success">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="package-header mb-3">
                                    <h5 class="package-title">{{ $package->name }}</h5>
                                    <div class="package-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= round($package->average_rating ?? 4.5) ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                        <span class="rating-text text-muted ms-1">({{ number_format($package->average_rating ?? 4.5, 1) }})</span>
                                    </div>
                                </div>
                                
                                <p class="package-description text-muted flex-grow-1">
                                    {{ Str::limit($package->description, 100) }}
                                </p>
                                
                                <div class="package-details mb-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-4">
                                            <div class="detail-item">
                                                <i class="fas fa-calendar text-primary"></i>
                                                <small class="d-block text-muted">{{ $package->duration }} Days</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="detail-item">
                                                <i class="fas fa-users text-info"></i>
                                                <small class="d-block text-muted">2-4 People</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt text-success"></i>
                                                <small class="d-block text-muted">Makkah + Madinah</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="package-price-section mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <span class="price-label text-muted">Starting from</span>
                                            <div class="package-price">
                                                <h4 class="text-primary mb-0">${{ number_format($package->base_price, 0) }}</h4>
                                                <small class="text-muted">per person</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="package-actions d-grid gap-2">
                                        @auth
                                            <x-customer.button href="{{ route('packages.details', $package->slug ?? $package->id) }}" variant="primary" size="md" fullWidth="true">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </x-customer.button>
                                            <x-customer.button href="#" variant="outline-secondary" size="sm" fullWidth="true">
                                                <i class="fas fa-shopping-cart me-2"></i>Book Now
                                            </x-customer.button>
                                        @else
                                            <x-customer.button href="{{ route('packages.details', $package->slug ?? $package->id) }}" variant="outline-primary" size="md" fullWidth="true">
                                                <i class="fas fa-info-circle me-2"></i>View Details
                                            </x-customer.button>
                                            <x-customer.button href="{{ route('customer.login') }}" variant="primary" size="md" fullWidth="true">
                                                <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                                            </x-customer.button>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </x-customer.card>
                    </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-5">
                    <x-customer.button href="{{ route('packages') }}" variant="outline-primary" size="lg">
                        <i class="fas fa-eye me-2"></i>View All Packages
                    </x-customer.button>
                </div>
            @else
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-4">
                        <img src="{{ asset('images/logo/seferet-logo.png') }}" alt="SeferEt" style="height: 80px; width: auto; opacity: 0.5;">
                    </div>
                    <h4 class="text-muted mb-2">Featured Packages Coming Soon</h4>
                    <p class="text-muted mb-4">Our partners are preparing amazing Umrah packages for you.</p>
                    <x-customer.button href="{{ route('packages') }}" variant="primary">
                        <i class="fas fa-search me-2"></i>Browse Available Packages
                    </x-customer.button>
                </div>
            @endif
        </div>
    </div>

    <!-- Popular Destinations Section -->
    <div class="destinations-section py-5 bg-light">
        <div class="container-fluid">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-map-marker-alt text-primary me-3"></i>
                    Popular Sacred Destinations
                </h2>
                <p class="section-subtitle text-muted">Explore the most visited holy places</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="destination-card">
                        <div class="destination-image">
                            <img src="https://images.unsplash.com/photo-1591604566759-3b00c50b0ec2?w=400&h=250&fit=crop" alt="Masjid al-Haram" class="card-img-top">
                            <div class="destination-overlay">
                                <h5 class="text-white">Masjid al-Haram</h5>
                                <p class="text-white-50 mb-0">Makkah, Saudi Arabia</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Starting from</span>
                                <h5 class="text-primary mb-0">$1,200</h5>
                            </div>
                            <small class="text-muted">15+ packages available</small>
                        </div>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="destination-card">
                        <div class="destination-image">
                            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=250&fit=crop" alt="Prophet's Mosque" class="card-img-top">
                            <div class="destination-overlay">
                                <h5 class="text-white">Prophet's Mosque</h5>
                                <p class="text-white-50 mb-0">Madinah, Saudi Arabia</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Starting from</span>
                                <h5 class="text-primary mb-0">$900</h5>
                            </div>
                            <small class="text-muted">12+ packages available</small>
                        </div>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="destination-card">
                        <div class="destination-image">
                            <img src="https://images.unsplash.com/photo-1564769662265-4900ba1b3a2b?w=400&h=250&fit=crop" alt="Combined Package" class="card-img-top">
                            <div class="destination-overlay">
                                <h5 class="text-white">Makkah + Madinah</h5>
                                <p class="text-white-50 mb-0">Complete Umrah Experience</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Starting from</span>
                                <h5 class="text-primary mb-0">$1,800</h5>
                            </div>
                            <small class="text-muted">25+ packages available</small>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Offers Section -->
    <div class="offers-section py-5">
        <div class="container-fluid">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-tag text-warning me-3"></i>
                    Special Offers & Discounts
                </h2>
                <p class="section-subtitle text-muted">Limited time deals on premium packages</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <x-customer.card variant="outlined" class="offer-card bg-gradient-primary text-white">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="mb-2">Early Bird Special</h4>
                                    <p class="mb-3 opacity-90">Book 3 months in advance and save up to 25% on all packages</p>
                                    <x-customer.button variant="secondary" size="sm">
                                        <i class="fas fa-percent me-2"></i>Claim Discount
                                    </x-customer.button>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="offer-badge">
                                        <div class="badge-circle">
                                            <span class="discount-text">25%</span>
                                            <small>OFF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-6">
                    <x-customer.card variant="outlined" class="offer-card bg-gradient-success text-white">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="mb-2">Group Booking</h4>
                                    <p class="mb-3 opacity-90">Travel with 8+ people and get exclusive group rates</p>
                                    <x-customer.button variant="warning" size="sm">
                                        <i class="fas fa-users me-2"></i>Group Rates
                                    </x-customer.button>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="offer-badge">
                                        <div class="badge-circle">
                                            <span class="discount-text">15%</span>
                                            <small>OFF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="why-choose-us-section py-5 bg-light">
        <div class="container-fluid">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-star text-warning me-3"></i>
                    Why Choose SeferEt?
                </h2>
                <p class="section-subtitle text-muted">Your trusted partner for spiritual journeys</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="feature-card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon text-primary mb-4">
                                <i class="fas fa-shield-alt fa-3x"></i>
                            </div>
                            <h5 class="feature-title mb-3">Trusted Partners</h5>
                            <p class="text-muted mb-0">All our B2B partners are verified and approved by our admin team for your safety and peace of mind.</p>
                        </div>
                    </x-customer.card>
                </div>
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="feature-card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon text-success mb-4">
                                <i class="fas fa-money-bill-wave fa-3x"></i>
                            </div>
                            <h5 class="feature-title mb-3">Best Prices</h5>
                            <p class="text-muted mb-0">Compare packages from multiple partners to find the most competitive deals for your budget.</p>
                        </div>
                    </x-customer.card>
                </div>
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="feature-card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon text-info mb-4">
                                <i class="fas fa-headset fa-3x"></i>
                            </div>
                            <h5 class="feature-title mb-3">24/7 Support</h5>
                            <p class="text-muted mb-0">Our dedicated support team is always available to assist you throughout your spiritual journey.</p>
                        </div>
                    </x-customer.card>
                </div>
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="feature-card h-100 text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon text-warning mb-4">
                                <img src="{{ asset('images/logo/seferet-logo.png') }}" alt="SeferEt" style="height: 48px; width: auto;">
                            </div>
                            <h5 class="feature-title mb-3">Spiritual Focus</h5>
                            <p class="text-muted mb-0">Every aspect of our service is designed specifically for meaningful and blessed Umrah experiences.</p>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action Section -->
    @guest
    <div class="cta-section py-5">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <x-customer.card variant="elevated" elevation="lg" class="bg-gradient-primary text-white">
                        <div class="card-body text-center py-5">
                            <div class="cta-content">
                                <h2 class="text-white mb-3">Ready to Start Your Umrah Journey?</h2>
                                <p class="text-white-50 mb-4 lead">
                                    Join thousands of satisfied customers who have trusted SeferEt with their spiritual journey.
                                </p>
                                <div class="cta-buttons d-flex flex-column flex-sm-row gap-3 justify-content-center">
                                    <x-customer.button href="{{ route('customer.register') }}" variant="secondary" size="lg">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </x-customer.button>
                                    <x-customer.button href="{{ route('customer.login') }}" variant="outline-light" size="lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </x-customer.button>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>
    @endguest

    <!-- Testimonials Section -->
    @if(count($testimonials) > 0)
    <div class="testimonials-section py-5 bg-light">
        <div class="container-fluid">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-quote-left text-primary me-3"></i>
                    What Our Customers Say
                </h2>
                <p class="section-subtitle text-muted">Hear from pilgrims who trusted us with their sacred journey</p>
            </div>
            
            <div class="row g-4">
                @foreach($testimonials as $testimonial)
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" class="testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="testimonial-content">
                                <div class="testimonial-quote mb-3">
                                    <i class="fas fa-quote-left text-primary fa-2x opacity-25"></i>
                                </div>
                                <p class="testimonial-text mb-4">"{{ $testimonial['message'] }}"</p>
                                <div class="testimonial-author d-flex align-items-center">
                                    <div class="author-avatar me-3">
                                        <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="author-info">
                                        <h6 class="author-name mb-0">{{ $testimonial['customer'] }}</h6>
                                        <small class="text-muted">Verified Customer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@endsection

@push('styles')
<style>
/* Home page hero with background image */
.hero-section .hero-background {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.85) 0%, rgba(30, 58, 138, 0.85) 100%), url('https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=1920&h=1080&fit=crop') center/cover;
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    min-height: 100vh; /* Full viewport height */
}

.hero-section .hero-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.1"/></svg>') bottom/cover;
    pointer-events: none;
}

@media (max-width: 768px) {
    .hero-section .hero-background {
        background-attachment: scroll;
    }
}

/* Why Choose Us Section Styles */
.feature-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    transition: transform 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1);
}

/* CTA Section Styles */
.cta-section {
    position: relative;
}

.cta-buttons .btn {
    min-width: 160px;
}

/* Testimonials Section Styles */
.testimonial-card {
    position: relative;
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-3px);
}

.testimonial-quote {
    position: relative;
}

.testimonial-text {
    font-style: italic;
    line-height: 1.6;
}

.author-avatar .avatar-placeholder {
    font-size: 0.9rem;
}

/* City Autocomplete Styles */
#city-suggestions {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    margin-top: 2px;
}

#city-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

#city-suggestions .list-group-item:first-child {
    border-top: none;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#city-suggestions .list-group-item:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

#city-suggestions .list-group-item:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

#city-suggestions .city-suggestion-item:hover {
    background-color: #e7f1ff;
}

#hotel-destination-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Departure City Autocomplete Styles */
#departure-city-suggestions {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    margin-top: 2px;
    left: 0 !important;
}

#departure-city-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

#departure-city-suggestions .list-group-item:first-child {
    border-top: none;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#departure-city-suggestions .list-group-item:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

#departure-city-suggestions .list-group-item:hover {
    background-color: #e7f1ff;
    color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.seferet-navbar');
    const logo = document.querySelector('.navbar-logo');
    const scrollThreshold = 100; // Adjust this value as needed
    
    // Preload images to prevent flickering
    const whiteLogo = new Image();
    whiteLogo.src = 'http://127.0.0.1:8000/images/logo/seferet-logo-notext-white.png';
    
    const coloredLogo = new Image();
    coloredLogo.src = 'http://127.0.0.1:8000/images/logo/seferet-logo-notext-colored.png';
    
    function handleScroll() {
        if (window.scrollY > scrollThreshold) {
            navbar.classList.remove('transparent');
            navbar.classList.add('scrolled');
            logo.src = 'http://127.0.0.1:8000/images/logo/seferet-logo-notext-colored.png';
        } else {
            navbar.classList.remove('scrolled');
            navbar.classList.add('transparent');
            logo.src = 'http://127.0.0.1:8000/images/logo/seferet-logo-notext-white.png';
        }
    }
    
    // Initial check in case page loads with scroll
    handleScroll();
    
    // Listen for scroll events
    window.addEventListener('scroll', handleScroll);
    // Hotel search form validation and enhancement
    const hotelForm = document.getElementById('hotel-search-form');
    if (!hotelForm) {
        console.error('Hotel search form not found');
        return;
    }
    
    const checkInInput = hotelForm.querySelector('input[name="check_in"]');
    const checkOutInput = hotelForm.querySelector('input[name="check_out"]');
    
    // City autocomplete
    const cityInput = document.getElementById('hotel-destination-input');
    const cityCodeInput = document.getElementById('hotel-location-code');
    const citySuggestions = document.getElementById('city-suggestions');
    let searchTimeout;
    
    console.log('City autocomplete initialized');
    console.log('City input found:', cityInput);
    console.log('City code input found:', cityCodeInput);
    console.log('Suggestions div found:', citySuggestions);
    
    if (!cityInput || !cityCodeInput || !citySuggestions) {
        console.error('City autocomplete elements not found');
        return;
    }
    
    cityInput.addEventListener('input', function() {
        const keyword = this.value.trim();
        console.log('City input changed:', keyword);
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Clear hidden input if user is typing
        cityCodeInput.value = '';
        
        if (keyword.length < 2) {
            citySuggestions.style.display = 'none';
            citySuggestions.innerHTML = '';
            return;
        }
        
        console.log('Searching for:', keyword);
        
        // Debounce search
        searchTimeout = setTimeout(function() {
            console.log('Making fetch call to /api/cities/search');
            fetch('/api/cities/search?keyword=' + encodeURIComponent(keyword))
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data);
                    if (data.success && data.data.length > 0) {
                        let html = '';
                        data.data.forEach(function(city) {
                            html += `<a href="#" class="list-group-item list-group-item-action city-suggestion-item" 
                                       data-code="${city.value}" 
                                       data-name="${city.city_name}">
                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                        <strong>${city.city_name}</strong>
                                        ${city.country ? '<small class="text-muted"> - ' + city.country + '</small>' : ''}
                                        <small class="badge bg-secondary ms-2">${city.value}</small>
                                     </a>`;
                        });
                        citySuggestions.innerHTML = html;
                        citySuggestions.style.display = 'block';
                    } else {
                        citySuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>No cities found</div>';
                        citySuggestions.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('API Error:', error);
                    citySuggestions.innerHTML = '<div class="list-group-item text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to search cities</div>';
                    citySuggestions.style.display = 'block';
                });
        }, 300); // 300ms debounce
    });
    
    // Handle city selection
    document.addEventListener('click', function(e) {
        const suggestionItem = e.target.closest('.city-suggestion-item');
        if (suggestionItem) {
            e.preventDefault();
            const cityCode = suggestionItem.getAttribute('data-code');
            const cityName = suggestionItem.getAttribute('data-name');
            
            cityInput.value = cityName;
            cityCodeInput.value = cityCode;
            citySuggestions.style.display = 'none';
            citySuggestions.innerHTML = '';
        }
        
        // Hide suggestions when clicking outside
        if (!e.target.closest('#hotel-destination-input') && !e.target.closest('#city-suggestions')) {
            citySuggestions.style.display = 'none';
        }
    });
    
    // Set minimum dates
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    
    if (checkInInput) checkInInput.setAttribute('min', today);
    if (checkOutInput) checkOutInput.setAttribute('min', tomorrow);
    
    // Update check-out minimum when check-in changes
    if (checkInInput) {
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const minCheckOutDate = new Date(checkInDate.getTime() + 86400000);
            const minCheckOutStr = minCheckOutDate.toISOString().split('T')[0];
            
            if (checkOutInput) {
                checkOutInput.setAttribute('min', minCheckOutStr);
                
                // Reset check-out if it's before the new minimum
                if (checkOutInput.value && checkOutInput.value <= this.value) {
                    checkOutInput.value = minCheckOutStr;
                }
            }
        });
    }
    
    // Form submission loading state
    if (hotelForm) {
        hotelForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
            }
        });
    }
    
    // Departure City Autocomplete for Package Search
    const departureCityInput = document.getElementById('package-departure-input');
    const departureCitySuggestions = document.getElementById('departure-city-suggestions');
    let departureSearchTimeout;
    
    if (departureCityInput && departureCitySuggestions) {
        console.log('Departure city autocomplete initialized');
        
        departureCityInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(departureSearchTimeout);
            
            if (query.length < 2) {
                departureCitySuggestions.style.display = 'none';
                departureCitySuggestions.innerHTML = '';
                return;
            }
            
            // Debounce search
            departureSearchTimeout = setTimeout(function() {
                fetch('{{ route("api.packages.departure-cities") }}?query=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.cities && data.cities.length > 0) {
                            let html = '';
                            data.cities.forEach(function(city) {
                                html += `<a href="#" class="list-group-item list-group-item-action departure-city-item" 
                                           data-city="${city}">
                                            <i class="fas fa-plane-departure me-2 text-primary"></i>
                                            <strong>${city}</strong>
                                         </a>`;
                            });
                            departureCitySuggestions.innerHTML = html;
                            departureCitySuggestions.style.display = 'block';
                        } else {
                            departureCitySuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>No departure cities found</div>';
                            departureCitySuggestions.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Departure city search error:', error);
                        departureCitySuggestions.style.display = 'none';
                    });
            }, 300); // 300ms debounce
        });
        
        // Handle departure city selection
        document.addEventListener('click', function(e) {
            const departureCityItem = e.target.closest('.departure-city-item');
            if (departureCityItem) {
                e.preventDefault();
                const cityName = departureCityItem.getAttribute('data-city');
                
                departureCityInput.value = cityName;
                departureCitySuggestions.style.display = 'none';
                departureCitySuggestions.innerHTML = '';
            }
            
            // Hide suggestions when clicking outside
            if (!e.target.closest('#package-departure-input') && !e.target.closest('#departure-city-suggestions')) {
                departureCitySuggestions.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
