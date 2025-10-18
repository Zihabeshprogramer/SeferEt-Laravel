@extends('layouts.b2b')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Booking Details - {{ $booking->booking_reference }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('b2b.travel-agent.bookings') }}">Bookings</a></li>
                        <li class="breadcrumb-item active">{{ $booking->booking_reference }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Booking Overview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Booking Information</h3>
                    <div class="card-tools">
                        <span class="badge {{ $booking->status_badge_class }} badge-lg">
                            {{ \App\Models\PackageBooking::STATUSES[$booking->status] }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Package Details</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Package Name:</strong></td>
                                    <td>
                                        <a href="{{ route('b2b.travel-agent.packages.show', $booking->package->id) }}">
                                            {{ $booking->package->name }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td>{{ $booking->duration_days }} days</td>
                                </tr>
                                <tr>
                                    <td><strong>Departure Date:</strong></td>
                                    <td>
                                        {{ $booking->departure_date->format('F j, Y') }}
                                        @if($booking->days_until_departure >= 0)
                                            <br><small class="text-info">{{ $booking->days_until_departure }} days to go</small>
                                        @else
                                            <br><small class="text-muted">{{ abs($booking->days_until_departure) }} days ago</small>
                                        @endif
                                    </td>
                                </tr>
                                @if($booking->return_date)
                                    <tr>
                                        <td><strong>Return Date:</strong></td>
                                        <td>{{ $booking->return_date->format('F j, Y') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>Booking Source:</strong></td>
                                    <td>{{ ucfirst($booking->booking_source) }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Customer Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Primary Contact:</strong></td>
                                    <td>{{ $booking->primary_contact_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $booking->primary_contact_email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $booking->primary_contact_phone }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Participants:</strong></td>
                                    <td>
                                        <span class="badge badge-light">{{ $booking->total_participants }} Total</span>
                                        <br>
                                        Adults: {{ $booking->adults }}
                                        @if($booking->children > 0) | Children: {{ $booking->children }} @endif
                                        @if($booking->infants > 0) | Infants: {{ $booking->infants }} @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Participant Details -->
                    @if($booking->participant_details)
                        <div class="mt-4">
                            <h5>Participant Details</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Date of Birth</th>
                                            <th>Passport/ID</th>
                                            <th>Special Requirements</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($booking->participant_details as $index => $participant)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $participant['name'] ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($participant['type'] ?? 'adult') }}</td>
                                                <td>{{ $participant['date_of_birth'] ?? 'N/A' }}</td>
                                                <td>{{ $participant['passport_number'] ?? $participant['id_number'] ?? 'N/A' }}</td>
                                                <td>{{ $participant['special_requirements'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Special Requirements -->
                    @if($booking->special_requirements || $booking->dietary_requirements)
                        <div class="mt-4">
                            <h5>Special Requirements</h5>
                            @if($booking->special_requirements)
                                <p><strong>General Requirements:</strong><br>{{ $booking->special_requirements }}</p>
                            @endif
                            @if($booking->dietary_requirements)
                                <p><strong>Dietary Requirements:</strong><br>{{ $booking->dietary_requirements }}</p>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Emergency Contact -->
                    @if($booking->emergency_contact_name)
                        <div class="mt-4">
                            <h5>Emergency Contact</h5>
                            <p>
                                <strong>Name:</strong> {{ $booking->emergency_contact_name }}<br>
                                <strong>Phone:</strong> {{ $booking->emergency_contact_phone }}<br>
                                <strong>Relationship:</strong> {{ $booking->emergency_contact_relationship }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Communication Log -->
            @if($booking->communication_log)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Communication History</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach(collect($booking->communication_log)->sortByDesc('timestamp') as $log)
                                <div class="time-label">
                                    <span class="bg-info">{{ \Carbon\Carbon::parse($log['timestamp'])->format('M j, Y H:i') }}</span>
                                </div>
                                <div>
                                    <i class="fas fa-comment bg-{{ $log['type'] === 'payment' ? 'success' : ($log['type'] === 'cancellation' ? 'danger' : 'primary') }}"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">
                                            {{ $log['user_name'] ?? 'System' }} 
                                            <span class="badge badge-{{ $log['type'] === 'payment' ? 'success' : ($log['type'] === 'cancellation' ? 'danger' : 'info') }}">
                                                {{ ucfirst($log['type']) }}
                                            </span>
                                        </h3>
                                        <div class="timeline-body">
                                            {{ $log['message'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Right Column - Actions & Payment -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        @if($booking->canBeConfirmed())
                            <button class="btn btn-success confirm-booking" data-id="{{ $booking->id }}">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                        @endif
                        
                        @if($booking->canBeCancelled())
                            <button class="btn btn-danger cancel-booking" data-id="{{ $booking->id }}">
                                <i class="fas fa-times"></i> Cancel Booking
                            </button>
                        @endif
                        
                        @if($booking->requiresPayment())
                            <button class="btn btn-warning update-payment" data-id="{{ $booking->id }}">
                                <i class="fas fa-credit-card"></i> Update Payment
                            </button>
                        @endif
                        
                        <button class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Details
                        </button>
                        
                        <button class="btn btn-secondary" onclick="sendEmail()">
                            <i class="fas fa-envelope"></i> Email Customer
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Information</h3>
                    <div class="card-tools">
                        <span class="badge {{ $booking->payment_status_badge_class }}">
                            {{ \App\Models\PackageBooking::PAYMENT_STATUSES[$booking->payment_status] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Package Price:</strong></td>
                            <td>{{ $booking->currency }} {{ number_format($booking->package_price, 2) }}</td>
                        </tr>
                        @if($booking->addon_price > 0)
                            <tr>
                                <td><strong>Add-ons:</strong></td>
                                <td>{{ $booking->currency }} {{ number_format($booking->addon_price, 2) }}</td>
                            </tr>
                        @endif
                        @if($booking->discount_amount > 0)
                            <tr>
                                <td><strong>Discount:</strong></td>
                                <td class="text-success">-{{ $booking->currency }} {{ number_format($booking->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if($booking->tax_amount > 0)
                            <tr>
                                <td><strong>Tax:</strong></td>
                                <td>{{ $booking->currency }} {{ number_format($booking->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if($booking->service_fee > 0)
                            <tr>
                                <td><strong>Service Fee:</strong></td>
                                <td>{{ $booking->currency }} {{ number_format($booking->service_fee, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="border-top">
                            <td><strong>Total Amount:</strong></td>
                            <td><strong>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr class="text-success">
                            <td><strong>Paid Amount:</strong></td>
                            <td><strong>{{ $booking->currency }} {{ number_format($booking->paid_amount, 2) }}</strong></td>
                        </tr>
                        @if($booking->pending_amount > 0)
                            <tr class="text-danger">
                                <td><strong>Pending:</strong></td>
                                <td><strong>{{ $booking->currency }} {{ number_format($booking->pending_amount, 2) }}</strong></td>
                            </tr>
                        @endif
                        @if($booking->payment_method)
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td>{{ \App\Models\PackageBooking::PAYMENT_METHODS[$booking->payment_method] }}</td>
                            </tr>
                        @endif
                        @if($booking->payment_due_date)
                            <tr>
                                <td><strong>Payment Due:</strong></td>
                                <td>
                                    {{ $booking->payment_due_date->format('M j, Y') }}
                                    @if($booking->payment_due_date->isPast() && $booking->payment_status !== 'paid')
                                        <br><small class="text-danger">OVERDUE</small>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                    
                    <!-- Payment Breakdown -->
                    @if($booking->pricing_breakdown)
                        <div class="mt-3">
                            <h6>Pricing Breakdown:</h6>
                            <small>
                                @foreach($booking->pricing_breakdown as $item => $amount)
                                    {{ ucfirst(str_replace('_', ' ', $item)) }}: {{ $booking->currency }} {{ number_format($amount, 2) }}<br>
                                @endforeach
                            </small>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Booking Timeline -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Booking Timeline</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-calendar-plus text-info"></i> 
                            <strong>Created:</strong> {{ $booking->created_at->format('M j, Y H:i') }}
                        </li>
                        
                        @if($booking->confirmed_at)
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i> 
                                <strong>Confirmed:</strong> {{ $booking->confirmed_at->format('M j, Y H:i') }}
                            </li>
                        @endif
                        
                        @if($booking->cancelled_at)
                            <li class="mb-2">
                                <i class="fas fa-times-circle text-danger"></i> 
                                <strong>Cancelled:</strong> {{ $booking->cancelled_at->format('M j, Y H:i') }}
                            </li>
                        @endif
                        
                        @if($booking->completed_at)
                            <li class="mb-2">
                                <i class="fas fa-flag-checkered text-primary"></i> 
                                <strong>Completed:</strong> {{ $booking->completed_at->format('M j, Y H:i') }}
                            </li>
                        @endif
                        
                        @if($booking->departure_reminder_sent_at)
                            <li class="mb-2">
                                <i class="fas fa-bell text-warning"></i> 
                                <strong>Reminder Sent:</strong> {{ $booking->departure_reminder_sent_at->format('M j, Y H:i') }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            
            <!-- Notes -->
            @if($booking->customer_notes || $booking->internal_notes)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Notes</h3>
                    </div>
                    <div class="card-body">
                        @if($booking->customer_notes)
                            <div class="mb-3">
                                <strong>Customer Notes:</strong>
                                <p class="mt-1">{{ $booking->customer_notes }}</p>
                            </div>
                        @endif
                        
                        @if($booking->internal_notes)
                            <div>
                                <strong>Internal Notes:</strong>
                                <p class="mt-1">{{ $booking->internal_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Include modals from index page -->
    @include('b2b.travel-agent.bookings.modals')
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Reuse modal functionality from index page
    let currentBookingId = {{ $booking->id }};

    // Confirm booking modal
    $('.confirm-booking').on('click', function() {
        $('#confirmBookingModal').modal('show');
    });

    // Cancel booking modal
    $('.cancel-booking').on('click', function() {
        $('#cancelBookingModal').modal('show');
    });

    // Update payment modal
    $('.update-payment').on('click', function() {
        // Pre-fill current values
        $('#paid_amount').val({{ $booking->paid_amount }});
        $('#payment_status').val('{{ $booking->payment_status }}');
        @if($booking->payment_method)
            $('#payment_method').val('{{ $booking->payment_method }}');
        @endif
        
        $('#updatePaymentModal').modal('show');
    });

    // Form submissions (same as index page)
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

    $('#cancelBookingForm').on('submit', function(e) {
        e.preventDefault();
        
        // Set max refund amount
        $('#refund_amount').attr('max', {{ $booking->paid_amount }});
        
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

    $('#updatePaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Set max paid amount
        $('#paid_amount').attr('max', {{ $booking->total_amount }});
        
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
});

function sendEmail() {
    // TODO: Implement email functionality
    toastr.info('Email functionality will be implemented soon.');
}
</script>
@endpush
