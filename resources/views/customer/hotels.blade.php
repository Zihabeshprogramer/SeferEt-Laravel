@extends('layouts.customer')

@section('title', 'Hotel Search - SeferEt')

@section('content')
    <!-- Hotels Search Header -->
    <div class="hotels-header bg-success text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-hotel me-3"></i>
                        Find Sacred Accommodations
                    </h1>
                    <p class="lead opacity-90 mb-0">Book comfortable stays near the Holy Mosques</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-bed fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Hotel Search Form -->
    <div class="search-section py-4 bg-light">
        <div class="container-fluid">
            <x-customer.card variant="elevated" elevation="md" padding="lg">
                <form class="hotel-search-form" method="GET" id="hotels-search-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Destination <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" 
                                       class="form-control" 
                                       id="hotels-destination-input"
                                       name="location_display" 
                                       placeholder="Type city name (e.g., Makkah, Dubai)..."
                                       autocomplete="off"
                                       value="{{ request('location_display') }}"
                                       required>
                                <input type="hidden" name="location" id="hotels-location-code" value="{{ request('location') }}" required>
                                <div id="hotels-city-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                            </div>
                            <small class="text-muted">Start typing to search cities</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Check-in <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="check_in" value="{{ request('check_in') }}" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Check-out <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="check_out" value="{{ request('check_out') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label fw-semibold">Rooms</label>
                            <select class="form-select" name="rooms">
                                <option value="1" {{ request('rooms') == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ request('rooms') == '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ request('rooms') == '3' ? 'selected' : '' }}>3</option>
                                <option value="4" {{ request('rooms') == '4' ? 'selected' : '' }}>4</option>
                                <option value="5" {{ request('rooms') == '5' ? 'selected' : '' }}>5+</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label fw-semibold">Guests</label>
                            <select class="form-select" name="guests">
                                <option value="1" {{ request('guests') == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ request('guests') == '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ request('guests') == '3' ? 'selected' : '' }}>3</option>
                                <option value="4" {{ request('guests') == '4' ? 'selected' : '' }}>4</option>
                                <option value="5" {{ request('guests') == '5' ? 'selected' : '' }}>5+</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <x-customer.button type="submit" variant="primary" size="lg" fullWidth="true">
                                <i class="fas fa-search me-2"></i>Search
                            </x-customer.button>
                        </div>
                    </div>
                </form>
            </x-customer.card>
        </div>
    </div>

    <!-- Hotel Results -->
    <div class="hotels-results py-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Available Hotels
                </h2>
                <div class="results-info">
                    <span class="text-muted">{{ count($hotels) }} hotels found</span>
                </div>
            </div>

            @if(count($hotels) > 0)
                <div class="row g-4">
                    @foreach($hotels as $hotel)
                    <div class="col-lg-6">
                        <x-customer.card variant="elevated" elevation="sm" hover="true" class="hotel-card h-100">
                            <div class="row g-0 h-100">
                                <div class="col-md-4">
                                    <div class="hotel-image">
                                        <img src="{{ $hotel['image'] }}" alt="{{ $hotel['name'] }}" class="img-fluid">
                                        <div class="hotel-badges">
                                            <span class="badge bg-primary">
                                                @for($i = 1; $i <= $hotel['stars']; $i++)
                                                    <i class="fas fa-star"></i>
                                                @endfor
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body h-100 d-flex flex-column">
                                        <div class="hotel-info mb-3">
                                            <h5 class="hotel-name mb-1">{{ $hotel['name'] }}</h5>
                                            <div class="hotel-location mb-2">
                                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                <span class="text-muted">{{ $hotel['location'] }}</span>
                                                <span class="distance-badge ms-2">
                                                    <i class="fas fa-mosque me-1"></i>{{ $hotel['distance_to_haram'] }}
                                                </span>
                                            </div>
                                            <div class="hotel-rating mb-2">
                                                <div class="stars">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= floor($hotel['rating']) ? 'text-warning' : 'text-muted' }}"></i>
                                                    @endfor
                                                </div>
                                                <span class="rating-text text-muted ms-1">({{ $hotel['rating'] }}/5)</span>
                                            </div>
                                            <div class="hotel-amenities">
                                                @foreach(array_slice($hotel['amenities'], 0, 4) as $amenity)
                                                    <span class="amenity-badge">{{ $amenity }}</span>
                                                @endforeach
                                                @if(count($hotel['amenities']) > 4)
                                                    <span class="more-amenities">+{{ count($hotel['amenities']) - 4 }} more</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="hotel-booking mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="price-info">
                                                    <span class="price-label text-muted">Per night from</span>
                                                    <h4 class="price text-success mb-0">${{ number_format($hotel['price']) }}</h4>
                                                    <small class="text-muted">+ taxes & fees</small>
                                                </div>
                                                <div class="booking-actions">
                                                    <x-customer.button href="{{ route('hotels.details', $hotel['id']) }}" variant="primary" size="sm">
                                                        <i class="fas fa-eye me-1"></i>View Details
                                                    </x-customer.button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-customer.card>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-hotel fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Hotels Found</h4>
                    <p class="text-muted mb-4">Try adjusting your search criteria to find available accommodations.</p>
                    <x-customer.button href="{{ route('home') }}" variant="primary">
                        <i class="fas fa-search me-2"></i>Search Again
                    </x-customer.button>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
.hotels-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.hotels-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.hotel-card {
    border-left: 4px solid var(--success-color);
    transition: all 0.3s ease;
}

.hotel-card:hover {
    border-left-color: var(--primary-color);
    transform: translateY(-2px);
}

.hotel-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.hotel-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.hotel-card:hover .hotel-image img {
    transform: scale(1.05);
}

.hotel-badges {
    position: absolute;
    top: 10px;
    left: 10px;
}

.hotel-name {
    font-weight: 600;
    color: var(--text-primary);
}

.distance-badge {
    background: var(--success-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
}

.amenity-badge {
    display: inline-block;
    background: var(--surface-variant-color);
    color: var(--text-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    margin: 0.125rem;
}

.more-amenities {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin-left: 0.5rem;
}

.price {
    font-weight: 700;
}

.price-label {
    font-size: 0.8rem;
    text-transform: uppercase;
}

/* City Autocomplete Styles */
#hotels-city-suggestions {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    margin-top: 2px;
}

#hotels-city-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

#hotels-city-suggestions .list-group-item:first-child {
    border-top: none;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#hotels-city-suggestions .list-group-item:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

#hotels-city-suggestions .list-group-item:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

#hotels-city-suggestions .city-suggestion-item:hover {
    background-color: #e7f1ff;
}

#hotels-destination-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 768px) {
    .hotels-header {
        background-attachment: scroll;
    }
    
    .hotel-image-container {
        height: 200px;
    }
    
    .row.g-0 {
        flex-direction: column;
    }
    
    .hotel-booking {
        text-align: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hotel search form validation and enhancement
    const hotelForm = document.getElementById('hotels-search-form');
    if (!hotelForm) {
        console.error('Hotels search form not found');
        return;
    }
    
    const checkInInput = hotelForm.querySelector('input[name="check_in"]');
    const checkOutInput = hotelForm.querySelector('input[name="check_out"]');
    
    // City autocomplete
    const cityInput = document.getElementById('hotels-destination-input');
    const cityCodeInput = document.getElementById('hotels-location-code');
    const citySuggestions = document.getElementById('hotels-city-suggestions');
    let searchTimeout;
    
    console.log('Hotels city autocomplete initialized');
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
        if (!e.target.closest('#hotels-destination-input') && !e.target.closest('#hotels-city-suggestions')) {
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
});
</script>
@endpush
