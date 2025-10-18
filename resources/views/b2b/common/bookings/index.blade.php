@extends('layouts.b2b')

@section('title', 'Bookings Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('b2b.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Bookings</li>
                    </ol>
                </div>
                <h4 class="page-title">Bookings Management</h4>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['confirmed'] }}</h3>
                    <p>Confirmed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['cancelled'] }}</h3>
                    <p>Cancelled</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> All Bookings
                    </h5>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" data-filter="all">All Bookings</a>
                                <a class="dropdown-item" href="#" data-filter="confirmed">Confirmed</a>
                                <a class="dropdown-item" href="#" data-filter="pending">Pending</a>
                                <a class="dropdown-item" href="#" data-filter="cancelled">Cancelled</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($bookings) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                    <tr>
                                        <td>
                                            <strong>#{{ $booking['id'] ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $booking['customer_name'] ?? 'Unknown Customer' }}</strong><br>
                                                <small class="text-muted">{{ $booking['customer_email'] ?? '' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $booking['service_name'] ?? 'Unknown Service' }}</strong><br>
                                                <small class="text-muted">{{ $booking['service_type'] ?? 'Service' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $booking['booking_date'] ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $booking['created_at'] ?? 'Unknown' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($booking['status'] ?? 'unknown') {
                                                    'confirmed' => 'success',
                                                    'pending' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}">
                                                {{ ucfirst($booking['status'] ?? 'Unknown') }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($booking['amount'] ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if(($booking['status'] ?? '') === 'pending')
                                                <button type="button" class="btn btn-success" title="Confirm">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Cancel">
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
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Bookings Found</h5>
                            <p class="text-muted">You don't have any bookings yet.</p>
                            @if(auth()->user()->hasRole('hotel_provider'))
                                <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-primary">
                                    <i class="fas fa-hotel mr-2"></i>Manage Your Hotels
                                </a>
                            @elseif(auth()->user()->hasRole('travel_agent'))
                                <a href="{{ route('b2b.travel-agent.packages') }}" class="btn btn-primary">
                                    <i class="fas fa-box mr-2"></i>Manage Your Packages
                                </a>
                            @elseif(auth()->user()->hasRole('transport_provider'))
                                <a href="{{ route('b2b.transport-provider.vehicles') }}" class="btn btn-primary">
                                    <i class="fas fa-bus mr-2"></i>Manage Your Vehicles
                                </a>
                            @else
                                <a href="{{ route('b2b.packages.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-2"></i>Create Your First Package
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Role-specific Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(auth()->user()->hasRole('hotel_provider'))
                            <div class="col-md-3">
                                <a href="{{ route('b2b.hotel-provider.dashboard') }}" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-hotel"></i><br>
                                    <small>Hotel Dashboard</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-list"></i><br>
                                    <small>Manage Hotels</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.hotel-provider.rates') }}" class="btn btn-outline-warning btn-block">
                                    <i class="fas fa-dollar-sign"></i><br>
                                    <small>Manage Rates</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.hotel-provider.reports') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-chart-line"></i><br>
                                    <small>View Reports</small>
                                </a>
                            </div>
                        @elseif(auth()->user()->hasRole('travel_agent'))
                            <div class="col-md-3">
                                <a href="{{ route('b2b.travel-agent.dashboard') }}" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-plane"></i><br>
                                    <small>Agent Dashboard</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.travel-agent.customers') }}" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-users"></i><br>
                                    <small>Manage Customers</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.travel-agent.packages') }}" class="btn btn-outline-warning btn-block">
                                    <i class="fas fa-box"></i><br>
                                    <small>Travel Packages</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.travel-agent.commissions') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-percentage"></i><br>
                                    <small>Commissions</small>
                                </a>
                            </div>
                        @elseif(auth()->user()->hasRole('transport_provider'))
                            <div class="col-md-3">
                                <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-bus"></i><br>
                                    <small>Transport Dashboard</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.transport-provider.vehicles') }}" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-car"></i><br>
                                    <small>Manage Vehicles</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.transport-provider.routes') }}" class="btn btn-outline-warning btn-block">
                                    <i class="fas fa-route"></i><br>
                                    <small>Manage Routes</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.transport-provider.drivers') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-id-card"></i><br>
                                    <small>Manage Drivers</small>
                                </a>
                            </div>
                        @else
                            <div class="col-md-3">
                                <a href="{{ route('b2b.packages') }}" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-box"></i><br>
                                    <small>Manage Packages</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.packages.create') }}" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-plus"></i><br>
                                    <small>Create Package</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.analytics') }}" class="btn btn-outline-warning btn-block">
                                    <i class="fas fa-chart-bar"></i><br>
                                    <small>View Analytics</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('b2b.profile') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-user"></i><br>
                                    <small>Update Profile</small>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if we have data
    @if(count($bookings) > 0)
        $('.table').DataTable({
            responsive: true,
            order: [[0, 'desc']], // Sort by booking ID descending
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    @endif

    // Filter functionality
    $('[data-filter]').on('click', function(e) {
        e.preventDefault();
        var filter = $(this).data('filter');
        
        if (filter === 'all') {
            $('tbody tr').show();
        } else {
            $('tbody tr').hide();
            $('tbody tr').each(function() {
                var status = $(this).find('.badge').text().toLowerCase().trim();
                if (status === filter) {
                    $(this).show();
                }
            });
        }
    });
});
</script>
@endpush
