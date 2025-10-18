@extends('layouts.b2b')

@section('title', 'Flight Details - ' . $flight->flight_number)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-plane text-info mr-2"></i>
                Flight {{ $flight->flight_number }}
                @if($flight->trip_type === 'round_trip')
                    <span class="badge badge-primary ml-2">Round Trip</span>
                @endif
                @if($flight->is_group_booking)
                    <span class="badge badge-success ml-1">Group Booking</span>
                @endif
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-route mr-1"></i>
                {{ $flight->departure_airport }} → {{ $flight->arrival_airport }}
                @if($flight->allows_agent_collaboration)
                    <span class="text-info ml-3"><i class="fas fa-handshake mr-1"></i>Collaboration Enabled</span>
                @endif
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.travel-agent.flights.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Flights
            </a>
            <a href="{{ route('b2b.travel-agent.flights.edit', $flight) }}" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i>
                Edit Flight
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Flight Status Card -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="info-box bg-gradient-primary">
                                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Departure</span>
                                    <span class="info-box-number">{{ $flight->departure_datetime->format('M d, Y') }}</span>
                                    <span class="progress-description">{{ $flight->departure_datetime->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Available Seats</span>
                                    <span class="info-box-number">{{ $flight->available_seats }}/{{ $flight->total_seats }}</span>
                                    <span class="progress-description">{{ number_format($flightStats['occupancy_rate'], 1) }}% occupied</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Starting Price</span>
                                    <span class="info-box-number">{{ $flight->economy_price }} {{ $flight->currency }}</span>
                                    @if($flight->group_economy_price)
                                        <span class="progress-description">Group: {{ $flight->group_economy_price }} {{ $flight->currency }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Revenue Potential</span>
                                    <span class="info-box-number">{{ number_format($flightStats['revenue_potential']) }}</span>
                                    <span class="progress-description">{{ $flight->currency }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flight Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plane mr-2"></i>Flight Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Outbound Flight</h6>
                            <div class="flight-segment">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $flight->airline }}</strong>
                                    <span class="badge badge-outline-primary">{{ $flight->flight_number }}</span>
                                </div>
                                <div class="route-info">
                                    <div class="d-flex justify-content-between">
                                        <div class="departure">
                                            <strong>{{ $flight->departure_airport }}</strong>
                                            <div class="time">{{ $flight->departure_datetime->format('H:i') }}</div>
                                            <small class="text-muted">{{ $flight->departure_datetime->format('D, M d') }}</small>
                                        </div>
                                        <div class="route-line">
                                            <i class="fas fa-arrow-right text-muted"></i>
                                        </div>
                                        <div class="arrival text-right">
                                            <strong>{{ $flight->arrival_airport }}</strong>
                                            <div class="time">{{ $flight->arrival_datetime->format('H:i') }}</div>
                                            <small class="text-muted">{{ $flight->arrival_datetime->format('D, M d') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($flight->trip_type === 'round_trip' && $flight->return_departure_datetime)
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Return Flight</h6>
                                <div class="flight-segment">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>{{ $flight->airline }}</strong>
                                        <span class="badge badge-outline-primary">{{ $flight->return_flight_number }}</span>
                                    </div>
                                    <div class="route-info">
                                        <div class="d-flex justify-content-between">
                                            <div class="departure">
                                                <strong>{{ $flight->arrival_airport }}</strong>
                                                <div class="time">{{ $flight->return_departure_datetime->format('H:i') }}</div>
                                                <small class="text-muted">{{ $flight->return_departure_datetime->format('D, M d') }}</small>
                                            </div>
                                            <div class="route-line">
                                                <i class="fas fa-arrow-right text-muted"></i>
                                            </div>
                                            <div class="arrival text-right">
                                                <strong>{{ $flight->departure_airport }}</strong>
                                                <div class="time">{{ $flight->return_arrival_datetime->format('H:i') }}</div>
                                                <small class="text-muted">{{ $flight->return_arrival_datetime->format('D, M d') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($flight->description)
                        <div class="mb-4">
                            <h6 class="text-primary">Description</h6>
                            <p class="text-muted">{{ $flight->description }}</p>
                        </div>
                    @endif

                    @if($flight->is_group_booking)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">Group Booking Settings</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-users text-muted mr-2"></i>Group Size: {{ $flight->min_group_size }}-{{ $flight->max_group_size }} passengers</li>
                                    @if($flight->group_discount_percentage)
                                        <li><i class="fas fa-percentage text-muted mr-2"></i>Group Discount: {{ $flight->group_discount_percentage }}%</li>
                                    @endif
                                    @if($flight->booking_deadline)
                                        <li><i class="fas fa-calendar text-muted mr-2"></i>Booking Deadline: {{ $flight->booking_deadline->format('M d, Y') }}</li>
                                    @endif
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Pricing</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-tag text-muted mr-2"></i>Regular Price: {{ $flight->economy_price }} {{ $flight->currency }}</li>
                                    @if($flight->group_economy_price)
                                        <li><i class="fas fa-tags text-muted mr-2"></i>Group Price: {{ $flight->group_economy_price }} {{ $flight->currency }}</li>
                                    @endif
                                    <li><i class="fas fa-credit-card text-muted mr-2"></i>Payment Terms: {{ ucwords(str_replace('_', ' ', $flight->payment_terms)) }}</li>
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if($flight->allows_agent_collaboration)
                        <div class="mb-4">
                            <h6 class="text-primary">Agent Collaboration</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-percentage text-muted mr-2"></i>Commission Rate: {{ $flight->collaboration_commission_percentage }}%</li>
                                    </ul>
                                </div>
                                @if($flight->collaboration_terms)
                                    <div class="col-md-6">
                                        <p class="text-muted small">{{ $flight->collaboration_terms }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Flight Management -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-cogs mr-2"></i>Flight Management
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Status</label>
                        <div>
                            <span class="badge badge-lg badge-{{ $flight->status === 'scheduled' ? 'success' : ($flight->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($flight->status) }}
                            </span>
                            @if($flight->is_active)
                                <span class="badge badge-lg badge-primary ml-1">Active</span>
                            @else
                                <span class="badge badge-lg badge-secondary ml-1">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Quick Actions</label>
                        <div class="btn-group-vertical w-100">
                            <form action="{{ route('b2b.travel-agent.flights.toggle-status', $flight) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-{{ $flight->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                    <i class="fas fa-{{ $flight->is_active ? 'pause' : 'play' }} mr-1"></i>
                                    {{ $flight->is_active ? 'Deactivate' : 'Activate' }} Flight
                                </button>
                            </form>
                            
                            <form action="{{ route('b2b.travel-agent.flights.duplicate', $flight) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm w-100">
                                    <i class="fas fa-copy mr-1"></i>
                                    Duplicate Flight
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($flight->allows_agent_collaboration)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Collaboration</label>
                            <p class="text-muted small mb-2">This flight is open for agent collaboration. Partner agents can book seats and earn {{ $flight->collaboration_commission_percentage }}% commission.</p>
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="alert('Collaboration management coming soon!')">
                                <i class="fas fa-handshake mr-1"></i>
                                Manage Collaborations
                            </button>
                        </div>
                    @endif

                    <div>
                        <label class="form-label fw-bold text-danger">Danger Zone</label>
                        <form action="{{ route('b2b.travel-agent.flights.destroy', $flight) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this flight? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="fas fa-trash mr-1"></i>
                                Delete Flight
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-gradient-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h5 class="mb-1">{{ $flightStats['total_packages'] }}</h5>
                                <small class="text-muted">Packages</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-1">{{ $flightStats['duration'] ?? 'N/A' }}</h5>
                            <small class="text-muted">Duration</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .flight-segment {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    
    .route-info {
        margin-top: 10px;
    }
    
    .route-line {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 15px;
    }
    
    .time {
        font-size: 1.1em;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .info-box {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .badge-lg {
        font-size: 0.9em;
        padding: 8px 12px;
    }
    
    .badge-outline-primary {
        border: 2px solid #007bff;
        background: transparent;
        color: #007bff;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Add any JavaScript for the flight show page here

});
</script>
@stop
