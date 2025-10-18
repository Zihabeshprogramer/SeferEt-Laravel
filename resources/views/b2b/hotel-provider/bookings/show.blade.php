@extends('layouts.b2b')

@section('title', 'Booking Details - ' . $booking->booking_reference)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-file-invoice text-info mr-2"></i>
                Booking Details
            </h1>
            <p class="text-muted">
                <strong>Reference:</strong> {{ $booking->booking_reference }}
                <span class="mx-2">|</span>
                <strong>Status:</strong>
                <span class="badge badge-{{ $booking->status_badge_class }} ml-1">
                    {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                </span>
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.hotel-provider.bookings.hotel', $booking->hotel->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Hotel Bookings
            </a>
            <button class="btn btn-outline-info" onclick="printBooking({{ $booking->id }})">
                <i class="fas fa-print mr-1"></i>
                Print
            </button>
            <div class="dropdown d-inline">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-cogs mr-1"></i>
                    Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @if($booking->canBeConfirmed())
                        <a class="dropdown-item" href="#" onclick="confirmBooking({{ $booking->id }})">
                            <i class="fas fa-check text-success mr-2"></i>Confirm Booking
                        </a>
                    @endif
                    
                    @if($booking->canCheckIn())
                        <a class="dropdown-item" href="#" onclick="checkInBooking({{ $booking->id }})">
                            <i class="fas fa-sign-in-alt text-primary mr-2"></i>Check In Guest
                        </a>
                    @endif
                    
                    @if($booking->canCheckOut())
                        <a class="dropdown-item" href="#" onclick="checkOutBooking({{ $booking->id }})">
                            <i class="fas fa-sign-out-alt text-warning mr-2"></i>Check Out Guest
                        </a>
                    @endif
                    
                    @if($booking->canBeCancelled())
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="cancelBooking({{ $booking->id }})">
                            <i class="fas fa-times text-danger mr-2"></i>Cancel Booking
                        </a>
                    @endif
                    
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="emailBooking({{ $booking->id }})">
                        <i class="fas fa-envelope mr-2"></i>Email Guest
                    </a>
                    <a class="dropdown-item" href="#" onclick="viewBookingHistory({{ $booking->id }})">
                        <i class="fas fa-history mr-2"></i>View History
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Booking Status Alert -->
    @if($booking->status === 'pending')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Pending Confirmation:</strong> This booking requires confirmation to be activated.
            @if($booking->canBeConfirmed())
                <button class="btn btn-sm btn-success ml-2" onclick="confirmBooking({{ $booking->id }})">
                    Confirm Now
                </button>
            @endif
        </div>
    @elseif($booking->status === 'confirmed' && $booking->check_in_date->isToday())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Check-in Today:</strong> Guest is scheduled to check in today.
            @if($booking->canCheckIn())
                <button class="btn btn-sm btn-primary ml-2" onclick="checkInBooking({{ $booking->id }})">
                    Check In Now
                </button>
            @endif
        </div>
    @elseif($booking->status === 'checked_in' && $booking->check_out_date->isToday())
        <div class="alert alert-warning">
            <i class="fas fa-clock mr-2"></i>
            <strong>Check-out Today:</strong> Guest is scheduled to check out today.
            @if($booking->canCheckOut())
                <button class="btn btn-sm btn-warning ml-2" onclick="checkOutBooking({{ $booking->id }})">
                    Check Out Now
                </button>
            @endif
        </div>
    @elseif($booking->status === 'cancelled')
        <div class="alert alert-danger">
            <i class="fas fa-times-circle mr-2"></i>
            <strong>Cancelled:</strong> This booking has been cancelled.
            @if($booking->cancelled_at)
                <span class="text-muted">Cancelled on {{ $booking->cancelled_at->format('M d, Y at H:i') }}</span>
            @endif
        </div>
    @endif

    <div class="row">
        <!-- Guest Information -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Guest Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Full Name</label>
                                <div class="h5">{{ $booking->guest_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Email Address</label>
                                <div>
                                    <i class="fas fa-envelope text-muted mr-1"></i>
                                    <a href="mailto:{{ $booking->guest_email }}">{{ $booking->guest_email }}</a>
                                </div>
                            </div>
                        </div>
                        @if($booking->guest_phone)
                            <div class="col-md-6">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Phone Number</label>
                                    <div>
                                        <i class="fas fa-phone text-muted mr-1"></i>
                                        <a href="tel:{{ $booking->guest_phone }}">{{ $booking->guest_phone }}</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Number of Guests</label>
                                <div>
                                    <i class="fas fa-users text-muted mr-1"></i>
                                    {{ $booking->guest_info }}
                                </div>
                            </div>
                        </div>
                        @if($booking->special_requests)
                            <div class="col-12">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Special Requests</label>
                                    <div class="bg-light p-3 rounded">{{ $booking->special_requests }}</div>
                                </div>
                            </div>
                        @endif
                        @if($booking->notes)
                            <div class="col-12">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Internal Notes</label>
                                    <div class="bg-warning-light p-3 rounded">{{ $booking->notes }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Hotel & Room Information -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bed mr-2"></i>
                        Hotel & Room Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-group mb-3">
                                <label class="text-muted">Hotel</label>
                                <div class="h5">{{ $booking->hotel->name }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $booking->hotel->address }}, {{ $booking->hotel->city }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Room Number</label>
                                <div class="h4 text-primary">{{ $booking->room->room_number }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Room Type</label>
                                <div>
                                    <span class="badge badge-info">{{ $booking->room->room_type }}</span>
                                </div>
                            </div>
                        </div>
                        @if($booking->room->name)
                            <div class="col-12">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Room Name</label>
                                    <div>{{ $booking->room->name }}</div>
                                </div>
                            </div>
                        @endif
                        @if($booking->room->description)
                            <div class="col-12">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Room Description</label>
                                    <div class="text-muted">{{ $booking->room->description }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Booking Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Booking Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-4">
                                <label class="text-muted">Check-in Date</label>
                                <div class="h4 text-success">
                                    <i class="fas fa-sign-in-alt mr-1"></i>
                                    {{ $booking->check_in_date->format('l, F j, Y') }}
                                </div>
                                <small class="text-muted">{{ $booking->check_in_date->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-4">
                                <label class="text-muted">Check-out Date</label>
                                <div class="h4 text-warning">
                                    <i class="fas fa-sign-out-alt mr-1"></i>
                                    {{ $booking->check_out_date->format('l, F j, Y') }}
                                </div>
                                <small class="text-muted">{{ $booking->check_out_date->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="text-muted">Duration</label>
                                <div class="h5">{{ $booking->nights }} night(s)</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="text-muted">Booking Date</label>
                                <div>{{ $booking->created_at->format('M j, Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="text-muted">Source</label>
                                <div>
                                    <span class="badge badge-secondary">{{ $booking->source ?? 'Direct' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Timeline -->
                    <div class="timeline mt-4">
                        <h6 class="text-muted mb-3">Booking Timeline</h6>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Booking Created</h6>
                                <p class="mb-1 text-muted">{{ $booking->created_at->format('M j, Y H:i') }}</p>
                                <small class="text-muted">Initial booking made by {{ $booking->guest_name }}</small>
                            </div>
                        </div>
                        
                        @if($booking->confirmed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Booking Confirmed</h6>
                                    <p class="mb-1 text-muted">{{ $booking->confirmed_at->format('M j, Y H:i') }}</p>
                                    <small class="text-muted">Booking was confirmed and activated</small>
                                </div>
                            </div>
                        @endif
                        
                        @if($booking->checked_in_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Guest Checked In</h6>
                                    <p class="mb-1 text-muted">{{ $booking->checked_in_at->format('M j, Y H:i') }}</p>
                                    <small class="text-muted">Guest arrived and checked into the room</small>
                                </div>
                            </div>
                        @endif
                        
                        @if($booking->checked_out_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Guest Checked Out</h6>
                                    <p class="mb-1 text-muted">{{ $booking->checked_out_at->format('M j, Y H:i') }}</p>
                                    <small class="text-muted">Guest completed stay and checked out</small>
                                </div>
                            </div>
                        @endif
                        
                        @if($booking->cancelled_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Booking Cancelled</h6>
                                    <p class="mb-1 text-muted">{{ $booking->cancelled_at->format('M j, Y H:i') }}</p>
                                    <small class="text-muted">Booking was cancelled</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Pricing -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Payment Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="pricing-breakdown">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Room Rate (per night)</span>
                            <span>${{ number_format($booking->room_rate, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Number of Nights</span>
                            <span>{{ $booking->nights }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>${{ number_format($booking->room_rate * $booking->nights, 2) }}</span>
                        </div>
                        @if($booking->tax_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax</span>
                                <span>${{ number_format($booking->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->service_fee > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Service Fee</span>
                                <span>${{ number_format($booking->service_fee, 2) }}</span>
                            </div>
                        @endif
                        @if($booking->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount</span>
                                <span>-${{ number_format($booking->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <strong>Total Amount</strong>
                            <strong class="text-primary">${{ number_format($booking->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="payment-status mt-4">
                        <label class="text-muted">Payment Status</label>
                        <div class="mb-3">
                            <span class="badge badge-{{ $booking->payment_status_badge_class }} badge-lg">
                                {{ ucwords($booking->payment_status) }}
                            </span>
                        </div>
                        
                        @if($booking->payment_method)
                            <div class="mb-3">
                                <label class="text-muted">Payment Method</label>
                                <div>{{ $booking->payment_method }}</div>
                            </div>
                        @endif
                        
                        @if($booking->paid_amount > 0)
                            <div class="mb-3">
                                <label class="text-muted">Amount Paid</label>
                                <div class="text-success">
                                    <strong>${{ number_format($booking->paid_amount, 2) }}</strong>
                                </div>
                            </div>
                        @endif
                        
                        @if($booking->payment_status === 'partial')
                            <div class="mb-3">
                                <label class="text-muted">Balance Due</label>
                                <div class="text-danger">
                                    <strong>${{ number_format($booking->total_amount - $booking->paid_amount, 2) }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($booking->payment_status === 'pending' || $booking->payment_status === 'partial')
                        <div class="payment-actions mt-3">
                            <button class="btn btn-success btn-block" onclick="markAsPaid({{ $booking->id }})">
                                <i class="fas fa-check mr-1"></i>
                                Mark as Paid
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Additional Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="text-muted">Booking Reference</label>
                                <div class="font-weight-bold">{{ $booking->booking_reference }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="text-muted">Confirmation Code</label>
                                <div class="font-weight-bold">{{ $booking->confirmation_code ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="text-muted">Currency</label>
                                <div>{{ $booking->currency ?? 'USD' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="text-muted">Last Updated</label>
                                <div>{{ $booking->updated_at->format('M j, Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($booking->cancellation_policy)
                        <div class="mt-3">
                            <label class="text-muted">Cancellation Policy</label>
                            <div class="bg-light p-3 rounded">{{ $booking->cancellation_policy }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Confirmation Modals -->
    <div class="modal fade" id="bookingActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingActionTitle">Confirm Action</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="bookingActionContent">
                        <p>Are you sure you want to perform this action?</p>
                    </div>
                    <div class="form-group" id="actionNotesGroup" style="display: none;">
                        <label for="actionNotes">Notes (Optional)</label>
                        <textarea class="form-control" id="actionNotes" rows="3" placeholder="Add any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Payment as Received</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="paymentAmount">Payment Amount</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="paymentAmount" 
                                   value="{{ $booking->total_amount - $booking->paid_amount }}" 
                                   step="0.01" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="paymentMethod">Payment Method</label>
                        <select class="form-control" id="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paymentNotes">Notes (Optional)</label>
                        <textarea class="form-control" id="paymentNotes" rows="2" placeholder="Payment details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="processPayment()">Record Payment</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .info-group label {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .pricing-breakdown {
        font-size: 0.9rem;
    }
    
    .timeline {
        position: relative;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 1.5rem;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 1.5rem;
        width: 2px;
        height: calc(100% - 1rem);
        background-color: #dee2e6;
    }
    
    .timeline-item:last-child::before {
        display: none;
    }
    
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #dee2e6;
    }
    
    .timeline-content {
        margin-left: 0.5rem;
    }
    
    .timeline-content h6 {
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    
    .bg-warning-light {
        background-color: #fff3cd !important;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

// Booking action functions
function confirmBooking(bookingId) {
    showActionModal(
        'Confirm Booking',
        'Are you sure you want to confirm this booking? This will activate the booking and make the room unavailable for the selected dates.',
        'confirm',
        bookingId,
        false
    );
}

function cancelBooking(bookingId) {
    showActionModal(
        'Cancel Booking',
        'Are you sure you want to cancel this booking? This action cannot be undone and may affect revenue.',
        'cancel',
        bookingId,
        true
    );
}

function checkInBooking(bookingId) {
    showActionModal(
        'Check In Guest',
        'Confirm that the guest has arrived and is checking into the room. The room will be marked as occupied.',
        'check-in',
        bookingId,
        false
    );
}

function checkOutBooking(bookingId) {
    showActionModal(
        'Check Out Guest',
        'Confirm that the guest has checked out and the room is available for cleaning/next guest.',
        'check-out',
        bookingId,
        false
    );
}

// Show action confirmation modal
function showActionModal(title, content, action, bookingId, showNotes = false) {
    $('#bookingActionTitle').text(title);
    $('#bookingActionContent').html(`<p>${content}</p>`);
    
    if (showNotes) {
        $('#actionNotesGroup').show();
        $('#actionNotes').val('');
    } else {
        $('#actionNotesGroup').hide();
    }
    
    // Store action data
    $('#bookingActionModal').data({
        action: action,
        bookingId: bookingId
    });
    
    $('#bookingActionModal').modal('show');
}

// Handle action confirmation
$('#confirmActionBtn').on('click', function() {
    const modal = $('#bookingActionModal');
    const action = modal.data('action');
    const bookingId = modal.data('bookingId');
    const notes = $('#actionNotes').val();
    
    // Show loading state
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    
    // Make AJAX request
    $.post(`{{ url('b2b/hotel-provider/bookings') }}/${bookingId}/${action}`, {
        _token: '{{ csrf_token() }}',
        notes: notes
    })
    .done(function(response) {
        if (response.success) {
            modal.modal('hide');
            toastr.success(response.message || 'Action completed successfully');
            // Reload page to show updated status
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else {
            toastr.error(response.message || 'Failed to perform action');
        }
    })
    .fail(function() {
        toastr.error('Network error. Please try again.');
    })
    .always(function() {
        $('#confirmActionBtn').prop('disabled', false).text('Confirm');
    });
});

// Payment functions
function markAsPaid(bookingId) {
    $('#paymentModal').modal('show');
}

function processPayment() {
    const amount = parseFloat($('#paymentAmount').val());
    const method = $('#paymentMethod').val();
    const notes = $('#paymentNotes').val();
    
    if (!amount || amount <= 0) {
        toastr.error('Please enter a valid payment amount');
        return;
    }
    
    // Show loading state
    const btn = event.target;
    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    
    $.post('{{ route("b2b.hotel-provider.bookings.payment", $booking->id) }}', {
        _token: '{{ csrf_token() }}',
        amount: amount,
        method: method,
        notes: notes
    })
    .done(function(response) {
        if (response.success) {
            $('#paymentModal').modal('hide');
            toastr.success('Payment recorded successfully');
            // Reload page to show updated payment status
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else {
            toastr.error(response.message || 'Failed to record payment');
        }
    })
    .fail(function() {
        toastr.error('Network error. Please try again.');
    })
    .always(function() {
        $(btn).prop('disabled', false).text('Record Payment');
    });
}

// Other functions
function printBooking(bookingId) {
    window.open(`{{ url('b2b/hotel-provider/bookings') }}/${bookingId}/print`, '_blank');
}

function emailBooking(bookingId) {
    toastr.info('Email functionality coming soon');
}

function viewBookingHistory(bookingId) {
    toastr.info('Booking history functionality coming soon');
}
</script>
@stop
