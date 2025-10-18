@extends('layouts.b2b')

@section('title', 'Booking Calendar - ' . $hotel->name)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-calendar-alt text-info mr-2"></i>
                Booking Calendar - {{ $hotel->name }}
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
            <a href="{{ route('b2b.hotel-provider.bookings.hotel', $hotel->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-list mr-1"></i>
                Booking List
            </a>
            <button class="btn btn-outline-info" id="refreshCalendar">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </button>
            <div class="dropdown d-inline">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-cog mr-1"></i>
                    Options
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" id="printCalendar">
                        <i class="fas fa-print mr-2"></i>Print Calendar
                    </a>
                    <a class="dropdown-item" href="#" id="exportCalendar">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" id="toggleWeekend">
                        <i class="fas fa-calendar-week mr-2"></i>Toggle Weekend
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Calendar Controls -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label small mb-1">View Period</label>
                                <select class="form-control form-control-sm" id="viewPeriod">
                                    <option value="month">Monthly View</option>
                                    <option value="week">Weekly View</option>
                                    <option value="3months">3-Month View</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label small mb-1">Date Range</label>
                                <input type="month" class="form-control form-control-sm" id="calendarMonth" 
                                       value="{{ now()->format('Y-m') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="legend-item">
                                <span class="legend-color bg-success"></span>
                                <small>Available</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="legend-item">
                                <span class="legend-color bg-danger"></span>
                                <small>Occupied</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="legend-item">
                                <span class="legend-color bg-warning"></span>
                                <small>Check-out</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="legend-item">
                                <span class="legend-color bg-info"></span>
                                <small>Check-in</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="legend-item">
                                <span class="legend-color bg-secondary"></span>
                                <small>Maintenance</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="legend-item">
                                <span class="legend-color bg-dark"></span>
                                <small>Blocked</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="calendar-nav">
                    <button class="btn btn-outline-primary btn-sm" id="prevPeriod">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </button>
                    <button class="btn btn-outline-primary btn-sm ml-2" id="todayBtn">
                        <i class="fas fa-calendar-day mr-1"></i>
                        Today
                    </button>
                    <button class="btn btn-outline-primary btn-sm ml-2" id="nextPeriod">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-title">
                    <h4 class="mb-0" id="calendarTitle">{{ now()->format('F Y') }}</h4>
                </div>
                <div class="view-options">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="compactView">
                            <i class="fas fa-compress-alt"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary active" id="normalView">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="calendar-container">
                <div class="calendar-grid" id="calendarGrid">
                    <!-- Calendar will be generated here -->
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Loading calendar...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Available Rooms</span>
                    <span class="info-box-number" id="availableRoomsCount">-</span>
                    <span class="progress-description">Today</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-gradient-danger">
                <span class="info-box-icon"><i class="fas fa-bed"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Occupied Rooms</span>
                    <span class="info-box-number" id="occupiedRoomsCount">-</span>
                    <span class="progress-description">Currently</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-sign-in-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Check-ins Today</span>
                    <span class="info-box-number" id="checkinsTodayCount">-</span>
                    <span class="progress-description">Expected</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Check-outs Today</span>
                    <span class="info-box-number" id="checkoutsTodayCount">-</span>
                    <span class="progress-description">Expected</span>
                </div>
            </div>
        </div>
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

    <!-- Room Block Modal -->
    <div class="modal fade" id="blockRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-ban mr-2"></i>
                        Block Room
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="blockStartDate">Start Date</label>
                        <input type="date" class="form-control" id="blockStartDate">
                    </div>
                    <div class="form-group">
                        <label for="blockEndDate">End Date</label>
                        <input type="date" class="form-control" id="blockEndDate">
                    </div>
                    <div class="form-group">
                        <label for="blockReason">Reason</label>
                        <select class="form-control" id="blockReason">
                            <option value="maintenance">Maintenance</option>
                            <option value="cleaning">Deep Cleaning</option>
                            <option value="renovation">Renovation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="blockNotes">Notes (Optional)</label>
                        <textarea class="form-control" id="blockNotes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmBlockRoom">Block Room</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .calendar-container {
        overflow-x: auto;
        min-height: 600px;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: 150px repeat(31, 1fr);
        gap: 1px;
        background-color: #e9ecef;
        min-width: 1200px;
    }
    
    .calendar-header {
        background: #6c757d;
        color: white;
        padding: 8px 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        border-right: 1px solid #dee2e6;
    }
    
    .room-header {
        background: #495057;
        color: white;
        padding: 12px 8px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .room-number {
        font-size: 1rem;
        font-weight: bold;
    }
    
    .room-type {
        font-size: 0.7rem;
        opacity: 0.8;
        margin-top: 2px;
    }
    
    .calendar-cell {
        background: white;
        padding: 2px;
        min-height: 50px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        border-right: 1px solid #dee2e6;
    }
    
    .calendar-cell:hover {
        background-color: #f8f9fa;
    }
    
    .calendar-cell.available {
        background-color: #d4edda;
        border-left: 3px solid #28a745;
    }
    
    .calendar-cell.occupied {
        background-color: #f8d7da;
        border-left: 3px solid #dc3545;
    }
    
    .calendar-cell.checkin {
        background-color: #cce5ff;
        border-left: 3px solid #007bff;
    }
    
    .calendar-cell.checkout {
        background-color: #fff3cd;
        border-left: 3px solid #ffc107;
    }
    
    .calendar-cell.maintenance {
        background-color: #e2e3e5;
        border-left: 3px solid #6c757d;
    }
    
    .calendar-cell.blocked {
        background-color: #d6d8db;
        border-left: 3px solid #343a40;
    }
    
    .booking-info {
        font-size: 0.65rem;
        line-height: 1.2;
        padding: 2px 4px;
    }
    
    .guest-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 1px;
    }
    
    .booking-ref {
        color: #666;
        text-decoration: none;
    }
    
    .booking-ref:hover {
        text-decoration: underline;
    }
    
    .booking-status {
        font-size: 0.6rem;
        padding: 1px 3px;
        border-radius: 2px;
        margin-top: 2px;
        display: inline-block;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        margin-right: 6px;
        border-radius: 2px;
        border: 1px solid #dee2e6;
    }
    
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .calendar-nav .btn {
        margin-right: 0.25rem;
    }
    
    .view-options .btn-group .btn {
        padding: 0.375rem 0.5rem;
    }
    
    .compact-view .calendar-cell {
        min-height: 35px;
    }
    
    .compact-view .booking-info {
        font-size: 0.6rem;
    }
    
    .compact-view .room-header {
        padding: 8px 6px;
    }
    
    .weekend-cell {
        background-color: #f8f9fa !important;
    }
    
    .today-cell {
        position: relative;
    }
    
    .today-cell::after {
        content: '';
        position: absolute;
        top: 2px;
        right: 2px;
        width: 8px;
        height: 8px;
        background: #dc3545;
        border-radius: 50%;
    }
    
    .room-actions {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .room-header:hover .room-actions {
        opacity: 1;
    }
    
    .room-actions .btn {
        padding: 2px 6px;
        font-size: 0.7rem;
    }
    
    @media (max-width: 768px) {
        .calendar-grid {
            grid-template-columns: 120px repeat(31, 50px);
            font-size: 0.8rem;
        }
        
        .room-header {
            padding: 8px 4px;
        }
        
        .calendar-cell {
            min-height: 40px;
        }
        
        .booking-info {
            font-size: 0.6rem;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let currentDate = new Date();
    let viewPeriod = 'month';
    let isCompactView = false;
    
    // Initialize calendar
    loadCalendar();
    
    // Event listeners
    $('#viewPeriod').on('change', function() {
        viewPeriod = $(this).val();
        loadCalendar();
    });
    
    $('#calendarMonth').on('change', function() {
        const [year, month] = $(this).val().split('-');
        currentDate = new Date(year, month - 1, 1);
        updateCalendarTitle();
        loadCalendar();
    });
    
    $('#prevPeriod').on('click', function() {
        navigatePeriod(-1);
    });
    
    $('#nextPeriod').on('click', function() {
        navigatePeriod(1);
    });
    
    $('#todayBtn').on('click', function() {
        currentDate = new Date();
        updateCalendarTitle();
        updateMonthInput();
        loadCalendar();
    });
    
    $('#refreshCalendar').on('click', function() {
        loadCalendar();
    });
    
    $('#compactView').on('click', function() {
        isCompactView = true;
        $('#normalView').removeClass('active');
        $(this).addClass('active');
        $('.calendar-container').addClass('compact-view');
    });
    
    $('#normalView').on('click', function() {
        isCompactView = false;
        $('#compactView').removeClass('active');
        $(this).addClass('active');
        $('.calendar-container').removeClass('compact-view');
    });
    
    $('#toggleWeekend').on('click', function() {
        $('.weekend-cell').toggle();
    });
    
    // Print and export functions
    $('#printCalendar').on('click', function() {
        window.print();
    });
    
    $('#exportCalendar').on('click', function() {
        exportCalendarData();
    });
    
    // Room blocking
    $('#confirmBlockRoom').on('click', function() {
        blockRoom();
    });
});

// Navigate calendar periods
function navigatePeriod(direction) {
    switch(viewPeriod) {
        case 'week':
            currentDate.setDate(currentDate.getDate() + (direction * 7));
            break;
        case 'month':
            currentDate.setMonth(currentDate.getMonth() + direction);
            break;
        case '3months':
            currentDate.setMonth(currentDate.getMonth() + (direction * 3));
            break;
    }
    updateCalendarTitle();
    updateMonthInput();
    loadCalendar();
}

// Update calendar title
function updateCalendarTitle() {
    let title = '';
    switch(viewPeriod) {
        case 'week':
            const weekStart = new Date(currentDate);
            weekStart.setDate(currentDate.getDate() - currentDate.getDay());
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            title = `${weekStart.toLocaleDateString()} - ${weekEnd.toLocaleDateString()}`;
            break;
        case 'month':
            title = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            break;
        case '3months':
            const endMonth = new Date(currentDate);
            endMonth.setMonth(currentDate.getMonth() + 2);
            title = `${currentDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })} - ${endMonth.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })}`;
            break;
    }
    $('#calendarTitle').text(title);
}

// Update month input
function updateMonthInput() {
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    $('#calendarMonth').val(`${year}-${month}`);
}

// Load calendar data
function loadCalendar() {
    $('#calendarGrid').html(`
        <div class="text-center py-5" style="grid-column: 1 / -1;">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="mt-2 text-muted">Loading calendar...</p>
        </div>
    `);
    
    const params = {
        date: currentDate.toISOString().split('T')[0],
        period: viewPeriod,
        hotel_id: {{ $hotel->id }}
    };
    
    $.get('{{ route("b2b.hotel-provider.bookings.calendar.data", $hotel->id) }}', params)
        .done(function(response) {
            if (response.success) {
                generateCalendarGrid(response.data);
                updateStats(response.stats);
            } else {
                showCalendarError('Failed to load calendar data: ' + response.message);
            }
        })
        .fail(function() {
            showCalendarError('Network error. Please try again.');
        });
}

// Generate calendar grid HTML
function generateCalendarGrid(data) {
    let html = '';
    
    // Generate header
    html += '<div class="room-header">Room</div>';
    for (let i = 0; i < data.dates.length; i++) {
        const date = new Date(data.dates[i]);
        const isWeekend = date.getDay() === 0 || date.getDay() === 6;
        const isToday = date.toDateString() === new Date().toDateString();
        
        html += `<div class="calendar-header ${isWeekend ? 'weekend-cell' : ''} ${isToday ? 'today-cell' : ''}">
            <div>${date.getDate()}</div>
            <div style="font-size: 0.6rem; opacity: 0.8;">${date.toLocaleDateString('en-US', { weekday: 'short' })}</div>
        </div>`;
    }
    
    // Generate room rows
    data.rooms.forEach(function(room) {
        html += `<div class="room-header">
            <div>
                <div class="room-number">Room ${room.room_number}</div>
                <div class="room-type">${room.room_type}</div>
            </div>
            <div class="room-actions">
                <button class="btn btn-outline-secondary btn-sm" onclick="blockRoom(${room.id})" title="Block Room">
                    <i class="fas fa-ban"></i>
                </button>
            </div>
        </div>`;
        
        data.dates.forEach(function(dateStr) {
            const booking = room.bookings[dateStr];
            const isWeekend = new Date(dateStr).getDay() === 0 || new Date(dateStr).getDay() === 6;
            const isToday = new Date(dateStr).toDateString() === new Date().toDateString();
            
            let cellClass = 'calendar-cell';
            let cellContent = '';
            
            if (isWeekend) cellClass += ' weekend-cell';
            if (isToday) cellClass += ' today-cell';
            
            if (booking) {
                switch (booking.status) {
                    case 'confirmed':
                        cellClass += ' occupied';
                        break;
                    case 'checked_in':
                        cellClass += ' occupied';
                        break;
                    case 'pending':
                        cellClass += ' checkin';
                        break;
                    default:
                        cellClass += ' available';
                }
                
                cellContent = `
                    <div class="booking-info">
                        <div class="guest-name">${booking.guest_name}</div>
                        <a href="#" class="booking-ref" onclick="viewBooking(${booking.id})">${booking.booking_reference}</a>
                        <div class="booking-status bg-${getStatusColor(booking.status)}">${formatStatus(booking.status)}</div>
                    </div>
                `;
            } else if (room.blocked && room.blocked[dateStr]) {
                cellClass += ' blocked';
                cellContent = `
                    <div class="booking-info">
                        <div class="guest-name">BLOCKED</div>
                        <div style="font-size: 0.6rem;">${room.blocked[dateStr].reason || 'Maintenance'}</div>
                    </div>
                `;
            } else {
                cellClass += ' available';
                cellContent = `
                    <div class="booking-info text-center" style="padding-top: 15px;">
                        <small class="text-success">Available</small>
                    </div>
                `;
            }
            
            html += `<div class="${cellClass}" data-room-id="${room.id}" data-date="${dateStr}" onclick="handleCellClick(${room.id}, '${dateStr}', ${booking ? booking.id : 'null'})">${cellContent}</div>`;
        });
    });
    
    $('#calendarGrid').html(html);
}

// Handle cell click
function handleCellClick(roomId, date, bookingId) {
    if (bookingId) {
        viewBooking(bookingId);
    } else {
        // Show available room options

        // Could open a modal to create a booking or block the room
    }
}

// View booking details
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

// Block room function
function blockRoom(roomId = null) {
    if (roomId) {
        $('#blockRoomModal').data('room-id', roomId).modal('show');
    } else {
        // Process room blocking
        const roomId = $('#blockRoomModal').data('room-id');
        const startDate = $('#blockStartDate').val();
        const endDate = $('#blockEndDate').val();
        const reason = $('#blockReason').val();
        const notes = $('#blockNotes').val();
        
        if (!startDate || !endDate) {
            toastr.error('Please select start and end dates');
            return;
        }
        
        $.post('{{ route("b2b.hotel-provider.rooms.block") }}', {
            _token: '{{ csrf_token() }}',
            room_id: roomId,
            start_date: startDate,
            end_date: endDate,
            reason: reason,
            notes: notes
        })
        .done(function(response) {
            if (response.success) {
                $('#blockRoomModal').modal('hide');
                toastr.success('Room blocked successfully');
                loadCalendar();
            } else {
                toastr.error(response.message || 'Failed to block room');
            }
        })
        .fail(function() {
            toastr.error('Network error. Please try again.');
        });
    }
}

// Update statistics
function updateStats(stats) {
    $('#availableRoomsCount').text(stats.available_rooms || 0);
    $('#occupiedRoomsCount').text(stats.occupied_rooms || 0);
    $('#checkinsTodayCount').text(stats.checkins_today || 0);
    $('#checkoutsTodayCount').text(stats.checkouts_today || 0);
}

// Show calendar error
function showCalendarError(message) {
    $('#calendarGrid').html(`
        <div class="text-center py-5 text-danger" style="grid-column: 1 / -1;">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <p>${message}</p>
            <button class="btn btn-outline-primary btn-sm" onclick="loadCalendar()">Try Again</button>
        </div>
    `);
}

// Export calendar data
function exportCalendarData() {
    const params = {
        date: currentDate.toISOString().split('T')[0],
        period: viewPeriod,
        hotel_id: {{ $hotel->id }},
        format: 'excel'
    };
    
    const queryString = new URLSearchParams(params).toString();
    window.open(`{{ route('b2b.hotel-provider.bookings.calendar.export', $hotel->id) }}?${queryString}`, '_blank');
}

// Helper functions
function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'confirmed': 'success',
        'checked_in': 'info',
        'checked_out': 'secondary',
        'cancelled': 'danger',
        'no_show': 'dark'
    };
    return colors[status] || 'secondary';
}

function formatStatus(status) {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}
</script>
@stop
