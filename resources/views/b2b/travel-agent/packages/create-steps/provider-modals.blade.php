<!-- Flight Search Modal -->
<div class="modal fade" id="flightSearchModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plane me-2"></i>
                    Search Flights
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Flight search form -->
                <div id="flightSearchForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-departure"></i></span>
                                <input type="text" class="form-control" id="flightOrigin" placeholder="Departure city or airport">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-arrival"></i></span>
                                <input type="text" class="form-control" id="flightDestination" placeholder="Arrival city or airport">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Departure Date</label>
                            <input type="date" class="form-control" id="flightDeparture">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Return Date</label>
                            <input type="date" class="form-control" id="flightReturn" placeholder="Leave empty for one-way">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trip Type</label>
                            <select class="form-control" id="flightTripType">
                                <option value="round_trip">Round Trip</option>
                                <option value="one_way">One Way</option>
                                <option value="multi_city">Multi-City</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Passengers</label>
                            <select class="form-control" id="flightPassengers">
                                <option value="1">1 Passenger</option>
                                <option value="2">2 Passengers</option>
                                <option value="3">3 Passengers</option>
                                <option value="4">4 Passengers</option>
                                <option value="5">5+ Passengers</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select class="form-control" id="flightClass">
                                <option value="economy">Economy</option>
                                <option value="premium_economy">Premium Economy</option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Airlines</label>
                            <select class="form-control" id="flightAirline">
                                <option value="">Any Airline</option>
                                <option value="turkish_airlines">Turkish Airlines</option>
                                <option value="emirates">Emirates</option>
                                <option value="lufthansa">Lufthansa</option>
                                <option value="qatar_airways">Qatar Airways</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-info d-block w-100" onclick="searchFlights()">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div id="flightSearchResults">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-plane fa-2x mb-2"></i>
                        <p>Enter search criteria above to find flights</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transport Search Modal -->
<div class="modal fade" id="transportSearchModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-bus me-2"></i>
                    Search Transport
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Transport search form -->
                <div id="transportSearchForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pickup Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" id="transportPickup" placeholder="Pickup address or location">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Drop-off Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-flag-checkered"></i></span>
                                <input type="text" class="form-control" id="transportDropoff" placeholder="Destination address or location">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Service Date</label>
                            <input type="date" class="form-control" id="transportDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service Time</label>
                            <input type="time" class="form-control" id="transportTime">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service Type</label>
                            <select class="form-control" id="transportType">
                                <option value="">All Types</option>
                                <option value="airport_transfer">Airport Transfer</option>
                                <option value="city_transfer">City Transfer</option>
                                <option value="intercity_bus">Intercity Bus</option>
                                <option value="private_car">Private Car</option>
                                <option value="shuttle">Shuttle Service</option>
                                <option value="tour_bus">Tour Bus</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Passengers</label>
                            <select class="form-control" id="transportPassengers">
                                <option value="1">1 Person</option>
                                <option value="2">2 People</option>
                                <option value="3">3 People</option>
                                <option value="4">4 People</option>
                                <option value="5">5-8 People</option>
                                <option value="9">9+ People</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vehicle Type</label>
                            <select class="form-control" id="transportVehicle">
                                <option value="">Any Vehicle</option>
                                <option value="sedan">Sedan</option>
                                <option value="suv">SUV</option>
                                <option value="van">Van/Minibus</option>
                                <option value="bus">Bus</option>
                                <option value="luxury">Luxury Vehicle</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Luggage</label>
                            <select class="form-control" id="transportLuggage">
                                <option value="standard">Standard</option>
                                <option value="extra">Extra Luggage</option>
                                <option value="none">No Luggage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-success d-block w-100" onclick="searchTransport()">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div id="transportSearchResults">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-bus fa-2x mb-2"></i>
                        <p>Enter search criteria above to find transport services</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- External Hotel Form Modal -->
<div class="modal fade" id="externalHotelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Add External Hotel Provider
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="externalHotelForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" id="externalHotelName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provider Company</label>
                            <input type="text" class="form-control" id="externalHotelProvider" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Hotel Address</label>
                            <textarea class="form-control" id="externalHotelAddress" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="externalHotelEmail" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="externalHotelPhone" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Star Rating</label>
                            <select class="form-control" id="externalHotelStars">
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="externalHotelCheckin" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="externalHotelCheckout" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Estimated Price (per night)</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input type="number" class="form-control" id="externalHotelPrice" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rooms Needed</label>
                            <input type="number" class="form-control" id="externalHotelRooms" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="externalHotelNotes" rows="3" placeholder="Special requirements, amenities, etc."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addExternalHotel()">
                    <i class="fas fa-plus me-1"></i> Add Hotel & Request Approval
                </button>
            </div>
        </div>
    </div>
</div>

<!-- External Flight Form Modal -->
<div class="modal fade" id="externalFlightModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Add External Flight Provider
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="externalFlightForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Flight Number</label>
                            <input type="text" class="form-control" id="externalFlightNumber" placeholder="e.g., TK1234">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Airline/Provider</label>
                            <input type="text" class="form-control" id="externalFlightAirline" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">From (Origin)</label>
                            <input type="text" class="form-control" id="externalFlightOrigin" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">To (Destination)</label>
                            <input type="text" class="form-control" id="externalFlightDestination" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Departure Date</label>
                            <input type="date" class="form-control" id="externalFlightDeparture" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Departure Time</label>
                            <input type="time" class="form-control" id="externalFlightDepartureTime">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Flight Class</label>
                            <select class="form-control" id="externalFlightClass">
                                <option value="economy">Economy</option>
                                <option value="premium_economy">Premium Economy</option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Return Date (Optional)</label>
                            <input type="date" class="form-control" id="externalFlightReturn">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Return Time</label>
                            <input type="time" class="form-control" id="externalFlightReturnTime">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Passengers</label>
                            <input type="number" class="form-control" id="externalFlightPassengers" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="externalFlightEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="externalFlightPhone" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Estimated Price (total)</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input type="number" class="form-control" id="externalFlightPrice" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <select class="form-control" id="externalFlightCurrency">
                                <option value="TRY">Turkish Lira (₺)</option>
                                <option value="USD">US Dollar ($)</option>
                                <option value="EUR">Euro (€)</option>
                                <option value="GBP">British Pound (£)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="externalFlightNotes" rows="3" placeholder="Baggage allowance, special requirements, etc."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="addExternalFlight()">
                    <i class="fas fa-plus me-1"></i> Add Flight & Request Approval
                </button>
            </div>
        </div>
    </div>
</div>

<!-- External Transport Form Modal -->
<div class="modal fade" id="externalTransportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Add External Transport Provider
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="externalTransportForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="externalTransportCompany" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Type</label>
                            <select class="form-control" id="externalTransportType" required>
                                <option value="">Select Service Type</option>
                                <option value="airport_transfer">Airport Transfer</option>
                                <option value="city_transfer">City Transfer</option>
                                <option value="intercity_bus">Intercity Bus</option>
                                <option value="private_car">Private Car</option>
                                <option value="shuttle">Shuttle Service</option>
                                <option value="tour_bus">Tour Bus</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pickup Location</label>
                            <input type="text" class="form-control" id="externalTransportPickup" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Drop-off Location</label>
                            <input type="text" class="form-control" id="externalTransportDropoff" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Service Date</label>
                            <input type="date" class="form-control" id="externalTransportDate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service Time</label>
                            <input type="time" class="form-control" id="externalTransportTime" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (hours)</label>
                            <input type="number" class="form-control" id="externalTransportDuration" step="0.5" min="0.5">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Vehicle Type</label>
                            <select class="form-control" id="externalTransportVehicle">
                                <option value="sedan">Sedan</option>
                                <option value="suv">SUV</option>
                                <option value="van">Van/Minibus</option>
                                <option value="bus">Bus</option>
                                <option value="luxury">Luxury Vehicle</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Passengers</label>
                            <input type="number" class="form-control" id="externalTransportPassengers" value="1" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Luggage Capacity</label>
                            <select class="form-control" id="externalTransportLuggage">
                                <option value="standard">Standard</option>
                                <option value="extra">Extra Luggage</option>
                                <option value="none">No Luggage</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="externalTransportEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="externalTransportPhone" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Estimated Price (total)</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input type="number" class="form-control" id="externalTransportPrice" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <select class="form-control" id="externalTransportCurrency">
                                <option value="TRY">Turkish Lira (₺)</option>
                                <option value="USD">US Dollar ($)</option>
                                <option value="EUR">Euro (€)</option>
                                <option value="GBP">British Pound (£)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="externalTransportNotes" rows="3" placeholder="Special requirements, amenities, route details, etc."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="addExternalTransport()">
                    <i class="fas fa-plus me-1"></i> Add Transport & Request Approval
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global utility functions
function showError(message) {
    if (typeof toastr !== 'undefined') {
        toastr.error(message);
    } else {
        console.error(message);
        alert('Error: ' + message);
    }
}

function showSuccess(message) {
    if (typeof toastr !== 'undefined') {
        toastr.success(message);
    } else {

        alert(message);
    }
}

// Flight Search Functions
function searchFlights() {
    const searchData = {
        departure_airport: document.getElementById('flightOrigin').value,
        arrival_airport: document.getElementById('flightDestination').value,
        departure_date: document.getElementById('flightDeparture').value,
        return_date: document.getElementById('flightReturn').value,
        trip_type: document.getElementById('flightTripType').value,
        passengers: document.getElementById('flightPassengers').value,
        class: document.getElementById('flightClass').value,
        airline: document.getElementById('flightAirline').value,
        source: 'platform'
    };
    
    // Validate required fields
    if (!searchData.departure_airport || !searchData.arrival_airport || !searchData.departure_date) {
        showError('Please fill in all required flight search fields.');
        return;
    }
    
    // Show loading
    const resultsDiv = document.getElementById('flightSearchResults');
    resultsDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-info" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Searching for flights...</p>
        </div>
    `;
    
    // Make AJAX request
    fetch('{{ route("b2b.travel-agent.api.providers.search-flights") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayFlightResults(data.flights);
        } else {
            showError('Flight search failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Flight search error:', error);
        showError('Flight search failed. Please try again.');
    });
}

function displayFlightResults(flights) {
    const resultsDiv = document.getElementById('flightSearchResults');
    
    if (!flights || flights.length === 0) {
        resultsDiv.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-circle fa-2x text-warning mb-2"></i>
                <p>No flights found matching your criteria.</p>
                <button type="button" class="btn btn-secondary" onclick="searchFlights()">
                    <i class="fas fa-redo me-1"></i> Search Again
                </button>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    flights.forEach(flight => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 flight-result-card" data-flight-id="${flight.id}">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="airline-logo me-2">
                                <i class="fas fa-plane text-info"></i>
                            </div>
                            <div>
                                <h6 class="card-title mb-0">${flight.airline_name}</h6>
                                <small class="text-muted">${flight.flight_number || 'Flight'}</small>
                            </div>
                        </div>
                        
                        <div class="flight-route mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-center">
                                    <strong>${flight.origin_code || flight.origin}</strong>
                                    <div class="small text-muted">${flight.departure_time}</div>
                                </div>
                                <div class="flex-grow-1 text-center">
                                    <i class="fas fa-plane text-muted"></i>
                                    <div class="small text-muted">${flight.duration || 'Direct'}</div>
                                </div>
                                <div class="text-center">
                                    <strong>${flight.destination_code || flight.destination}</strong>
                                    <div class="small text-muted">${flight.arrival_time}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge bg-info">${flight.class}</span>
                                ${flight.stops ? `<span class="badge bg-secondary ms-1">${flight.stops} stops</span>` : ''}
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-success">${flight.currency} ${flight.estimated_price}</strong>
                                <small class="text-muted d-block">per person</small>
                            </div>
                            <button type="button" class="btn btn-info btn-sm" 
                                    onclick="requestFlightApproval(${flight.id}, '${flight.airline_name}', '${flight.flight_number}')">
                                <i class="fas fa-plus me-1"></i> Request
                            </button>
                        </div>
                        
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-building me-1"></i>Provider: ${flight.provider_name}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    resultsDiv.innerHTML = html;
}

// Transport Search Functions
function searchTransport() {
    const searchData = {
        pickup_location: document.getElementById('transportPickup').value,
        dropoff_location: document.getElementById('transportDropoff').value,
        service_date: document.getElementById('transportDate').value,
        service_time: document.getElementById('transportTime').value,
        service_type: document.getElementById('transportType').value,
        passengers: document.getElementById('transportPassengers').value,
        vehicle_type: document.getElementById('transportVehicle').value,
        luggage_requirements: document.getElementById('transportLuggage').value,
        source: 'platform'
    };
    
    // Validate required fields
    if (!searchData.pickup_location || !searchData.dropoff_location || !searchData.service_date) {
        showError('Please fill in all required transport search fields.');
        return;
    }
    
    // Show loading
    const resultsDiv = document.getElementById('transportSearchResults');
    resultsDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Searching for transport services...</p>
        </div>
    `;
    
    // Make AJAX request
    fetch('{{ route("b2b.travel-agent.api.providers.search-transport") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayTransportResults(data.transport_services);
        } else {
            showError('Transport search failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Transport search error:', error);
        showError('Transport search failed. Please try again.');
    });
}

function displayTransportResults(services) {
    const resultsDiv = document.getElementById('transportSearchResults');
    
    if (!services || services.length === 0) {
        resultsDiv.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-circle fa-2x text-warning mb-2"></i>
                <p>No transport services found matching your criteria.</p>
                <button type="button" class="btn btn-secondary" onclick="searchTransport()">
                    <i class="fas fa-redo me-1"></i> Search Again
                </button>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    services.forEach(service => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 transport-result-card" data-transport-id="${service.id}">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="transport-icon me-2">
                                <i class="fas ${getTransportIcon(service.service_type)} text-success"></i>
                            </div>
                            <div>
                                <h6 class="card-title mb-0">${service.company_name}</h6>
                                <small class="text-muted">${service.service_type_label}</small>
                            </div>
                        </div>
                        
                        <div class="transport-route mb-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                <small class="text-muted">${service.pickup_location}</small>
                            </div>
                            <div class="d-flex align-items-center mt-1">
                                <i class="fas fa-flag-checkered text-muted me-1"></i>
                                <small class="text-muted">${service.dropoff_location}</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge bg-success">${service.vehicle_type}</span>
                                <span class="badge bg-secondary ms-1">${service.passenger_capacity} seats</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-success">${service.currency} ${service.estimated_price}</strong>
                                <small class="text-muted d-block">total price</small>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" 
                                    onclick="requestTransportApproval(${service.id}, '${service.company_name}', '${service.service_type}')">
                                <i class="fas fa-plus me-1"></i> Request
                            </button>
                        </div>
                        
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-building me-1"></i>Provider: ${service.provider_name}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    resultsDiv.innerHTML = html;
}

function getTransportIcon(serviceType) {
    const iconMap = {
        'airport_transfer': 'fa-plane',
        'city_transfer': 'fa-car',
        'intercity_bus': 'fa-bus',
        'private_car': 'fa-car-side',
        'shuttle': 'fa-shuttle-van',
        'tour_bus': 'fa-bus'
    };
    return iconMap[serviceType] || 'fa-car';
}

// Request approval functions
function requestFlightApproval(flightId, airlineName, flightNumber) {
    if (!packageId) {
        showError('Please save your package first before requesting provider approvals.');
        return;
    }
    
    if (!confirm(`Request approval from ${airlineName} for ${flightNumber}?`)) {
        return;
    }
    
    const requestData = {
        package_id: packageId,
        service_type: 'flight',
        service_id: flightId,
        provider_id: null,
        request_details: {
            airline_name: airlineName,
            flight_number: flightNumber,
            origin: document.getElementById('flightOrigin').value,
            destination: document.getElementById('flightDestination').value,
            departure_date: document.getElementById('flightDeparture').value,
            return_date: document.getElementById('flightReturn').value,
            passengers: document.getElementById('flightPassengers').value,
            class: document.getElementById('flightClass').value
        },
        service_start_date: document.getElementById('flightDeparture').value,
        service_end_date: document.getElementById('flightReturn').value || document.getElementById('flightDeparture').value,
        travel_agent_notes: 'Flight booking request for package'
    };
    
    sendProviderRequest(requestData, 'flightSearchModal');
}

function requestTransportApproval(transportId, companyName, serviceType) {
    if (!packageId) {
        showError('Please save your package first before requesting provider approvals.');
        return;
    }
    
    if (!confirm(`Request approval from ${companyName} for ${serviceType} service?`)) {
        return;
    }
    
    const requestData = {
        package_id: packageId,
        service_type: 'transport',
        service_id: transportId,
        provider_id: null,
        request_details: {
            company_name: companyName,
            service_type: serviceType,
            pickup_location: document.getElementById('transportPickup').value,
            dropoff_location: document.getElementById('transportDropoff').value,
            service_date: document.getElementById('transportDate').value,
            service_time: document.getElementById('transportTime').value,
            passengers: document.getElementById('transportPassengers').value,
            vehicle_type: document.getElementById('transportVehicle').value
        },
        service_start_date: document.getElementById('transportDate').value,
        service_end_date: document.getElementById('transportDate').value,
        travel_agent_notes: 'Transport service request for package'
    };
    
    sendProviderRequest(requestData, 'transportSearchModal');
}

// External provider functions
function addExternalHotel() {
    const hotelName = document.getElementById('externalHotelName').value;
    const hotelProvider = document.getElementById('externalHotelProvider').value;

    // Generate a stable unique ID for this external provider entry
    const externalId = generateExternalId('hotel', `${hotelName}_${hotelProvider}`);

    // Prevent duplicate pending requests for this provider
    if (checkExistingRequest(externalId, 'hotel')) {
        return;
    }

    const formData = {
        package_id: packageId,
        service_type: 'hotel',
        service_id: externalId,
        provider_id: null,
        external_provider: true,
        request_details: {
            hotel_name: document.getElementById('externalHotelName').value,
            provider_company: document.getElementById('externalHotelProvider').value,
            address: document.getElementById('externalHotelAddress').value,
            contact_email: document.getElementById('externalHotelEmail').value,
            contact_phone: document.getElementById('externalHotelPhone').value,
            star_rating: document.getElementById('externalHotelStars').value,
            check_in: document.getElementById('externalHotelCheckin').value,
            check_out: document.getElementById('externalHotelCheckout').value,
            estimated_price: document.getElementById('externalHotelPrice').value,
            rooms_needed: document.getElementById('externalHotelRooms').value,
            additional_notes: document.getElementById('externalHotelNotes').value
        },
        service_start_date: document.getElementById('externalHotelCheckin').value,
        service_end_date: document.getElementById('externalHotelCheckout').value,
        travel_agent_notes: 'External hotel provider request'
    };
    
    sendProviderRequest(formData, 'externalHotelModal');
}

function addExternalFlight() {
    const flightNumber = document.getElementById('externalFlightNumber').value;
    const airlineName = document.getElementById('externalFlightAirline').value;

    // Generate a stable unique ID for this external flight
    const externalId = generateExternalId('flight', `${airlineName}_${flightNumber}`);

    // Prevent duplicate pending requests for this provider
    if (checkExistingRequest(externalId, 'flight')) {
        return;
    }

    const formData = {
        package_id: packageId,
        service_type: 'flight',
        service_id: externalId,
        provider_id: null,
        external_provider: true,
        request_details: {
            flight_number: document.getElementById('externalFlightNumber').value,
            airline_name: document.getElementById('externalFlightAirline').value,
            origin: document.getElementById('externalFlightOrigin').value,
            destination: document.getElementById('externalFlightDestination').value,
            departure_date: document.getElementById('externalFlightDeparture').value,
            departure_time: document.getElementById('externalFlightDepartureTime').value,
            return_date: document.getElementById('externalFlightReturn').value,
            return_time: document.getElementById('externalFlightReturnTime').value,
            flight_class: document.getElementById('externalFlightClass').value,
            passengers: document.getElementById('externalFlightPassengers').value,
            contact_email: document.getElementById('externalFlightEmail').value,
            contact_phone: document.getElementById('externalFlightPhone').value,
            estimated_price: document.getElementById('externalFlightPrice').value,
            currency: document.getElementById('externalFlightCurrency').value,
            additional_notes: document.getElementById('externalFlightNotes').value
        },
        service_start_date: document.getElementById('externalFlightDeparture').value,
        service_end_date: document.getElementById('externalFlightReturn').value || document.getElementById('externalFlightDeparture').value,
        travel_agent_notes: 'External flight provider request'
    };
    
    sendProviderRequest(formData, 'externalFlightModal');
}

function addExternalTransport() {
    const companyName = document.getElementById('externalTransportCompany').value;
    const serviceType = document.getElementById('externalTransportType').value;

    // Generate a stable unique ID for this external transport entry
    const externalId = generateExternalId('transport', `${companyName}_${serviceType}`);

    // Prevent duplicate pending requests for this provider
    if (checkExistingRequest(externalId, 'transport')) {
        return;
    }

    const formData = {
        package_id: packageId,
        service_type: 'transport',
        service_id: externalId,
        provider_id: null,
        external_provider: true,
        request_details: {
            company_name: document.getElementById('externalTransportCompany').value,
            service_type: document.getElementById('externalTransportType').value,
            pickup_location: document.getElementById('externalTransportPickup').value,
            dropoff_location: document.getElementById('externalTransportDropoff').value,
            service_date: document.getElementById('externalTransportDate').value,
            service_time: document.getElementById('externalTransportTime').value,
            duration: document.getElementById('externalTransportDuration').value,
            vehicle_type: document.getElementById('externalTransportVehicle').value,
            passengers: document.getElementById('externalTransportPassengers').value,
            luggage_capacity: document.getElementById('externalTransportLuggage').value,
            contact_email: document.getElementById('externalTransportEmail').value,
            contact_phone: document.getElementById('externalTransportPhone').value,
            estimated_price: document.getElementById('externalTransportPrice').value,
            currency: document.getElementById('externalTransportCurrency').value,
            additional_notes: document.getElementById('externalTransportNotes').value
        },
        service_start_date: document.getElementById('externalTransportDate').value,
        service_end_date: document.getElementById('externalTransportDate').value,
        travel_agent_notes: 'External transport provider request'
    };
    
    sendProviderRequest(formData, 'externalTransportModal');
}

// Generic function to send provider requests
function sendProviderRequest(requestData, modalId) {
    try {
        // Validate required fields
        if (!requestData.package_id) {
            throw new Error('Package ID is required');
        }
        if (!requestData.service_type) {
            throw new Error('Service type is required');
        }
        
        // For external providers, validate the request details
        if (requestData.external_provider && requestData.request_details) {
            const validationErrors = validateExternalProvider(
                requestData.service_type,
                requestData.request_details
            );
            if (validationErrors.length > 0) {
                throw new Error('Validation failed:\n' + validationErrors.join('\n'));
            }
        }

        // Transform old format to new ServiceRequest API format
        const serviceRequestData = {
            package_id: requestData.package_id,
            provider_id: requestData.service_id, // The service entity ID (hotel/flight/transport ID)
            provider_type: requestData.service_type,
            item_id: requestData.service_id,
            special_requirements: requestData.travel_agent_notes,
            expires_in_hours: 72, // Default 3 days expiry
            external_provider: requestData.external_provider || false
        };
    
    // Add quantity if available from request details
    if (requestData.request_details) {
        if (requestData.request_details.rooms_needed) {
            serviceRequestData.requested_quantity = parseInt(requestData.request_details.rooms_needed);
        } else if (requestData.request_details.passengers) {
            serviceRequestData.requested_quantity = parseInt(requestData.request_details.passengers);
        }
        
        // Store request details for external providers
        if (requestData.external_provider) {
            serviceRequestData.provider_details = requestData.request_details;
        }
    }
    
    fetch('{{ route("b2b.travel-agent.api.service-requests.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(serviceRequestData)
    })
    .then(response => response.json())
.then(data => {
        if (data.success) {
            // Prepare request info for both draft and UI
            const requestInfo = {
                request_id: data.data.id,
                request_uuid: data.data.uuid,
                request_status: data.data.status,
                request_created_at: data.data.created_at,
                request_expires_at: data.data.expires_at,
                external_provider: !!serviceRequestData.external_provider,
                provider_details: serviceRequestData.provider_details,
                requested_quantity: serviceRequestData.requested_quantity || null,
                updated_at: new Date().toISOString()
            };

            // First update package draft data
            const draftData = window.draftData?.data || {};
            const listKey = {
                'hotel': 'selected_hotels',
                'flight': 'selected_flights',
                'transport': 'selected_transport'
            }[serviceRequestData.provider_type] || 'selected_' + serviceRequestData.provider_type + 's';

            // Find and update provider in draft data
            if (draftData[listKey] && Array.isArray(draftData[listKey])) {
                const provider = draftData[listKey].find(p => p.id == serviceRequestData.provider_id);
                if (provider) {
                    provider.request_info = requestInfo;
                    provider.service_request_id = data.data.id;
                    provider.service_request_status = data.data.status;
                }
            }

            // Save updated draft data
            if (typeof window.saveDraftData === 'function') {
                window.saveDraftData();
            } else if (typeof saveDraftChanges === 'function') {
                saveDraftChanges();
            }

            // Update provider card in UI
            const provider = draftData[listKey]?.find(p => p.id == serviceRequestData.provider_id);
            if (provider) {
                provider.service_request = {
                    ...data.data,
                    provider_details: serviceRequestData.provider_details
                };
                updateProviderCardAfterRequest(provider.id, serviceRequestData.provider_type, data.data);
            }

            showSuccess('Service request sent successfully!');

            // Close the modal last, after all updates are done
            if (bootstrap.Modal.getInstance(document.getElementById(modalId))) {
                bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
            }

            // Re-render all providers to ensure consistent state
            if (typeof MergedProviderSelector !== 'undefined' && typeof MergedProviderSelector.renderAllProviders === 'function') {
                setTimeout(() => MergedProviderSelector.renderAllProviders(), 100);
            }
        } else {
            showError('Request failed: ' + (data.message || 'Unknown error'));
    })
    .catch(error => {
        console.error('Service request error:', error);
        showError('Service request failed. Please try again.');
    });
}

/**
 * Update provider card status after service request is sent
 */
function updateProviderCardAfterRequest(serviceId, providerType, requestData) {
    console.log('🔍 Looking for provider card to update:', {
        serviceId: serviceId,
        providerType: providerType,
        requestData: requestData
    });
    
    // Try multiple strategies to find the provider card
    let providerCard = null;
    
    // Strategy 1: Look for cards with data attributes
    const selectors = [
        `[data-provider-id="${serviceId}"]`,
        `[data-service-id="${serviceId}"]`,
        `[data-hotel-id="${serviceId}"]`,
        `[data-flight-id="${serviceId}"]`,
        `[data-transport-id="${serviceId}"]`,
        `.provider-card[data-id="${serviceId}"]`,
        `.hotel-card[data-id="${serviceId}"]`,
        `.flight-card[data-id="${serviceId}"]`,
        `.transport-card[data-id="${serviceId}"]`
    ];
    
    for (const selector of selectors) {
        providerCard = document.querySelector(selector);
        if (providerCard) {

            break;
        }
    }
    
    // Strategy 2: Look in selected providers containers
    if (!providerCard) {
        const containers = [
            '#selectedHotels',
            '#selectedFlights', 
            '#selectedTransport'
        ];
        
        for (const containerId of containers) {
            const container = document.querySelector(containerId);
            if (container) {
                const cards = container.querySelectorAll('.card, .provider-card');
                cards.forEach(card => {
                    // Look for any data attribute that matches our service ID
                    const attrs = card.attributes;
                    for (let i = 0; i < attrs.length; i++) {
                        if (attrs[i].name.startsWith('data-') && attrs[i].value == serviceId) {
                            providerCard = card;

                            break;
                        }
                    }
                    if (providerCard) return;
                });
            }
            if (providerCard) break;
        }
    }
    
    // Strategy 3: Look for button that was clicked (if it has onclick with the service ID)
    if (!providerCard) {
        const buttons = document.querySelectorAll('button[onclick]');
        buttons.forEach(btn => {
            if (btn.onclick && btn.onclick.toString().includes(serviceId)) {
                providerCard = btn.closest('.card, .provider-card');
                if (providerCard) {

                    return;
                }
            }
        });
    }
    
    if (!providerCard) {
        console.error('❌ Provider card not found! Available cards:');
        document.querySelectorAll('.card, .provider-card').forEach((card, index) => {
            console.log(`Card ${index}:`, card, 'Attributes:', [...card.attributes].map(a => `${a.name}="${a.value}"`));
        });
        return;
    }
    

    
    // Create the pending approval status
    const statusHTML = `
        <div class="alert alert-warning border-0 rounded mb-2 p-2">
            <div class="d-flex align-items-center">
                <i class="fas fa-clock text-warning me-2"></i>
                <strong class="small">Pending Approval</strong>
            </div>
            <div class="text-muted small mt-1">Request sent to provider. Awaiting response.</div>
        </div>
        <div class="mt-2 d-flex gap-1">
            <button class="btn btn-outline-info btn-sm" onclick="viewRequestStatus('${requestData?.uuid || 'unknown'}')">
                <i class="fas fa-eye"></i> View Status
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="cancelRequest('${requestData?.uuid || 'unknown'}')">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="btn btn-outline-primary btn-sm" onclick="contactProvider()">
                <i class="fas fa-phone"></i> Contact
            </button>
        </div>
    `;
    
    // Update or add the status section
    let statusSection = providerCard.querySelector('.provider-status, .approval-status, .service-request-status');
    if (statusSection) {
        statusSection.innerHTML = statusHTML;
    } else {
        // Add status section if it doesn't exist
        const cardBody = providerCard.querySelector('.card-body');
        if (cardBody) {
            const statusDiv = document.createElement('div');
            statusDiv.className = 'service-request-status mt-2';
            statusDiv.innerHTML = statusHTML;
            cardBody.appendChild(statusDiv);
        }
    }
    
    // Update or hide the original "Request Approval" button
    const requestButtons = providerCard.querySelectorAll('button');
    requestButtons.forEach(btn => {
        if (btn.textContent.includes('Request') && !btn.textContent.includes('View Status')) {
            btn.style.display = 'none'; // Hide the original request button
        }
    });
    

}

// Helper functions for the action buttons
function viewRequestStatus(requestId) {
    if (typeof showSuccess === 'function') {
        showSuccess(`Request ID: ${requestId} - Status: Pending. The provider will respond soon.`);
    } else {
        alert(`Request Status: Pending\nRequest ID: ${requestId}\n\nThe provider will respond to your request soon.`);
    }
}

function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this service request?')) {
        if (typeof showSuccess === 'function') {
            showSuccess('Request cancelled successfully.');
        } else {
            alert('Request cancelled successfully.');
        }
        // Here you could make an API call to actually cancel the request
    }
}

function contactProvider() {
    if (typeof showSuccess === 'function') {
        showSuccess('Provider contact information will be displayed here.');
    } else {
        alert('Provider contact information will be displayed here.');
    }
}

/**
 * Update provider card when request is approved
 */
function updateProviderCardApproved(serviceId, providerType, requestData) {

    
    // Find the provider card (using same strategy as before)
    let providerCard = null;
    const selectors = [
        `[data-provider-id="${serviceId}"]`,
        `[data-service-id="${serviceId}"]`,
        `[data-hotel-id="${serviceId}"]`,
        `[data-flight-id="${serviceId}"]`,
        `[data-transport-id="${serviceId}"]`
    ];
    
    for (const selector of selectors) {
        providerCard = document.querySelector(selector);
        if (providerCard) break;
    }
    
    if (!providerCard) {

        return;
    }
    
    // Create the approved status
    const approvedStatusHTML = `
        <div class="alert alert-success border-0 rounded mb-2 p-2">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="small">Approved</strong>
            </div>
            <div class="text-muted small mt-1">Provider has approved your request. Ready to proceed!</div>
        </div>
        <div class="mt-2 d-flex gap-1">
            <button class="btn btn-success btn-sm" onclick="viewApprovedRequest('${requestData?.uuid || 'unknown'}')">
                <i class="fas fa-check"></i> View Details
            </button>
            <button class="btn btn-outline-info btn-sm" onclick="downloadContract('${requestData?.uuid || 'unknown'}')">
                <i class="fas fa-download"></i> Contract
            </button>
        </div>
    `;
    
    // Update the status section
    let statusSection = providerCard.querySelector('.service-request-status');
    if (statusSection) {
        statusSection.innerHTML = approvedStatusHTML;

    }
}

/**
 * Update provider card when request is rejected
 */
function updateProviderCardRejected(serviceId, providerType, requestData, reason) {

    
    // Find the provider card
    let providerCard = null;
    const selectors = [
        `[data-provider-id="${serviceId}"]`,
        `[data-service-id="${serviceId}"]`,
        `[data-hotel-id="${serviceId}"]`,
        `[data-flight-id="${serviceId}"]`,
        `[data-transport-id="${serviceId}"]`
    ];
    
    for (const selector of selectors) {
        providerCard = document.querySelector(selector);
        if (providerCard) break;
    }
    
    if (!providerCard) {

        return;
    }
    
    // Create the rejected status
    const rejectedStatusHTML = `
        <div class="alert alert-danger border-0 rounded mb-2 p-2">
            <div class="d-flex align-items-center">
                <i class="fas fa-times-circle text-danger me-2"></i>
                <strong class="small">Rejected</strong>
            </div>
            <div class="text-muted small mt-1">Provider declined your request. ${reason || 'No reason provided.'}</div>
        </div>
        <div class="mt-2 d-flex gap-1">
            <button class="btn btn-outline-primary btn-sm" onclick="retryRequest('${serviceId}', '${providerType}')">
                <i class="fas fa-redo"></i> Try Again
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="findAlternative('${providerType}')">
                <i class="fas fa-search"></i> Find Alternative
            </button>
        </div>
    `;
    
    // Update the status section
    let statusSection = providerCard.querySelector('.service-request-status');
    if (statusSection) {
        statusSection.innerHTML = rejectedStatusHTML;

    }
}

// Helper functions for approved/rejected actions
function viewApprovedRequest(requestId) {
    if (typeof showSuccess === 'function') {
        showSuccess('Approved request details will be shown here.');
    } else {
        alert('Approved request details will be shown here.');
    }
}

function downloadContract(requestId) {
    if (typeof showSuccess === 'function') {
        showSuccess('Contract download will start here.');
    } else {
        alert('Contract download will start here.');
    }
}

function retryRequest(serviceId, providerType) {
    if (confirm('Do you want to send a new request to this provider?')) {
        if (typeof showSuccess === 'function') {
            showSuccess('New request will be created.');
        } else {
            alert('New request will be created.');
        }
    }
}

function findAlternative(providerType) {
    if (typeof showSuccess === 'function') {
        showSuccess(`Looking for alternative ${providerType} providers...`);
    } else {
        alert(`Looking for alternative ${providerType} providers...`);
    }
}

function updateProviderRequestInfo(providerId, providerType, requestInfo) {
    try {
        const listKey = {
            'hotel': 'selected_hotels',
            'flight': 'selected_flights',
            'transport': 'selected_transport'
        }[providerType] || ('selected_' + providerType + 's');
        
        const draftData = window.draftData?.data || {};
        
        if (draftData[listKey] && Array.isArray(draftData[listKey])) {
            const provider = draftData[listKey].find(p => p.id == providerId);
            if (provider) {
                // Update request_info structure
                provider.request_info = {
                    ...requestInfo,
                    updated_at: new Date().toISOString()
                };
                
                // Also update legacy fields for backward compatibility
                provider.service_request_id = requestInfo.request_id || requestInfo.id;
                provider.service_request_status = requestInfo.request_status || requestInfo.status;
                
                // Update provider's service_request for immediate UI updates
                if (requestInfo.request_id) {
                    provider.service_request = {
                        id: requestInfo.request_id,
                        uuid: requestInfo.request_uuid,
                        status: requestInfo.request_status,
                        created_at: requestInfo.request_created_at,
                        expires_at: requestInfo.request_expires_at,
                        requested_quantity: requestInfo.requested_quantity,
                        provider_details: requestInfo.provider_details
                    };
                }
                
                // Save updated draft data
                if (typeof window.saveDraftData === 'function') {
                    window.saveDraftData();
                } else if (typeof saveDraftChanges === 'function') {
                    saveDraftChanges();
                }
                

            } else {
                console.warn(`Provider ${providerId} not found in ${listKey}`);
            }
        } else {
            console.warn(`${listKey} not found in draft data`);
        }
    } catch (error) {
        console.error('Error updating provider request info:', error);
    }
}

function validateExternalProvider(type, data) {
    const errors = [];

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validatePhone(phone) {
        return /^[+]?[0-9\s-()]{8,}$/.test(phone);
    }

    // Common validations for all types
    if (data.contact_email && !validateEmail(data.contact_email)) {
        errors.push('Please enter a valid email address');
    }
    if (data.contact_phone && !validatePhone(data.contact_phone)) {
        errors.push('Please enter a valid phone number');
    }

    // Type-specific validations
    switch (type) {
        case 'hotel':
            if (!data.hotel_name?.trim()) errors.push('Hotel name is required');
            if (!data.provider_company?.trim()) errors.push('Provider company is required');
            if (!data.check_in) errors.push('Check-in date is required');
            if (!data.check_out) errors.push('Check-out date is required');
            if (data.check_in && data.check_out && new Date(data.check_out) <= new Date(data.check_in)) {
                errors.push('Check-out date must be after check-in date');
            }
            break;

        case 'flight':
            if (!data.airline_name?.trim()) errors.push('Airline name is required');
            if (!data.origin?.trim()) errors.push('Origin is required');
            if (!data.destination?.trim()) errors.push('Destination is required');
            if (data.origin === data.destination) errors.push('Origin and destination cannot be the same');
            if (!data.departure_date) errors.push('Departure date is required');
            break;

        case 'transport':
            if (!data.company_name?.trim()) errors.push('Company name is required');
            if (!data.pickup_location?.trim()) errors.push('Pickup location is required');
            if (!data.dropoff_location?.trim()) errors.push('Drop-off location is required');
            if (data.pickup_location === data.dropoff_location) errors.push('Pickup and drop-off locations cannot be the same');
            if (!data.service_date) errors.push('Service date is required');
            break;
    }

    return errors;
}

function generateExternalId(type, name = '') {
    const timestamp = Date.now();
    const safeName = (name || '').toString()
        .replace(/[^a-z0-9]/gi, '')
        .toLowerCase()
        .slice(0, 10);
    return `ext_${type}_${safeName}_${timestamp}`;
}

function checkExistingRequest(providerId, providerType) {

    
    // Get the list key based on provider type
    const listKey = {
        'hotel': 'selected_hotels',
        'flight': 'selected_flights',
        'transport': 'selected_transport'
    }[providerType] || ('selected_' + providerType + 's');
    
    // Get current draft data
    const draftData = window.draftData?.data || {};
    
    // Find the provider in the selected list
    if (draftData[listKey] && Array.isArray(draftData[listKey])) {
        const provider = draftData[listKey].find(p => p.id == providerId);
        if (provider?.request_info) {
            const request = provider.request_info;
            // Check if request is still pending and not expired
            if (request.request_status === 'pending') {
                const expiresAt = new Date(request.request_expires_at);
                if (expiresAt > new Date()) {
                    const message = `A pending request already exists for this ${providerType}.\n\n` +
                        `Request ID: ${request.request_id}\n` +
                        `Created: ${new Date(request.request_created_at).toLocaleString()}\n` +
                        `Expires: ${expiresAt.toLocaleString()}\n\n` +
                        'Please wait for response or cancel the existing request.';
                    showError(message);
                    return true;
                }
            }
        }
    }
    return false;
}

</script>
