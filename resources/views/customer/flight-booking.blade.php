@extends('layouts.customer')

@section('title', 'Book Flight - SeferEt')

@section('content')
    <!-- Booking Header -->
    <div class="booking-header bg-primary text-white py-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h2 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        Complete Your Booking
                    </h2>
                    <p class="mb-0 opacity-75">Enter passenger details to complete your flight booking</p>
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section py-5">
        <div class="container-fluid">
            <div class="row">
                <!-- Flight Summary Card -->
                <div class="col-lg-4 order-lg-2 mb-4">
                    <x-customer.card variant="elevated" elevation="md" class="sticky-top" style="top: 20px;">
                        <h4 class="card-title mb-3">
                            <i class="fas fa-plane text-primary me-2"></i>
                            Flight Summary
                        </h4>
                        <div id="flightSummary" class="flight-summary-content">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading flight details...</p>
                            </div>
                        </div>
                    </x-customer.card>
                </div>

                <!-- Booking Form -->
                <div class="col-lg-8 order-lg-1">
                    <x-customer.card variant="elevated" elevation="md" padding="lg">
                        <form id="bookingForm">
                            @csrf
                            
                            <!-- Passenger Information -->
                            <div class="section-header mb-4">
                                <h4>
                                    <i class="fas fa-users text-primary me-2"></i>
                                    Passenger Information
                                </h4>
                                <p class="text-muted small mb-0">Enter details for all passengers (as shown on passport)</p>
                            </div>

                            <div id="passengersContainer"></div>

                            <!-- Contact Information -->
                            <div class="section-header mb-4 mt-5">
                                <h4>
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    Contact Information
                                </h4>
                                <p class="text-muted small mb-0">Booking confirmation will be sent to this email</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="contactEmail" value="{{ $customer->email ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="contactPhone" placeholder="+1234567890" required>
                                </div>
                            </div>

                            @guest
                            <!-- Guest Information -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guestName" placeholder="Your full name" required>
                                    <small class="text-muted">This will be used for booking confirmation</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="guestEmail" placeholder="Confirm your email" required>
                                </div>
                            </div>
                            @endguest

                            <div class="row g-3
                            </div>

                            <!-- Special Requests -->
                            <div class="section-header mb-4 mt-5">
                                <h4>
                                    <i class="fas fa-comment-alt text-primary me-2"></i>
                                    Special Requests
                                </h4>
                                <p class="text-muted small mb-0">Any special requirements or requests (optional)</p>
                            </div>

                            <div class="mb-4">
                                <textarea class="form-control" id="specialRequests" rows="3" placeholder="e.g., Wheelchair assistance, dietary requirements"></textarea>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    I agree to the <a href="#" class="text-primary">Terms and Conditions</a> and <a href="#" class="text-primary">Privacy Policy</a>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBooking">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Confirm Booking
                                </button>
                                <a href="{{ route('flights') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Search
                                </a>
                            </div>
                        </form>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.booking-header {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
}

.section-header {
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.75rem;
}

.passenger-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    background: #f9fafb;
}

.passenger-card h5 {
    color: #1e40af;
    font-weight: 600;
}

.flight-summary-content {
    font-size: 0.95rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.summary-row:last-child {
    border-bottom: none;
    font-weight: 600;
    font-size: 1.1rem;
    color: #059669;
}

.route-display {
    background: #e0f2fe;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.route-display .route-arrow {
    color: #1e40af;
    font-size: 1.25rem;
}
</style>
@endpush

@push('scripts')
<script>
const offerHash = '{{ $hash }}';
let offerData = null;

// Load offer from sessionStorage
function loadOfferData() {
    const stored = sessionStorage.getItem(`offer_${offerHash}`);
    if (stored) {
        offerData = JSON.parse(stored);
        displayFlightSummary(offerData);
        generatePassengerForms(offerData);
    } else {
        alert('Offer not found. Redirecting to search...');
        window.location.href = '{{ route("flights") }}';
    }
}

function displayFlightSummary(offer) {
    const price = offer.price;
    const itinerary = offer.itineraries[0];
    const segments = itinerary.segments;
    const firstSegment = segments[0];
    const lastSegment = segments[segments.length - 1];
    
    const departureDate = new Date(firstSegment.departure.at);
    const arrivalDate = new Date(lastSegment.arrival.at);
    const stops = segments.length - 1;
    const adults = offer.travelerPricings?.length || 1;
    
    const duration = itinerary.duration.replace('PT', '').replace('H', 'h ').replace('M', 'm');
    
    const html = `
        <div class="route-display">
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-center">
                    <div class="fs-4 fw-bold">${firstSegment.departure.iataCode}</div>
                    <div class="small text-muted">${departureDate.toLocaleDateString()}</div>
                </div>
                <div class="route-arrow">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="text-center">
                    <div class="fs-4 fw-bold">${lastSegment.arrival.iataCode}</div>
                    <div class="small text-muted">${arrivalDate.toLocaleDateString()}</div>
                </div>
            </div>
        </div>
        
        <div class="summary-details">
            <div class="summary-row">
                <span class="text-muted">Duration:</span>
                <span class="fw-semibold">${duration}</span>
            </div>
            <div class="summary-row">
                <span class="text-muted">Stops:</span>
                <span class="fw-semibold">${stops === 0 ? 'Direct' : stops + ' stop(s)'}</span>
            </div>
            <div class="summary-row">
                <span class="text-muted">Cabin:</span>
                <span class="fw-semibold">${offer.travelerPricings?.[0]?.fareDetailsBySegment?.[0]?.cabin || 'Economy'}</span>
            </div>
            <div class="summary-row">
                <span class="text-muted">Passengers:</span>
                <span class="fw-semibold">${adults}</span>
            </div>
            <div class="summary-row">
                <span class="text-muted">Price per person:</span>
                <span class="fw-semibold">${price.currency} ${parseFloat(price.total).toFixed(2)}</span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span class="text-success">${price.currency} ${(parseFloat(price.total) * adults).toFixed(2)}</span>
            </div>
        </div>
    `;
    
    document.getElementById('flightSummary').innerHTML = html;
}

function generatePassengerForms(offer) {
    const adults = offer.travelerPricings?.length || 1;
    const container = document.getElementById('passengersContainer');
    container.innerHTML = '';
    
    for (let i = 0; i < adults; i++) {
        const passengerHtml = `
            <div class="passenger-card">
                <h5 class="mb-3">
                    <i class="fas fa-user me-2"></i>
                    Passenger ${i + 1} ${i === 0 ? '(Primary)' : ''}
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control passenger-input" data-passenger="${i}" data-field="firstName" placeholder="As shown on passport" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control passenger-input" data-passenger="${i}" data-field="lastName" placeholder="As shown on passport" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control passenger-input" data-passenger="${i}" data-field="dateOfBirth" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select passenger-input" data-passenger="${i}" data-field="gender" required>
                            <option value="">Select</option>
                            <option value="MALE">Male</option>
                            <option value="FEMALE">Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nationality <span class="text-danger">*</span></label>
                        <input type="text" class="form-control passenger-input" data-passenger="${i}" data-field="nationality" placeholder="2-letter code (e.g., US)" maxlength="2" required>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += passengerHtml;
    }
}

document.getElementById('bookingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBooking');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    try {
        // Collect passenger data
        const passengers = [];
        const passengerInputs = document.querySelectorAll('.passenger-input');
        const adultsCount = offerData.travelerPricings?.length || 1;
        
        for (let i = 0; i < adultsCount; i++) {
            const passenger = {
                id: (i + 1).toString(),
                dateOfBirth: document.querySelector(`[data-passenger="${i}"][data-field="dateOfBirth"]`).value,
                name: {
                    firstName: document.querySelector(`[data-passenger="${i}"][data-field="firstName"]`).value,
                    lastName: document.querySelector(`[data-passenger="${i}"][data-field="lastName"]`).value
                },
                gender: document.querySelector(`[data-passenger="${i}"][data-field="gender"]`).value,
                contact: {
                    emailAddress: document.getElementById('contactEmail').value,
                    phones: [{
                        deviceType: 'MOBILE',
                        countryCallingCode: '1',
                        number: document.getElementById('contactPhone').value.replace(/[^0-9]/g, '')
                    }]
                },
                documents: [{
                    documentType: 'PASSPORT',
                    nationality: document.querySelector(`[data-passenger="${i}"][data-field="nationality"]`).value.toUpperCase()
                }]
            };
            passengers.push(passenger);
        }
        
        // Prepare booking payload
        const bookingData = {
            offer: offerData,
            travelers: passengers,
            @auth
            customer_id: {{ auth()->id() }},
            @else
            guest_email: document.getElementById('guestEmail').value,
            guest_name: document.getElementById('guestName').value,
            @endauth
            special_requests: document.getElementById('specialRequests').value
        };
        
        // Submit booking
        const response = await fetch('/api/flights/book', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                'Authorization': 'Bearer {{ auth()->user()->createToken("booking")->plainTextToken ?? "" }}'
            },
            body: JSON.stringify(bookingData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear stored offer
            sessionStorage.removeItem(`offer_${offerHash}`);
            
            // Show success and redirect
            alert('Booking confirmed! PNR: ' + result.data.pnr);
            @auth
            window.location.href = '{{ route("customer.flights.my-bookings") }}';
            @else
            // For guests, redirect to flights page with success message
            sessionStorage.setItem('bookingSuccess', JSON.stringify({
                pnr: result.data.pnr,
                bookingRef: result.data.booking.booking_reference
            }));
            window.location.href = '{{ route("flights") }}';
            @endauth
        } else {
            throw new Error(result.message || 'Booking failed');
        }
    } catch (error) {
        console.error('Booking error:', error);
        alert('Failed to complete booking: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirm Booking';
    }
});

// Load offer data on page load
loadOfferData();
</script>
@endpush
