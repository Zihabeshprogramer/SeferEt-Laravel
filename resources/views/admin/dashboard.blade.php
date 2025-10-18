@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['totalUsers'] }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users.moderation') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['totalPartners'] }}</h3>
                    <p>Total B2B Partners</p>
                </div>
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <a href="{{ route('admin.partners.management') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pendingPartners'] }}</h3>
                    <p>Pending B2B Partners</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.partners.management') }}?approval_status=pending" class="small-box-footer">
                    Needs Review <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['activePartners'] }}</h3>
                    <p>Active B2B Partners</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.partners.management') }}?status=active" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Revenue Overview
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Booking Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="bookingsChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Partners & Recent Activity -->
    <div class="row">
        @if($pendingPartners->count() > 0)
        <div class="col-md-12 mb-3">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Pending B2B Partner Approvals ({{ $pendingPartners->count() }})
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.partners.management') }}?approval_status=pending" class="btn btn-warning btn-sm">
                            <i class="fas fa-eye mr-1"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Contact</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPartners as $partner)
                                <tr>
                                    <td>
                                        <strong>{{ $partner->company_name }}</strong><br>
                                        <small class="text-muted">{{ $partner->company_registration_number }}</small>
                                    </td>
                                    <td>
                                        {{ $partner->name }}<br>
                                        <small class="text-muted">{{ $partner->email }}</small><br>
                                        <small class="text-muted">{{ $partner->phone }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $partner->created_at->format('M d, Y') }}</span><br>
                                        <small class="text-muted">{{ $partner->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.partners.show', $partner->id) }}" 
                                               class="btn btn-info btn-sm" 
                                               data-toggle="tooltip" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.partners.approve', $partner->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-success btn-sm" 
                                                        data-toggle="tooltip" 
                                                        title="Approve Partner"
                                                        onclick="return confirm('Are you sure you want to approve this partner?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#rejectModal{{ $partner->id }}"
                                                    data-toggle="tooltip" 
                                                    title="Reject Partner">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $partner->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Reject Partner</h4>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.partners.reject', $partner->id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to reject <strong>{{ $partner->company_name }}</strong>?</p>
                                                            <div class="form-group">
                                                                <label for="reason{{ $partner->id }}">Reason (Optional):</label>
                                                                <textarea class="form-control" 
                                                                          id="reason{{ $partner->id }}" 
                                                                          name="reason" 
                                                                          rows="3" 
                                                                          placeholder="Enter rejection reason..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Partner</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-inverse">
                        <div class="time-label">
                            <span class="bg-primary">Today</span>
                        </div>
                        <div>
                            <i class="fas fa-user bg-success"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> 2 mins ago</span>
                                <h3 class="timeline-header">New user registration</h3>
                                <div class="timeline-body">
                                    A new customer has registered for Umrah packages.
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-calendar bg-warning"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> 15 mins ago</span>
                                <h3 class="timeline-header">New booking received</h3>
                                <div class="timeline-body">
                                    Customer booked "Premium Umrah Package" for $2,500.
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-box bg-info"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> 1 hour ago</span>
                                <h3 class="timeline-header">Package updated</h3>
                                <div class="timeline-body">
                                    Partner updated "Economy Umrah Package" pricing.
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.moderation') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-user-shield mr-2"></i>
                            Manage Admin Users
                        </a>
                        <a href="{{ route('admin.partners.management') }}" class="btn btn-info btn-block">
                            <i class="fas fa-handshake mr-2"></i>
                            Manage Partners
                        </a>
                        <a href="{{ route('admin.packages') }}" class="btn btn-success btn-block">
                            <i class="fas fa-box mr-2"></i>
                            Review Packages
                        </a>
                        <a href="{{ route('admin.settings') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-cogs mr-2"></i>
                            System Settings
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server mr-2"></i>
                        System Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">API Status</span>
                            <span class="info-box-number">Online</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-database"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Database</span>
                            <span class="info-box-number">Connected</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Reviews</span>
                            <span class="info-box-number">5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
