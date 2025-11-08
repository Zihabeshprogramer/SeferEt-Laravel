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
                    
                    <form method="GET" action="{{ route('packages') }}" id="filtersForm">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}" id="sortHidden">
                        @endif

                    <!-- Search -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Search Packages</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search by name or destination..." value="{{ request('search') }}" id="packageSearch">
                            <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('filtersForm').submit();">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Departure City -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Departure City</label>
                        <div class="position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="departure-filter-input"
                                   name="departure" 
                                   placeholder="Select departure city"
                                   value="{{ request('departure') }}"
                                   autocomplete="off">
                            <div id="departure-filter-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>
                        @if(request('departure'))
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-plane-departure me-1"></i>Departing from {{ request('departure') }}
                            </small>
                        @endif
                    </div>
                    
                    <!-- Travelers -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Travelers</label>
                        <select class="form-select" name="travelers" id="travelersFilter">
                            <option value="">Any Number</option>
                            <option value="1" {{ request('travelers') == '1' ? 'selected' : '' }}>1 Person</option>
                            <option value="2" {{ request('travelers') == '2' ? 'selected' : '' }}>2 People</option>
                            <option value="3" {{ request('travelers') == '3' ? 'selected' : '' }}>3 People</option>
                            <option value="4" {{ request('travelers') == '4' ? 'selected' : '' }}>4 People</option>
                            <option value="5" {{ request('travelers') == '5' ? 'selected' : '' }}>5+ People</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Price Range</label>
                        @foreach($filterOptions['price_ranges'] as $key => $range)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="price_range[]" value="{{ $key }}" id="price_{{ $key }}" 
                                   {{ in_array($key, request('price_range', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="price_{{ $key }}">
                                {{ $range }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Duration -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Duration</label>
                        @foreach($filterOptions['durations'] as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="duration[]" value="{{ $key }}" id="duration_{{ $key }}" 
                                   {{ in_array($key, request('duration', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="duration_{{ $key }}">
                                {{ $label }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Destinations -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Destinations</label>
                        <div class="position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="destination-filter-input"
                                   placeholder="Search and select destinations"
                                   autocomplete="off">
                            <div id="destination-filter-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>
                        
                        <!-- Selected Destinations Tags -->
                        <div id="selected-destinations" class="mt-2 d-flex flex-wrap gap-2">
                            @if(request('destinations'))
                                @foreach(request('destinations') as $destination)
                                    <span class="badge bg-primary destination-tag" data-destination="{{ $destination }}">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $destination }}
                                        <button type="button" class="btn-close btn-close-white btn-sm ms-1" style="font-size: 0.6rem;" aria-label="Remove"></button>
                                    </span>
                                @endforeach
                            @endif
                        </div>
                        
                        <!-- Hidden inputs for selected destinations -->
                        <div id="destination-hidden-inputs">
                            @if(request('destinations'))
                                @foreach(request('destinations') as $destination)
                                    <input type="hidden" name="destinations[]" value="{{ $destination }}">
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Star Rating -->
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Minimum Rating</label>
                        <select class="form-select" name="rating" id="ratingFilter">
                            <option value="">Any Rating</option>
                            @foreach($filterOptions['ratings'] as $value => $label)
                            <option value="{{ $value }}" {{ request('rating') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Package Type -->
                    @if(!empty($filterOptions['types']) && count($filterOptions['types']) > 0)
                    <div class="filter-section mb-4">
                        <label class="form-label fw-semibold">Package Type</label>
                        <div class="position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="type-filter-input"
                                   placeholder="Search and select types"
                                   autocomplete="off">
                            <div id="type-filter-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>
                        
                        <!-- Selected Types Tags -->
                        <div id="selected-types" class="mt-2 d-flex flex-wrap gap-2">
                            @if(request('type'))
                                @foreach(request('type') as $typeKey)
                                    @if(isset($filterOptions['types'][$typeKey]))
                                        <span class="badge bg-info text-dark type-tag" data-type="{{ $typeKey }}" data-label="{{ $filterOptions['types'][$typeKey] }}">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $filterOptions['types'][$typeKey] }}
                                            <button type="button" class="btn-close btn-sm ms-1" style="font-size: 0.6rem;" aria-label="Remove"></button>
                                        </span>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        
                        <!-- Hidden inputs for selected types -->
                        <div id="type-hidden-inputs">
                            @if(request('type'))
                                @foreach(request('type') as $typeKey)
                                    <input type="hidden" name="type[]" value="{{ $typeKey }}" data-type="{{ $typeKey }}">
                                @endforeach
                            @endif
                        </div>
                        
                        <!-- All available types for JS (hidden) -->
                        <script id="available-types-data" type="application/json">
                            {!! json_encode($filterOptions['types']) !!}
                        </script>
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <x-customer.button type="submit" form="filtersForm" variant="primary" size="md" fullWidth="true" id="applyFilters">
                            <i class="fas fa-check me-2"></i>Apply Filters
                        </x-customer.button>
                        <x-customer.button href="{{ route('packages') }}" variant="outline-secondary" size="sm" fullWidth="true" id="clearFilters">
                            <i class="fas fa-times me-2"></i>Clear All Filters
                        </x-customer.button>
                    </div>
                    </form>
                </x-customer.card>
            </div>

            <!-- Packages Content -->
            <div class="col-lg-9">
                <!-- Results Header -->
                <div class="results-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">Available Packages</h5>
                        <p class="text-muted mb-0" id="resultCount">
                            @if(request('search'))
                                Search results for "{{ request('search') }}" - {{ $packages->total() }} packages found
                            @else
                                Showing {{ $packages->count() }} of {{ $packages->total() }} packages
                            @endif
                        </p>
                    </div>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <label class="form-label small mb-0 me-2">Sort by:</label>
                        <select class="form-select form-select-sm" name="sort" style="width: auto;" id="sortBy">
                            <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                            <option value="duration" {{ request('sort') == 'duration' ? 'selected' : '' }}>Duration</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Packages -->
                <div class="packages-grid" id="packagesContainer">
                    @forelse($packages as $package)
                    <x-customer.card variant="elevated" elevation="md" padding="none" class="package-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <div class="package-image-container">
                                    @if($package->main_image)
                                        <img src="{{ $package->main_image }}" class="package-image" alt="{{ $package->name }}" loading="lazy">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=400&h=300&fit=crop" 
                                             class="package-image" alt="{{ $package->name }}" loading="lazy">
                                    @endif
                                    
                                    @if($package->is_featured)
                                    <div class="package-badge">
                                        <span class="badge bg-warning">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    </div>
                                    @endif
                                    
                                    @if($package->is_premium)
                                    <div class="package-badge" style="top: 3rem; left: 1rem;">
                                        <span class="badge bg-danger">
                                            <i class="fas fa-crown me-1"></i>Premium
                                        </span>
                                    </div>
                                    @endif
                                    
                                    <div class="package-overlay">
                                        <div class="rating-overlay">
                                            @if($package->average_rating > 0)
                                                <i class="fas fa-star text-warning"></i>
                                                <span class="text-white ms-1">{{ number_format($package->average_rating, 1) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="package-content p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="package-title mb-2">{{ $package->name }}</h5>
                                            <div class="package-meta mb-2">
                                                <span class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>{{ $package->formatted_duration }}
                                                </span>
                                                <span class="text-muted ms-3">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $package->destinations_text }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="price-display text-end">
                                            <h4 class="text-primary mb-0">{{ $package->formatted_price }}</h4>
                                            <small class="text-muted">per person</small>
                                        </div>
                                    </div>
                                    
                                    <p class="package-description text-muted mb-3">
                                        {{ $package->description }}
                                    </p>
                                    
                                    <div class="package-features mb-3">
                                        @foreach($package->includes as $include)
                                        <span class="feature-tag">
                                            <i class="fas fa-check text-success me-1"></i>{{ $include }}
                                        </span>
                                        @endforeach
                                        
                                        @foreach($package->features as $feature)
                                        <span class="feature-tag">
                                            <i class="fas fa-star text-warning me-1"></i>{{ $feature }}
                                        </span>
                                        @endforeach
                                    </div>
                                    
                                    <div class="package-actions d-flex gap-2">
                                        <x-customer.button 
                                            href="{{ $package->url }}" 
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
                    @empty
                    <!-- Empty State -->
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h4 class="empty-title">No packages found</h4>
                        <p class="empty-description text-muted">
                            @if(request('search'))
                                We couldn't find any packages matching "{{ request('search') }}". Try adjusting your search or filters.
                            @else
                                No packages are currently available with the selected filters.
                            @endif
                        </p>
                        <div class="empty-actions mt-3">
                            @if(request()->hasAny(['search', 'price_range', 'duration', 'destinations', 'type', 'rating']))
                                <x-customer.button href="{{ route('packages') }}" variant="primary">
                                    <i class="fas fa-refresh me-2"></i>Clear All Filters
                                </x-customer.button>
                            @endif
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($packages->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $packages->withQueryString()->links() }}
                </div>
                @endif
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

/* Custom Pagination Styles */
.pagination {
    gap: 0.25rem;
}

.pagination .page-item .page-link {
    border-radius: 8px;
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    transition: all 0.2s ease;
    font-weight: 500;
}

.pagination .page-item .page-link:hover {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.2);
}

.pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.3);
}

.pagination .page-item.disabled .page-link {
    background-color: var(--surface-variant-color);
    border-color: var(--border-color);
    color: var(--text-disabled);
    cursor: not-allowed;
}

.pagination .page-link i {
    font-size: 0.875rem;
}

/* Mobile Pagination */
@media (max-width: 576px) {
    .pagination .page-item .page-link {
        padding: 0.375rem 0.625rem;
        font-size: 0.875rem;
    }
    
    nav p.small {
        font-size: 0.75rem !important;
    }
}

/* Departure City Autocomplete Styles */
#departure-filter-suggestions {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    margin-top: 2px;
    background: white;
}

#departure-filter-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

#departure-filter-suggestions .list-group-item:first-child {
    border-top: none;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#departure-filter-suggestions .list-group-item:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

#departure-filter-suggestions .list-group-item:hover {
    background-color: #e7f1ff;
    color: #0d6efd;
}

/* Destination Autocomplete Styles */
#destination-filter-suggestions {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    margin-top: 2px;
    background: white;
}

#destination-filter-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

#destination-filter-suggestions .list-group-item:first-child {
    border-top: none;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#destination-filter-suggestions .list-group-item:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

#destination-filter-suggestions .list-group-item:hover {
    background-color: #e7f1ff;
    color: #0d6efd;
}

/* Destination Tags Styles */
.destination-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.6rem;
    font-size: 0.875rem;
    cursor: default;
}

.destination-tag .btn-close {
    margin-left: 0.4rem;
    cursor: pointer;
    opacity: 0.8;
}

.destination-tag .btn-close:hover {
    opacity: 1;
}

/* Package Type Autocomplete Styles */
#type-filter-suggestions {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    margin-top: 2px;
    background: white;
}

#type-filter-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

#type-filter-suggestions .list-group-item:first-child {
    border-top: none;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#type-filter-suggestions .list-group-item:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

#type-filter-suggestions .list-group-item:hover {
    background-color: #cff4fc;
    color: #055160;
}

/* Package Type Tags Styles */
.type-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.6rem;
    font-size: 0.875rem;
    cursor: default;
}

.type-tag .btn-close {
    margin-left: 0.4rem;
    cursor: pointer;
    opacity: 0.8;
}

.type-tag .btn-close:hover {
    opacity: 1;
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
    const filtersForm = document.getElementById('filtersForm');

    // Auto-submit form when sort changes
    if (sortBy) {
        sortBy.addEventListener('change', function() {
            // Update hidden sort field in filters form
            const sortHidden = document.getElementById('sortHidden');
            if (sortHidden) {
                sortHidden.value = this.value;
            } else {
                // Create hidden input if it doesn't exist
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sort';
                input.id = 'sortHidden';
                input.value = this.value;
                filtersForm.appendChild(input);
            }
            
            if (filtersForm) {
                filtersForm.submit();
            }
        });
    }
    
    // Auto-submit filter form on Enter in search box
    if (packageSearch) {
        packageSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (filtersForm) {
                    filtersForm.submit();
                }
            }
        });
    }
    
    // Filter change handlers
    const filterInputs = document.querySelectorAll('#filtersForm input[type="checkbox"], #filtersForm select');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Debounce the form submission
            clearTimeout(input.submitTimer);
            input.submitTimer = setTimeout(() => {
                if (filtersForm) {
                    filtersForm.submit();
                }
            }, 300);
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Loading state for form submissions
    if (filtersForm) {
        filtersForm.addEventListener('submit', function() {
            const submitBtn = filtersForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Filtering...';
                submitBtn.disabled = true;
                
                // Re-enable after timeout as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    }

    // Show user feedback for no results
    const noResultsMessage = document.querySelector('.empty-state');
    if (noResultsMessage) {
        console.log('No packages found with current filters');
    }
    
    // Departure City Autocomplete for Filter
    const departureFilterInput = document.getElementById('departure-filter-input');
    const departureFilterSuggestions = document.getElementById('departure-filter-suggestions');
    let departureFilterTimeout;
    
    if (departureFilterInput && departureFilterSuggestions) {
        departureFilterInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(departureFilterTimeout);
            
            if (query.length < 2) {
                departureFilterSuggestions.style.display = 'none';
                departureFilterSuggestions.innerHTML = '';
                return;
            }
            
            departureFilterTimeout = setTimeout(function() {
                fetch('{{ route("api.packages.departure-cities") }}?query=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.cities && data.cities.length > 0) {
                            let html = '';
                            data.cities.forEach(function(city) {
                                html += `<a href="#" class="list-group-item list-group-item-action departure-filter-item" 
                                           data-city="${city}">
                                            <i class="fas fa-plane-departure me-2 text-primary"></i>
                                            <strong>${city}</strong>
                                         </a>`;
                            });
                            departureFilterSuggestions.innerHTML = html;
                            departureFilterSuggestions.style.display = 'block';
                        } else {
                            departureFilterSuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>No cities found</div>';
                            departureFilterSuggestions.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Departure city search error:', error);
                        departureFilterSuggestions.style.display = 'none';
                    });
            }, 300);
        });
        
        // Handle city selection
        document.addEventListener('click', function(e) {
            const departureFilterItem = e.target.closest('.departure-filter-item');
            if (departureFilterItem) {
                e.preventDefault();
                const cityName = departureFilterItem.getAttribute('data-city');
                
                departureFilterInput.value = cityName;
                departureFilterSuggestions.style.display = 'none';
                departureFilterSuggestions.innerHTML = '';
                
                // Auto-submit form after selection
                if (filtersForm) {
                    filtersForm.submit();
                }
            }
            
        // Hide suggestions when clicking outside
            if (!e.target.closest('#departure-filter-input') && !e.target.closest('#departure-filter-suggestions')) {
                departureFilterSuggestions.style.display = 'none';
            }
        });
    }
    
    // Destination Multi-Select Autocomplete for Filter
    const destinationFilterInput = document.getElementById('destination-filter-input');
    const destinationFilterSuggestions = document.getElementById('destination-filter-suggestions');
    const selectedDestinationsContainer = document.getElementById('selected-destinations');
    const destinationHiddenInputsContainer = document.getElementById('destination-hidden-inputs');
    let destinationFilterTimeout;
    let selectedDestinations = [];
    
    // Initialize selected destinations from existing tags
    if (selectedDestinationsContainer) {
        const existingTags = selectedDestinationsContainer.querySelectorAll('.destination-tag');
        existingTags.forEach(tag => {
            const destination = tag.getAttribute('data-destination');
            if (destination && !selectedDestinations.includes(destination)) {
                selectedDestinations.push(destination);
            }
        });
    }
    
    if (destinationFilterInput && destinationFilterSuggestions) {
        destinationFilterInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(destinationFilterTimeout);
            
            if (query.length < 2) {
                destinationFilterSuggestions.style.display = 'none';
                destinationFilterSuggestions.innerHTML = '';
                return;
            }
            
            destinationFilterTimeout = setTimeout(function() {
                fetch('{{ route("api.packages.destinations") }}?query=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.destinations && data.destinations.length > 0) {
                            let html = '';
                            data.destinations.forEach(function(destination) {
                                // Don't show already selected destinations
                                if (!selectedDestinations.includes(destination)) {
                                    html += `<a href="#" class="list-group-item list-group-item-action destination-filter-item" 
                                               data-destination="${destination}">
                                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                                <strong>${destination}</strong>
                                             </a>`;
                                }
                            });
                            if (html) {
                                destinationFilterSuggestions.innerHTML = html;
                                destinationFilterSuggestions.style.display = 'block';
                            } else {
                                destinationFilterSuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>All matching destinations selected</div>';
                                destinationFilterSuggestions.style.display = 'block';
                            }
                        } else {
                            destinationFilterSuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>No destinations found</div>';
                            destinationFilterSuggestions.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Destination search error:', error);
                        destinationFilterSuggestions.style.display = 'none';
                    });
            }, 300);
        });
        
        // Handle destination selection
        document.addEventListener('click', function(e) {
            const destinationFilterItem = e.target.closest('.destination-filter-item');
            if (destinationFilterItem) {
                e.preventDefault();
                const destinationName = destinationFilterItem.getAttribute('data-destination');
                
                // Add to selected destinations if not already added
                if (!selectedDestinations.includes(destinationName)) {
                    selectedDestinations.push(destinationName);
                    
                    // Create tag element
                    const tagHtml = `
                        <span class="badge bg-primary destination-tag" data-destination="${destinationName}">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            ${destinationName}
                            <button type="button" class="btn-close btn-close-white btn-sm ms-1" style="font-size: 0.6rem;" aria-label="Remove"></button>
                        </span>
                    `;
                    selectedDestinationsContainer.insertAdjacentHTML('beforeend', tagHtml);
                    
                    // Create hidden input
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'destinations[]';
                    hiddenInput.value = destinationName;
                    hiddenInput.setAttribute('data-destination', destinationName);
                    destinationHiddenInputsContainer.appendChild(hiddenInput);
                }
                
                // Clear input and hide suggestions
                destinationFilterInput.value = '';
                destinationFilterSuggestions.style.display = 'none';
                destinationFilterSuggestions.innerHTML = '';
                
                // Auto-submit form after selection
                if (filtersForm) {
                    filtersForm.submit();
                }
            }
            
            // Hide suggestions when clicking outside
            if (!e.target.closest('#destination-filter-input') && !e.target.closest('#destination-filter-suggestions')) {
                destinationFilterSuggestions.style.display = 'none';
            }
        });
        
        // Handle destination tag removal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.destination-tag .btn-close')) {
                e.preventDefault();
                const tag = e.target.closest('.destination-tag');
                const destination = tag.getAttribute('data-destination');
                
                // Remove from array
                const index = selectedDestinations.indexOf(destination);
                if (index > -1) {
                    selectedDestinations.splice(index, 1);
                }
                
                // Remove tag element
                tag.remove();
                
                // Remove hidden input
                const hiddenInput = destinationHiddenInputsContainer.querySelector(`input[data-destination="${destination}"]`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                
                // Auto-submit form after removal
                if (filtersForm) {
                    filtersForm.submit();
                }
            }
        });
    }
    
    // Package Type Multi-Select Autocomplete
    const typeFilterInput = document.getElementById('type-filter-input');
    const typeFilterSuggestions = document.getElementById('type-filter-suggestions');
    const selectedTypesContainer = document.getElementById('selected-types');
    const typeHiddenInputsContainer = document.getElementById('type-hidden-inputs');
    const availableTypesScript = document.getElementById('available-types-data');
    let selectedTypes = [];
    let availableTypes = {};
    
    // Load available types from JSON script
    if (availableTypesScript) {
        try {
            availableTypes = JSON.parse(availableTypesScript.textContent);
            console.log('Loaded available types:', availableTypes);
            console.log('Number of types:', Object.keys(availableTypes).length);
        } catch (e) {
            console.error('Failed to parse available types:', e);
        }
    } else {
        console.error('Available types script not found');
    }
    
    // Initialize selected types from existing tags
    if (selectedTypesContainer) {
        const existingTags = selectedTypesContainer.querySelectorAll('.type-tag');
        existingTags.forEach(tag => {
            const typeKey = tag.getAttribute('data-type');
            if (typeKey && !selectedTypes.includes(typeKey)) {
                selectedTypes.push(typeKey);
            }
        });
    }
    
    if (typeFilterInput && typeFilterSuggestions && Object.keys(availableTypes).length > 0) {
        // Show all types on focus if input is empty
        typeFilterInput.addEventListener('focus', function() {
            if (this.value.trim() === '') {
                showTypesSuggestions('');
            }
        });
        
        typeFilterInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            showTypesSuggestions(query);
        });
        
        function showTypesSuggestions(query) {
            let html = '';
            let matchCount = 0;
            
            Object.keys(availableTypes).forEach(function(typeKey) {
                const typeLabel = availableTypes[typeKey];
                
                // Don't show already selected types
                if (!selectedTypes.includes(typeKey)) {
                    // Filter by query
                    if (query === '' || typeLabel.toLowerCase().includes(query)) {
                        html += `<a href="#" class="list-group-item list-group-item-action type-filter-item" 
                                   data-type="${typeKey}"
                                   data-label="${typeLabel}">
                                    <i class="fas fa-tag me-2 text-info"></i>
                                    <strong>${typeLabel}</strong>
                                 </a>`;
                        matchCount++;
                    }
                }
            });
            
            if (html) {
                typeFilterSuggestions.innerHTML = html;
                typeFilterSuggestions.style.display = 'block';
            } else if (matchCount === 0 && query !== '') {
                typeFilterSuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>No types found</div>';
                typeFilterSuggestions.style.display = 'block';
            } else {
                typeFilterSuggestions.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>All types selected</div>';
                typeFilterSuggestions.style.display = 'block';
            }
        }
        
        // Handle type selection
        document.addEventListener('click', function(e) {
            const typeFilterItem = e.target.closest('.type-filter-item');
            if (typeFilterItem) {
                e.preventDefault();
                const typeKey = typeFilterItem.getAttribute('data-type');
                const typeLabel = typeFilterItem.getAttribute('data-label');
                
                // Add to selected types if not already added
                if (!selectedTypes.includes(typeKey)) {
                    selectedTypes.push(typeKey);
                    
                    // Create tag element
                    const tagHtml = `
                        <span class="badge bg-info text-dark type-tag" data-type="${typeKey}" data-label="${typeLabel}">
                            <i class="fas fa-tag me-1"></i>
                            ${typeLabel}
                            <button type="button" class="btn-close btn-sm ms-1" style="font-size: 0.6rem;" aria-label="Remove"></button>
                        </span>
                    `;
                    selectedTypesContainer.insertAdjacentHTML('beforeend', tagHtml);
                    
                    // Create hidden input
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'type[]';
                    hiddenInput.value = typeKey;
                    hiddenInput.setAttribute('data-type', typeKey);
                    typeHiddenInputsContainer.appendChild(hiddenInput);
                }
                
                // Clear input and hide suggestions
                typeFilterInput.value = '';
                typeFilterSuggestions.style.display = 'none';
                typeFilterSuggestions.innerHTML = '';
                
                // Auto-submit form after selection
                if (filtersForm) {
                    filtersForm.submit();
                }
            }
            
            // Hide suggestions when clicking outside
            if (!e.target.closest('#type-filter-input') && !e.target.closest('#type-filter-suggestions')) {
                typeFilterSuggestions.style.display = 'none';
            }
        });
        
        // Handle type tag removal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.type-tag .btn-close')) {
                e.preventDefault();
                const tag = e.target.closest('.type-tag');
                const typeKey = tag.getAttribute('data-type');
                
                // Remove from array
                const index = selectedTypes.indexOf(typeKey);
                if (index > -1) {
                    selectedTypes.splice(index, 1);
                }
                
                // Remove tag element
                tag.remove();
                
                // Remove hidden input
                const hiddenInput = typeHiddenInputsContainer.querySelector(`input[data-type="${typeKey}"]`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                
                // Auto-submit form after removal
                if (filtersForm) {
                    filtersForm.submit();
                }
            }
        });
    }
});
</script>
@endpush
