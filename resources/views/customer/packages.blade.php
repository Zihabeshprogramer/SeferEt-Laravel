@extends('layouts.customer')

@section('title', 'Umrah Packages - SeferEt')

@section('content')
    <!-- Packages Header -->
    <div class="packages-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h2 mb-3">
                        <i class="fas fa-box me-3"></i>
                        Discover Our Umrah Packages
                    </h1>
                    <p class="lead mb-0">Choose from our carefully curated selection of spiritual journey packages, designed to provide you with an unforgettable pilgrimage experience.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="stats-mini d-flex justify-content-lg-end gap-4">
                        <div class="text-center">
                            <h4 class="text-white mb-1">150+</h4>
                            <small class="opacity-75">Packages</small>
                        </div>
                        <div class="text-center">
                            <h4 class="text-white mb-1">4.8★</h4>
                            <small class="opacity-75">Avg Rating</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row g-4">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="filters-card sticky-top">
                    <h5 class="mb-4">
                        <i class="fas fa-filter text-primary me-2"></i>
                        Filter Packages
                    </h5>

                    <!-- Search -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Search Packages</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search by name or destination..." id="packageSearch">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Price Range</label>
                        @foreach($filters['price_ranges'] as $key => $range)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="{{ $key }}" id="price_{{ $key }}">
                            <label class="form-check-label" for="price_{{ $key }}">
                                {{ $range }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Duration -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Duration</label>
                        @foreach($filters['durations'] as $days => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="{{ $days }}" id="duration_{{ $days }}">
                            <label class="form-check-label" for="duration_{{ $days }}">
                                {{ $label }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Destinations -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Destinations</label>
                        @foreach($filters['destinations'] as $destination)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="{{ $destination }}" id="dest_{{ str_replace(' ', '_', $destination) }}">
                            <label class="form-check-label" for="dest_{{ str_replace(' ', '_', $destination) }}">
                                {{ $destination }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Star Rating -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Minimum Rating</label>
                        <select class="form-select" id="ratingFilter">
                            <option value="">Any Rating</option>
                            <option value="4.5">4.5+ Stars</option>
                            <option value="4.0">4.0+ Stars</option>
                            <option value="3.5">3.5+ Stars</option>
                            <option value="3.0">3.0+ Stars</option>
                        </select>
                    </div>

                    <!-- Package Type -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Package Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="luxury" id="type_luxury">
                            <label class="form-check-label" for="type_luxury">Luxury</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="family" id="type_family">
                            <label class="form-check-label" for="type_family">Family</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="budget" id="type_budget">
                            <label class="form-check-label" for="type_budget">Budget</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="group" id="type_group">
                            <label class="form-check-label" for="type_group">Group</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <x-customer.button variant="primary" size="md" fullWidth="true" id="applyFilters">
                            <i class="fas fa-check me-2"></i>Apply Filters
                        </x-customer.button>
                        <x-customer.button variant="outline-secondary" size="sm" fullWidth="true" id="clearFilters">
                            <i class="fas fa-times me-2"></i>Clear All
                        </x-customer.button>
                    </div>
                </x-customer.card>
            </div>

            <!-- Packages Content -->
            <div class="col-lg-9">
                <!-- Results Header -->
                <div class="results-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">Available Packages</h5>
                        <p class="text-muted mb-0" id="resultCount">Showing all packages</p>
                    </div>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <label class="form-label small mb-0 me-2">Sort by:</label>
                        <select class="form-select form-select-sm" style="width: auto;" id="sortBy">
                            <option value="featured">Featured</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="rating">Highest Rated</option>
                            <option value="duration">Duration</option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>
                </div>

                <!-- Sample Packages (since packages array is empty) -->
                <div class="packages-grid" id="packagesContainer">
                    <!-- Package 1 -->
                    <x-customer.card variant="elevated" elevation="md" padding="none" class="package-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <div class="package-image-container">
                                    <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop" 
                                         class="package-image" alt="Premium Umrah Package">
                                    <div class="package-badge">
                                        <span class="badge bg-warning">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    </div>
                                    <div class="package-overlay">
                                        <div class="rating-overlay">
                                            <i class="fas fa-star text-warning"></i>
                                            <span class="text-white ms-1">4.8</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="package-content p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="package-title mb-2">Premium Umrah Experience</h5>
                                            <div class="package-meta mb-2">
                                                <span class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>14 Days
                                                </span>
                                                <span class="text-muted ms-3">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Makkah & Madinah
                                                </span>
                                            </div>
                                        </div>
                                        <div class="price-display text-end">
                                            <h4 class="text-primary mb-0">$3,500</h4>
                                            <small class="text-muted">per person</small>
                                        </div>
                                    </div>
                                    
                                    <p class="package-description text-muted mb-3">
                                        Complete spiritual journey with 5-star accommodations in Makkah and Madinah, guided tours, and premium transportation services.
                                    </p>
                                    
                                    <div class="package-features mb-3">
                                        <span class="feature-tag">
                                            <i class="fas fa-hotel text-primary me-1"></i>5-Star Hotel
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-plane text-success me-1"></i>Direct Flights
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-user-tie text-info me-1"></i>Guide Included
                                        </span>
                                    </div>
                                    
                                    <div class="package-actions d-flex gap-2">
                                        <x-customer.button 
                                            href="{{ route('packages.details', 1) }}" 
                                            variant="primary" 
                                            size="md"
                                            class="flex-grow-1"
                                        >
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-heart"></i>
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-share-alt"></i>
                                        </x-customer.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>

                    <!-- Package 2 -->
                    <x-customer.card variant="elevated" elevation="md" padding="none" class="package-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <div class="package-image-container">
                                    <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop" 
                                         class="package-image" alt="Family Umrah Package">
                                    <div class="package-overlay">
                                        <div class="rating-overlay">
                                            <i class="fas fa-star text-warning"></i>
                                            <span class="text-white ms-1">4.7</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="package-content p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="package-title mb-2">Family Umrah Package</h5>
                                            <div class="package-meta mb-2">
                                                <span class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>10 Days
                                                </span>
                                                <span class="text-muted ms-3">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Makkah & Madinah
                                                </span>
                                            </div>
                                        </div>
                                        <div class="price-display text-end">
                                            <h4 class="text-primary mb-0">$2,800</h4>
                                            <small class="text-muted">per person</small>
                                        </div>
                                    </div>
                                    
                                    <p class="package-description text-muted mb-3">
                                        Perfect for families seeking a meaningful spiritual experience with child-friendly services and spacious accommodations.
                                    </p>
                                    
                                    <div class="package-features mb-3">
                                        <span class="feature-tag">
                                            <i class="fas fa-users text-primary me-1"></i>Family Rooms
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-child text-success me-1"></i>Kid Friendly
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-utensils text-info me-1"></i>Meals Included
                                        </span>
                                    </div>
                                    
                                    <div class="package-actions d-flex gap-2">
                                        <x-customer.button 
                                            href="{{ route('packages.details', 2) }}" 
                                            variant="primary" 
                                            size="md"
                                            class="flex-grow-1"
                                        >
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-heart"></i>
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-share-alt"></i>
                                        </x-customer.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>

                    <!-- Package 3 -->
                    <x-customer.card variant="elevated" elevation="md" padding="none" class="package-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <div class="package-image-container">
                                    <img src="https://images.unsplash.com/photo-1549308509-7e78b5f4b8a9?w=400&h=300&fit=crop" 
                                         class="package-image" alt="Budget Spiritual Journey">
                                    <div class="package-badge">
                                        <span class="badge bg-success">
                                            <i class="fas fa-tag me-1"></i>Best Value
                                        </span>
                                    </div>
                                    <div class="package-overlay">
                                        <div class="rating-overlay">
                                            <i class="fas fa-star text-warning"></i>
                                            <span class="text-white ms-1">4.5</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="package-content p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="package-title mb-2">Budget Spiritual Journey</h5>
                                            <div class="package-meta mb-2">
                                                <span class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>7 Days
                                                </span>
                                                <span class="text-muted ms-3">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Makkah & Madinah
                                                </span>
                                            </div>
                                        </div>
                                        <div class="price-display text-end">
                                            <h4 class="text-primary mb-0">$1,800</h4>
                                            <small class="text-muted">per person</small>
                                        </div>
                                    </div>
                                    
                                    <p class="package-description text-muted mb-3">
                                        Affordable yet comprehensive Umrah package without compromising on the essential spiritual experience.
                                    </p>
                                    
                                    <div class="package-features mb-3">
                                        <span class="feature-tag">
                                            <i class="fas fa-bed text-primary me-1"></i>3-Star Hotel
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-plane text-success me-1"></i>Return Flights
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-passport text-info me-1"></i>Visa Support
                                        </span>
                                    </div>
                                    
                                    <div class="package-actions d-flex gap-2">
                                        <x-customer.button 
                                            href="{{ route('packages.details', 3) }}" 
                                            variant="primary" 
                                            size="md"
                                            class="flex-grow-1"
                                        >
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-heart"></i>
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-share-alt"></i>
                                        </x-customer.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>

                    <!-- Package 4 -->
                    <x-customer.card variant="elevated" elevation="md" padding="none" class="package-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <div class="package-image-container">
                                    <img src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?w=400&h=300&fit=crop" 
                                         class="package-image" alt="Luxury Umrah Retreat">
                                    <div class="package-badge">
                                        <span class="badge bg-danger">
                                            <i class="fas fa-crown me-1"></i>Luxury
                                        </span>
                                    </div>
                                    <div class="package-overlay">
                                        <div class="rating-overlay">
                                            <i class="fas fa-star text-warning"></i>
                                            <span class="text-white ms-1">4.9</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="package-content p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="package-title mb-2">Luxury Umrah Retreat</h5>
                                            <div class="package-meta mb-2">
                                                <span class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>21 Days
                                                </span>
                                                <span class="text-muted ms-3">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Makkah & Madinah
                                                </span>
                                            </div>
                                        </div>
                                        <div class="price-display text-end">
                                            <h4 class="text-primary mb-0">$5,200</h4>
                                            <small class="text-muted">per person</small>
                                        </div>
                                    </div>
                                    
                                    <p class="package-description text-muted mb-3">
                                        Ultimate luxury experience with exclusive services, private transfers, and personalized spiritual guidance.
                                    </p>
                                    
                                    <div class="package-features mb-3">
                                        <span class="feature-tag">
                                            <i class="fas fa-gem text-primary me-1"></i>5-Star Luxury
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-car text-success me-1"></i>Private Transfer
                                        </span>
                                        <span class="feature-tag">
                                            <i class="fas fa-concierge-bell text-info me-1"></i>Concierge
                                        </span>
                                    </div>
                                    
                                    <div class="package-actions d-flex gap-2">
                                        <x-customer.button 
                                            href="{{ route('packages.details', 4) }}" 
                                            variant="primary" 
                                            size="md"
                                            class="flex-grow-1"
                                        >
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-heart"></i>
                                        </x-customer.button>
                                        <x-customer.button variant="outline-primary" size="md">
                                            <i class="fas fa-share-alt"></i>
                                        </x-customer.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>

                <!-- Load More -->
                <div class="text-center mt-4">
                    <x-customer.button variant="outline-primary" size="lg" id="loadMore">
                        <i class="fas fa-plus me-2"></i>Load More Packages
                    </x-customer.button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.packages-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.packages-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.filters-card {
    position: sticky;
    top: 2rem;
}

.filter-section {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1rem;
}

.filter-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.package-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.package-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(var(--primary-rgb), 0.15) !important;
}

.package-image-container {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.package-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.package-card:hover .package-image {
    transform: scale(1.05);
}

.package-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 2;
}

.package-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 2rem 1rem 1rem;
}

.rating-overlay {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.package-title {
    color: var(--text-primary);
    font-weight: 600;
}

.package-meta {
    font-size: 0.9rem;
}

.package-description {
    font-size: 0.95rem;
    line-height: 1.5;
}

.feature-tag {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--surface-variant-color);
    border-radius: 20px;
    font-size: 0.8rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}

.price-display h4 {
    font-weight: 700;
}

.results-header {
    padding: 1rem;
    background: var(--surface-variant-color);
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .packages-header {
        background-attachment: scroll;
        text-align: center;
    }
    
    .stats-mini {
        justify-content: center !important;
    }
    
    .filters-card {
        position: relative;
        top: auto;
        margin-bottom: 2rem;
    }
    
    .package-image-container {
        height: 200px;
    }
    
    .package-content {
        padding: 1.5rem !important;
    }
    
    .package-actions {
        flex-direction: column;
    }
    
    .package-actions .flex-grow-1 {
        flex-grow: 0 !important;
    }
}

/* Loading animation */
.package-card.loading {
    opacity: 0.7;
    pointer-events: none;
}

.package-card.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const packageSearch = document.getElementById('packageSearch');
    const sortBy = document.getElementById('sortBy');
    const packagesContainer = document.getElementById('packagesContainer');
    const resultCount = document.getElementById('resultCount');

    // Apply filters
    applyFiltersBtn.addEventListener('click', function() {
        applyFilters();
    });

    // Clear filters
    clearFiltersBtn.addEventListener('click', function() {
        // Reset all form elements
        document.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.checked = false;
        });
        packageSearch.value = '';
        sortBy.value = 'featured';
        document.getElementById('ratingFilter').value = '';
        
        // Reapply filters (which will show all packages)
        applyFilters();
    });

    // Search functionality
    packageSearch.addEventListener('input', function() {
        applyFilters();
    });

    // Sort functionality
    sortBy.addEventListener('change', function() {
        applySorting();
    });

    // Load more functionality
    document.getElementById('loadMore').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        
        // Simulate loading
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-plus me-2"></i>Load More Packages';
            // Add more packages here
        }, 1000);
    });

    function applyFilters() {
        const searchTerm = packageSearch.value.toLowerCase();
        const selectedPrices = getCheckedValues('.filter-section input[id^="price_"]');
        const selectedDurations = getCheckedValues('.filter-section input[id^="duration_"]');
        const selectedDestinations = getCheckedValues('.filter-section input[id^="dest_"]');
        const selectedTypes = getCheckedValues('.filter-section input[id^="type_"]');
        const minRating = document.getElementById('ratingFilter').value;

        const packages = document.querySelectorAll('.package-card');
        let visibleCount = 0;

        packages.forEach(packageCard => {
            let shouldShow = true;

            // Apply search filter
            if (searchTerm) {
                const title = packageCard.querySelector('.package-title').textContent.toLowerCase();
                const description = packageCard.querySelector('.package-description').textContent.toLowerCase();
                if (!title.includes(searchTerm) && !description.includes(searchTerm)) {
                    shouldShow = false;
                }
            }

            // Apply other filters here based on package data
            // This is simplified since we're using static HTML

            if (shouldShow) {
                packageCard.style.display = 'block';
                visibleCount++;
            } else {
                packageCard.style.display = 'none';
            }
        });

        updateResultCount(visibleCount);
    }

    function applySorting() {
        const sortValue = sortBy.value;
        const packagesContainer = document.getElementById('packagesContainer');
        const packages = Array.from(packagesContainer.querySelectorAll('.package-card'));

        packages.sort((a, b) => {
            switch (sortValue) {
                case 'price_low':
                    return getPriceFromElement(a) - getPriceFromElement(b);
                case 'price_high':
                    return getPriceFromElement(b) - getPriceFromElement(a);
                case 'rating':
                    return getRatingFromElement(b) - getRatingFromElement(a);
                case 'duration':
                    return getDurationFromElement(a) - getDurationFromElement(b);
                default:
                    return 0; // Keep original order for 'featured' and 'newest'
            }
        });

        // Reorder elements in DOM
        packages.forEach(package => packagesContainer.appendChild(package));
    }

    function getCheckedValues(selector) {
        return Array.from(document.querySelectorAll(selector + ':checked')).map(cb => cb.value);
    }

    function getPriceFromElement(element) {
        const priceText = element.querySelector('.price-display h4').textContent;
        return parseInt(priceText.replace(/[^0-9]/g, ''));
    }

    function getRatingFromElement(element) {
        const ratingText = element.querySelector('.rating-overlay span').textContent;
        return parseFloat(ratingText);
    }

    function getDurationFromElement(element) {
        const durationText = element.querySelector('.package-meta span').textContent;
        return parseInt(durationText.replace(/[^0-9]/g, ''));
    }

    function updateResultCount(count) {
        resultCount.textContent = count === 1 ? 'Showing 1 package' : `Showing ${count} packages`;
    }

    // Initialize
    updateResultCount(document.querySelectorAll('.package-card').length);
});
</script>
@endpush
