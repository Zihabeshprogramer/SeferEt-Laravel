<!-- Transport Search Modal -->
<div class="modal fade" id="transportSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bus text-primary me-2"></i>
                    Search Transport Services
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Search Form -->
                <div class="search-form-container mb-4">
                    <form id="transportSearchForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="transportFrom" class="form-label fw-bold">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" id="transportFrom" 
                                       placeholder="Departure location">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="transportTo" class="form-label fw-bold">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" id="transportTo" 
                                       placeholder="Arrival location">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="transportDate" class="form-label fw-bold">Date</label>
                            <input type="date" class="form-control" id="transportDate">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="transportPassengers" class="form-label fw-bold">Passengers</label>
                            <select class="form-select" id="transportPassengers">
                                <option value="1">1 Passenger</option>
                                <option value="2" selected>2 Passengers</option>
                                <option value="3">3 Passengers</option>
                                <option value="4">4 Passengers</option>
                                <option value="5">5-10 Passengers</option>
                                <option value="10">10+ Passengers</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="transportType" class="form-label fw-bold">Vehicle Type</label>
                            <select class="form-select" id="transportType">
                                <option value="">Any Type</option>
                                <option value="bus">Bus</option>
                                <option value="minibus">Minibus</option>
                                <option value="van">Van</option>
                                <option value="car">Private Car</option>
                                <option value="luxury_car">Luxury Car</option>
                                <option value="suv">SUV</option>
                                <option value="coach">Coach</option>
                                <option value="shuttle">Shuttle</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="transportTime" class="form-label fw-bold">Preferred Time</label>
                            <select class="form-select" id="transportTime">
                                <option value="">Any Time</option>
                                <option value="morning">Morning (6AM-12PM)</option>
                                <option value="afternoon">Afternoon (12PM-6PM)</option>
                                <option value="evening">Evening (6PM-10PM)</option>
                                <option value="flexible">Flexible</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="transportDuration" class="form-label fw-bold">Max Duration</label>
                            <select class="form-select" id="transportDuration">
                                <option value="">Any Duration</option>
                                <option value="1">Up to 1 hour</option>
                                <option value="2">Up to 2 hours</option>
                                <option value="4">Up to 4 hours</option>
                                <option value="8">Up to 8 hours</option>
                                <option value="12">Up to 12 hours</option>
                            </select>
                        </div>
                    </form>
                </div>
                
                <!-- Advanced Filters (Collapsible) -->
                <div class="accordion accordion-flush mb-4" id="transportFiltersAccordion">
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#transportFilters">
                                <i class="fas fa-filter me-2"></i>
                                Advanced Filters
                            </button>
                        </div>
                        <div id="transportFilters" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="transportPriceRange" class="form-label fw-bold">Price Range</label>
                                        <select class="form-select" id="transportPriceRange">
                                            <option value="">Any Price</option>
                                            <option value="0-100">₺0 - ₺100</option>
                                            <option value="100-300">₺100 - ₺300</option>
                                            <option value="300-500">₺300 - ₺500</option>
                                            <option value="500-1000">₺500 - ₺1,000</option>
                                            <option value="1000+">₺1,000+</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="transportCompany" class="form-label fw-bold">Preferred Company</label>
                                        <select class="form-select" id="transportCompany">
                                            <option value="">Any Company</option>
                                            <option value="metro">Metro Turizm</option>
                                            <option value="pamukkale">Pamukkale Turizm</option>
                                            <option value="ulusoy">Ulusoy</option>
                                            <option value="kamil">Kamil Koç</option>
                                            <option value="varan">Varan</option>
                                            <option value="nilüfer">Nilüfer Turizm</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="transportSortBy" class="form-label fw-bold">Sort By</label>
                                        <select class="form-select" id="transportSortBy">
                                            <option value="price">Price: Low to High</option>
                                            <option value="duration">Duration: Shortest</option>
                                            <option value="departure">Departure Time</option>
                                            <option value="rating">Company Rating</option>
                                            <option value="comfort">Comfort Level</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Features & Amenities</label>
                                        <div class="amenities-checkboxes">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="transportWifi" value="wifi">
                                                <label class="form-check-label" for="transportWifi">
                                                    <i class="fas fa-wifi text-info me-1"></i> WiFi
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="transportAc" value="ac">
                                                <label class="form-check-label" for="transportAc">
                                                    <i class="fas fa-snowflake text-primary me-1"></i> Air Conditioning
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="transportTv" value="tv">
                                                <label class="form-check-label" for="transportTv">
                                                    <i class="fas fa-tv text-secondary me-1"></i> Entertainment System
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="transportUsb" value="usb">
                                                <label class="form-check-label" for="transportUsb">
                                                    <i class="fas fa-plug text-warning me-1"></i> USB Charging
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="transportBathroom" value="bathroom">
                                                <label class="form-check-label" for="transportBathroom">
                                                    <i class="fas fa-restroom text-info me-1"></i> Onboard Bathroom
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="transportRefreshments" value="refreshments">
                                                <label class="form-check-label" for="transportRefreshments">
                                                    <i class="fas fa-coffee text-brown me-1"></i> Refreshments
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Service Type</label>
                                        <div class="service-type-radios">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="transportAny" name="serviceType" value="" checked>
                                                <label class="form-check-label" for="transportAny">Any Service</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="transportScheduled" name="serviceType" value="scheduled">
                                                <label class="form-check-label" for="transportScheduled">
                                                    <i class="fas fa-clock text-primary me-1"></i> Scheduled Service
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="transportPrivate" name="serviceType" value="private">
                                                <label class="form-check-label" for="transportPrivate">
                                                    <i class="fas fa-user-tie text-success me-1"></i> Private Transfer
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="transportShared" name="serviceType" value="shared">
                                                <label class="form-check-label" for="transportShared">
                                                    <i class="fas fa-users text-info me-1"></i> Shared Transfer
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="transportShuttle" name="serviceType" value="shuttle">
                                                <label class="form-check-label" for="transportShuttle">
                                                    <i class="fas fa-shuttle-van text-warning me-1"></i> Airport Shuttle
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Loading Indicator -->
                <div id="transportSearchLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Searching for transport services...</p>
                </div>
                
                <!-- Search Results -->
                <div id="transportSearchResults" class="search-results-container">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h6>Start Your Transport Search</h6>
                        <p>Enter departure and arrival locations to find available transport services</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="transportClearFilters">
                    <i class="fas fa-undo me-1"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Transport search modal specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Set default date
    const dateInput = document.getElementById('transportDate');
    if (dateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.value = tomorrow.toISOString().split('T')[0];
    }
    
    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('transportClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // Reset all form fields except from/to locations and date
            document.getElementById('transportPassengers').value = '2';
            document.getElementById('transportType').value = '';
            document.getElementById('transportTime').value = '';
            document.getElementById('transportDuration').value = '';
            document.getElementById('transportPriceRange').value = '';
            document.getElementById('transportCompany').value = '';
            document.getElementById('transportSortBy').value = 'price';
            
            // Reset checkboxes and radio buttons
            document.querySelectorAll('#transportFilters input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
            document.querySelector('#transportFilters input[type="radio"][value=""]').checked = true;
            
            // Trigger new search if from and to are filled
            if (document.getElementById('transportFrom').value.trim() && 
                document.getElementById('transportTo').value.trim()) {
                document.getElementById('transportSearchForm').dispatchEvent(new Event('submit'));
            }
        });
    }
    
    // Swap from and to locations
    const swapBtn = document.createElement('button');
    swapBtn.type = 'button';
    swapBtn.className = 'btn btn-outline-secondary btn-sm position-absolute';
    swapBtn.style.cssText = 'right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;';
    swapBtn.innerHTML = '<i class="fas fa-exchange-alt"></i>';
    swapBtn.title = 'Swap departure and arrival locations';
    
    const toGroup = document.getElementById('transportTo').closest('.col-md-3');
    toGroup.style.position = 'relative';
    toGroup.appendChild(swapBtn);
    
    swapBtn.addEventListener('click', function() {
        const from = document.getElementById('transportFrom').value;
        const to = document.getElementById('transportTo').value;
        
        document.getElementById('transportFrom').value = to;
        document.getElementById('transportTo').value = from;
    });
    
    // Auto-suggest popular routes
    const popularRoutes = [
        { from: 'Istanbul', to: 'Ankara' },
        { from: 'Istanbul', to: 'Izmir' },
        { from: 'Ankara', to: 'Antalya' },
        { from: 'Istanbul', to: 'Cappadocia' },
        { from: 'Izmir', to: 'Pamukkale' },
        { from: 'Antalya', to: 'Kas' },
        { from: 'Istanbul', to: 'Bursa' },
        { from: 'Ankara', to: 'Konya' }
    ];
    
    // Add popular routes quick select
    const quickRoutesContainer = document.createElement('div');
    quickRoutesContainer.className = 'popular-routes mt-2';
    quickRoutesContainer.innerHTML = `
        <small class="text-muted">Popular routes:</small><br>
        ${popularRoutes.map(route => 
            `<button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1" 
                     onclick="setRoute('${route.from}', '${route.to}')">${route.from} → ${route.to}</button>`
        ).join('')}
    `;
    
    document.getElementById('transportSearchForm').appendChild(quickRoutesContainer);
});

function setRoute(from, to) {
    document.getElementById('transportFrom').value = from;
    document.getElementById('transportTo').value = to;
}
</script>
