@extends('layouts.customer')

@section('title', 'Flight Search - SeferEt')

@section('content')
    <!-- Flights Search Header -->
    <div class="flights-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-plane me-3"></i>
                        Find Your Perfect Flight
                    </h1>
                    <p class="lead opacity-90 mb-0">Compare and book flights to the Holy Cities</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-plane-departure fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Search Form -->
    <div class="search-section py-4 bg-light">
        <div class="container-fluid">
            <x-customer.card variant="elevated" elevation="md" padding="lg">
                <form id="flightSearchForm" class="flight-search-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-departure text-primary"></i></span>
                                <input type="text" class="form-control airport-autocomplete" id="originInput" name="origin" placeholder="Airport code" autocomplete="off" required>
                                <div class="airport-suggestions" id="originSuggestions"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-arrival text-success"></i></span>
                                <input type="text" class="form-control airport-autocomplete" id="destinationInput" name="destination" placeholder="Airport code" autocomplete="off" required>
                                <div class="airport-suggestions" id="destinationSuggestions"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Departure</label>
                            <input type="date" class="form-control" name="departure_date" id="departureDate" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Return</label>
                            <input type="date" class="form-control" name="return_date" id="returnDate">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Passengers</label>
                            <select class="form-select" name="adults" id="adults">
                                <option value="1">1 Passenger</option>
                                <option value="2">2 Passengers</option>
                                <option value="3">3 Passengers</option>
                                <option value="4">4 Passengers</option>
                                <option value="5">5 Passengers</option>
                                <option value="6">6 Passengers</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </div>
                </form>
            </x-customer.card>
        </div>
    </div>

    <!-- Filters and Sort -->
    <div class="filters-section py-3 bg-white border-bottom" id="filtersSection" style="display: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Sort by</label>
                    <select class="form-select form-select-sm" id="sortBy">
                        <option value="">Best Match</option>
                        <option value="price">Lowest Price</option>
                        <option value="duration">Shortest Duration</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Stops</label>
                    <select class="form-select form-select-sm" id="filterStops">
                        <option value="">Any</option>
                        <option value="0">Direct Only</option>
                        <option value="1">1 Stop Max</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Price Range</label>
                    <select class="form-select form-select-sm" id="filterPrice">
                        <option value="">Any Price</option>
                        <option value="0-500">$0 - $500</option>
                        <option value="500-1000">$500 - $1000</option>
                        <option value="1000-2000">$1000 - $2000</option>
                        <option value="2000-">$2000+</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearFilters" onclick="clearAllFilters()">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Results -->
    <div class="flights-results py-5">
        <div class="container-fluid">
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Searching for flights...</p>
            </div>

            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4" id="resultsHeader" style="display: none !important;">
                <h2 class="section-title">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Available Flights
                </h2>
                <div class="results-info">
                    <span class="text-muted" id="resultsCount">0 flights found</span>
                </div>
            </div>

            <!-- Results Container -->
            <div id="flightResults" class="row g-4"></div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <div class="empty-icon mb-4">
                    <i class="fas fa-plane fa-4x text-muted opacity-50"></i>
                </div>
                <h4 class="text-muted mb-2">No Flights Found</h4>
                <p class="text-muted mb-4">Try adjusting your search criteria to find available flights.</p>
            </div>

            <!-- Initial State -->
            <div id="initialState" class="text-center py-5">
                <div class="empty-icon mb-4">
                    <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                </div>
                <h4 class="text-muted mb-2">Search for Flights</h4>
                <p class="text-muted">Enter your travel details above to find available flights.</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.flights-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.flights-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.input-group {
    position: relative;
}

.airport-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.airport-suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.airport-suggestion-item:hover {
    background-color: #f8f9fa;
}

.airport-suggestion-item:last-child {
    border-bottom: none;
}

.airport-suggestion-item .airport-code {
    font-weight: 600;
    color: #1e40af;
}

.airport-suggestion-item .airport-name {
    font-size: 0.875rem;
    color: #6b7280;
}

.flight-card {
    border-left: 4px solid var(--primary-color);
    transition: all 0.3s ease;
}

.flight-card:hover {
    border-left-color: var(--secondary-color);
    transform: translateY(-2px);
}

.path-line {
    height: 2px;
    background: linear-gradient(to right, var(--primary-color), var(--success-color));
    margin: 1rem 0;
}

.path-line i {
    background: white;
    padding: 0.5rem;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.duration-badge {
    position: absolute;
    top: -1.5rem;
    left: 50%;
    transform: translateX(-50%);
}

.time {
    font-weight: 700;
    color: var(--text-primary);
}

.location {
    font-weight: 600;
    color: var(--text-secondary);
}

.airline-name {
    font-weight: 600;
    color: var(--text-primary);
}

.price {
    font-weight: 700;
}

.pagination {
    margin-top: 2rem;
}

.pagination .page-link {
    color: #1e40af;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
}

.pagination .page-item.active .page-link {
    background-color: #1e40af;
    border-color: #1e40af;
    color: white;
}

.pagination .page-link:hover {
    background-color: #e0f2fe;
    border-color: #1e40af;
    color: #1e40af;
}

.pagination .page-item.disabled .page-link {
    color: #6b7280;
    background-color: #f9fafb;
}

@media (max-width: 768px) {
    .flights-header {
        background-attachment: scroll;
    }
    
    .flight-route .row {
        text-align: center;
    }
    
    .path-line {
        display: none;
    }
    
    .flight-info, .flight-booking {
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
let flightResults = [];
let originalFlightResults = []; // Store original unfiltered results
let currentDictionaries = null;
let currentPage = 1;
let itemsPerPage = 10;
let totalResults = 0;

// Check for URL parameters from home page
window.addEventListener('DOMContentLoaded', function() {
    // Check for guest booking success
    const bookingSuccess = sessionStorage.getItem('bookingSuccess');
    if (bookingSuccess) {
        const booking = JSON.parse(bookingSuccess);
        sessionStorage.removeItem('bookingSuccess');
        
        // Show success alert
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Booking Confirmed!</h4>
                <p class="mb-2">Your flight booking has been successfully confirmed.</p>
                <hr>
                <p class="mb-0">
                    <strong>PNR:</strong> ${booking.pnr}<br>
                    <strong>Booking Reference:</strong> ${booking.bookingRef}<br>
                    <small class="text-muted">Please save these details for your records. A confirmation email has been sent to your email address.</small>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        document.querySelector('.search-section').insertAdjacentHTML('afterend', alertHtml);
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    
    // Support both old and new parameter names
    const origin = urlParams.get('origin') || urlParams.get('from');
    const destination = urlParams.get('destination') || urlParams.get('to');
    const departureDate = urlParams.get('departure_date');
    const returnDate = urlParams.get('return_date');
    const adults = urlParams.get('adults') || urlParams.get('passengers') || '1';
    const travelClass = urlParams.get('travel_class');
    
    if (origin || destination || departureDate) {
        if (origin) document.getElementById('originInput').value = origin.toUpperCase();
        if (destination) document.getElementById('destinationInput').value = destination.toUpperCase();
        if (departureDate) document.getElementById('departureDate').value = departureDate;
        if (returnDate) document.getElementById('returnDate').value = returnDate;
        if (adults && !adults.includes('+')) {
            document.getElementById('adults').value = adults;
        }
        
        // Auto-submit search if we have minimum required params
        if (origin && destination && departureDate) {
            setTimeout(() => {
                document.getElementById('flightSearchForm').dispatchEvent(new Event('submit'));
            }, 100);
        }
    }
});

// Airport autocomplete
function setupAirportAutocomplete(inputId, suggestionsId) {
    const input = document.getElementById(inputId);
    const suggestionsBox = document.getElementById(suggestionsId);
    let debounceTimer;

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const keyword = this.value.trim();

        if (keyword.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/flights/airports?keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        displayAirportSuggestions(data.data, suggestionsBox, input);
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Airport search error:', error);
                    suggestionsBox.style.display = 'none';
                });
        }, 300);
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
}

function displayAirportSuggestions(airports, suggestionsBox, input) {
    suggestionsBox.innerHTML = '';
    
    airports.forEach(airport => {
        const item = document.createElement('div');
        item.className = 'airport-suggestion-item';
        item.innerHTML = `
            <div class="airport-code">${airport.iataCode}</div>
            <div class="airport-name">${airport.name}, ${airport.address?.cityName || ''}</div>
        `;
        
        item.addEventListener('click', () => {
            input.value = airport.iataCode;
            suggestionsBox.style.display = 'none';
        });
        
        suggestionsBox.appendChild(item);
    });
    
    suggestionsBox.style.display = 'block';
}

// Flight search form submission
document.getElementById('flightSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    
    searchFlights(params);
});

function searchFlights(params) {
    // Show loading
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('initialState').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('resultsHeader').style.display = 'none';
    document.getElementById('filtersSection').style.display = 'none';
    document.getElementById('flightResults').innerHTML = '';

    fetch(`/api/flights/search?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingSpinner').style.display = 'none';
            
            if (data.success && data.data && data.data.length > 0) {
                flightResults = data.data;
                originalFlightResults = [...data.data]; // Store copy of original results
                currentDictionaries = data.dictionaries;
                displayFlightResults(data.data, data.dictionaries);
                document.getElementById('resultsHeader').style.display = 'flex';
                document.getElementById('filtersSection').style.display = 'block';
                document.getElementById('resultsCount').textContent = `${data.data.length} flights found`;
            } else {
                document.getElementById('emptyState').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Flight search error:', error);
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            alert('Failed to search flights. Please try again.');
        });
}

function displayFlightResults(flights, dictionaries, page = 1) {
    currentPage = page;
    totalResults = flights.length;
    
    // Calculate pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedFlights = flights.slice(startIndex, endIndex);
    const totalPages = Math.ceil(totalResults / itemsPerPage);
    
    const container = document.getElementById('flightResults');
    container.innerHTML = '';

    paginatedFlights.forEach(flight => {
        const offerHtml = renderFlightOffer(flight, dictionaries);
        container.innerHTML += offerHtml;
    });
    
    // Add pagination controls
    if (totalPages > 1) {
        const paginationHtml = createPagination(currentPage, totalPages);
        container.innerHTML += paginationHtml;
    }
}

function createPagination(currentPage, totalPages) {
    let html = `
        <div class="col-12">
            <nav aria-label="Flight results pagination">
                <ul class="pagination justify-content-center mt-4">
    `;
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    `;
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a></li>`;
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    `;
    
    html += `
                </ul>
            </nav>
            <div class="text-center text-muted mb-4">
                Showing ${(currentPage - 1) * itemsPerPage + 1} to ${Math.min(currentPage * itemsPerPage, totalResults)} of ${totalResults} flights
            </div>
        </div>
    `;
    
    return html;
}

function changePage(page) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    displayFlightResults(flightResults, currentDictionaries, page);
}

function renderFlightOffer(offer, dictionaries) {
    const price = offer.price.total;
    const currency = offer.price.currency;
    const itinerary = offer.itineraries[0];
    const segments = itinerary.segments;
    const firstSegment = segments[0];
    const lastSegment = segments[segments.length - 1];
    
    const departureTime = new Date(firstSegment.departure.at);
    const arrivalTime = new Date(lastSegment.arrival.at);
    const stops = segments.length - 1;
    
    const duration = itinerary.duration.replace('PT', '').replace('H', 'h ').replace('M', 'm');
    
    const carrierCode = firstSegment.carrierCode;
    const airlineName = dictionaries?.carriers?.[carrierCode] || carrierCode;
    
    const offerHash = generateOfferHash(offer);
    
    const bookingUrl = '{{ auth()->check() ? "/customer/flights/book/" : "/customer/login" }}' + ({{ auth()->check() ? 'true' : 'false' }} ? offerHash : '');
    
    return `
        <div class="col-12">
            <div class="card flight-card shadow-sm hover-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="flight-info">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="airline-logo me-3">
                                        <i class="fas fa-plane-departure fa-2x text-primary"></i>
                                    </div>
                                    <div class="airline-details">
                                        <h5 class="airline-name mb-1">${airlineName}</h5>
                                        <small class="text-muted">${offer.travelerPricings?.[0]?.fareDetailsBySegment?.[0]?.cabin || 'Economy'}</small>
                                    </div>
                                    ${stops === 0 ? '<span class="badge bg-success ms-3">Direct Flight</span>' : `<span class="badge bg-warning ms-3">${stops} Stop${stops > 1 ? 's' : ''}</span>`}
                                </div>
                                <div class="flight-route">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 text-center">
                                            <div class="departure-info">
                                                <h4 class="time mb-0">${departureTime.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}</h4>
                                                <p class="location mb-0">${firstSegment.departure.iataCode}</p>
                                                <small class="text-muted">${departureTime.toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="flight-path text-center">
                                                <div class="path-line position-relative">
                                                    <i class="fas fa-plane text-primary"></i>
                                                    <div class="duration-badge">
                                                        <small class="bg-light px-2 py-1 rounded text-muted">${duration}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="arrival-info">
                                                <h4 class="time mb-0">${arrivalTime.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}</h4>
                                                <p class="location mb-0">${lastSegment.arrival.iataCode}</p>
                                                <small class="text-muted">${arrivalTime.toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="flight-booking text-center">
                                <div class="price-info mb-3">
                                    <span class="price-label text-muted">Starting from</span>
                                    <h3 class="price text-success mb-0">${currency} ${parseFloat(price).toFixed(2)}</h3>
                                    <small class="text-muted">per person</small>
                                </div>
                                <div class="booking-actions d-grid gap-2">
                                    <button class="btn btn-primary btn-md" data-offer='${JSON.stringify(offer)}' onclick="handleBooking(this)">
                                        <i class="fas fa-shopping-cart me-2"></i>Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateOfferHash(offer) {
    // Use offer ID or create a hash based on key fields
    return btoa(JSON.stringify({
        id: offer.id,
        price: offer.price,
        segments: offer.itineraries[0].segments.map(s => ({
            departure: s.departure.iataCode,
            arrival: s.arrival.iataCode,
            at: s.departure.at
        }))
    })).replace(/[^a-zA-Z0-9]/g, '').substring(0, 32);
}

function handleBooking(button) {
    const offer = JSON.parse(button.dataset.offer);
    const offerHash = generateOfferHash(offer);
    
    // Store offer in sessionStorage
    sessionStorage.setItem(`offer_${offerHash}`, JSON.stringify(offer));
    
    // Redirect to booking page (accessible to both guests and authenticated users)
    window.location.href = `/customer/flights/book/${offerHash}`;
}

function applyFilters() {
    // Start with original results
    let filteredResults = [...originalFlightResults];
    
    // Apply price filter
    const priceFilter = document.getElementById('filterPrice')?.value;
    if (priceFilter && priceFilter !== '') {
        const [min, max] = priceFilter.split('-').map(p => p === '' ? Infinity : parseFloat(p));
        filteredResults = filteredResults.filter(flight => {
            const price = parseFloat(flight.price.total);
            return price >= (min || 0) && price <= (max || Infinity);
        });
    }
    
    // Apply stops filter
    const stopsFilter = document.getElementById('filterStops')?.value;
    if (stopsFilter !== '' && stopsFilter !== null) {
        const maxStops = parseInt(stopsFilter);
        filteredResults = filteredResults.filter(flight => {
            const stops = flight.itineraries[0].segments.length - 1;
            return stops <= maxStops;
        });
    }
    
    // Apply sorting
    const sortBy = document.getElementById('sortBy')?.value;
    if (sortBy === 'price') {
        filteredResults.sort((a, b) => parseFloat(a.price.total) - parseFloat(b.price.total));
    } else if (sortBy === 'duration') {
        filteredResults.sort((a, b) => {
            const durationA = parseDuration(a.itineraries[0].duration);
            const durationB = parseDuration(b.itineraries[0].duration);
            return durationA - durationB;
        });
    }
    
    // Update flightResults with filtered data
    flightResults = filteredResults;
    
    // Update clear filters button visibility
    const hasActiveFilters = priceFilter || stopsFilter !== '' || sortBy;
    const clearBtn = document.getElementById('clearFilters');
    if (clearBtn) {
        if (hasActiveFilters) {
            clearBtn.classList.remove('btn-outline-secondary');
            clearBtn.classList.add('btn-warning');
        } else {
            clearBtn.classList.remove('btn-warning');
            clearBtn.classList.add('btn-outline-secondary');
        }
    }
    
    // Reset to page 1 when filters change
    displayFlightResults(filteredResults, currentDictionaries, 1);
    
    // Update results count
    const resultsText = filteredResults.length === originalFlightResults.length 
        ? `${filteredResults.length} flights found`
        : `${filteredResults.length} of ${originalFlightResults.length} flights`;
    document.getElementById('resultsCount').textContent = resultsText;
}

function parseDuration(duration) {
    const matches = duration.match(/(\d+)H|(\d+)M/g) || [];
    let minutes = 0;
    matches.forEach(match => {
        if (match.includes('H')) minutes += parseInt(match) * 60;
        if (match.includes('M')) minutes += parseInt(match);
    });
    return minutes;
}

function clearAllFilters() {
    // Reset all filter dropdowns
    if (document.getElementById('sortBy')) {
        document.getElementById('sortBy').value = '';
    }
    if (document.getElementById('filterStops')) {
        document.getElementById('filterStops').value = '';
    }
    if (document.getElementById('filterPrice')) {
        document.getElementById('filterPrice').value = '';
    }
    
    // Reapply filters (which will show all results)
    applyFilters();
}

// Set min date for departure
const today = new Date().toISOString().split('T')[0];
document.getElementById('departureDate').setAttribute('min', today);

// Update return date min when departure changes
document.getElementById('departureDate').addEventListener('change', function() {
    document.getElementById('returnDate').setAttribute('min', this.value);
});

// Initialize autocomplete
setupAirportAutocomplete('originInput', 'originSuggestions');
setupAirportAutocomplete('destinationInput', 'destinationSuggestions');

// Initialize filters (after DOM is loaded)
if (document.getElementById('sortBy')) {
    document.getElementById('sortBy').addEventListener('change', applyFilters);
}
if (document.getElementById('filterStops')) {
    document.getElementById('filterStops').addEventListener('change', applyFilters);
}
if (document.getElementById('filterPrice')) {
    document.getElementById('filterPrice').addEventListener('change', applyFilters);
}
</script>
@endpush
