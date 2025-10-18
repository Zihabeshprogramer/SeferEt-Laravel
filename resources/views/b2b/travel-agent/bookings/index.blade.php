@extends('layouts.b2b')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Package Bookings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Bookings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_bookings'] }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-suitcase-rolling"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_bookings'] }}</h3>
                    <p>Pending Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($stats['pending_payments'], 2) }}</h3>
                    <p>Pending Payments</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Confirmed Bookings</span>
                    <span class="info-box-number">{{ $stats['confirmed_bookings'] }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-flag-checkered"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed Trips</span>
                    <span class="info-box-number">{{ $stats['completed_bookings'] }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg. Participants</span>
                    <span class="info-box-number">{{ number_format($stats['average_participants'], 1) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Package Bookings Management</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="exportBookings">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('b2b.travel-agent.bookings') }}" class="row mb-3">
                <div class="col-md-2">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\PackageBooking::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select name="payment_status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Payment Status</option>
                        @foreach(\App\Models\PackageBooking::PAYMENT_STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ $paymentStatus === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select name="package_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Packages</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" {{ $packageId == $package->id ? 'selected' : '' }}>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" 
                           placeholder="From Date" onchange="this.form.submit()">
                </div>
                
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" 
                           placeholder="To Date" onchange="this.form.submit()">
                </div>
                
                <div class="col-md-2">
                    <a href="{{ route('b2b.travel-agent.bookings') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>

            <!-- Bookings Table -->
            <div class="table-responsive">
                <table id="bookingsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Booking Reference</th>
                            <th>Package</th>
                            <th>Customer</th>
                            <th>Departure Date</th>
                            <th>Participants</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->booking_reference }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $booking->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('b2b.travel-agent.packages.show', $booking->package->id) }}">
                                        {{ $booking->package->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $booking->duration_days }} days</small>
                                </td>
                                <td>
                                    {{ $booking->primary_contact_name }}
                                    <br>
                                    <small class="text-muted">{{ $booking->primary_contact_email }}</small>
                                </td>
                                <td>
                                    {{ $booking->departure_date->format('M d, Y') }}
                                    <br>
                                    @if($booking->days_until_departure >= 0)
                                        <small class="text-info">{{ $booking->days_until_departure }} days to go</small>
                                    @else
                                        <small class="text-muted">{{ abs($booking->days_until_departure) }} days ago</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light">{{ $booking->total_participants }}</span>
                                    <br>
                                    <small class="text-muted">
                                        A: {{ $booking->adults }} 
                                        @if($booking->children > 0) | C: {{ $booking->children }} @endif
                                        @if($booking->infants > 0) | I: {{ $booking->infants }} @endif
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</strong>
                                    <br>
                                    <small class="text-success">Paid: {{ number_format($booking->paid_amount, 2) }}</small>
                                    @if($booking->pending_amount > 0)
                                        <br><small class="text-danger">Pending: {{ number_format($booking->pending_amount, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $booking->status_badge_class }}">
                                        {{ \App\Models\PackageBooking::STATUSES[$booking->status] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $booking->payment_status_badge_class }}">
                                        {{ \App\Models\PackageBooking::PAYMENT_STATUSES[$booking->payment_status] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('b2b.travel-agent.bookings.show', $booking->id) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($booking->canBeConfirmed())
                                            <button class="btn btn-sm btn-success confirm-booking" 
                                                    data-id="{{ $booking->id }}" title="Confirm Booking">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        @if($booking->canBeCancelled())
                                            <button class="btn btn-sm btn-danger cancel-booking" 
                                                    data-id="{{ $booking->id }}" title="Cancel Booking">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        @if($booking->requiresPayment())
                                            <button class="btn btn-sm btn-warning update-payment" 
                                                    data-id="{{ $booking->id }}" title="Update Payment">
                                                <i class="fas fa-credit-card"></i>
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
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results
                </div>
                <div>
                    {{ $bookings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmBookingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Booking</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="confirmBookingForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="internal_notes">Internal Notes (Optional)</label>
                            <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="send_confirmation_email" 
                                       name="send_confirmation_email" checked>
                                <label class="form-check-label" for="send_confirmation_email">
                                    Send confirmation email to customer
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div class="modal fade" id="cancelBookingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cancelBookingForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="cancellation_reason">Cancellation Reason *</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                      rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="refund_amount">Refund Amount (Optional)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                                       step="0.01" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify_customer" 
                                       name="notify_customer" checked>
                                <label class="form-check-label" for="notify_customer">
                                    Notify customer about cancellation
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment Update Modal -->
    <div class="modal fade" id="updatePaymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Payment Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updatePaymentForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="payment_status">Payment Status *</label>
                            <select class="form-control" id="payment_status" name="payment_status" required>
                                @foreach(\App\Models\PackageBooking::PAYMENT_STATUSES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paid_amount">Paid Amount *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="">Select Method</option>
                                @foreach(\App\Models\PackageBooking::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_notes">Payment Notes</label>
                            <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    let currentBookingId = null;

    // Confirm booking modal
    $('.confirm-booking').on('click', function() {
        currentBookingId = $(this).data('id');
        $('#confirmBookingModal').modal('show');
    });

    // Cancel booking modal
    $('.cancel-booking').on('click', function() {
        currentBookingId = $(this).data('id');
        $('#cancelBookingModal').modal('show');
    });

    // Update payment modal
    $('.update-payment').on('click', function() {
        currentBookingId = $(this).data('id');
        $('#updatePaymentModal').modal('show');
    });

    // Handle confirm booking form submission
    $('#confirmBookingForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: `/b2b/travel-agent/bookings/${currentBookingId}/confirm`,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#confirmBookingModal').modal('hide');
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while confirming the booking.');
                console.error(xhr);
            }
        });
    });

    // Handle cancel booking form submission
    $('#cancelBookingForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: `/b2b/travel-agent/bookings/${currentBookingId}/cancel`,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#cancelBookingModal').modal('hide');
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while cancelling the booking.');
                console.error(xhr);
            }
        });
    });

    // Handle update payment form submission
    $('#updatePaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: `/b2b/travel-agent/bookings/${currentBookingId}/update-payment`,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#updatePaymentModal').modal('hide');
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while updating payment status.');
                console.error(xhr);
            }
        });
    });

    // Export bookings
    $('#exportBookings').on('click', function() {
        window.location.href = '{{ route("b2b.travel-agent.bookings.export") }}' + window.location.search;
    });
});
</script>
@endpush
