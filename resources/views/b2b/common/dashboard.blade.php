@extends('layouts.b2b')

@section('title', 'B2B Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Welcome Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="text-white mb-1">
                                <i class="fas fa-handshake mr-2"></i>
                                Welcome back, {{ $partner->name }}!
                            </h4>
                            <p class="text-white-50 mb-0">
                                <i class="fas fa-building mr-1"></i>
                                {{ $partner->company_name }}
                            </p>
                            <p class="text-white-50 mb-0">
                                <small>Last login: {{ $partner->last_login_at ? $partner->last_login_at->format('M d, Y \a\t H:i') : 'Never' }}</small>
                            </p>
                        </div>
                        <div class="text-right text-white">
                            <i class="fas fa-user-tie fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['totalPackages'] }}</h3>
                    <p>Total Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="{{ route('b2b.packages') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['activePackages'] }}</h3>
                    <p>Active Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('b2b.packages') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['totalBookings'] }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <a href="{{ route('b2b.bookings') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($stats['totalRevenue']) }}</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('b2b.analytics') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Recent Bookings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('b2b.bookings') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye mr-1"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($recentBookings) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBookings as $booking)
                                    <tr>
                                        <td>{{ $booking['customer'] }}</td>
                                        <td>{{ $booking['package'] }}</td>
                                        <td>{{ $booking['date'] }}</td>
                                        <td>
                                            <span class="badge badge-{{ $booking['status_color'] }}">
                                                {{ $booking['status'] }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($booking['amount']) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Recent Bookings</h5>
                            <p class="text-muted">Your recent bookings will appear here.</p>
                            <a href="{{ route('b2b.packages.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Create Your First Package
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Company Info -->
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
                    <div class="d-grid gap-2">
                        <a href="{{ route('b2b.packages.create') }}" class="btn btn-success btn-block">
                            <i class="fas fa-plus mr-2"></i>
                            Create New Package
                        </a>
                        <a href="{{ route('b2b.packages') }}" class="btn btn-info btn-block">
                            <i class="fas fa-box mr-2"></i>
                            Manage Packages
                        </a>
                        <a href="{{ route('b2b.bookings') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-calendar-check mr-2"></i>
                            View Bookings
                        </a>
                        <a href="{{ route('b2b.profile') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-user-edit mr-2"></i>
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-2"></i>
                        Company Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-building"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Company</span>
                            <span class="info-box-number">{{ $partner->company_name }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-certificate"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Registration</span>
                            <span class="info-box-number">{{ $partner->company_registration_number }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-phone"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Phone</span>
                            <span class="info-box-number">{{ $partner->phone }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $partner->status_badge }}">
                            <i class="fas fa-{{ $partner->status === 'active' ? 'check-circle' : 'clock' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Status</span>
                            <span class="info-box-number text-capitalize">{{ $partner->status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Packages -->
    @if(count($topPackages) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star mr-2"></i>
                        Top Performing Packages
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($topPackages as $package)
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-gradient-success">
                                    <i class="fas fa-box"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $package['name'] }}</span>
                                    <span class="info-box-number">{{ $package['bookings'] }} bookings</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: {{ $package['percentage'] }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $package['percentage'] }}% of total bookings
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        // You can implement auto-refresh functionality here
        // location.reload();
    }, 300000); // 5 minutes
});
</script>
@endsection
