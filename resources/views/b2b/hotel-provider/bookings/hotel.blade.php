@extends('layouts.b2b')

@section('title', 'Bookings for ' . $hotel->name)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-hotel text-info mr-2"></i>
                {{ $hotel->name }} - Bookings
            </h1>
            <p class="text-muted">
                <i class="fas fa-map-marker-alt mr-1"></i>
                {{ $hotel->address }}, {{ $hotel->city }}
                <span class="mx-2">|</span>
                <i class="fas fa-bed mr-1"></i>
                {{ $hotel->rooms()->count() }} rooms
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.hotel-provider.bookings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                All Bookings
            </a>
            <button class="btn btn-outline-info" id="refreshBookings">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </button>
            <div class="dropdown d-inline">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Quick Actions
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('b2b.hotel-provider.bookings.calendar', $hotel->id) }}">
                        <i class="fas fa-calendar mr-2"></i>View Calendar
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportHotelBookings('csv')">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('b2b.hotel-provider.hotels.show', $hotel->id) }}">
                        <i class="fas fa-cog mr-2"></i>Hotel Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Hotel Booking Statistics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total</span>
                    <span class="info-box-number">{{ $stats['total_bookings'] ?? 0 }}</span>
                    <span class="progress-description">All bookings</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Confirmed</span>
                    <span class="info-box-number">{{ $stats['confirmed'] ?? 0 }}</span>
                    <span class="progress-description">Active</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $stats['pending'] ?? 0 }}</span>
                    <span class="progress-description">Need action</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Checked In</span>
                    <span class="info-box-number">{{ $stats['checked_in'] ?? 0 }}</span>
                    <span class="progress-description">Current guests</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4">
            <div class="info-box bg-gradient-secondary">
                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Occupancy</span>
                    <span class="info-box-number">{{ round($stats['occupancy_rate'] ?? 0, 1) }}%</span>
                    <span class="progress-description">Current rate</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4">
            <div class="info-box bg-gradient-dark">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Revenue</span>
                    <span class="info-box-number" style="font-size: 0.9rem;">${{ number_format($stats['revenue'] ?? 0, 0) }}</span>
                    <span class="progress-description">This month</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <select class="form-control form-control-sm" id="filterStatus">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" id="filterRoom">
                        <option value="">All Rooms</option>
                        @foreach($hotel->rooms as $room)
                            <option value="{{ $room->id }}">Room {{ $room->room_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" id="filterDateRange" placeholder="Select date range">
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="searchBookings" placeholder="Search bookings...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 text-right">
                    <button class="btn btn-secondary btn-sm" id="resetFilters">
                        <i class="fas fa-undo mr-1"></i>
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Status Overview -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-th-large mr-2"></i>
                Room Status Overview
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleRoomView()">
                    <i class="fas fa-expand-arrows-alt"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row" id="roomStatusGrid">
                @foreach($hotel->rooms as $room)
                    <div class="col-md-2 col-sm-3 col-4 mb-3">
                        <div class="room-status-card {{ $room->current_status_class }}" data-room-id="{{ $room->id }}">
                            <div class="room-number">{{ $room->room_number }}</div>
                            <div class="room-type">{{ $room->room_type }}</div>
                            <div class="room-status">{{ $room->current_status }}</div>
                            @if($room->current_booking)
                                <div class="guest-name">{{ $room->current_booking->guest_name }}</div>
                                <div class="checkout-date">Out: {{ $room->current_booking->check_out_date->format('M d') }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bookings Timeline -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Today's Check-ins
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $todayCheckins->count() }}</span>
                    </div>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @forelse($todayCheckins as $booking)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>{{ $booking->guest_name }}</strong>
                                <br>
                                <small class="text-muted">Room {{ $booking->room->room_number }}</small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $booking->status_badge_class }}">
                                    {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                </span>
                                @if($booking->canCheckIn())
                                    <br>
                                    <button class="btn btn-sm btn-primary mt-1" onclick="checkInBooking({{ $booking->id }})">
                                        Check In
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No check-ins scheduled for today</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Today's Check-outs
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">{{ $todayCheckouts->count() }}</span>
                    </div>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @forelse($todayCheckouts as $booking)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>{{ $booking->guest_name }}</strong>
                                <br>
                                <small class="text-muted">Room {{ $booking->room->room_number }}</small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $booking->status_badge_class }}">
                                    {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                                </span>
                                @if($booking->canCheckOut())
                                    <br>
                                    <button class="btn btn-sm btn-warning mt-1" onclick="checkOutBooking({{ $booking->id }})">
                                        Check Out
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No check-outs scheduled for today</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card shadow">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Hotel Bookings
            </h3>
            <div class="card-tools">
                <span class="badge badge-info" id="bookingsCount">{{ $bookings->total() ?? 0 }} bookings</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="hotelBookingsTable">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Dates</th>
                            <th>Nights</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="hotelBookingsTableBody">
                        @forelse($bookings as $booking)
                            <tr data-booking-id="{{ $booking->id }}" class="booking-row">
                                <td>
                                    <strong class="text-primary">{{ $booking->booking_reference }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $booking->created_at->format('M d, H:i') }}</small>
                                </td>
                                <td>
                                    <div>
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
                                </td>
                                <td class="text-center">
                                    <div class="room-info">
                                        <strong>{{ $booking->room->room_number }}</strong>
                                        @if($booking->room->name)
                                            <br>
                                            <small class="text-muted">{{ $booking->room->name }}</small>
                                        @endif
                                        <br>
                                        <span class="badge badge-light">{{ $booking->room->room_type }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <strong>In:</strong> {{ $booking->check_in_date->format('M d, Y') }}
                                        <br>
                                        <strong>Out:</strong> {{ $booking->check_out_date->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $booking->nights }}</span>
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
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="printBooking({{ $booking->id }})">
                                                    <i class="fas fa-print mr-2"></i>Print Voucher
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="emailBooking({{ $booking->id }})">
                                                    <i class="fas fa-envelope mr-2"></i>Email Guest
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="viewBookingHistory({{ $booking->id }})">
                                                    <i class="fas fa-history mr-2"></i>View History
                                                </a>
                                                @if($booking->status === 'checked_in')
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="#" onclick="addBookingNote({{ $booking->id }})">
                                                        <i class="fas fa-sticky-note mr-2"></i>Add Note
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <h5>No Bookings Found</h5>
                                        <p>This hotel has no bookings matching your current filters.</p>
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
    
    .table-sm td {
        vertical-align: middle;
        padding: 0.5rem 0.25rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .room-status-card {
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .room-status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .room-status-card.available {
        background-color: #d4edda;
        border-color: #28a745;
    }
    
    .room-status-card.occupied {
        background-color: #f8d7da;
        border-color: #dc3545;
    }
    
    .room-status-card.maintenance {
        background-color: #fff3cd;
        border-color: #ffc107;
    }
    
    .room-status-card.cleaning {
        background-color: #cce5ff;
        border-color: #007bff;
    }
    
    .room-number {
        font-weight: bold;
        font-size: 1.1em;
    }
    
    .room-type {
        font-size: 0.8em;
        color: #6c757d;
    }
    
    .room-status {
        font-size: 0.75em;
        margin-top: 5px;
        font-weight: 500;
    }
    
    .guest-name {
        font-size: 0.7em;
        margin-top: 5px;
        font-weight: 500;
    }
    
    .checkout-date {
        font-size: 0.65em;
        color: #6c757d;
    }
    
    .date-info, .room-info {
        font-size: 0.85em;
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
        filterHotelBookings();
    });

    $('#filterDateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        filterHotelBookings();
    });

    // Filter event listeners
    $('#filterStatus, #filterRoom').on('change', filterHotelBookings);
    
    // Search functionality
    let searchTimeout;
    $('#searchBookings').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterHotelBookings, 500);
    });

    $('#clearSearch').on('click', function() {
        $('#searchBookings').val('');
        filterHotelBookings();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterStatus').val('');
        $('#filterRoom').val('');
        $('#filterDateRange').val('');
        $('#searchBookings').val('');
        filterHotelBookings();
    });

    // Refresh bookings
    $('#refreshBookings').on('click', function() {
        location.reload();
    });

    // Room status card click
    $('.room-status-card').on('click', function() {
        const roomId = $(this).data('room-id');
        filterByRoom(roomId);
    });
});

// Filter hotel bookings function
function filterHotelBookings() {
    const filters = {
        status: $('#filterStatus').val(),
        room: $('#filterRoom').val(),
        dateRange: $('#filterDateRange').val(),
        search: $('#searchBookings').val(),
        hotel_id: {{ $hotel->id }}
    };

    // Show loading state
    $('#hotelBookingsTableBody').html(`
        <tr>
            <td colspan="10" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading bookings...</p>
            </td>
        </tr>
    `);

    // Make AJAX request
    $.get('{{ route("b2b.hotel-provider.bookings.hotel.data", $hotel->id) }}', filters)
        .done(function(response) {
            if (response.success) {
                updateHotelBookingsTable(response.data);
                updateHotelStats(response.stats);
            } else {
                showErrorInTable('Failed to load bookings: ' + response.message);
            }
        })
        .fail(function() {
            showErrorInTable('Network error. Please try again.');
        });
}

// Filter by specific room
function filterByRoom(roomId) {
    $('#filterRoom').val(roomId);
    filterHotelBookings();
}

// Update hotel bookings table
function updateHotelBookingsTable(bookings) {
    let html = '';
    
    if (bookings.length === 0) {
        html = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <h5>No Bookings Found</h5>
                        <p>This hotel has no bookings matching your current filters.</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        bookings.forEach(function(booking) {
            html += generateHotelBookingRow(booking);
        });
    }
    
    $('#hotelBookingsTableBody').html(html);
    $('#bookingsCount').text(bookings.length + ' bookings');
}

// Generate hotel booking row HTML
function generateHotelBookingRow(booking) {
    return `
        <tr data-booking-id="${booking.id}" class="booking-row">
            <td>
                <strong class="text-primary">${booking.booking_reference}</strong>
                <br>
                <small class="text-muted">${formatDateTime(booking.created_at)}</small>
            </td>
            <td>
                <div>
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
            </td>
            <td class="text-center">
                <div class="room-info">
                    <strong>${booking.room_number}</strong>
                    ${booking.room_name ? `<br><small class="text-muted">${booking.room_name}</small>` : ''}
                    <br>
                    <span class="badge badge-light">${booking.room_type}</span>
                </div>
            </td>
            <td>
                <div class="date-info">
                    <strong>In:</strong> ${formatDate(booking.check_in_date)}
                    <br>
                    <strong>Out:</strong> ${formatDate(booking.check_out_date)}
                </div>
            </td>
            <td class="text-center">
                <span class="badge badge-secondary">${booking.nights}</span>
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
                ${generateHotelActionButtons(booking)}
            </td>
        </tr>
    `;
}

// Generate action buttons for hotel bookings
function generateHotelActionButtons(booking) {
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
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="printBooking(${booking.id})">
                        <i class="fas fa-print mr-2"></i>Print Voucher
                    </a>
                    <a class="dropdown-item" href="#" onclick="emailBooking(${booking.id})">
                        <i class="fas fa-envelope mr-2"></i>Email Guest
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="viewBookingHistory(${booking.id})">
                        <i class="fas fa-history mr-2"></i>View History
                    </a>
                    ${booking.status === 'checked_in' ? `
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="addBookingNote(${booking.id})">
                        <i class="fas fa-sticky-note mr-2"></i>Add Note
                    </a>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    return buttons;
}

// Update hotel statistics
function updateHotelStats(stats) {
    // Update the statistics cards with new data
    $('.info-box-number').each(function(index) {
        const statKeys = ['total_bookings', 'confirmed', 'pending', 'checked_in', 'occupancy_rate', 'revenue'];
        const value = stats[statKeys[index]];
        if (statKeys[index] === 'occupancy_rate') {
            $(this).text(Math.round(value || 0) + '%');
        } else if (statKeys[index] === 'revenue') {
            $(this).text('$' + formatMoney(value || 0));
        } else {
            $(this).text(value || 0);
        }
    });
}

// Show error in table
function showErrorInTable(message) {
    $('#hotelBookingsTableBody').html(`
        <tr>
            <td colspan="10" class="text-center py-4 text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p>${message}</p>
                <button class="btn btn-outline-primary btn-sm" onclick="filterHotelBookings()">Try Again</button>
            </td>
        </tr>
    `);
}

// Toggle room view
function toggleRoomView() {
    const grid = $('#roomStatusGrid');
    if (grid.hasClass('row')) {
        grid.removeClass('row').addClass('d-flex flex-wrap');
        grid.find('.col-md-2').removeClass('col-md-2 col-sm-3 col-4').addClass('flex-fill');
    } else {
        grid.removeClass('d-flex flex-wrap').addClass('row');
        grid.find('.flex-fill').removeClass('flex-fill').addClass('col-md-2 col-sm-3 col-4');
    }
}

// Export hotel bookings
function exportHotelBookings(format) {
    const filters = {
        status: $('#filterStatus').val(),
        room: $('#filterRoom').val(),
        dateRange: $('#filterDateRange').val(),
        search: $('#searchBookings').val(),
        hotel_id: {{ $hotel->id }},
        format: format
    };
    
    const params = new URLSearchParams(filters).toString();
    window.open(`{{ route('b2b.hotel-provider.bookings.hotel.export', $hotel->id) }}?${params}`, '_blank');
}

// Include common booking functions from the main bookings view
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
            filterHotelBookings(); // Refresh the table
            location.reload(); // Refresh to update room status
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

function printBooking(bookingId) {
    window.open(`{{ url('b2b/hotel-provider/bookings') }}/${bookingId}/print`, '_blank');
}

function emailBooking(bookingId) {
    toastr.info('Email functionality coming soon');
}

function viewBookingHistory(bookingId) {
    toastr.info('Booking history functionality coming soon');
}

function addBookingNote(bookingId) {
    toastr.info('Add booking note functionality coming soon');
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
