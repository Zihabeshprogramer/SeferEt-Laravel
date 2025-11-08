@extends('layouts.b2b')

@section('title', 'Hotel Details')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-hotel text-info mr-2"></i>
                {{ $hotel->name }}
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Hotels
            </a>
            <a href="{{ route('b2b.hotel-provider.hotels.edit', $hotel) }}" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i>
                Edit Hotel
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Hotel Details Card -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Hotel Information
                    </h3>
                    <div class="card-tools">
                        @if($hotel->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @elseif($hotel->status === 'pending')
                            <span class="badge badge-warning">Pending Approval</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Hotel Name</label>
                                <p class="form-control-static">{{ $hotel->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Hotel Type</label>
                                <p class="form-control-static">
                                    <span class="badge badge-primary">
                                        {{ ucfirst(str_replace('_', ' ', $hotel->type)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Star Rating</label>
                                <p class="form-control-static">
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $hotel->star_rating ? '' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Description</label>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $hotel->description }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">
                                    <i class="fas fa-map-marker-alt mr-2 text-danger"></i>
                                    Address
                                </label>
                                <p class="form-control-static">
                                    {{ $hotel->address }}<br>
                                    {{ $hotel->city }}, {{ $hotel->country }} {{ $hotel->postal_code }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @if($hotel->distance_to_haram)
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">
                                        <i class="fas fa-kaaba mr-2 text-success"></i>
                                        Distance to Haram
                                    </label>
                                    <p class="form-control-static text-success">{{ $hotel->distance_to_haram }} km</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">
                                    <i class="fas fa-phone mr-2 text-primary"></i>
                                    Phone
                                </label>
                                <p class="form-control-static">{{ $hotel->phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">
                                    <i class="fas fa-envelope mr-2 text-info"></i>
                                    Email
                                </label>
                                <p class="form-control-static">{{ $hotel->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @if($hotel->website)
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">
                                        <i class="fas fa-globe mr-2 text-primary"></i>
                                        Website
                                    </label>
                                    <p class="form-control-static">
                                        <a href="{{ $hotel->website }}" target="_blank">{{ $hotel->website }}</a>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Check-in/Check-out Times -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">
                                    <i class="fas fa-sign-in-alt mr-2 text-success"></i>
                                    Check-in Time
                                </label>
                                <p class="form-control-static">{{ $hotel->check_in_time }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">
                                    <i class="fas fa-sign-out-alt mr-2 text-warning"></i>
                                    Check-out Time
                                </label>
                                <p class="form-control-static">{{ $hotel->check_out_time }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    @if($hotel->amenities && count($hotel->amenities) > 0)
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">
                                <i class="fas fa-concierge-bell mr-2 text-info"></i>
                                Amenities
                            </label>
                            <div class="mt-2">
                                @foreach($hotel->amenities as $amenity)
                                    <span class="badge badge-light mr-2 mb-2">
                                        <i class="fas fa-check mr-1 text-success"></i>
                                        {{ ucfirst(str_replace('_', ' ', $amenity)) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Policies -->
                    @if($hotel->policy_cancellation || $hotel->policy_children || $hotel->policy_pets)
                        <div class="row">
                            <div class="col-12">
                                <label class="font-weight-bold text-muted">
                                    <i class="fas fa-file-contract mr-2 text-secondary"></i>
                                    Hotel Policies
                                </label>
                            </div>
                            @if($hotel->policy_cancellation)
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="text-danger">Cancellation Policy</h6>
                                            <p class="text-sm">{{ $hotel->policy_cancellation }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($hotel->policy_children)
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="text-info">Children Policy</h6>
                                            <p class="text-sm">{{ $hotel->policy_children }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($hotel->policy_pets)
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="text-warning">Pet Policy</h6>
                                            <p class="text-sm">{{ $hotel->policy_pets }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hotel Images -->
            @if($hotel->images && count($hotel->images) > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-images mr-2"></i>
                            Hotel Images
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($hotel->images as $image)
                                <div class="col-md-4 mb-3">
                                    <img src="{{ Storage::url($image['sizes']['medium'] ?? $image['sizes']['original'] ?? $image) }}" 
                                         class="img-fluid rounded" 
                                         alt="Hotel Image"
                                         onclick="showImageModal('{{ Storage::url($image['sizes']['large'] ?? $image['sizes']['original'] ?? $image) }}')">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100" role="group">
                        <a href="{{ route('b2b.hotel-provider.hotels.edit', $hotel) }}" 
                           class="btn btn-warning mb-2">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Hotel Details
                        </a>
                        
                        <form action="{{ route('b2b.hotel-provider.hotels.toggle-status', $hotel) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn {{ $hotel->status === 'active' ? 'btn-danger' : 'btn-success' }} mb-2"
                                    onclick="return confirm('Are you sure you want to {{ $hotel->status === 'active' ? 'deactivate' : 'activate' }} this hotel?')">
                                <i class="fas fa-{{ $hotel->status === 'active' ? 'pause' : 'play' }} mr-2"></i>
                                {{ $hotel->status === 'active' ? 'Deactivate' : 'Activate' }} Hotel
                            </button>
                        </form>
                        
                        <a href="{{ route('b2b.hotel-provider.hotels.rooms', $hotel) }}" 
                           class="btn btn-info mb-2">
                            <i class="fas fa-bed mr-2"></i>
                            Manage Rooms
                        </a>
                        
                        <a href="{{ route('b2b.hotel-provider.bookings.hotel', $hotel) }}" 
                           class="btn btn-primary mb-2">
                            <i class="fas fa-calendar-check mr-2"></i>
                            View Bookings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hotel Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Hotel Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-bed"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Rooms</span>
                            <span class="info-box-number">{{ $hotelStats['total_rooms'] }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Available Rooms</span>
                            <span class="info-box-number">{{ $hotelStats['available_rooms'] }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Bookings This Month</span>
                            <span class="info-box-number">{{ $hotelStats['bookings_this_month'] }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Revenue This Month</span>
                            <span class="info-box-number">SAR {{ number_format($hotelStats['revenue_this_month'], 2) }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-percentage"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Occupancy Rate</span>
                            <span class="info-box-number">{{ $hotelStats['occupancy_rate'] }}%</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-star"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Rating</span>
                            <span class="info-box-number">{{ number_format($hotelStats['average_rating'], 1) }}/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hotel Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Hotel Image">
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .form-control-static {
        padding: 0.375rem 0;
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    .card img {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .card img:hover {
        transform: scale(1.05);
    }
</style>
@stop

@section('js')
<script>
    function showImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        $('#imageModal').modal('show');
    }

    // Success messages
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>
@stop
