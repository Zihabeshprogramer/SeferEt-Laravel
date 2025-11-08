@extends('layouts.customer')

@section('title', 'My Flight Bookings - SeferEt')

@section('content')
    <!-- Bookings Header -->
    <div class="bookings-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-clipboard-list me-3"></i>
                        My Flight Bookings
                    </h1>
                    <p class="lead opacity-90 mb-0">View and manage your flight bookings</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('flights') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-2"></i>
                        Book New Flight
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="bookings-section py-5">
        <div class="container-fluid">
            <!-- Loading State -->
            <div id="loadingState" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading your bookings...</p>
            </div>

            <!-- Bookings List -->
            <div id="bookingsContainer" style="display: none;"></div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <div class="empty-icon mb-4">
                    <i class="fas fa-inbox fa-4x text-muted opacity-50"></i>
                </div>
                <h4 class="text-muted mb-2">No Bookings Yet</h4>
                <p class="text-muted mb-4">You haven't booked any flights. Start planning your journey!</p>
                <a href="{{ route('flights') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>
                    Search Flights
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.bookings-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.95), rgba(30, 58, 138, 0.95)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
}

.booking-card {
    border-left: 4px solid #10b981;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.booking-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.booking-card.cancelled {
    border-left-color: #ef4444;
    opacity: 0.75;
}

.booking-card.pending {
    border-left-color: #f59e0b;
}

.status-badge {
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    font-weight: 600;
    border-radius: 0.375rem;
}

.status-confirmed {
    background-color: #d1fae5;
    color: #065f46;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-cancelled {
    background-color: #fee2e2;
    color: #991b1b;
}

.booking-detail-row {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
}

.booking-detail-row i {
    width: 24px;
    color: #6b7280;
}

.flight-route-visual {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: #f3f4f6;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.route-point {
    text-align: center;
}

.route-point .code {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e40af;
}

.route-point .date {
    font-size: 0.875rem;
    color: #6b7280;
}

.route-arrow {
    margin: 0 2rem;
    color: #1e40af;
    font-size: 1.5rem;
}
</style>
@endpush

@push('scripts')
<script>
async function loadBookings() {
    try {
        const response = await fetch('/api/flights/bookings', {
            headers: {
                'Authorization': 'Bearer {{ auth()->user()->createToken("view-bookings")->plainTextToken ?? "" }}',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        document.getElementById('loadingState').style.display = 'none';

        if (result.success && result.data && result.data.length > 0) {
            displayBookings(result.data);
            document.getElementById('bookingsContainer').style.display = 'block';
        } else {
            document.getElementById('emptyState').style.display = 'block';
        }
    } catch (error) {
        console.error('Failed to load bookings:', error);
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    }
}

function displayBookings(bookings) {
    const container = document.getElementById('bookingsContainer');
    container.innerHTML = '';

    bookings.forEach(booking => {
        const card = createBookingCard(booking);
        container.innerHTML += card;
    });
}

function createBookingCard(booking) {
    const offer = booking.offer;
    const statusClass = booking.status.toLowerCase();
    const statusLabel = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);

    // Extract flight details
    const segments = offer.segments || [];
    const firstSegment = segments[0]?.segments?.[0];
    const lastSegment = segments[0]?.segments?.[segments[0]?.segments?.length - 1];

    const origin = offer.origin || firstSegment?.departure?.iataCode || 'N/A';
    const destination = offer.destination || lastSegment?.arrival?.iataCode || 'N/A';
    const departureDate = offer.departure_date || (firstSegment?.departure?.at ? new Date(firstSegment.departure.at).toLocaleDateString() : 'N/A');

    return `
        <x-customer.card variant="elevated" elevation="sm" class="booking-card ${statusClass}">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">
                                    <i class="fas fa-plane-departure text-primary me-2"></i>
                                    ${origin} → ${destination}
                                </h5>
                                <p class="text-muted small mb-0">
                                    Booking Reference: <strong>${booking.booking_reference}</strong>
                                    ${booking.pnr ? ` | PNR: <strong>${booking.pnr}</strong>` : ''}
                                </p>
                            </div>
                            <span class="status-badge status-${statusClass}">
                                ${statusLabel}
                            </span>
                        </div>

                        <div class="flight-route-visual">
                            <div class="route-point">
                                <div class="code">${origin}</div>
                                <div class="date">${departureDate}</div>
                            </div>
                            <div class="route-arrow">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div class="route-point">
                                <div class="code">${destination}</div>
                                <div class="date">${offer.return_date ? new Date(offer.return_date).toLocaleDateString() : 'One-way'}</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="booking-detail-row">
                                    <i class="fas fa-users"></i>
                                    <span>${booking.passengers} Passenger${booking.passengers > 1 ? 's' : ''}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="booking-detail-row">
                                    <i class="fas fa-chair"></i>
                                    <span>${booking.flight_class || 'Economy'}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="booking-detail-row">
                                    <i class="fas fa-calendar"></i>
                                    <span>Booked ${new Date(booking.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="text-muted small">Total Amount</div>
                                <h3 class="text-success mb-0">${booking.currency} ${parseFloat(booking.total_amount).toFixed(2)}</h3>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewBookingDetails('${booking.id}')">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </button>
                                ${booking.status === 'confirmed' ? `
                                    <button class="btn btn-outline-secondary btn-sm" onclick="downloadTicket('${booking.id}')">
                                        <i class="fas fa-download me-2"></i>Download
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>

                ${booking.passenger_name ? `
                    <div class="mt-3 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i> Primary Passenger:
                                    <strong>${booking.passenger_name}</strong>
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i> ${booking.passenger_email}
                                </small>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        </x-customer.card>
    `;
}

function viewBookingDetails(bookingId) {
    alert('Booking details view - Feature coming soon! Booking ID: ' + bookingId);
}

function downloadTicket(bookingId) {
    alert('Download ticket - Feature coming soon! Booking ID: ' + bookingId);
}

// Load bookings on page load
loadBookings();
</script>
@endpush
