@extends('layouts.b2b')

@section('title', $package->name . ' - Package Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Header Section -->
            <div class="header-section mb-4">
                <div class="card bg-gradient-primary text-white border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h1 class="h3 mb-2">
                                    <i class="fas fa-suitcase-rolling me-2"></i>
                                    {{ $package->name }}
                                </h1>
                                <p class="mb-0 opacity-90">
                                    {{ $package->description ?? 'Discover an amazing travel experience with this carefully crafted package.' }}
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <div class="action-buttons">
                                    <a href="{{ route('b2b.travel-agent.packages.index') }}" class="btn btn-light btn-sm me-2">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Packages
                                    </a>
                                    <div class="btn-group">
                                        <a href="{{ route('b2b.travel-agent.packages.edit', $package->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-{{ $package->status === 'active' ? 'secondary' : 'success' }} btn-sm toggle-status-btn"
                                                data-package-id="{{ $package->id }}"
                                                data-package-name="{{ $package->name }}"
                                                data-current-status="{{ $package->status }}">
                                            <i class="fas fa-{{ $package->status === 'active' ? 'pause' : 'play' }} me-1"></i>
                                            {{ $package->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Package Meta Info -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge bg-light text-dark">ID: #{{ $package->id }}</span>
                                    <span class="badge bg-{{ $package->status === 'active' ? 'success' : ($package->status === 'draft' ? 'secondary' : 'danger') }}">
                                        <i class="fas fa-{{ $package->status === 'active' ? 'check-circle' : ($package->status === 'draft' ? 'edit' : 'pause-circle') }} me-1"></i>
                                        {{ ucfirst($package->status) }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar me-1"></i> Created {{ $package->created_at->format('M d, Y') }}
                                    </span>
                                    @if($package->approval_status)
                                    <span class="badge bg-{{ $package->approval_status === 'approved' ? 'success' : ($package->approval_status === 'pending' ? 'warning' : 'danger') }}">
                                        <i class="fas fa-{{ $package->approval_status === 'approved' ? 'check-double' : ($package->approval_status === 'pending' ? 'clock' : 'times-circle') }} me-1"></i>
                                        {{ ucfirst($package->approval_status) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-1">{{ $packageStats['duration_days'] ?? $package->duration }}</h4>
                                <p class="mb-0 opacity-90">Duration (Days)</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-1">{{ number_format($package->base_price, 0) }} {{ $package->currency }}</h4>
                                <p class="mb-0 opacity-90">Base Price</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-dark border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-1">{{ $package->max_participants ?? 'Unlimited' }}</h4>
                                <p class="mb-0 opacity-90">Max Participants</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-danger text-white border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-1">{{ number_format($package->views_count ?? 0) }}</h4>
                                <p class="mb-0 opacity-90">Total Views</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-eye fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="row">
                <!-- Main Package Information -->
                <div class="col-lg-8 mb-4">
                    @if($package->hasImages())
                    <!-- Image Gallery Section -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-images text-primary me-2"></i>
                                Package Images
                                <small class="text-muted ms-2">({{ $package->getImageCount() }} images)</small>
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Main Image Display -->
                            <div class="main-image-container mb-4 text-center">
                                @php $mainImage = $package->getMainImage(); @endphp
                                @if($mainImage)
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset('storage/' . $mainImage['sizes']['large']) }}" 
                                         alt="{{ $mainImage['alt_text'] ?? $package->name }}" 
                                         class="img-fluid rounded shadow" 
                                         style="max-height: 500px; width: auto; max-width: 100%;" 
                                         id="mainPackageImage">
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i>Main Image
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Image Thumbnails Gallery -->
                            @if($package->getImageCount() > 1)
                            <div class="image-thumbnails">
                                <h6 class="text-muted mb-3">All Images:</h6>
                                <div class="row g-2">
                                    @foreach($package->getImagesWithUrls() as $image)
                                    <div class="col-md-2 col-sm-3 col-4">
                                        <div class="thumbnail-wrapper position-relative">
                                            <img src="{{ $image['urls']['thumbnail'] }}" 
                                                 alt="{{ $image['alt_text'] ?? $package->name }}" 
                                                 class="img-fluid rounded thumbnail-image"
                                                 style="width: 100%; height: 80px; object-fit: cover; cursor: pointer; border: 2px solid {{ $image['is_main'] ? '#ffc107' : '#dee2e6' }};" 
                                                 onclick="changeMainImage('{{ $image['urls']['large'] }}')">
                                            @if($image['is_main'])
                                            <div class="position-absolute top-0 end-0 p-1">
                                                <i class="fas fa-star text-warning" style="font-size: 0.8rem; filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.5));"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block text-center mt-1 text-truncate" style="font-size: 0.7rem;">
                                            {{ $image['original_name'] ?? 'Image' }}
                                        </small>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Package Details Section -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Package Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-list">
                                        <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-semibold text-muted">Package Type:</span>
                                            <span class="badge bg-primary">{{ ucfirst($package->type) }}</span>
                                        </div>
                                        <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-semibold text-muted">Duration:</span>
                                            <span class="fw-bold">{{ $package->duration }} days</span>
                                        </div>
                                        <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-semibold text-muted">Base Price:</span>
                                            <span class="text-success fw-bold fs-6">{{ number_format($package->base_price, 2) }} {{ $package->currency }}</span>
                                        </div>
                                        <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-semibold text-muted">Max Participants:</span>
                                            <span class="fw-bold">{{ $package->max_participants ?? 'Unlimited' }}</span>
                                        </div>
                                        @if($package->start_date)
                                        <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-semibold text-muted">Start Date:</span>
                                            <span class="fw-bold">{{ $package->start_date->format('M d, Y') }}</span>
                                        </div>
                                        @endif
                                        @if($package->end_date)
                                        <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-semibold text-muted">End Date:</span>
                                            <span class="fw-bold">{{ $package->end_date->format('M d, Y') }}</span>
                                        </div>
                                        @endif
                                        @if($package->difficulty_level)
                                        <div class="info-item d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-semibold text-muted">Difficulty:</span>
                                            <span class="badge bg-{{ $package->difficulty_level === 'easy' ? 'success' : ($package->difficulty_level === 'moderate' ? 'info' : ($package->difficulty_level === 'challenging' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($package->difficulty_level) }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Package Features -->
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-star me-1"></i> Package Features
                                    </h6>
                                    <div class="features-list mb-4">
                                        @if($package->is_featured)
                                            <span class="badge bg-warning text-dark me-1 mb-2"><i class="fas fa-star me-1"></i>Featured</span>
                                        @endif
                                        @if($package->is_premium)
                                            <span class="badge bg-primary me-1 mb-2"><i class="fas fa-crown me-1"></i>Premium</span>
                                        @endif
                                        @if($package->instant_booking)
                                            <span class="badge bg-success me-1 mb-2"><i class="fas fa-bolt me-1"></i>Instant Booking</span>
                                        @endif
                                        @if($package->allow_customization)
                                            <span class="badge bg-info me-1 mb-2"><i class="fas fa-edit me-1"></i>Customizable</span>
                                        @endif
                                        @if($package->uses_b2b_services)
                                            <span class="badge bg-secondary me-1 mb-2"><i class="fas fa-handshake me-1"></i>B2B Services</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Service Sources -->
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-cogs me-1"></i> Service Sources
                                    </h6>
                                    <div class="sources-list mb-4">
                                        @if($package->flight_source)
                                        <span class="badge bg-outline-primary me-1 mb-2">
                                            <i class="fas fa-plane me-1"></i>{{ ucfirst($package->flight_source) }} Flights
                                        </span>
                                        @endif
                                        @if($package->hotel_source)
                                        <span class="badge bg-outline-success me-1 mb-2">
                                            <i class="fas fa-bed me-1"></i>{{ ucfirst($package->hotel_source) }} Hotels
                                        </span>
                                        @endif
                                        @if($package->transport_source)
                                        <span class="badge bg-outline-warning me-1 mb-2">
                                            <i class="fas fa-bus me-1"></i>{{ ucfirst($package->transport_source) }} Transport
                                        </span>
                                        @endif
                                    </div>
                                    
                                    @if($package->destinations && count($package->destinations) > 0)
                                    <!-- Destinations -->
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-map-marker-alt me-1"></i> Destinations
                                    </h6>
                                    <div class="destinations-list">
                                        @foreach($package->destinations as $destination)
                                            <span class="badge bg-outline-primary me-1 mb-2">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $destination }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($package->detailed_description)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-align-left me-1"></i> Detailed Description
                                    </h6>
                                    <div class="detailed-description bg-light p-3 rounded">
                                        {{ strip_tags($package->detailed_description) }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Package Statistics -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                Package Statistics
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                                <div class="flex-shrink-0 me-3">
                                    <div class="icon-circle bg-info text-white">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark">{{ $packageStats['total_flights'] }}</div>
                                    <div class="small text-muted">Total Flights</div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                                <div class="flex-shrink-0 me-3">
                                    <div class="icon-circle bg-success text-white">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark">{{ $packageStats['total_hotels'] }}</div>
                                    <div class="small text-muted">Total Hotels</div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                                <div class="flex-shrink-0 me-3">
                                    <div class="icon-circle bg-warning text-white">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark">{{ $packageStats['total_transport'] }}</div>
                                    <div class="small text-muted">Transport Services</div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0 me-3">
                                    <div class="icon-circle bg-danger text-white">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark">{{ $packageStats['total_activities'] }}</div>
                                    <div class="small text-muted">Activities</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($package->approvedBy || $package->approval_status)
                    <!-- Approval Information -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Approval Information
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-muted">Status:</span>
                                <span class="badge bg-{{ $package->approval_status === 'approved' ? 'success' : ($package->approval_status === 'pending' ? 'warning' : 'danger') }}">
                                    <i class="fas fa-{{ $package->approval_status === 'approved' ? 'check-double' : ($package->approval_status === 'pending' ? 'clock' : 'times-circle') }} me-1"></i>
                                    {{ ucfirst($package->approval_status) }}
                                </span>
                            </div>
                            
                            @if($package->approval_status === 'approved' && $package->approvedBy)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-muted">Approved by:</span>
                                <span class="fw-bold">{{ $package->approvedBy->name }}</span>
                            </div>
                            @endif
                            
                            @if($package->approved_at)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold text-muted">Approved on:</span>
                                <span class="fw-bold">{{ $package->approved_at->format('M d, Y H:i') }}</span>
                            </div>
                            @endif
                            
                            @if($package->approval_notes)
                            <div class="mt-3">
                                <span class="fw-semibold text-muted d-block mb-2">Notes:</span>
                                <div class="bg-light p-2 rounded small">{{ $package->approval_notes }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Associated Services -->
            @if($package->flights->count() > 0 || $package->hotels->count() > 0 || $package->transportServices->count() > 0)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-link text-primary me-2"></i>
                        Associated Services
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($package->flights->count() > 0)
                    <!-- Flights Section -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-plane me-2"></i>
                            Flights ({{ $package->flights->count() }})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Airline</th>
                                        <th>Flight Number</th>
                                        <th>Route</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($package->flights as $flight)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $flight->airline }}</span></td>
                                        <td><span class="badge bg-primary">{{ $flight->flight_number }}</span></td>
                                        <td>{{ $flight->departure_airport }} <i class="fas fa-arrow-right mx-1 text-muted"></i> {{ $flight->arrival_airport }}</td>
                                        <td><small>{{ $flight->departure_datetime ? $flight->departure_datetime->format('M d, H:i') : 'N/A' }}</small></td>
                                        <td><small>{{ $flight->arrival_datetime ? $flight->arrival_datetime->format('M d, H:i') : 'N/A' }}</small></td>
                                        <td><span class="text-success fw-bold">{{ number_format($flight->economy_price, 2) }} {{ $flight->currency }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    @if($package->hotels->count() > 0)
                    <!-- Hotels Section -->
                    <div class="mb-4">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-bed me-2"></i>
                            Hotels ({{ $package->hotels->count() }})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hotel Name</th>
                                        <th>Location</th>
                                        <th>Rating</th>
                                        <th>Room Type</th>
                                        <th>Nights</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($package->hotels as $hotel)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $hotel->name }}</span></td>
                                        <td><small>{{ $hotel->city }}, {{ $hotel->country }}</small></td>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= ($hotel->star_rating ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </td>
                                        <td><span class="badge bg-info">{{ $hotel->pivot->room_type ?? 'Standard' }}</span></td>
                                        <td><span class="fw-semibold">{{ $hotel->pivot->nights ?? 1 }} night{{ ($hotel->pivot->nights ?? 1) > 1 ? 's' : '' }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    @if($package->transportServices->count() > 0)
                    <!-- Transport Section -->
                    <div>
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-bus me-2"></i>
                            Transport Services ({{ $package->transportServices->count() }})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Type</th>
                                        <th>Route</th>
                                        <th>Provider</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($package->transportServices as $transport)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $transport->service_name }}</span></td>
                                        <td><span class="badge bg-warning text-dark">{{ ucfirst($transport->transport_type) }}</span></td>
                                        <td><small>{{ $transport->route ?? 'N/A' }}</small></td>
                                        <td><small>{{ $transport->provider ? $transport->provider->company_name : 'N/A' }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Package Activities/Itinerary -->
            @if($package->packageActivities->count() > 0)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        Itinerary & Activities ({{ $package->packageActivities->count() }})
                    </h5>
                </div>
                <div class="card-body p-4">
                    @php
                        $activitiesByDay = $package->packageActivities->groupBy('day_number');
                    @endphp
                    
                    @foreach($activitiesByDay as $day => $activities)
                    <div class="day-section mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="day-badge me-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">{{ $day }}</span>
                                </div>
                            </div>
                            <h6 class="mb-0 fw-bold text-primary">Day {{ $day }}</h6>
                        </div>
                        
                        @foreach($activities->sortBy('display_order') as $activity)
                        <div class="activity-item mb-3 p-3 border-start border-primary border-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-2 text-dark">{{ $activity->name ?? $activity->activity_name }}</h6>
                                    <p class="text-muted mb-3">{{ $activity->description }}</p>
                                    
                                    <div class="activity-details d-flex flex-wrap gap-3">
                                        @if($activity->start_time || $activity->end_time)
                                        <div class="detail-item">
                                            <i class="fas fa-clock text-info me-1"></i>
                                            <small class="text-muted">
                                                {{ $activity->start_time ? $activity->start_time->format('H:i') : '' }}
                                                {{ $activity->end_time ? ' - ' . $activity->end_time->format('H:i') : '' }}
                                            </small>
                                        </div>
                                        @endif
                                        
                                        @if($activity->location)
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt text-success me-1"></i>
                                            <small class="text-muted">{{ $activity->location }}</small>
                                        </div>
                                        @endif
                                        
                                        @if($activity->category)
                                        <div class="detail-item">
                                            <span class="badge bg-secondary">{{ ucfirst($activity->category) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="activity-meta text-end">
                                    @if($activity->additional_cost && $activity->additional_cost > 0)
                                    <div class="mb-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-plus-circle me-1"></i>
                                            +{{ number_format($activity->additional_cost, 2) }} {{ $package->currency }}
                                        </span>
                                    </div>
                                    @endif
                                    
                                    @if($activity->is_optional)
                                    <div>
                                        <span class="badge bg-info">Optional</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Modern Show Page Styles */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Stats Cards */
.stats-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Badge Styles */
.badge {
    font-weight: 500;
    letter-spacing: 0.025em;
}

.badge.bg-outline-primary {
    color: #0d6efd;
    border: 1px solid #0d6efd;
    background-color: transparent;
}

.badge.bg-outline-success {
    color: #198754;
    border: 1px solid #198754;
    background-color: transparent;
}

.badge.bg-outline-warning {
    color: #fd7e14;
    border: 1px solid #fd7e14;
    background-color: transparent;
}

/* Icon Circles */
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* Info List Styles */
.info-list .info-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-list .info-item:last-child {
    border-bottom: none;
}

/* Image Gallery Styles */
.main-image-container img {
    transition: all 0.3s ease;
    border-radius: 0.5rem;
}

.thumbnail-image {
    transition: all 0.3s ease;
    cursor: pointer;
    border-radius: 0.375rem;
}

.thumbnail-image:hover {
    transform: scale(1.05);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.2);
}

/* Activity Timeline */
.activity-item {
    transition: all 0.2s ease;
}

.activity-item:hover {
    transform: translateX(5px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.day-badge {
    position: relative;
}

.day-badge::after {
    content: '';
    position: absolute;
    top: 50%;
    right: -15px;
    width: 30px;
    height: 2px;
    background: linear-gradient(to right, #0d6efd, transparent);
    transform: translateY(-50%);
}

/* Service Tables */
.table th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

/* Detailed Description */
.detailed-description {
    line-height: 1.7;
    color: #6c757d;
    font-size: 0.95rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .stats-card .card-body {
        padding: 1rem;
    }
    
    .main-image-container img {
        max-height: 300px;
    }
    
    .activity-details {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .day-badge::after {
        display: none;
    }
}

@media (max-width: 576px) {
    .header-section h1 {
        font-size: 1.5rem;
    }
    
    .activity-meta {
        text-align: start !important;
        margin-top: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Success and error messages
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Image Gallery Functions
    window.changeMainImage = function(imageUrl) {
        const mainImage = document.getElementById('mainPackageImage');
        if (mainImage) {
            // Add fade effect
            mainImage.style.opacity = '0';
            mainImage.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                mainImage.src = imageUrl;
                mainImage.style.opacity = '1';
                mainImage.style.transform = 'scale(1)';
            }, 200);
        }
    };
    
    // Enhanced thumbnail hover effects
    $('.thumbnail-image').on('mouseenter', function() {
        $(this).addClass('shadow-lg');
    }).on('mouseleave', function() {
        $(this).removeClass('shadow-lg');
    });
    
    // Activity item animations
    const observeElements = () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.activity-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(item);
        });
    };
    
    // Initialize animations
    if (document.querySelectorAll('.activity-item').length > 0) {
        observeElements();
    }
    
    // Stats cards hover enhancement
    $('.stats-card').hover(
        function() {
            $(this).addClass('stats-card');
        },
        function() {
            // Keep the class for CSS transitions
        }
    );
    
    // Toggle Package Status functionality
    $(document).on('click', '.toggle-status-btn', function(e) {
        e.preventDefault();
        
        const packageId = $(this).data('package-id');
        const packageName = $(this).data('package-name');
        const currentStatus = $(this).data('current-status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        const buttonElement = $(this);
        
        // Show confirmation with SweetAlert2
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)} Package?`,
                html: `Are you sure you want to <strong>${action}</strong> ${packageName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: newStatus === 'active' ? '#198754' : '#6c757d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `<i class="fas fa-${newStatus === 'active' ? 'play' : 'pause'} me-2"></i>Yes, ${action.charAt(0).toUpperCase() + action.slice(1)}!`,
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`{{ url('b2b/travel-agent/packages') }}/${packageId}/toggle-status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json().catch(() => ({ success: true }));
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Update button appearance
                    if (newStatus === 'active') {
                        buttonElement.removeClass('btn-success').addClass('btn-secondary');
                        buttonElement.find('i').removeClass('fa-play').addClass('fa-pause');
                        buttonElement.html('<i class="fas fa-pause me-1"></i>Deactivate');
                        buttonElement.data('current-status', 'active');
                    } else {
                        buttonElement.removeClass('btn-secondary').addClass('btn-success');
                        buttonElement.find('i').removeClass('fa-pause').addClass('fa-play');
                        buttonElement.html('<i class="fas fa-play me-1"></i>Activate');
                        buttonElement.data('current-status', 'inactive');
                    }
                    
                    // Update all status badges on the page
                    $('.badge').each(function() {
                        if ($(this).text().toLowerCase().includes(currentStatus)) {
                            $(this).removeClass('bg-success bg-secondary bg-warning bg-danger')
                                  .addClass('bg-' + (newStatus === 'active' ? 'success' : 'secondary'))
                                  .html('<i class="fas fa-' + (newStatus === 'active' ? 'check-circle' : 'pause-circle') + ' me-1"></i>' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        }
                    });
                    
                    // Show success message
                    Swal.fire({
                        title: `${action.charAt(0).toUpperCase() + action.slice(1)}d!`,
                        text: `${packageName} has been ${action}d successfully.`,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                        timerProgressBar: true
                    });
                    
                    // Show toastr as fallback
                    if (typeof toastr !== 'undefined') {
                        toastr.success(`Package ${action}d successfully!`);
                    }
                }
            });
        } else {
            // Fallback to simple confirm dialog
            if (confirm(`Are you sure you want to ${action} ${packageName}?`)) {
                // Make the API request
                fetch(`{{ url('b2b/travel-agent/packages') }}/${packageId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success !== false) {
                        location.reload();
                    } else {
                        alert('Failed to update package status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the package status.');
                });
            }
        }
    });
    
    // Enhanced table responsiveness
    $('.table-responsive').each(function() {
        const table = $(this).find('table');
        if (table.length && window.innerWidth < 768) {
            table.addClass('table-sm');
        }
    });
    
    // Smooth scrolling for internal links
    $('a[href^="#"]').click(function(event) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });
});
</script>
@endpush
