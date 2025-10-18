@extends('layouts.b2b')

@section('title', 'All Bookings')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-bookmark"></i> All Bookings Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">All Bookings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <!-- Overall Stats -->
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
                    <h3>${{ number_format($stats['total_revenue'], 0) }}</h3>
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
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['confirmed_bookings'] }}</h3>
                    <p>Confirmed Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Type Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fas fa-map-marked-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Package Tours</span>
                    <span class="info-box-number">{{ $stats['package_bookings']['count'] }}</span>
                    <small class="text-muted">${{ number_format($stats['package_bookings']['revenue'], 0) }} revenue</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-hotel"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Hotel Bookings</span>
                    <span class="info-box-number">{{ $stats['hotel_bookings']['count'] }}</span>
                    <small class="text-muted">${{ number_format($stats['hotel_bookings']['revenue'], 0) }} revenue</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-plane"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Flight Bookings</span>
                    <span class="info-box-number">{{ $stats['flight_bookings']['count'] }}</span>
                    <small class="text-muted">${{ number_format($stats['flight_bookings']['revenue'], 0) }} revenue</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-bus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Transport Bookings</span>
                    <span class="info-box-number">{{ $stats['transport_bookings']['count'] }}</span>
                    <small class="text-muted">${{ number_format($stats['transport_bookings']['revenue'], 0) }} revenue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> All Bookings Overview
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="refreshBookings">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button type="button" class="btn btn-success btn-sm" id="exportBookings">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Advanced Filters -->
            <form method="GET" action="{{ route('b2b.travel-agent.bookings.all') }}" class="row mb-4">
                <div class="col-md-2">
                    <label class="form-label">Booking Type</label>
                    <select name="booking_type" class="form-control" onchange="this.form.submit()">
                        @foreach($filterOptions['booking_types'] as $key => $label)
                            <option value="{{ $key }}" {{ $bookingType === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($filterOptions['statuses'] as $key => $label)
                            <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Payment Status</option>
                        @foreach($filterOptions['payment_statuses'] as $key => $label)
                            <option value="{{ $key }}" {{ $paymentStatus === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" 
                           onchange="this.form.submit()">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" 
                           onchange="this.form.submit()">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <a href="{{ route('b2b.travel-agent.bookings.all') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            <!-- Bookings Table -->
            <div class="table-responsive">
                <table id="allBookingsTable" class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Service</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <span class="badge 
                                        @switch($booking->booking_type)
                                            @case('package') badge-purple @break
                                            @case('hotel') badge-success @break
                                            @case('flight') badge-info @break
                                            @case('transport') badge-warning @break
                                            @default badge-secondary
                                        @endswitch
                                    ">
                                        @switch($booking->booking_type)
                                            @case('package') <i class="fas fa-map-marked-alt"></i> Package @break
                                            @case('hotel') <i class="fas fa-hotel"></i> Hotel @break
                                            @case('flight') <i class="fas fa-plane"></i> Flight @break
                                            @case('transport') <i class="fas fa-bus"></i> Transport @break
                                            @default {{ ucfirst($booking->booking_type) }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $booking->booking_reference ?? 'N/A' }}</strong>
                                    @if($booking->confirmation_code ?? false)
                                        <br><small class="text-muted">{{ $booking->confirmation_code }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="service-info">
                                        <strong>{{ $booking->service_name }}</strong>
                                        @if($booking->booking_type === 'flight' && $booking->flight)
                                            <br><small class="text-muted">{{ $booking->flight->route }}</small>
                                        @elseif($booking->booking_type === 'hotel' && $booking->room)
                                            <br><small class="text-muted">Room: {{ $booking->room->formatted_room_number }}</small>
                                        @elseif($booking->booking_type === 'transport')
                                            <br><small class="text-muted">{{ $booking->pickup_location ?? 'Transport Service' }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $customerName = '';
                                        $customerEmail = '';
                                        switch($booking->booking_type) {
                                            case 'package':
                                                $customerName = $booking->primary_contact_name;
                                                $customerEmail = $booking->primary_contact_email;
                                                break;
                                            case 'hotel':
                                                $customerName = $booking->guest_name;
                                                $customerEmail = $booking->guest_email;
                                                break;
                                            case 'flight':
                                                $customerName = $booking->passenger_name;
                                                $customerEmail = $booking->passenger_email;
                                                break;
                                            case 'transport':
                                                $customerName = $booking->passenger_name ?? $booking->customer_name;
                                                $customerEmail = $booking->passenger_email ?? $booking->customer_email;
                                                break;
                                        }
                                    @endphp
                                    
                                    <strong>{{ $customerName }}</strong>
                                    @if($customerEmail)
                                        <br><small class="text-muted">{{ $customerEmail }}</small>
                                    @endif
                                    
                                    @if($booking->booking_type === 'flight' && $booking->passengers > 1)
                                        <br><small class="badge badge-light">{{ $booking->passengers }} passengers</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $referenceDate = null;
                                        switch($booking->booking_type) {
                                            case 'package':
                                                $referenceDate = $booking->departure_date;
                                                break;
                                            case 'hotel':
                                                $referenceDate = $booking->check_in_date;
                                                break;
                                            case 'flight':
                                                $referenceDate = $booking->flight->departure_datetime ?? null;
                                                break;
                                            case 'transport':
                                                $referenceDate = $booking->pickup_datetime;
                                                break;
                                        }
                                    @endphp
                                    
                                    @if($referenceDate)
                                        <strong>{{ $referenceDate->format('M d, Y') }}</strong>
                                        @if($booking->booking_type === 'flight' || $booking->booking_type === 'transport')
                                            <br><small class="text-muted">{{ $referenceDate->format('H:i') }}</small>
                                        @endif
                                        
                                        @if($referenceDate->isFuture())
                                            <br><small class="text-info">{{ $referenceDate->diffForHumans() }}</small>
                                        @else
                                            <br><small class="text-muted">{{ $referenceDate->diffForHumans() }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $booking->currency ?? 'USD' }} {{ number_format($booking->total_amount ?? 0, 2) }}</strong>
                                    @if(($booking->paid_amount ?? 0) > 0)
                                        <br><small class="text-success">Paid: {{ number_format($booking->paid_amount, 2) }}</small>
                                    @endif
                                    @if(($booking->total_amount - $booking->paid_amount) > 0)
                                        <br><small class="text-danger">Due: {{ number_format($booking->total_amount - $booking->paid_amount, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($booking->status) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            'completed' => 'badge-primary',
                                            'no_show' => 'badge-secondary',
                                            default => 'badge-light'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $paymentStatusClass = match($booking->payment_status ?? 'pending') {
                                            'pending' => 'badge-warning',
                                            'partial' => 'badge-info',
                                            'paid' => 'badge-success',
                                            'refunded' => 'badge-secondary',
                                            default => 'badge-light'
                                        };
                                    @endphp
                                    <span class="badge {{ $paymentStatusClass }}">
                                        {{ ucfirst($booking->payment_status ?? 'pending') }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $booking->created_at->format('M d, Y') }}</small>
                                    <br><small class="text-muted">{{ $booking->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('b2b.travel-agent.bookings.show-universal', [$booking->booking_type, $booking->id]) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if(!in_array($booking->status, ['cancelled', 'completed']))
                                            <button class="btn btn-sm btn-danger cancel-booking" 
                                                    data-type="{{ $booking->booking_type }}" 
                                                    data-id="{{ $booking->id }}" 
                                                    data-reference="{{ $booking->booking_reference }}"
                                                    title="Cancel Booking">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        @if(!in_array($booking->status, ['cancelled', 'completed']) && ($booking->payment_status ?? 'pending') !== 'paid')
                                            <button class="btn btn-sm btn-warning update-payment" 
                                                    data-type="{{ $booking->booking_type }}" 
                                                    data-id="{{ $booking->id }}"
                                                    data-total="{{ $booking->total_amount }}"
                                                    data-paid="{{ $booking->paid_amount ?? 0 }}"
                                                    title="Update Payment">
                                                <i class="fas fa-credit-card"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Bookings Found</h5>
                                        <p class="text-muted">No bookings match your current filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Booking Count Info -->
            @if($bookings->count() > 0)
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Showing {{ $bookings->count() }} bookings 
                        @if($bookingType !== 'all')
                            (filtered by {{ $filterOptions['booking_types'][$bookingType] }})
                        @endif
                    </small>
                </div>
            @endif
        </div>
    </div>

    <!-- Universal Cancel Booking Modal -->
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
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            You are about to cancel booking: <strong id="cancelBookingReference"></strong>
                        </div>
                        
                        <div class="form-group">
                            <label for="cancellation_reason">Cancellation Reason *</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                      rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
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
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Universal Payment Update Modal -->
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_status">Payment Status *</label>
                                    <select class="form-control" id="payment_status" name="payment_status" required>
                                        @foreach($filterOptions['payment_statuses'] as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <select class="form-control" id="payment_method" name="payment_method">
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
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
                            <small class="form-text text-muted">
                                Total Amount: $<span id="totalAmountDisplay">0.00</span> | 
                                Current Paid: $<span id="currentPaidDisplay">0.00</span>
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_notes">Payment Notes</label>
                            <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"
                                      placeholder="Optional notes about this payment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Update Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
.badge-purple {
    background-color: #6f42c1;
    color: white;
}

.service-info strong {
    display: block;
    line-height: 1.2;
}

.table td {
    vertical-align: middle;
}

.btn-group .btn {
    margin: 0 1px;
}

#allBookingsTable tbody tr:hover {
    background-color: #f8f9fa;
}

.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;
    background: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
    width: 100%;
}
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    let currentBookingType = null;
    let currentBookingId = null;

    // Cancel booking modal
    $('.cancel-booking').on('click', function() {
        currentBookingType = $(this).data('type');
        currentBookingId = $(this).data('id');
        const reference = $(this).data('reference');
        
        $('#cancelBookingReference').text(reference);
        $('#cancelBookingModal').modal('show');
    });

    // Update payment modal
    $('.update-payment').on('click', function() {
        currentBookingType = $(this).data('type');
        currentBookingId = $(this).data('id');
        const totalAmount = parseFloat($(this).data('total')) || 0;
        const paidAmount = parseFloat($(this).data('paid')) || 0;
        
        $('#totalAmountDisplay').text(totalAmount.toFixed(2));
        $('#currentPaidDisplay').text(paidAmount.toFixed(2));
        $('#paid_amount').attr('max', totalAmount);
        $('#paid_amount').val(paidAmount);
        
        $('#updatePaymentModal').modal('show');
    });

    // Handle cancel booking form submission
    $('#cancelBookingForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentBookingType || !currentBookingId) {
            toastr.error('Invalid booking selection');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cancelling...');
        
        $.ajax({
            url: `/b2b/travel-agent/bookings/${currentBookingType}/${currentBookingId}/cancel`,
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
                let message = 'An error occurred while cancelling the booking.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
                console.error(xhr);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-times"></i> Cancel Booking');
            }
        });
    });

    // Handle update payment form submission
    $('#updatePaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentBookingType || !currentBookingId) {
            toastr.error('Invalid booking selection');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: `/b2b/travel-agent/bookings/${currentBookingType}/${currentBookingId}/update-payment`,
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
                let message = 'An error occurred while updating payment status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
                console.error(xhr);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-credit-card"></i> Update Payment');
            }
        });
    });

    // Refresh bookings
    $('#refreshBookings').on('click', function() {
        location.reload();
    });

    // Export bookings
    $('#exportBookings').on('click', function() {
        const params = new URLSearchParams(window.location.search);
        const exportUrl = '{{ route("b2b.travel-agent.bookings.export-all") }}?' + params.toString();
        window.open(exportUrl, '_blank');
    });

    // Auto-update payment amount based on status
    $('#payment_status').on('change', function() {
        const status = $(this).val();
        const totalAmount = parseFloat($('#totalAmountDisplay').text()) || 0;
        const currentPaid = parseFloat($('#currentPaidDisplay').text()) || 0;
        
        switch(status) {
            case 'paid':
                $('#paid_amount').val(totalAmount.toFixed(2));
                break;
            case 'pending':
                $('#paid_amount').val('0.00');
                break;
            case 'partial':
                // Keep current value or set to half if current is 0
                if (currentPaid === 0) {
                    $('#paid_amount').val((totalAmount / 2).toFixed(2));
                }
                break;
            case 'refunded':
                $('#paid_amount').val('0.00');
                break;
        }
    });
});
</script>
@endpush
