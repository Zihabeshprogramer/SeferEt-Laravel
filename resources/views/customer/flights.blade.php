@extends('layouts.customer')

@section('title', 'Flight Search - SeferEt')

@section('content')
    <!-- Flights Search Header -->
    <div class="flights-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-plane me-3"></i>
                        Find Your Perfect Flight
                    </h1>
                    <p class="lead opacity-90 mb-0">Compare and book flights to the Holy Cities</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-plane-departure fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Search Form -->
    <div class="search-section py-4 bg-light">
        <div class="container-fluid">
            <x-customer.card variant="elevated" elevation="md" padding="lg">
                <form class="flight-search-form" method="GET">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-departure text-primary"></i></span>
                                <input type="text" class="form-control" name="from" placeholder="Departure city" value="{{ request('from') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-arrival text-success"></i></span>
                                <select class="form-select" name="to">
                                    <option value="">Select destination</option>
                                    <option value="JED" {{ request('to') == 'JED' ? 'selected' : '' }}>Jeddah (JED)</option>
                                    <option value="MED" {{ request('to') == 'MED' ? 'selected' : '' }}>Madinah (MED)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Departure</label>
                            <input type="date" class="form-control" name="departure_date" value="{{ request('departure_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Return</label>
                            <input type="date" class="form-control" name="return_date" value="{{ request('return_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Passengers</label>
                            <select class="form-select" name="passengers">
                                <option value="1" {{ request('passengers') == '1' ? 'selected' : '' }}>1 Passenger</option>
                                <option value="2" {{ request('passengers') == '2' ? 'selected' : '' }}>2 Passengers</option>
                                <option value="3" {{ request('passengers') == '3' ? 'selected' : '' }}>3 Passengers</option>
                                <option value="4+" {{ request('passengers') == '4+' ? 'selected' : '' }}>4+ Passengers</option>
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

    <!-- Flight Results -->
    <div class="flights-results py-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Available Flights
                </h2>
                <div class="results-info">
                    <span class="text-muted">{{ count($flights) }} flights found</span>
                </div>
            </div>

            @if(count($flights) > 0)
                <div class="row g-4">
                    @foreach($flights as $flight)
                    <div class="col-12">
                        <x-customer.card variant="elevated" elevation="sm" hover="true" class="flight-card">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="flight-info">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="airline-logo me-3">
                                                    <i class="fas fa-plane-departure fa-2x text-primary"></i>
                                                </div>
                                                <div class="airline-details">
                                                    <h5 class="airline-name mb-1">{{ $flight['airline'] }}</h5>
                                                    <small class="text-muted">{{ $flight['class'] }}</small>
                                                </div>
                                                @if($flight['stops'] == 0)
                                                    <span class="badge bg-success ms-3">Direct Flight</span>
                                                @else
                                                    <span class="badge bg-warning ms-3">{{ $flight['stops'] }} Stop{{ $flight['stops'] > 1 ? 's' : '' }}</span>
                                                @endif
                                            </div>

                                            <div class="flight-route">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3 text-center">
                                                        <div class="departure-info">
                                                            <h4 class="time mb-0">{{ date('H:i', strtotime($flight['departure'])) }}</h4>
                                                            <p class="location mb-0">{{ $flight['from'] }}</p>
                                                            <small class="text-muted">{{ date('M d', strtotime($flight['departure'])) }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="flight-path text-center">
                                                            <div class="path-line position-relative">
                                                                <i class="fas fa-plane text-primary"></i>
                                                                <div class="duration-badge">
                                                                    <small class="bg-light px-2 py-1 rounded text-muted">{{ $flight['duration'] }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <div class="arrival-info">
                                                            <h4 class="time mb-0">{{ date('H:i', strtotime($flight['arrival'])) }}</h4>
                                                            <p class="location mb-0">{{ $flight['to'] }}</p>
                                                            <small class="text-muted">{{ date('M d', strtotime($flight['arrival'])) }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="flight-booking text-center">
                                            <div class="price-info mb-3">
                                                <span class="price-label text-muted">Starting from</span>
                                                <h3 class="price text-success mb-0">${{ number_format($flight['price']) }}</h3>
                                                <small class="text-muted">per person</small>
                                            </div>
                                            <div class="booking-actions d-grid gap-2">
                                                @auth
                                                    <x-customer.button variant="primary" size="md" fullWidth="true">
                                                        <i class="fas fa-shopping-cart me-2"></i>Book Now
                                                    </x-customer.button>
                                                    <x-customer.button variant="outline-secondary" size="sm" fullWidth="true">
                                                        <i class="fas fa-info-circle me-2"></i>Flight Details
                                                    </x-customer.button>
                                                @else
                                                    <x-customer.button href="{{ route('customer.login') }}" variant="primary" size="md" fullWidth="true">
                                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                                                    </x-customer.button>
                                                @endauth
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
                        <i class="fas fa-plane fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Flights Found</h4>
                    <p class="text-muted mb-4">Try adjusting your search criteria to find available flights.</p>
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
.flights-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.flights-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.flight-card {
    border-left: 4px solid var(--primary-color);
    transition: all 0.3s ease;
}

.flight-card:hover {
    border-left-color: var(--secondary-color);
    transform: translateY(-2px);
}

.path-line {
    height: 2px;
    background: linear-gradient(to right, var(--primary-color), var(--success-color));
    margin: 1rem 0;
}

.path-line i {
    background: white;
    padding: 0.5rem;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.duration-badge {
    position: absolute;
    top: -1.5rem;
    left: 50%;
    transform: translateX(-50%);
}

.time {
    font-weight: 700;
    color: var(--text-primary);
}

.location {
    font-weight: 600;
    color: var(--text-secondary);
}

.airline-name {
    font-weight: 600;
    color: var(--text-primary);
}

.price {
    font-weight: 700;
}

@media (max-width: 768px) {
    .flights-header {
        background-attachment: scroll;
    }
    
    .flight-route .row {
        text-align: center;
    }
    
    .path-line {
        display: none;
    }
    
    .flight-info, .flight-booking {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
