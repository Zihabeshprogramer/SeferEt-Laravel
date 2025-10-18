@extends('layouts.admin')

@section('title', 'Bookings Management')

@section('page-title', 'Bookings Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Bookings</li>
@endsection

@section('content')
    <!-- Stats Cards -->
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-check mr-2"></i>
                All Bookings
            </h3>
            <div class="card-tools">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter mr-1"></i>All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-check mr-1"></i>Confirmed
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-clock mr-1"></i>Pending
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times mr-1"></i>Cancelled
                    </button>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-wrench"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" role="menu">
                        <a href="#" class="dropdown-item">Export to PDF</a>
                        <a href="#" class="dropdown-item">Export to Excel</a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">Bulk Actions</a>
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
                                <th style="width: 10px">#</th>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Travel Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Booked Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->id ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $booking->booking_id ?? 'BMH-' . str_pad($booking->id ?? 1, 6, '0', STR_PAD_LEFT) }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-muted mr-2" style="font-size: 1.2rem;"></i>
                                            <div>
                                                <strong>{{ $booking->customer->name ?? 'John Doe' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $booking->customer->email ?? 'john@example.com' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $booking->package->name ?? 'Premium Umrah Package' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $booking->package->partner->company_name ?? 'Sample Travel Agency' }}</small>
                                    </td>
                                    <td>
                                        {{ isset($booking->travel_date) ? $booking->travel_date->format('M d, Y') : 'Mar 15, 2024' }}
                                        <br>
                                        <small class="text-muted">{{ $booking->duration ?? '14' }} days</small>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($booking->total_amount ?? 3500, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $booking->travelers_count ?? 2 }} travelers</small>
                                    </td>
                                    <td>
                                        @php
                                            $status = $booking->status ?? 'pending';
                                        @endphp
                                        @if($status === 'confirmed')
                                            <span class="badge badge-success">Confirmed</span>
                                        @elseif($status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($status === 'cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                        @elseif($status === 'completed')
                                            <span class="badge badge-info">Completed</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ isset($booking->created_at) ? $booking->created_at->format('M d, Y') : 'Jan 20, 2024' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if(($booking->status ?? 'pending') === 'pending')
                                                <button type="button" class="btn btn-success" title="Confirm Booking">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Cancel Booking">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#"><i class="fas fa-print mr-2"></i>Print</a>
                                                    <a class="dropdown-item" href="#"><i class="fas fa-envelope mr-2"></i>Send Email</a>
                                                    <a class="dropdown-item" href="#"><i class="fas fa-edit mr-2"></i>Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Bookings Found</h5>
                                        <p class="text-muted">No bookings have been made yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Bookings Found</h5>
                    <p class="text-muted">There are no bookings in the system yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Booking management functionality can be added here

});
</script>
@endsection
