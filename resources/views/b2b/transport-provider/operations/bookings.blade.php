@extends('layouts.b2b')

@section('title', 'Transport Bookings')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-bus text-info mr-2"></i>
                Transport Bookings
            </h1>
            <small class="text-muted">Manage your transport service bookings</small>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-success" onclick="exportBookings()">
                <i class="fas fa-download mr-1"></i>
                Export Bookings
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_bookings']) }}</h4>
                            <small>Total Bookings</small>
                        </div>
                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['confirmed_bookings']) }}</h4>
                            <small>Confirmed Bookings</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['pending_bookings']) }}</h4>
                            <small>Pending Bookings</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">${{ number_format($stats['total_revenue'], 2) }}</h4>
                            <small>Total Revenue</small>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>
                        Filters
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <form method="GET" action="{{ route('b2b.transport-provider.operations.bookings') }}" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="status">Booking Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\TransportBooking::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-control">
                                <option value="">All Payment Statuses</option>
                                @foreach(\App\Models\TransportBooking::PAYMENT_STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ request('payment_status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="date_from">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="date_to">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <a href="{{ route('b2b.transport-provider.operations.bookings') }}" class="btn btn-outline-secondary ml-1">
                                    <i class="fas fa-times mr-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Transport Bookings
                    </h3>
                </div>
                
                <div class="card-body p-0">
                    @if($bookings->total() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Booking Ref</th>
                                        <th>Service</th>
                                        <th>Passenger</th>
                                        <th>Route</th>
                                        <th>Pickup Time</th>
                                        <th>Passengers</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Amount</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                        <tr>
                                            <td>
                                                <strong>{{ $booking->booking_reference }}</strong>
                                                <br><small class="text-muted">{{ $booking->confirmation_code }}</small>
                                            </td>
                                            <td>
                                                {{ $booking->transportService->service_name ?? 'N/A' }}
                                                <br><small class="text-muted">{{ ucfirst($booking->transport_type) }}</small>
                                            </td>
                                            <td>
                                                {{ $booking->passenger_name }}
                                                <br><small class="text-muted">{{ $booking->passenger_phone }}</small>
                                            </td>
                                            <td>
                                                <span class="route-info">
                                                    {{ $booking->pickup_location }}
                                                    <br><i class="fas fa-arrow-down text-muted"></i>
                                                    <br>{{ $booking->dropoff_location }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $booking->pickup_datetime->format('M j, Y') }}
                                                <br><strong>{{ $booking->pickup_datetime->format('g:i A') }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ $booking->total_passengers }}</span>
                                                <br><small class="text-muted">{{ $booking->adults }}A {{ $booking->children }}C</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'confirmed' => 'success',
                                                        'in_progress' => 'info',
                                                        'completed' => 'primary',
                                                        'cancelled' => 'danger',
                                                        'no_show' => 'dark'
                                                    ];
                                                    $color = $statusColors[$booking->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $color }}">{{ $booking->status_label }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $paymentColors = [
                                                        'pending' => 'warning',
                                                        'paid' => 'success',
                                                        'partial' => 'info',
                                                        'refunded' => 'danger',
                                                        'failed' => 'danger'
                                                    ];
                                                    $paymentColor = $paymentColors[$booking->payment_status] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $paymentColor }}">{{ $booking->payment_status_label }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</strong>
                                                @if($booking->paid_amount > 0)
                                                    <br><small class="text-success">Paid: {{ $booking->currency }} {{ number_format($booking->paid_amount, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewBooking({{ $booking->id }})" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($booking->status === 'pending')
                                                        <button class="btn btn-sm btn-success" onclick="confirmBooking({{ $booking->id }})" title="Confirm Booking">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($booking->status === 'confirmed')
                                                        <button class="btn btn-sm btn-info" onclick="startBooking({{ $booking->id }})" title="Start Trip">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($booking->status === 'in_progress')
                                                        <button class="btn btn-sm btn-primary" onclick="completeBooking({{ $booking->id }})" title="Complete Trip">
                                                            <i class="fas fa-flag-checkered"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if(!in_array($booking->status, ['completed', 'cancelled']))
                                                        <button class="btn btn-sm btn-danger" onclick="cancelBooking({{ $booking->id }})" title="Cancel Booking">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $bookings->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Bookings Found</h4>
                            <p class="text-muted">You don't have any transport bookings yet.</p>
                            @if(request()->hasAny(['status', 'payment_status', 'search', 'date_from', 'date_to']))
                                <p class="text-muted">Try adjusting your filters or <a href="{{ route('b2b.transport-provider.operations.bookings') }}">clear all filters</a>.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    @if($bookings->total() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Quick Summary
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-right">
                                    <h5>{{ $stats['total_bookings'] }}</h5>
                                    <small class="text-muted">Total Bookings</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-right">
                                    <h5>${{ number_format($stats['total_revenue'], 2) }}</h5>
                                    <small class="text-muted">Total Revenue</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-right">
                                    <h5>${{ number_format($stats['paid_amount'], 2) }}</h5>
                                    <small class="text-muted">Paid Amount</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h5>${{ number_format($stats['total_revenue'] - $stats['paid_amount'], 2) }}</h5>
                                <small class="text-muted">Pending Payment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .opacity-75 {
            opacity: 0.75;
        }
        .route-info {
            font-size: 12px;
            line-height: 1.3;
        }
        .border-right {
            border-right: 1px solid #dee2e6 !important;
        }
        @media (max-width: 768px) {
            .border-right {
                border-right: none !important;
                border-bottom: 1px solid #dee2e6 !important;
                margin-bottom: 15px;
                padding-bottom: 15px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        function viewBooking(bookingId) {
            // Implement booking details modal or redirect to details page
            window.open(`{{ route('b2b.transport-provider.bookings.show', ':id') }}`.replace(':id', bookingId), '_blank');
        }
        
        function confirmBooking(bookingId) {
            if (confirm('Are you sure you want to confirm this booking?')) {
                $.ajax({
                    url: `{{ route('b2b.transport-provider.bookings.confirm', ':id') }}`.replace(':id', bookingId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        pickup_notes: prompt('Add any pickup notes (optional):')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error confirming booking.');
                    }
                });
            }
        }
        
        function startBooking(bookingId) {
            if (confirm('Mark this trip as started?')) {
                $.ajax({
                    url: `{{ route('b2b.transport-provider.bookings.start', ':id') }}`.replace(':id', bookingId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error starting trip.');
                    }
                });
            }
        }
        
        function completeBooking(bookingId) {
            if (confirm('Mark this trip as completed?')) {
                $.ajax({
                    url: `{{ route('b2b.transport-provider.bookings.complete', ':id') }}`.replace(':id', bookingId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error completing trip.');
                    }
                });
            }
        }
        
        function cancelBooking(bookingId) {
            const reason = prompt('Please provide a cancellation reason:');
            if (reason) {
                $.ajax({
                    url: `{{ route('b2b.transport-provider.bookings.cancel', ':id') }}`.replace(':id', bookingId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cancellation_reason: reason
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error cancelling booking.');
                    }
                });
            }
        }
        
        function exportBookings() {
            // Implement export functionality
            toastr.info('Export functionality will be implemented soon.');
        }
    </script>
@stop
