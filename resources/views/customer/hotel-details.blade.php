@extends('layouts.customer')

@section('title', $hotel['name'] . ' - ' . $hotel['location'] . ' - SeferEt Hotels')

@section('content')
    <!-- Hotel Header -->
    <div class="hotel-detail-header">
        <div class="container-fluid">
            <div class="row">
                <!-- Hotel Gallery -->
                <div class="col-lg-8">
                    <div class="hotel-gallery">
                        <div id="hotelCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach($hotel['images'] as $index => $image)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ $image }}" class="d-block w-100 hotel-main-image" alt="{{ $hotel['name'] }}">
                                </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#hotelCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#hotelCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                            
                            <!-- Thumbnail indicators -->
                            <div class="carousel-indicators-custom">
                                @foreach($hotel['images'] as $index => $image)
                                <button type="button" data-bs-target="#hotelCarousel" data-bs-slide-to="{{ $index }}" 
                                        class="{{ $index === 0 ? 'active' : '' }}" 
                                        style="background-image: url('{{ $image }}')"></button>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- View All Photos Button -->
                        <div class="gallery-overlay">
                            <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#galleryModal">
                                <i class="fas fa-images me-2"></i>View All {{ count($hotel['images']) }} Photos
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Booking Card -->
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="xl" padding="lg" class="quick-booking-card">
                        <div class="hotel-quick-info mb-3">
                            <h1 class="h4 text-primary mb-2">{{ $hotel['name'] }}</h1>
                            <div class="hotel-rating mb-2">
                                @for($i = 0; $i < $hotel['stars']; $i++)
                                    <i class="fas fa-star text-warning"></i>
                                @endfor
                                <span class="ms-2 text-muted">({{ $hotel['stars'] }} Star Hotel)</span>
                            </div>
                            <p class="location mb-0">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                {{ $hotel['location'] }}
                            </p>
                        </div>

                        <div class="price-display text-center mb-3">
                            <h3 class="text-primary mb-0">${{ number_format($hotel['price_per_night']) }}</h3>
                            <small class="text-muted">per night</small>
                        </div>

                        <div class="booking-form">
                            <form>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small">Check-in</label>
                                        <input type="date" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Check-out</label>
                                        <input type="date" class="form-control" value="{{ date('Y-m-d', strtotime('+5 days')) }}">
                                    </div>
                                </div>
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small">Guests</label>
                                        <select class="form-select">
                                            <option value="1">1 Guest</option>
                                            <option value="2" selected>2 Guests</option>
                                            <option value="3">3 Guests</option>
                                            <option value="4">4 Guests</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Rooms</label>
                                        <select class="form-select">
                                            <option value="1" selected>1 Room</option>
                                            <option value="2">2 Rooms</option>
                                            <option value="3">3 Rooms</option>
                                        </select>
                                    </div>
                                </div>

                                @auth
                                    <x-customer.button 
                                        href="{{ route('hotels.checkout', $hotel['id']) }}" 
                                        variant="primary" 
                                        size="lg" 
                                        fullWidth="true" 
                                        class="mb-2"
                                    >
                                        <i class="fas fa-calendar-check me-2"></i>Book Now
                                    </x-customer.button>
                                @else
                                    <x-customer.button 
                                        href="{{ route('customer.login') }}" 
                                        variant="primary" 
                                        size="lg" 
                                        fullWidth="true" 
                                        class="mb-2"
                                    >
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                                    </x-customer.button>
                                @endauth
                                
                                <div class="row g-2">
                                    <div class="col-6">
                                        <x-customer.button variant="outline-primary" size="md" fullWidth="true">
                                            <i class="fas fa-heart me-1"></i>Save
                                        </x-customer.button>
                                    </div>
                                    <div class="col-6">
                                        <x-customer.button variant="outline-primary" size="md" fullWidth="true">
                                            <i class="fas fa-share me-1"></i>Share
                                        </x-customer.button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Hotel Overview -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-building text-primary me-2"></i>
                        Hotel Overview
                    </h2>
                    
                    <div class="hotel-overview">
                        <p class="lead">{{ $hotel['description'] }}</p>
                        
                        <div class="hotel-highlights mt-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <h6><i class="fas fa-map-marker-alt text-danger me-2"></i>Location</h6>
                                        <p class="mb-0">{{ $hotel['location'] }} • {{ $hotel['distance_to_center'] }} from city center</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <h6><i class="fas fa-bed text-primary me-2"></i>Room Options</h6>
                                        <p class="mb-0">{{ $hotel['total_rooms'] }} rooms • Various configurations</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <h6><i class="fas fa-clock text-success me-2"></i>Check-in/out</h6>
                                        <p class="mb-0">Check-in: 3:00 PM • Check-out: 12:00 PM</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <h6><i class="fas fa-shield-alt text-info me-2"></i>Policies</h6>
                                        <p class="mb-0">Free cancellation • {{ $hotel['payment_policy'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Hotel Amenities -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-concierge-bell text-primary me-2"></i>
                        Hotel Amenities
                    </h2>
                    
                    <div class="amenities-grid">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="amenity-category mb-3">
                                    <i class="fas fa-wifi text-primary me-2"></i>Connectivity
                                </h6>
                                <ul class="amenity-list">
                                    <li><i class="fas fa-check text-success me-2"></i>Free WiFi throughout</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Business center</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Meeting rooms</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Conference facilities</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="amenity-category mb-3">
                                    <i class="fas fa-swimming-pool text-primary me-2"></i>Recreation
                                </h6>
                                <ul class="amenity-list">
                                    <li><i class="fas fa-check text-success me-2"></i>Outdoor swimming pool</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Fitness center</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Spa services</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Terrace/Garden</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="amenity-category mb-3">
                                    <i class="fas fa-utensils text-primary me-2"></i>Dining
                                </h6>
                                <ul class="amenity-list">
                                    <li><i class="fas fa-check text-success me-2"></i>Restaurant</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Bar/Lounge</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Room service</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Complimentary breakfast</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="amenity-category mb-3">
                                    <i class="fas fa-concierge-bell text-primary me-2"></i>Services
                                </h6>
                                <ul class="amenity-list">
                                    <li><i class="fas fa-check text-success me-2"></i>24-hour front desk</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Concierge service</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Laundry service</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Airport shuttle</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Room Types -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-bed text-primary me-2"></i>
                        Available Room Types
                    </h2>
                    
                    <div class="room-types">
                        @foreach($hotel['room_types'] as $room)
                        <div class="room-card mb-4 p-3 border rounded">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="{{ $room['image'] }}" class="room-image rounded" alt="{{ $room['name'] }}">
                                </div>
                                <div class="col-md-6">
                                    <h5 class="room-name">{{ $room['name'] }}</h5>
                                    <p class="room-description text-muted mb-2">{{ $room['description'] }}</p>
                                    <div class="room-features">
                                        <span class="feature-badge me-2">
                                            <i class="fas fa-users me-1"></i>{{ $room['max_guests'] }} guests
                                        </span>
                                        <span class="feature-badge me-2">
                                            <i class="fas fa-bed me-1"></i>{{ $room['beds'] }}
                                        </span>
                                        <span class="feature-badge me-2">
                                            <i class="fas fa-expand me-1"></i>{{ $room['size'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <div class="room-pricing">
                                        <h5 class="text-primary mb-1">${{ number_format($room['price_per_night']) }}</h5>
                                        <small class="text-muted">per night</small>
                                        <div class="mt-2">
                                            <x-customer.button variant="outline-primary" size="sm" fullWidth="true">
                                                <i class="fas fa-plus me-1"></i>Select Room
                                            </x-customer.button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-customer.card>

                <!-- Guest Reviews -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-star text-primary me-2"></i>
                        Guest Reviews
                    </h2>
                    
                    <div class="reviews-summary mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <div class="overall-rating">
                                    <h2 class="rating-score text-primary mb-2">{{ $hotel['rating'] }}</h2>
                                    <div class="stars mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($hotel['rating']))
                                                <i class="fas fa-star text-warning"></i>
                                            @elseif($i <= $hotel['rating'])
                                                <i class="fas fa-star-half-alt text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="rating-text">{{ $hotel['rating_text'] }}</p>
                                    <small class="text-muted">{{ $hotel['review_count'] }} reviews</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="rating-breakdown">
                                    @foreach($hotel['rating_breakdown'] as $category => $score)
                                    <div class="rating-category mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="category-name">{{ ucfirst($category) }}</span>
                                            <span class="category-score">{{ $score }}</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ ($score/5)*100 }}%"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="recent-reviews">
                        <h5 class="mb-3">Recent Reviews</h5>
                        @foreach($hotel['recent_reviews'] as $review)
                        <div class="review-card mb-3 p-3 border rounded">
                            <div class="review-header mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="reviewer-name mb-1">{{ $review['name'] }}</h6>
                                        <div class="review-stars mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review['rating'])
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $review['date'] }}</small>
                                </div>
                            </div>
                            <p class="review-text mb-0">{{ $review['comment'] }}</p>
                        </div>
                        @endforeach
                        
                        <div class="text-center">
                            <x-customer.button variant="outline-primary" size="sm">
                                <i class="fas fa-plus me-1"></i>Load More Reviews
                            </x-customer.button>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Location & Map -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-map-marked-alt text-primary me-2"></i>
                        Location & Nearby Attractions
                    </h2>
                    
                    <div class="location-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="map-placeholder bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-map fa-3x mb-3"></i>
                                        <p>Interactive Map<br><small>{{ $hotel['address'] }}</small></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Nearby Attractions</h6>
                                <div class="nearby-attractions">
                                    @foreach($hotel['nearby_attractions'] as $attraction)
                                    <div class="attraction-item mb-3 d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-danger me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $attraction['name'] }}</h6>
                                            <small class="text-muted">{{ $attraction['distance'] }}</small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <h6 class="mb-3 mt-4">Transportation</h6>
                                <div class="transportation-info">
                                    <div class="transport-item mb-2">
                                        <i class="fas fa-plane text-primary me-2"></i>
                                        <span>Airport: {{ $hotel['airport_distance'] }}</span>
                                    </div>
                                    <div class="transport-item mb-2">
                                        <i class="fas fa-train text-success me-2"></i>
                                        <span>Train Station: {{ $hotel['train_distance'] }}</span>
                                    </div>
                                    <div class="transport-item">
                                        <i class="fas fa-bus text-info me-2"></i>
                                        <span>Bus Stop: {{ $hotel['bus_distance'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-customer.card>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Booking Support -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h6 class="mb-3">
                        <i class="fas fa-headset text-primary me-2"></i>
                        Need Help Booking?
                    </h6>
                    
                    <div class="support-options">
                        <div class="support-item mb-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Call Us</h6>
                                    <small class="text-muted">+1 (555) 123-4567</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="support-item mb-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-comments text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1">Live Chat</h6>
                                    <small class="text-muted">Available 24/7</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="support-item p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-info me-3"></i>
                                <div>
                                    <h6 class="mb-1">Email Support</h6>
                                    <small class="text-muted">help@seferet.com</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Special Offers -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h6 class="mb-3">
                        <i class="fas fa-tag text-warning me-2"></i>
                        Special Offers
                    </h6>
                    
                    <div class="offers">
                        <div class="offer-item mb-3 p-3 border rounded bg-light">
                            <h6 class="text-success mb-2">
                                <i class="fas fa-percent me-1"></i>
                                Early Bird Discount
                            </h6>
                            <p class="small mb-0">Book 30 days in advance and save up to 20%</p>
                        </div>
                        
                        <div class="offer-item mb-3 p-3 border rounded bg-light">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Extended Stay
                            </h6>
                            <p class="small mb-0">Stay 3+ nights and get 15% off your booking</p>
                        </div>
                        
                        <div class="offer-item p-3 border rounded bg-light">
                            <h6 class="text-warning mb-2">
                                <i class="fas fa-gift me-1"></i>
                                Free Upgrade
                            </h6>
                            <p class="small mb-0">Subject to availability at check-in</p>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Similar Hotels -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="similar-hotels-card">
                    <h6 class="mb-3">
                        <i class="fas fa-building text-primary me-2"></i>
                        Similar Hotels
                    </h6>
                    
                    <div class="similar-hotels">
                        @foreach($similar_hotels as $similar)
                        <div class="similar-hotel-item mb-3 p-2 border rounded">
                            <div class="row g-2 align-items-center">
                                <div class="col-4">
                                    <img src="{{ $similar['image'] }}" class="similar-hotel-image rounded" alt="{{ $similar['name'] }}">
                                </div>
                                <div class="col-8">
                                    <h6 class="mb-1 small">{{ $similar['name'] }}</h6>
                                    <div class="hotel-stars mb-1">
                                        @for($i = 0; $i < $similar['stars']; $i++)
                                            <i class="fas fa-star text-warning small"></i>
                                        @endfor
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $similar['location'] }}</small>
                                        <strong class="text-primary small">${{ $similar['price'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <div class="text-center">
                            <x-customer.button href="{{ route('hotels') }}" variant="outline-primary" size="sm">
                                <i class="fas fa-search me-1"></i>View More
                            </x-customer.button>
                        </div>
                    </div>
                </x-customer.card>
            </div>
        </div>
    </div>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $hotel['name'] }} - Photo Gallery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        @foreach($hotel['images'] as $image)
                        <div class="col-md-4">
                            <img src="{{ $image }}" class="img-fluid rounded gallery-image" alt="{{ $hotel['name'] }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.hotel-detail-header {
    margin: -1rem -1rem 0 -1rem;
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.3), rgba(30, 58, 138, 0.3));
    position: relative;
}

.hotel-detail-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
    z-index: 1;
}

.hotel-gallery {
    position: relative;
    height: 400px;
    overflow: hidden;
    border-radius: 12px;
}

.hotel-main-image {
    height: 400px;
    object-fit: cover;
    border-radius: 12px;
}

.carousel-indicators-custom {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: auto;
    display: flex;
    gap: 8px;
    margin: 0;
}

.carousel-indicators-custom button {
    width: 60px;
    height: 40px;
    border-radius: 6px;
    border: 2px solid white;
    background-size: cover !important;
    background-position: center !important;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.carousel-indicators-custom button.active {
    opacity: 1;
    border-color: var(--primary-color);
}

.gallery-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
}

.quick-booking-card {
    position: sticky;
    top: 2rem;
    z-index: 100;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.amenity-category {
    color: var(--text-primary);
    font-weight: 600;
}

.amenity-list {
    list-style: none;
    padding: 0;
}

.amenity-list li {
    padding: 0.25rem 0;
    font-size: 0.9rem;
}

.room-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.feature-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: var(--surface-variant-color);
    border-radius: 20px;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.rating-score {
    font-size: 2.5rem;
    font-weight: 700;
}

.rating-category .progress {
    background: var(--surface-variant-color);
}

.review-card {
    transition: all 0.2s ease;
}

.review-card:hover {
    border-color: var(--primary-color) !important;
}

.similar-hotel-image {
    width: 100%;
    height: 60px;
    object-fit: cover;
}

.map-placeholder {
    border: 2px dashed var(--border-color);
}

.support-item:hover,
.offer-item:hover {
    background: var(--surface-variant-color) !important;
}

.gallery-image {
    height: 200px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.gallery-image:hover {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .hotel-gallery {
        height: 250px;
        margin: 0 -1rem;
        border-radius: 0;
    }
    
    .hotel-main-image {
        height: 250px;
        border-radius: 0;
    }
    
    .quick-booking-card {
        position: relative;
        top: auto;
        margin-top: 2rem;
    }
    
    .carousel-indicators-custom {
        display: none;
    }
    
    .rating-score {
        font-size: 2rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel
    const carousel = new bootstrap.Carousel(document.getElementById('hotelCarousel'), {
        interval: 5000,
        ride: 'carousel'
    });
    
    // Gallery modal image click handling
    const galleryImages = document.querySelectorAll('.gallery-image');
    galleryImages.forEach(image => {
        image.addEventListener('click', function() {
            // You can implement a lightbox here

        });
    });
    
    // Booking form validation
    const checkInInput = document.querySelector('input[type="date"]:first-of-type');
    const checkOutInput = document.querySelector('input[type="date"]:last-of-type');
    
    checkInInput.addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        const minCheckOut = new Date(checkInDate.getTime() + (24 * 60 * 60 * 1000)); // Next day
        checkOutInput.min = minCheckOut.toISOString().split('T')[0];
        
        if (new Date(checkOutInput.value) <= checkInDate) {
            checkOutInput.value = minCheckOut.toISOString().split('T')[0];
        }
    });
    
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script>
@endpush
