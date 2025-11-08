@extends('layouts.customer')

@section('title', $package->name . ' - SeferEt')

@section('content')
    <!-- Package Hero Section -->
    <div class="package-hero">
        <div class="hero-image-carousel">
            <div id="packageCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @if(!empty($package->images))
                        @foreach($package->images as $index => $image)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $image['urls']['large'] ?? $image['urls']['medium'] ?? asset('storage/' . $image['sizes']['medium']) }}" 
                                 class="d-block w-100" alt="{{ $package->name }}">
                        </div>
                        @endforeach
                    @else
                        <!-- Default images if no package images -->
                        <div class="carousel-item active">
                            <img src="https://images.unsplash.com/photo-1591604021695-0c4b8d9e384b?w=1200&h=600&fit=crop" class="d-block w-100" alt="Kaaba">
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=1200&h=600&fit=crop" class="d-block w-100" alt="Prophet's Mosque">
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1564769662265-4900ba1b3a2b?w=1200&h=600&fit=crop" class="d-block w-100" alt="Makkah">
                        </div>
                    @endif
                </div>
                @if(!empty($package->images) && count($package->images) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#packageCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#packageCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                @endif
            </div>
            
            <div class="hero-overlay">
                <div class="container-fluid">
                    <div class="hero-content">
                        <div class="package-badges">
                            @if($package->is_featured)
                            <span class="badge bg-success"><i class="fas fa-star me-1"></i>Featured</span>
                            @endif
                            @if($package->is_premium)
                            <span class="badge bg-danger"><i class="fas fa-crown me-1"></i>Premium</span>
                            @endif
                            <span class="badge bg-primary"><i class="fas fa-certificate me-1"></i>Verified Partner</span>
                        </div>
                        <h1 class="package-hero-title">{{ $package->name }}</h1>
                        <p class="package-hero-subtitle">{{ $package->formatted_duration }} Spiritual Journey</p>
                        <div class="hero-rating">
                            @if($package->average_rating > 0)
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $package->average_rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ms-2 text-white">({{ number_format($package->average_rating, 1) }}/5) • {{ $package->reviews_count }} Reviews</span>
                            @else
                                <span class="text-white">New Package</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Package Overview -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <div class="package-overview">
                        <h2 class="section-title mb-3">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Package Overview
                        </h2>
                        <p class="package-description">{{ $package->description }}</p>
                        @if($package->detailed_description)
                        <div class="detailed-description mt-3">
                            <h6>About This Package</h6>
                            <p class="text-muted">{{ $package->detailed_description }}</p>
                        </div>
                        @endif
                        
                        <div class="package-highlights mt-4">
                            <div class="row g-3">
                                <div class="col-md-3 text-center">
                                    <div class="highlight-item">
                                        <i class="fas fa-calendar text-primary fa-2x mb-2"></i>
                                        <h6>Duration</h6>
                                        <p class="text-muted">{{ $package->formatted_duration }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="highlight-item">
                                        <i class="fas fa-users text-success fa-2x mb-2"></i>
                                        <h6>Group Size</h6>
                                        <p class="text-muted">
                                            @if($package->max_participants)
                                                Max {{ $package->max_participants }} People
                                            @else
                                                Flexible Group Size
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="highlight-item">
                                        <i class="fas fa-plane text-info fa-2x mb-2"></i>
                                        <h6>Flights</h6>
                                        <p class="text-muted">
                                            @if(isset($package->includes_flights) && $package->includes_flights)
                                                Flights Included
                                            @else
                                                Flights Available
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="highlight-item">
                                        <i class="fas fa-hotel text-warning fa-2x mb-2"></i>
                                        <h6>Accommodation</h6>
                                        <p class="text-muted">
                                            @if(isset($package->includes_accommodation) && $package->includes_accommodation)
                                                Hotels Included
                                            @else
                                                Hotels Available
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Itinerary -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-route text-primary me-2"></i>
                        Day-by-Day Itinerary
                    </h2>
                    
                    <div class="itinerary-timeline">
                        @if(!empty($package->activities))
                            @foreach($package->activities as $day => $dayActivities)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <div class="marker-circle">{{ $day }}</div>
                                    </div>
                                    <div class="timeline-content">
                                        <h5>Day {{ $day }}</h5>
                                        @foreach($dayActivities as $activity)
                                            <div class="activity-item mb-3">
                                                @if($activity['title'])
                                                    <h6 class="activity-title">{{ $activity['title'] }}</h6>
                                                @endif
                                                @if($activity['description'])
                                                    <p class="text-muted mb-2">{{ $activity['description'] }}</p>
                                                @endif
                                                <div class="day-activities">
                                                    @if($activity['location'])
                                                    <span class="activity-tag">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ $activity['location'] }}
                                                    </span>
                                                    @endif
                                                    @if($activity['is_included'])
                                                    <span class="activity-tag">
                                                        <i class="fas fa-check me-1"></i>
                                                        Included
                                                    </span>
                                                    @elseif($activity['additional_cost'])
                                                    <span class="activity-tag">
                                                        <i class="fas fa-dollar-sign me-1"></i>
                                                        ${{ $activity['additional_cost'] }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Default itinerary if no activities defined -->
                            @for($day = 1; $day <= $package->duration; $day++)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <div class="marker-circle">{{ $day }}</div>
                                    </div>
                                    <div class="timeline-content">
                                        <h5>Day {{ $day }} - 
                                            @if($day <= 3) Arrival & Makkah
                                            @elseif($day <= 7) Makkah - Umrah Rituals
                                            @elseif($day <= 10) Transfer to Madinah
                                            @elseif($day <= 12) Madinah - Ziyarat
                                            @else Departure
                                            @endif
                                        </h5>
                                        <p class="text-muted mb-2">
                                            @if($day == 1) Arrival at Jeddah Airport, transfer to hotel, rest and preparation
                                            @elseif($day == 2) Check-in, perform first Umrah, visit Masjid al-Haram
                                            @elseif($day <= 7) Perform Umrah rituals, Tawaf, Sa'i, spiritual activities
                                            @elseif($day == 8) Transfer from Makkah to Madinah
                                            @elseif($day <= 12) Visit Prophet's Mosque, Ziyarat tours, historical sites
                                            @else Final prayers, shopping, departure to home country
                                            @endif
                                        </p>
                                        <div class="day-activities">
                                            <span class="activity-tag">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                @if($day <= 7) Makkah @elseif($day <= 12) Madinah @else Airport @endif
                                            </span>
                                            @if(isset($package->includes_meals) && $package->includes_meals)
                                            <span class="activity-tag">
                                                <i class="fas fa-utensils me-1"></i>
                                                Meals Included
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        @endif
                    </div>
                </x-customer.card>

                <!-- What's Included -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        What's Included
                    </h2>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="inclusion-list">
                                @if(!empty($package->inclusions))
                                    @foreach(array_slice($package->inclusions, 0, ceil(count($package->inclusions) / 2)) as $inclusion)
                                    <li><i class="fas fa-check text-success me-2"></i>{{ $inclusion }}</li>
                                    @endforeach
                                @else
                                    @foreach($package->includes as $include)
                                    <li><i class="fas fa-check text-success me-2"></i>{{ $include }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="inclusion-list">
                                @if(!empty($package->inclusions) && count($package->inclusions) > 1)
                                    @foreach(array_slice($package->inclusions, ceil(count($package->inclusions) / 2)) as $inclusion)
                                    <li><i class="fas fa-check text-success me-2"></i>{{ $inclusion }}</li>
                                    @endforeach
                                @endif
                                
                                @foreach($package->features as $feature)
                                <li><i class="fas fa-star text-warning me-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    @if(!empty($package->exclusions))
                    <div class="exclusions-section mt-4">
                        <h6 class="text-danger"><i class="fas fa-times me-2"></i>Not Included</h6>
                        <ul class="exclusion-list text-muted">
                            @foreach($package->exclusions as $exclusion)
                            <li>{{ $exclusion }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </x-customer.card>

                <!-- Reviews -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-star text-warning me-2"></i>
                        Customer Reviews (156)
                    </h2>
                    
                    <div class="reviews-summary mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="overall-rating">
                                    <h3 class="rating-number">4.8</h3>
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-warning"></i>
                                        @endfor
                                    </div>
                                    <p class="text-muted">Overall Rating</p>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="rating-breakdown">
                                    @for($stars = 5; $stars >= 1; $stars--)
                                        <div class="rating-bar-item">
                                            <span class="rating-label">{{ $stars }} stars</span>
                                            <div class="rating-bar">
                                                <div class="rating-fill" style="width: {{ $stars == 5 ? '75%' : ($stars == 4 ? '20%' : '5%') }}"></div>
                                            </div>
                                            <span class="rating-count">{{ $stars == 5 ? '117' : ($stars == 4 ? '31' : '8') }}</span>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="reviews-list">
                        @foreach(['Ahmad Hassan', 'Fatima Al-Zahra', 'Mohammed Ali'] as $index => $reviewer)
                            <div class="review-item {{ $index > 0 ? 'border-top pt-4' : '' }} mb-4">
                                <div class="d-flex mb-3">
                                    <div class="reviewer-avatar me-3">
                                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                                    </div>
                                    <div class="reviewer-info">
                                        <h6 class="reviewer-name mb-1">{{ $reviewer }}</h6>
                                        <div class="review-meta">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-warning"></i>
                                            @endfor
                                            <span class="text-muted ms-2">{{ ['2 weeks ago', '1 month ago', '2 months ago'][$index] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="review-text">
                                    @if($index == 0)
                                        "Alhamdulillah, this was an amazing experience. The organization was perfect, hotels were excellent, and the guide was very knowledgeable about Islamic history."
                                    @elseif($index == 1)
                                        "Subhanallah! Everything was well arranged. The proximity to Haram made it easy to pray all five times. Highly recommend this package to families."
                                    @else
                                        "May Allah reward the organizers. The journey was smooth, food was halal and delicious, and we felt safe throughout the trip."
                                    @endif
                                </p>
                            </div>
                        @endforeach
                        
                        <div class="text-center">
                            <x-customer.button variant="outline-primary" size="sm">
                                <i class="fas fa-chevron-down me-2"></i>Show More Reviews
                            </x-customer.button>
                        </div>
                    </div>
                </x-customer.card>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Booking Card -->
                <x-customer.card variant="elevated" elevation="lg" padding="lg" class="booking-card sticky-top mb-4">
                    <div class="booking-header mb-3">
                        <div class="price-display">
                            <span class="price-label">Starting from</span>
                            <h3 class="package-price text-primary">{{ $package->formatted_price }}</h3>
                            <span class="price-note">per person</span>
                            @if($package->child_price && $package->child_price < $package->base_price)
                                <div class="child-price mt-2">
                                    <small class="text-muted">Child: ${{ number_format($package->child_price) }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="partner-info">
                            <small class="text-muted">By {{ $package->creator_name }}</small>
                        </div>
                    </div>

                    <div class="booking-options mb-4">
                        <div class="mb-3">
                            <label class="form-label">Travel Date</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 month')) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Travelers</label>
                            <select class="form-select">
                                <option value="1">1 Person</option>
                                <option value="2" selected>2 People</option>
                                <option value="3">3 People</option>
                                <option value="4">4 People</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type</label>
                            <select class="form-select">
                                <option value="double">Double Room</option>
                                <option value="twin">Twin Room</option>
                                <option value="triple">Triple Room</option>
                                <option value="quad">Quad Room</option>
                            </select>
                        </div>
                    </div>

                    <div class="booking-actions">
                        @auth
                            <x-customer.button 
                                href="{{ route('customer.packages.checkout', $package->id) }}" 
                                variant="primary" 
                                size="lg" 
                                fullWidth="true" 
                                class="mb-3"
                            >
                                <i class="fas fa-shopping-cart me-2"></i>Book This Package
                            </x-customer.button>
                        @else
                            <x-customer.button 
                                href="{{ route('customer.login') }}" 
                                variant="primary" 
                                size="lg" 
                                fullWidth="true" 
                                class="mb-3"
                            >
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                            </x-customer.button>
                        @endauth
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <x-customer.button variant="outline-secondary" size="md" fullWidth="true">
                                    <i class="fas fa-heart me-1"></i>Save
                                </x-customer.button>
                            </div>
                            <div class="col-6">
                                <x-customer.button variant="outline-secondary" size="md" fullWidth="true">
                                    <i class="fas fa-share me-1"></i>Share
                                </x-customer.button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-guarantee mt-4 pt-4 border-top">
                        <div class="guarantee-items">
                            <div class="guarantee-item mb-2">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                <small>Free cancellation up to 48 hours</small>
                            </div>
                            <div class="guarantee-item mb-2">
                                <i class="fas fa-medal text-warning me-2"></i>
                                <small>Best price guarantee</small>
                            </div>
                            <div class="guarantee-item">
                                <i class="fas fa-headset text-primary me-2"></i>
                                <small>24/7 customer support</small>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Contact Info -->
                <x-customer.card variant="elevated" elevation="md" padding="md" class="contact-card">
                    <h6 class="mb-3">
                        <i class="fas fa-phone text-primary me-2"></i>
                        Need Help?
                    </h6>
                    <div class="contact-info">
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:+1234567890">+1 (234) 567-8900</a>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:support@seferet.com">support@seferet.com</a>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            <span class="text-muted">Available 24/7</span>
                        </p>
                    </div>
                </x-customer.card>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.package-hero {
    height: 60vh;
    position: relative;
}

.hero-image-carousel {
    height: 100%;
}

.hero-image-carousel .carousel-inner,
.hero-image-carousel .carousel-item {
    height: 100%;
}

.hero-image-carousel img {
    height: 100%;
    object-fit: cover;
}

.hero-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 3rem 0 2rem;
    color: white;
}

.package-hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.package-hero-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.highlight-item {
    padding: 1rem;
    border-radius: 8px;
    background: var(--surface-variant-color);
}

.timeline-item {
    display: flex;
    margin-bottom: 2rem;
}

.timeline-marker {
    flex: 0 0 auto;
    margin-right: 1rem;
}

.marker-circle {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.timeline-content {
    flex: 1;
}

.activity-tag {
    display: inline-block;
    background: var(--surface-variant-color);
    color: var(--text-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-right: 0.5rem;
}

.inclusion-list {
    list-style: none;
    padding: 0;
}

.inclusion-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.inclusion-list li:last-child {
    border-bottom: none;
}

.overall-rating {
    text-align: center;
}

.rating-number {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.rating-bar-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.rating-label {
    flex: 0 0 80px;
    font-size: 0.9rem;
}

.rating-bar {
    flex: 1;
    height: 8px;
    background: var(--border-color);
    border-radius: 4px;
    margin: 0 1rem;
    overflow: hidden;
}

.rating-fill {
    height: 100%;
    background: var(--warning-color);
}

.rating-count {
    flex: 0 0 30px;
    text-align: right;
    font-size: 0.9rem;
    color: var(--text-muted);
}

.booking-card {
    position: sticky;
    top: 2rem;
}

.package-price {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.price-label {
    font-size: 0.9rem;
    color: var(--text-muted);
    text-transform: uppercase;
}

.price-note {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.guarantee-item {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .package-hero {
        height: 40vh;
    }
    
    .package-hero-title {
        font-size: 1.8rem;
    }
    
    .booking-card {
        position: relative;
        top: auto;
    }
    
    .timeline-item {
        flex-direction: column;
    }
    
    .timeline-marker {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
