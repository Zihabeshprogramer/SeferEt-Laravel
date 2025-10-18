<!-- Flight Search Modal -->
<div class="modal fade" id="flightSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plane text-primary me-2"></i>
                    Search Flights
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Search Form -->
                <div class="search-form-container mb-4">
                    <form id="flightSearchForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="flightDeparture" class="form-label fw-bold">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-departure"></i></span>
                                <input type="text" class="form-control" id="flightDeparture" 
                                       placeholder="Departure city or airport">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="flightArrival" class="form-label fw-bold">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-arrival"></i></span>
                                <input type="text" class="form-control" id="flightArrival" 
                                       placeholder="Arrival city or airport">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="flightDepartureDate" class="form-label fw-bold">Departure</label>
                            <input type="date" class="form-control" id="flightDepartureDate">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="flightReturnDate" class="form-label fw-bold">Return</label>
                            <input type="date" class="form-control" id="flightReturnDate">
                            <small class="form-text text-muted">Optional for one-way</small>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="flightPassengers" class="form-label fw-bold">Passengers</label>
                            <select class="form-select" id="flightPassengers">
                                <option value="1">1 Passenger</option>
                                <option value="2" selected>2 Passengers</option>
                                <option value="3">3 Passengers</option>
                                <option value="4">4 Passengers</option>
                                <option value="5">5+ Passengers</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="flightClass" class="form-label fw-bold">Class</label>
                            <select class="form-select" id="flightClass">
                                <option value="economy" selected>Economy</option>
                                <option value="premium_economy">Premium Economy</option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="flightType" class="form-label fw-bold">Trip Type</label>
                            <select class="form-select" id="flightType">
                                <option value="round_trip" selected>Round Trip</option>
                                <option value="one_way">One Way</option>
                                <option value="multi_city">Multi-City</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="flightAirline" class="form-label fw-bold">Preferred Airline</label>
                            <select class="form-select" id="flightAirline">
                                <option value="">Any Airline</option>
                                <option value="TK">Turkish Airlines</option>
                                <option value="PC">Pegasus</option>
                                <option value="XQ">SunExpress</option>
                                <option value="LH">Lufthansa</option>
                                <option value="EK">Emirates</option>
                                <option value="QR">Qatar Airways</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i> Search Flights
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Advanced Filters (Collapsible) -->
                <div class="accordion accordion-flush mb-4" id="flightFiltersAccordion">
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#flightFilters">
                                <i class="fas fa-filter me-2"></i>
                                Advanced Filters
                            </button>
                        </div>
                        <div id="flightFilters" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="flightPriceRange" class="form-label fw-bold">Price Range</label>
                                        <select class="form-select" id="flightPriceRange">
                                            <option value="">Any Price</option>
                                            <option value="0-500">₺0 - ₺500</option>
                                            <option value="500-1000">₺500 - ₺1,000</option>
                                            <option value="1000-2000">₺1,000 - ₺2,000</option>
                                            <option value="2000-5000">₺2,000 - ₺5,000</option>
                                            <option value="5000+">₺5,000+</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="flightDuration" class="form-label fw-bold">Max Duration</label>
                                        <select class="form-select" id="flightDuration">
                                            <option value="">Any Duration</option>
                                            <option value="2">Up to 2 hours</option>
                                            <option value="4">Up to 4 hours</option>
                                            <option value="8">Up to 8 hours</option>
                                            <option value="12">Up to 12 hours</option>
                                            <option value="24">Up to 24 hours</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="flightStops" class="form-label fw-bold">Stops</label>
                                        <select class="form-select" id="flightStops">
                                            <option value="">Any</option>
                                            <option value="0">Non-stop</option>
                                            <option value="1">1 Stop</option>
                                            <option value="2">2+ Stops</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Departure Time</label>
                                        <div class="time-preferences">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flightMorning" value="morning">
                                                <label class="form-check-label" for="flightMorning">
                                                    <i class="fas fa-sun text-warning me-1"></i> Morning (6AM-12PM)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flightAfternoon" value="afternoon">
                                                <label class="form-check-label" for="flightAfternoon">
                                                    <i class="fas fa-sun text-orange me-1"></i> Afternoon (12PM-6PM)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flightEvening" value="evening">
                                                <label class="form-check-label" for="flightEvening">
                                                    <i class="fas fa-moon text-info me-1"></i> Evening (6PM-12AM)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Preferences</label>
                                        <div class="flight-preferences">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flightRefundable" value="refundable">
                                                <label class="form-check-label" for="flightRefundable">
                                                    <i class="fas fa-undo text-success me-1"></i> Refundable
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flightBaggage" value="baggage">
                                                <label class="form-check-label" for="flightBaggage">
                                                    <i class="fas fa-suitcase text-primary me-1"></i> Baggage Included
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flightWifi" value="wifi">
                                                <label class="form-check-label" for="flightWifi">
                                                    <i class="fas fa-wifi text-info me-1"></i> WiFi Available
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="flightSortBy" class="form-label fw-bold">Sort By</label>
                                        <select class="form-select" id="flightSortBy">
                                            <option value="price">Price: Low to High</option>
                                            <option value="duration">Duration: Shortest</option>
                                            <option value="departure">Departure Time</option>
                                            <option value="arrival">Arrival Time</option>
                                            <option value="rating">Airline Rating</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Loading Indicator -->
                <div id="flightSearchLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Searching for flights...</p>
                </div>
                
                <!-- Search Results -->
                <div id="flightSearchResults" class="search-results-container">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h6>Start Your Flight Search</h6>
                        <p>Enter departure and arrival cities to find available flights</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="flightClearFilters">
                    <i class="fas fa-undo me-1"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Flight search modal specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const departureInput = document.getElementById('flightDepartureDate');
    const returnInput = document.getElementById('flightReturnDate');
    
    if (departureInput && returnInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const weekLater = new Date();
        weekLater.setDate(weekLater.getDate() + 8);
        
        departureInput.value = tomorrow.toISOString().split('T')[0];
        returnInput.value = weekLater.toISOString().split('T')[0];
        
        // Validate return is after departure
        departureInput.addEventListener('change', function() {
            const departureDate = new Date(this.value);
            const returnDate = new Date(returnInput.value);
            
            if (returnDate <= departureDate) {
                const newReturn = new Date(departureDate);
                newReturn.setDate(newReturn.getDate() + 1);
                returnInput.value = newReturn.toISOString().split('T')[0];
            }
        });
    }
    
    // Handle flight type changes
    const flightTypeSelect = document.getElementById('flightType');
    if (flightTypeSelect) {
        flightTypeSelect.addEventListener('change', function() {
            const returnField = returnInput.closest('.col-md-2');
            if (this.value === 'one_way') {
                returnField.style.display = 'none';
                returnInput.required = false;
            } else {
                returnField.style.display = 'block';
                returnInput.required = false;
            }
        });
    }
    
    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('flightClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // Reset all form fields except departure/arrival cities and dates
            document.getElementById('flightPassengers').value = '2';
            document.getElementById('flightClass').value = 'economy';
            document.getElementById('flightType').value = 'round_trip';
            document.getElementById('flightAirline').value = '';
            document.getElementById('flightPriceRange').value = '';
            document.getElementById('flightDuration').value = '';
            document.getElementById('flightStops').value = '';
            document.getElementById('flightSortBy').value = 'price';
            
            // Reset checkboxes
            document.querySelectorAll('#flightFilters input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
            // Trigger new search if departure and arrival are filled
            if (document.getElementById('flightDeparture').value.trim() && 
                document.getElementById('flightArrival').value.trim()) {
                document.getElementById('flightSearchForm').dispatchEvent(new Event('submit'));
            }
        });
    }
    
    // Swap departure and arrival
    const swapBtn = document.createElement('button');
    swapBtn.type = 'button';
    swapBtn.className = 'btn btn-outline-secondary btn-sm position-absolute';
    swapBtn.style.cssText = 'right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;';
    swapBtn.innerHTML = '<i class="fas fa-exchange-alt"></i>';
    swapBtn.title = 'Swap departure and arrival';
    
    const arrivalGroup = document.getElementById('flightArrival').closest('.col-md-3');
    arrivalGroup.style.position = 'relative';
    arrivalGroup.appendChild(swapBtn);
    
    swapBtn.addEventListener('click', function() {
        const departure = document.getElementById('flightDeparture').value;
        const arrival = document.getElementById('flightArrival').value;
        
        document.getElementById('flightDeparture').value = arrival;
        document.getElementById('flightArrival').value = departure;
    });
});
</script>
