@extends('layouts.customer')

@section('title', 'Flight Details - ' . $flight['from'] . ' to ' . $flight['to'] . ' - SeferEt')

@section('content')
    <!-- Flight Header -->
    <div class="flight-detail-header bg-primary text-white py-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">
                        <i class="fas fa-plane me-2"></i>
                        {{ $flight['from'] }} → {{ $flight['to'] }}
                    </h1>
                    <p class="mb-0">{{ $flight['airline'] }} • {{ date('M d, Y', strtotime($flight['departure'])) }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="price-display">
                        <h3 class="text-white mb-0">${{ number_format($flight['price']) }}</h3>
                        <small class="opacity-75">per person</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Flight Summary -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Flight Details
                    </h2>
                    
                    <div class="flight-summary">
                        <div class="row align-items-center mb-4">
                            <div class="col-md-2 text-center">
                                <div class="airline-info">
                                    <i class="fas fa-plane-departure fa-2x text-primary mb-2"></i>
                                    <h6>{{ $flight['airline'] }}</h6>
                                    <small class="text-muted">{{ $flight['class'] }}</small>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="flight-route-detail">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 text-center">
                                            <div class="departure-detail">
                                                <h4 class="time">{{ date('H:i', strtotime($flight['departure'])) }}</h4>
                                                <p class="location">{{ $flight['from'] }}</p>
                                                <small class="text-muted">{{ date('M d, Y', strtotime($flight['departure'])) }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="flight-duration">
                                                <div class="duration-line">
                                                    <i class="fas fa-plane"></i>
                                                </div>
                                                <p class="duration-text">{{ $flight['duration'] }}</p>
                                                @if($flight['stops'] > 0)
                                                    <small class="text-muted">{{ $flight['stops'] }} stop(s)</small>
                                                @else
                                                    <small class="text-success">Direct flight</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="arrival-detail">
                                                <h4 class="time">{{ date('H:i', strtotime($flight['arrival'])) }}</h4>
                                                <p class="location">{{ $flight['to'] }}</p>
                                                <small class="text-muted">{{ date('M d, Y', strtotime($flight['arrival'])) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flight-badges">
                            @if($flight['stops'] == 0)
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-check me-1"></i>Direct Flight
                                </span>
                            @endif
                            <span class="badge bg-primary me-2">
                                <i class="fas fa-wifi me-1"></i>WiFi Available
                            </span>
                            <span class="badge bg-info me-2">
                                <i class="fas fa-utensils me-1"></i>Meal Included
                            </span>
                            <span class="badge bg-warning">
                                <i class="fas fa-suitcase me-1"></i>23kg Baggage
                            </span>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Baggage Information -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-suitcase text-primary me-2"></i>
                        Baggage Information
                    </h2>
                    
                    <div class="baggage-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="baggage-item mb-3">
                                    <h6><i class="fas fa-briefcase text-success me-2"></i>Carry-on Baggage</h6>
                                    <ul class="list-unstyled ps-4">
                                        <li><i class="fas fa-check text-success me-2"></i>1 piece up to 7 kg</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Dimensions: 55 x 40 x 20 cm</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Personal item included</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="baggage-item mb-3">
                                    <h6><i class="fas fa-suitcase text-primary me-2"></i>Checked Baggage</h6>
                                    <ul class="list-unstyled ps-4">
                                        <li><i class="fas fa-check text-success me-2"></i>1 piece up to 23 kg (included)</li>
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Additional bags: $75 each</li>
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Overweight: $25/kg</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="baggage-restrictions mt-3 p-3 bg-light rounded">
                            <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Important Notes</h6>
                            <ul class="list-unstyled mb-0">
                                <li><small>• Liquids in carry-on must be in containers ≤ 100ml</small></li>
                                <li><small>• Electronics with batteries must be in carry-on</small></li>
                                <li><small>• Check airline website for complete prohibited items list</small></li>
                            </ul>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Seat Selection Preview -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-chair text-primary me-2"></i>
                        Seat Selection
                    </h2>
                    
                    <div class="seat-preview">
                        <p class="text-muted mb-3">Choose your preferred seats during checkout</p>
                        
                        <div class="seat-options">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="seat-option-card text-center p-3 border rounded">
                                        <i class="fas fa-chair fa-2x text-success mb-2"></i>
                                        <h6>Standard Seat</h6>
                                        <p class="text-muted small mb-2">Regular legroom</p>
                                        <span class="badge bg-success">FREE</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="seat-option-card text-center p-3 border rounded">
                                        <i class="fas fa-chair fa-2x text-primary mb-2"></i>
                                        <h6>Extra Legroom</h6>
                                        <p class="text-muted small mb-2">32-34" pitch</p>
                                        <span class="badge bg-primary">+$25</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="seat-option-card text-center p-3 border rounded">
                                        <i class="fas fa-chair fa-2x text-warning mb-2"></i>
                                        <h6>Business Select</h6>
                                        <p class="text-muted small mb-2">Priority boarding</p>
                                        <span class="badge bg-warning">+$75</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Airline Policies -->
                <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                    <h2 class="section-title mb-4">
                        <i class="fas fa-file-contract text-primary me-2"></i>
                        Airline Policies
                    </h2>
                    
                    <div class="policies-content">
                        <div class="accordion" id="policiesAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cancellation">
                                        <i class="fas fa-times-circle me-2"></i>Cancellation Policy
                                    </button>
                                </h3>
                                <div id="cancellation" class="accordion-collapse collapse" data-bs-parent="#policiesAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled">
                                            <li><strong>Free cancellation:</strong> Up to 24 hours after booking (if booked 7+ days before departure)</li>
                                            <li><strong>Cancellation fees:</strong> $200 per person within 30 days of departure</li>
                                            <li><strong>No refund:</strong> Within 7 days of departure</li>
                                            <li><strong>Travel insurance:</strong> Recommended for flexible cancellation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#changes">
                                        <i class="fas fa-edit me-2"></i>Change Policy
                                    </button>
                                </h3>
                                <div id="changes" class="accordion-collapse collapse" data-bs-parent="#policiesAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled">
                                            <li><strong>Date changes:</strong> $150 fee + fare difference</li>
                                            <li><strong>Name changes:</strong> $75 fee (minor corrections only)</li>
                                            <li><strong>Route changes:</strong> Subject to availability and fare rules</li>
                                            <li><strong>Class upgrades:</strong> Available during check-in</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#checkin">
                                        <i class="fas fa-mobile-alt me-2"></i>Check-in Information
                                    </button>
                                </h3>
                                <div id="checkin" class="accordion-collapse collapse" data-bs-parent="#policiesAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled">
                                            <li><strong>Online check-in:</strong> 24 hours to 1 hour before departure</li>
                                            <li><strong>Airport check-in:</strong> Opens 3 hours before international flights</li>
                                            <li><strong>Boarding gate closes:</strong> 45 minutes before departure</li>
                                            <li><strong>Required documents:</strong> Passport, visa (if required), booking confirmation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-customer.card>
            </div>

            <!-- Booking Sidebar -->
            <div class="col-lg-4">
                <!-- Price Summary -->
                <x-customer.card variant="elevated" elevation="lg" padding="lg" class="booking-card sticky-top mb-4">
                    <div class="booking-header mb-3">
                        <h4 class="text-primary mb-2">
                            <i class="fas fa-calculator me-2"></i>Price Summary
                        </h4>
                    </div>

                    <div class="price-breakdown mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Base fare (1 adult)</span>
                            <span>${{ number_format($flight['price'] - 150) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Taxes & fees</span>
                            <span>$120</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Airport charges</span>
                            <span>$30</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Price</strong>
                            <strong class="text-primary">${{ number_format($flight['price']) }}</strong>
                        </div>
                        <small class="text-muted">Includes all mandatory charges</small>
                    </div>

                    <div class="passenger-selection mb-4">
                        <label class="form-label">Number of Passengers</label>
                        <select class="form-select" id="passengerCount">
                            <option value="1" selected>1 Passenger</option>
                            <option value="2">2 Passengers</option>
                            <option value="3">3 Passengers</option>
                            <option value="4">4 Passengers</option>
                        </select>
                    </div>

                    <div class="booking-actions">
                        @auth
                            <x-customer.button 
                                href="{{ route('flights.checkout', $flight['id']) }}" 
                                variant="primary" 
                                size="lg" 
                                fullWidth="true" 
                                class="mb-3"
                            >
                                <i class="fas fa-credit-card me-2"></i>Book This Flight
                            </x-customer.button>
                        @else
                            <x-customer.button 
                                href="{{ route('customer.login') }}" 
                                variant="primary" 
                                size="lg" 
                                fullWidth="true" 
                                class="mb-3"
                            >
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                            </x-customer.button>
                        @endauth
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <x-customer.button variant="outline-secondary" size="md" fullWidth="true">
                                    <i class="fas fa-heart me-1"></i>Save
                                </x-customer.button>
                            </div>
                            <div class="col-6">
                                <x-customer.button variant="outline-secondary" size="md" fullWidth="true">
                                    <i class="fas fa-share me-1"></i>Share
                                </x-customer.button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-assurance mt-4 pt-4 border-top">
                        <div class="assurance-items">
                            <div class="assurance-item mb-2">
                                <i class="fas fa-lock text-success me-2"></i>
                                <small>Secure payment processing</small>
                            </div>
                            <div class="assurance-item mb-2">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                <small>IATA protected booking</small>
                            </div>
                            <div class="assurance-item">
                                <i class="fas fa-phone text-info me-2"></i>
                                <small>24/7 booking support</small>
                            </div>
                        </div>
                    </div>
                </x-customer.card>

                <!-- Alternative Flights -->
                <x-customer.card variant="elevated" elevation="md" padding="md" class="alternatives-card">
                    <h6 class="mb-3">
                        <i class="fas fa-exchange-alt text-primary me-2"></i>
                        Alternative Flights
                    </h6>
                    
                    <div class="alternative-flights">
                        <div class="alternative-item mb-3 p-2 border rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Qatar Airways</strong><br>
                                    <small class="text-muted">Same route • 1 stop</small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">${{ number_format($flight['price'] - 200) }}</strong><br>
                                    <small class="text-muted">2h longer</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alternative-item mb-3 p-2 border rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Turkish Airlines</strong><br>
                                    <small class="text-muted">Same route • 1 stop</small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-warning">${{ number_format($flight['price'] + 150) }}</strong><br>
                                    <small class="text-muted">Premium</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <x-customer.button href="{{ route('flights') }}" variant="outline-primary" size="sm">
                                <i class="fas fa-search me-1"></i>See All Options
                            </x-customer.button>
                        </div>
                    </div>
                </x-customer.card>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.flight-detail-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&h=400&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.flight-detail-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.flight-route-detail .time {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.flight-route-detail .location {
    font-weight: 600;
    color: var(--text-secondary);
    margin: 0.25rem 0;
}

.flight-duration {
    position: relative;
}

.duration-line {
    height: 2px;
    background: linear-gradient(to right, var(--primary-color), var(--success-color));
    margin: 1rem 0;
    position: relative;
}

.duration-line i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    color: var(--primary-color);
    padding: 0.5rem;
    border-radius: 50%;
}

.duration-text {
    font-weight: 600;
    color: var(--text-primary);
    margin: 0.5rem 0;
}

.baggage-item h6 {
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.seat-option-card {
    transition: all 0.3s ease;
}

.seat-option-card:hover {
    border-color: var(--primary-color) !important;
    box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.1);
}

.booking-card {
    position: sticky;
    top: 2rem;
}

.price-breakdown {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    background: var(--surface-variant-color);
}

.assurance-item {
    display: flex;
    align-items: center;
}

.alternative-item {
    transition: all 0.2s ease;
}

.alternative-item:hover {
    background: var(--surface-variant-color);
    border-color: var(--primary-color) !important;
}

.accordion-button {
    background: var(--surface-variant-color);
    color: var(--text-primary);
    border: none;
}

.accordion-button:not(.collapsed) {
    background: var(--primary-color);
    color: white;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.25);
}

@media (max-width: 768px) {
    .flight-detail-header {
        background-attachment: scroll;
    }
    
    .booking-card {
        position: relative;
        top: auto;
    }
    
    .flight-route-detail .row {
        text-align: center;
    }
    
    .duration-line {
        display: none;
    }
    
    .price-display h3 {
        font-size: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update total price when passenger count changes
    const passengerSelect = document.getElementById('passengerCount');
    const basePrice = {{ $flight['price'] }};
    
    passengerSelect.addEventListener('change', function() {
        const passengerCount = parseInt(this.value);
        const totalPrice = basePrice * passengerCount;
        
        // Update price display (you can enhance this to update all price elements)
        const priceElements = document.querySelectorAll('.text-primary strong');
        priceElements.forEach(element => {
            if (element.textContent.includes('$')) {
                element.textContent = '$' + totalPrice.toLocaleString();
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
