<!-- Hotel Search Modal -->
<div class="modal fade" id="hotelSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bed text-primary me-2"></i>
                    Search Hotels
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Search Form -->
                <div class="search-form-container mb-4">
                    <form id="hotelSearchForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="hotelLocation" class="form-label fw-bold">Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" id="hotelLocation" 
                                   placeholder="Enter city or hotel name...">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hotelCheckin" class="form-label fw-bold">Check-in</label>
                            <input type="date" class="form-control" id="hotelCheckin">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hotelCheckout" class="form-label fw-bold">Check-out</label>
                            <input type="date" class="form-control" id="hotelCheckout">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="hotelRooms" class="form-label fw-bold">Rooms</label>
                            <select class="form-select" id="hotelRooms">
                                <option value="1">1 Room</option>
                                <option value="2">2 Rooms</option>
                                <option value="3">3 Rooms</option>
                                <option value="4">4 Rooms</option>
                                <option value="5">5+ Rooms</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hotelGuests" class="form-label fw-bold">Guests</label>
                            <select class="form-select" id="hotelGuests">
                                <option value="1">1 Guest</option>
                                <option value="2" selected>2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                                <option value="5">5+ Guests</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hotelStarRating" class="form-label fw-bold">Star Rating</label>
                            <select class="form-select" id="hotelStarRating">
                                <option value="">Any Rating</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars & Above</option>
                                <option value="3">3 Stars & Above</option>
                                <option value="2">2 Stars & Above</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hotelPriceRange" class="form-label fw-bold">Price Range</label>
                            <select class="form-select" id="hotelPriceRange">
                                <option value="">Any Price</option>
                                <option value="0-100">₺0 - ₺100</option>
                                <option value="100-300">₺100 - ₺300</option>
                                <option value="300-500">₺300 - ₺500</option>
                                <option value="500-1000">₺500 - ₺1000</option>
                                <option value="1000+">₺1000+</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i> Search Hotels
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Advanced Filters (Collapsible) -->
                <div class="accordion accordion-flush mb-4" id="hotelFiltersAccordion">
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#hotelFilters">
                                <i class="fas fa-filter me-2"></i>
                                Advanced Filters
                            </button>
                        </div>
                        <div id="hotelFilters" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Amenities</label>
                                        <div class="amenities-checkboxes">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="hotelWifi" value="wifi">
                                                <label class="form-check-label" for="hotelWifi">
                                                    <i class="fas fa-wifi me-1"></i> Free WiFi
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="hotelPool" value="pool">
                                                <label class="form-check-label" for="hotelPool">
                                                    <i class="fas fa-swimming-pool me-1"></i> Swimming Pool
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="hotelParking" value="parking">
                                                <label class="form-check-label" for="hotelParking">
                                                    <i class="fas fa-parking me-1"></i> Free Parking
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="hotelGym" value="gym">
                                                <label class="form-check-label" for="hotelGym">
                                                    <i class="fas fa-dumbbell me-1"></i> Fitness Center
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Property Type</label>
                                        <div class="property-type-radios">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="hotelTypeAny" name="hotelType" value="" checked>
                                                <label class="form-check-label" for="hotelTypeAny">Any Type</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="hotelTypeHotel" name="hotelType" value="hotel">
                                                <label class="form-check-label" for="hotelTypeHotel">Hotel</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="hotelTypeBoutique" name="hotelType" value="boutique">
                                                <label class="form-check-label" for="hotelTypeBoutique">Boutique Hotel</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="hotelTypeResort" name="hotelType" value="resort">
                                                <label class="form-check-label" for="hotelTypeResort">Resort</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="hotelSortBy" class="form-label fw-bold">Sort By</label>
                                        <select class="form-select" id="hotelSortBy">
                                            <option value="relevance">Relevance</option>
                                            <option value="price_low">Price: Low to High</option>
                                            <option value="price_high">Price: High to Low</option>
                                            <option value="rating">Guest Rating</option>
                                            <option value="distance">Distance from Center</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Loading Indicator -->
                <div id="hotelSearchLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Searching for hotels...</p>
                </div>
                
                <!-- Search Results -->
                <div id="hotelSearchResults" class="search-results-container">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h6>Start Your Hotel Search</h6>
                        <p>Enter location and dates to find available hotels</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="hotelClearFilters">
                    <i class="fas fa-undo me-1"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Hotel search modal specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const checkinInput = document.getElementById('hotelCheckin');
    const checkoutInput = document.getElementById('hotelCheckout');
    
    if (checkinInput && checkoutInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dayAfter = new Date();
        dayAfter.setDate(dayAfter.getDate() + 2);
        
        checkinInput.value = tomorrow.toISOString().split('T')[0];
        checkoutInput.value = dayAfter.toISOString().split('T')[0];
        
        // Validate checkout is after checkin
        checkinInput.addEventListener('change', function() {
            const checkinDate = new Date(this.value);
            const checkoutDate = new Date(checkoutInput.value);
            
            if (checkoutDate <= checkinDate) {
                const newCheckout = new Date(checkinDate);
                newCheckout.setDate(newCheckout.getDate() + 1);
                checkoutInput.value = newCheckout.toISOString().split('T')[0];
            }
        });
    }
    
    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('hotelClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // Reset all form fields except location and dates
            document.getElementById('hotelRooms').value = '1';
            document.getElementById('hotelGuests').value = '2';
            document.getElementById('hotelStarRating').value = '';
            document.getElementById('hotelPriceRange').value = '';
            document.getElementById('hotelSortBy').value = 'relevance';
            
            // Reset checkboxes and radio buttons
            document.querySelectorAll('#hotelFilters input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
            document.querySelector('#hotelFilters input[type="radio"][value=""]').checked = true;
            
            // Trigger new search if location is filled
            if (document.getElementById('hotelLocation').value.trim()) {
                document.getElementById('hotelSearchForm').dispatchEvent(new Event('submit'));
            }
        });
    }
});
</script>
