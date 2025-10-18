@extends('layouts.b2b')

@section('title', 'Hotel Bookings Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-calendar-check text-info mr-2"></i>
                Bookings Management
            </h1>
            <p class="text-muted">Manage all bookings across your hotels</p>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-outline-info" id="refreshBookings">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </button>
            <div class="dropdown d-inline">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-download mr-1"></i>
                    Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="exportBookings('csv')">
                        <i class="fas fa-file-csv mr-1"></i> Export CSV
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportBookings('pdf')">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Booking Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Bookings</span>
                    <span class="info-box-number" id="totalBookings">{{ $stats['total_bookings'] ?? 0 }}</span>
                    <span class="progress-description">All time bookings</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Confirmed</span>
                    <span class="info-box-number" id="confirmedBookings">{{ $stats['confirmed_bookings'] ?? 0 }}</span>
                    <span class="progress-description">Active bookings</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number" id="pendingBookings">{{ $stats['pending_bookings'] ?? 0 }}</span>
                    <span class="progress-description">Needs action</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Revenue</span>
                    <span class="info-box-number" id="totalRevenue">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</span>
                    <span class="progress-description">This month</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter mr-2"></i>
                Filters & Search
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-secondary" id="resetFilters">
                    <i class="fas fa-undo mr-1"></i>
                    Reset Filters
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterStatus">Status</label>
                        <select class="form-control" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterHotel">Hotel</label>
                        <select class="form-control" id="filterHotel">
                            <option value="">All Hotels</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterDateRange">Date Range</label>
                        <input type="text" class="form-control" id="filterDateRange" placeholder="Select date range">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="searchBookings">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchBookings" placeholder="Booking ref, guest name...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card shadow">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Recent Bookings
            </h3>
            <div class="card-tools">
                <span class="badge badge-info" id="bookingsCount">{{ $bookings->total() ?? 0 }} bookings</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest Details</th>
                            <th>Hotel & Room</th>
                            <th>Dates</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
                        @forelse($bookings as $booking)
                            <tr data-booking-id="{{ $booking->id }}" class="booking-row">
                                <td>
                                    <strong class="text-primary">{{ $booking->booking_reference }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $booking->created_at->format('d M Y, H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ml-2">
                                            <strong>{{ $booking->guest_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope mr-1"></i>{{ $booking->guest_email }}
                                            </small>
                                            @if($booking->guest_phone)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone mr-1"></i>{{ $booking->guest_phone }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $booking->hotel->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Room: {{ $booking->room->room_number }}
                                        @if($booking->room->name)
                                            ({{ $booking->room->name }})
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <div>
                                        <strong>Check-in:</strong> {{ $booking->check_in_date->format('M d, Y') }}
                                        <br>
                                        <strong>Check-out:</strong> {{ $booking->check_out_date->format('M d, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $booking->nights }} night(s)</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ $booking->guest_info }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $booking->status_badge_class }}">
                                        {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $booking->payment_status_badge_class }}">
                                        {{ ucwords($booking->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>${{ number_format($booking->total_amount, 2) }}</strong>
                                    <br>
                                    <small class="text-muted">${{ number_format($booking->room_rate, 2) }}/night</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick="viewBooking({{ $booking->id }})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        @if($booking->canBeConfirmed())
                                            <button class="btn btn-outline-success" onclick="confirmBooking({{ $booking->id }})" title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        @if($booking->canCheckIn())
                                            <button class="btn btn-outline-primary" onclick="checkInBooking({{ $booking->id }})" title="Check In">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </button>
                                        @endif
                                        
                                        @if($booking->canCheckOut())
                                            <button class="btn btn-outline-warning" onclick="checkOutBooking({{ $booking->id }})" title="Check Out">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </button>
                                        @endif
                                        
                                        @if($booking->canBeCancelled())
                                            <button class="btn btn-outline-danger" onclick="cancelBooking({{ $booking->id }})" title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" title="More Actions">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#" onclick="printBooking({{ $booking->id }})">
                                                    <i class="fas fa-print mr-2"></i>Print
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="emailBooking({{ $booking->id }})">
                                                    <i class="fas fa-envelope mr-2"></i>Email Guest
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="viewBookingHistory({{ $booking->id }})">
                                                    <i class="fas fa-history mr-2"></i>View History
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <h5>No Bookings Found</h5>
                                        <p>No bookings match your current filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($bookings->hasPages())
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results
                        </small>
                    </div>
                    <div class="col-md-6 text-right">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Booking Detail Modal -->
    <div class="modal fade" id="bookingDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Booking Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="bookingDetailContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading booking details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Action Confirmation Modal -->
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
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .booking-row:hover {
        background-color: #f8f9fa;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#filterDateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
    });

    $('#filterDateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        filterBookings();
    });

    $('#filterDateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        filterBookings();
    });

    // Filter event listeners
    $('#filterStatus, #filterHotel').on('change', filterBookings);
    
    // Search functionality
    let searchTimeout;
    $('#searchBookings').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterBookings, 500);
    });

    $('#clearSearch').on('click', function() {
        $('#searchBookings').val('');
        filterBookings();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterStatus').val('');
        $('#filterHotel').val('');
        $('#filterDateRange').val('');
        $('#searchBookings').val('');
        filterBookings();
    });

    // Refresh bookings
    $('#refreshBookings').on('click', function() {
        location.reload();
    });
});

// Filter bookings function
function filterBookings() {
    const filters = {
        status: $('#filterStatus').val(),
        hotel: $('#filterHotel').val(),
        dateRange: $('#filterDateRange').val(),
        search: $('#searchBookings').val()
    };

    // Show loading state
    $('#bookingsTableBody').html(`
        <tr>
            <td colspan="9" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading bookings...</p>
            </td>
        </tr>
    `);

    // Make AJAX request
    $.get('{{ route("b2b.hotel-provider.bookings.data") }}', filters)
        .done(function(response) {
            if (response.success) {
                updateBookingsTable(response.data);
                updateBookingsStats(response.stats);
            } else {
                showErrorInTable('Failed to load bookings: ' + response.message);
            }
        })
        .fail(function() {
            showErrorInTable('Network error. Please try again.');
        });
}

// Update bookings table
function updateBookingsTable(bookings) {
    let html = '';
    
    if (bookings.length === 0) {
        html = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <h5>No Bookings Found</h5>
                        <p>No bookings match your current filters.</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        bookings.forEach(function(booking) {
            html += generateBookingRow(booking);
        });
    }
    
    $('#bookingsTableBody').html(html);
    $('#bookingsCount').text(bookings.length + ' bookings');
}

// Generate booking row HTML
function generateBookingRow(booking) {
    return `
        <tr data-booking-id="${booking.id}" class="booking-row">
            <td>
                <strong class="text-primary">${booking.booking_reference}</strong>
                <br>
                <small class="text-muted">${formatDateTime(booking.created_at)}</small>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="ml-2">
                        <strong>${booking.guest_name}</strong>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-envelope mr-1"></i>${booking.guest_email}
                        </small>
                        ${booking.guest_phone ? `
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-phone mr-1"></i>${booking.guest_phone}
                        </small>
                        ` : ''}
                    </div>
                </div>
            </td>
            <td>
                <strong>${booking.hotel_name}</strong>
                <br>
                <small class="text-muted">Room: ${booking.room_number}${booking.room_name ? ` (${booking.room_name})` : ''}</small>
            </td>
            <td>
                <div>
                    <strong>Check-in:</strong> ${formatDate(booking.check_in_date)}
                    <br>
                    <strong>Check-out:</strong> ${formatDate(booking.check_out_date)}
                    <br>
                    <small class="text-muted">${booking.nights} night(s)</small>
                </div>
            </td>
            <td class="text-center">
                <span class="badge badge-light">
                    <i class="fas fa-users mr-1"></i>
                    ${booking.guest_info}
                </span>
            </td>
            <td>
                <span class="badge badge-${booking.status_badge_class}">
                    ${formatStatus(booking.status)}
                </span>
            </td>
            <td>
                <span class="badge badge-${booking.payment_status_badge_class}">
                    ${formatStatus(booking.payment_status)}
                </span>
            </td>
            <td>
                <strong>$${formatMoney(booking.total_amount)}</strong>
                <br>
                <small class="text-muted">$${formatMoney(booking.room_rate)}/night</small>
            </td>
            <td>
                ${generateActionButtons(booking)}
            </td>
        </tr>
    `;
}

// Generate action buttons based on booking status
function generateActionButtons(booking) {
    let buttons = `
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-info" onclick="viewBooking(${booking.id})" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
    `;
    
    if (booking.can_be_confirmed) {
        buttons += `
            <button class="btn btn-outline-success" onclick="confirmBooking(${booking.id})" title="Confirm">
                <i class="fas fa-check"></i>
            </button>
        `;
    }
    
    if (booking.can_check_in) {
        buttons += `
            <button class="btn btn-outline-primary" onclick="checkInBooking(${booking.id})" title="Check In">
                <i class="fas fa-sign-in-alt"></i>
            </button>
        `;
    }
    
    if (booking.can_check_out) {
        buttons += `
            <button class="btn btn-outline-warning" onclick="checkOutBooking(${booking.id})" title="Check Out">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        `;
    }
    
    if (booking.can_be_cancelled) {
        buttons += `
            <button class="btn btn-outline-danger" onclick="cancelBooking(${booking.id})" title="Cancel">
                <i class="fas fa-times"></i>
            </button>
        `;
    }
    
    buttons += `
            <div class="btn-group" role="group">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" title="More Actions">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="printBooking(${booking.id})">
                        <i class="fas fa-print mr-2"></i>Print
                    </a>
                    <a class="dropdown-item" href="#" onclick="emailBooking(${booking.id})">
                        <i class="fas fa-envelope mr-2"></i>Email Guest
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="viewBookingHistory(${booking.id})">
                        <i class="fas fa-history mr-2"></i>View History
                    </a>
                </div>
            </div>
        </div>
    `;
    
    return buttons;
}

// Update booking statistics
function updateBookingsStats(stats) {
    $('#totalBookings').text(stats.total_bookings || 0);
    $('#confirmedBookings').text(stats.confirmed_bookings || 0);
    $('#pendingBookings').text(stats.pending_bookings || 0);
    $('#totalRevenue').text('$' + formatMoney(stats.total_revenue || 0));
}

// Show error in table
function showErrorInTable(message) {
    $('#bookingsTableBody').html(`
        <tr>
            <td colspan="9" class="text-center py-4 text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p>${message}</p>
                <button class="btn btn-outline-primary btn-sm" onclick="filterBookings()">Try Again</button>
            </td>
        </tr>
    `);
}

// Booking action functions
function viewBooking(bookingId) {
    $('#bookingDetailModal').modal('show');
    
    $.get(`{{ url('b2b/hotel-provider/bookings') }}/${bookingId}`)
        .done(function(response) {
            if (response.success) {
                $('#bookingDetailContent').html(response.html);
            } else {
                $('#bookingDetailContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Failed to load booking details: ${response.message}
                    </div>
                `);
            }
        })
        .fail(function() {
            $('#bookingDetailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Network error. Please try again.
                </div>
            `);
        });
}

function confirmBooking(bookingId) {
    showActionModal(
        'Confirm Booking',
        'Are you sure you want to confirm this booking?',
        'confirm',
        bookingId,
        false
    );
}

function cancelBooking(bookingId) {
    showActionModal(
        'Cancel Booking',
        'Are you sure you want to cancel this booking? This action cannot be undone.',
        'cancel',
        bookingId,
        true
    );
}

function checkInBooking(bookingId) {
    showActionModal(
        'Check In Guest',
        'Confirm that the guest has arrived and is checking in.',
        'check-in',
        bookingId,
        false
    );
}

function checkOutBooking(bookingId) {
    showActionModal(
        'Check Out Guest',
        'Confirm that the guest has checked out and the room is available.',
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
            filterBookings(); // Refresh the table
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

// Export functions
function exportBookings(format) {
    const filters = {
        status: $('#filterStatus').val(),
        hotel: $('#filterHotel').val(),
        dateRange: $('#filterDateRange').val(),
        search: $('#searchBookings').val(),
        format: format
    };
    
    const params = new URLSearchParams(filters).toString();
    window.open(`{{ route('b2b.hotel-provider.bookings.export') }}?${params}`, '_blank');
}

function printBooking(bookingId) {
    window.open(`{{ url('b2b/hotel-provider/bookings') }}/${bookingId}/print`, '_blank');
}

function emailBooking(bookingId) {
    // Implementation for sending booking confirmation email
    toastr.info('Email functionality coming soon');
}

function viewBookingHistory(bookingId) {
    // Implementation for viewing booking history
    toastr.info('Booking history functionality coming soon');
}

// Utility functions
function formatDateTime(dateTime) {
    return moment(dateTime).format('MMM DD, YYYY HH:mm');
}

function formatDate(date) {
    return moment(date).format('MMM DD, YYYY');
}

function formatStatus(status) {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatMoney(amount) {
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Initialize tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop
