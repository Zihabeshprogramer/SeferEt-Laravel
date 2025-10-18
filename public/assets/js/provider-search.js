/**
 * Provider Search System - JavaScript
 * Handles AJAX search for hotels, flights, and transport services
 */
class ProviderSearch {
    constructor(options = {}) {
        this.options = {
            apiRoutes: {
                searchHotels: '/api/b2b/travel-agent/providers/search-hotels',
                searchFlights: '/api/b2b/travel-agent/providers/search-flights',
                searchTransport: '/api/b2b/travel-agent/providers/search-transport',
                checkAvailability: '/api/b2b/travel-agent/providers/check-availability',
                calculatePricing: '/api/b2b/travel-agent/providers/calculate-pricing'
            },
            debounceDelay: 500,
            minQueryLength: 2,
            maxResults: 20,
            ...options
        };
        this.searchTimers = {};
        this.searchCache = {};
        this.currentSearches = {};
    }
    initialize() {
        this.setupHotelSearch();
        this.setupFlightSearch();
        this.setupTransportSearch();
    }
    setupHotelSearch() {
        const modal = document.getElementById('hotelSearchModal');
        if (!modal) return;
        // Initialize search form
        const searchForm = modal.querySelector('#hotelSearchForm');
        const resultsContainer = modal.querySelector('#hotelSearchResults');
        const loadingIndicator = modal.querySelector('#hotelSearchLoading');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.searchHotels();
            });
            // Auto-search on input change
            const locationInput = searchForm.querySelector('#hotelLocation');
            if (locationInput) {
                locationInput.addEventListener('input', (e) => {
                    this.debouncedSearch('hotels', e.target.value);
                });
            }
        }
    }
    setupFlightSearch() {
        const modal = document.getElementById('flightSearchModal');
        if (!modal) return;
        const searchForm = modal.querySelector('#flightSearchForm');
        const resultsContainer = modal.querySelector('#flightSearchResults');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.searchFlights();
            });
        }
    }
    setupTransportSearch() {
        const modal = document.getElementById('transportSearchModal');
        if (!modal) return;
        const searchForm = modal.querySelector('#transportSearchForm');
        const resultsContainer = modal.querySelector('#transportSearchResults');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.searchTransport();
            });
        }
    }
    debouncedSearch(type, query) {
        // Clear previous timer
        if (this.searchTimers[type]) {
            clearTimeout(this.searchTimers[type]);
        }
        // Set new timer
        this.searchTimers[type] = setTimeout(() => {
            this.performSearch(type, query);
        }, this.options.debounceDelay);
    }
    async performSearch(type, query) {
        if (query.length < this.options.minQueryLength) return;
        // Check cache first
        const cacheKey = `${type}-${query}`;
        if (this.searchCache[cacheKey]) {
            this.displayResults(type, this.searchCache[cacheKey]);
            return;
        }
        try {
            this.showLoading(type);
            const searchParams = this.getSearchParams(type, query);
            const results = await this.callSearchAPI(type, searchParams);
            // Cache results
            this.searchCache[cacheKey] = results;
            this.displayResults(type, results);
        } catch (error) {
            this.showError(type, 'Search failed. Please try again.');
        } finally {
            this.hideLoading(type);
        }
    }
    getSearchParams(type, query) {
        const params = { type, query };
        switch (type) {
            case 'hotels':
                const hotelForm = document.getElementById('hotelSearchForm');
                if (hotelForm) {
                    params.location = hotelForm.querySelector('#hotelLocation')?.value;
                    params.checkin = hotelForm.querySelector('#hotelCheckin')?.value;
                    params.checkout = hotelForm.querySelector('#hotelCheckout')?.value;
                    params.rooms = hotelForm.querySelector('#hotelRooms')?.value || 1;
                    params.guests = hotelForm.querySelector('#hotelGuests')?.value || 2;
                    params.starRating = hotelForm.querySelector('#hotelStarRating')?.value;
                }
                break;
            case 'flights':
                const flightForm = document.getElementById('flightSearchForm');
                if (flightForm) {
                    params.departure = flightForm.querySelector('#flightDeparture')?.value;
                    params.arrival = flightForm.querySelector('#flightArrival')?.value;
                    params.departureDate = flightForm.querySelector('#flightDepartureDate')?.value;
                    params.returnDate = flightForm.querySelector('#flightReturnDate')?.value;
                    params.passengers = flightForm.querySelector('#flightPassengers')?.value || 1;
                    params.class = flightForm.querySelector('#flightClass')?.value || 'economy';
                }
                break;
            case 'transport':
                const transportForm = document.getElementById('transportSearchForm');
                if (transportForm) {
                    params.from = transportForm.querySelector('#transportFrom')?.value;
                    params.to = transportForm.querySelector('#transportTo')?.value;
                    params.date = transportForm.querySelector('#transportDate')?.value;
                    params.passengers = transportForm.querySelector('#transportPassengers')?.value || 1;
                    params.vehicleType = transportForm.querySelector('#transportType')?.value;
                }
                break;
        }
        return params;
    }
    async callSearchAPI(type, params) {
        let searchRoute;
        switch (type) {
            case 'hotels':
                searchRoute = this.options.apiRoutes.searchHotels;
                break;
            case 'flights':
                searchRoute = this.options.apiRoutes.searchFlights;
                break;
            case 'transport':
                searchRoute = this.options.apiRoutes.searchTransport;
                break;
            default:
                throw new Error(`Unknown search type: ${type}`);
        }
        const response = await fetch(searchRoute, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Search failed');
        }
        return result.data;
    }
    displayResults(type, results) {
        const container = document.getElementById(`${type}SearchResults`);
        if (!container) return;
        if (!results || results.length === 0) {
            container.innerHTML = this.getNoResultsHTML(type);
            return;
        }
        const resultsHTML = results.map(item => {
            switch (type) {
                case 'hotels':
                    return this.createHotelResultCard(item);
                case 'flights':
                    return this.createFlightResultCard(item);
                case 'transport':
                    return this.createTransportResultCard(item);
                default:
                    return '';
            }
        }).join('');
        container.innerHTML = `<div class="search-results-grid">${resultsHTML}</div>`;
        // Bind selection events
        this.bindResultSelectionEvents(type, container);
    }
    createHotelResultCard(hotel) {
        return `
            <div class="search-result-card hotel-result" data-provider-id="${hotel.id}">
                <div class="result-image">
                    <img src="${hotel.image || '/images/hotel-placeholder.jpg'}" alt="${hotel.name}" class="img-fluid">
                    <div class="result-rating">
                        ${this.generateStarRating(hotel.rating || 0)}
                    </div>
                </div>
                <div class="result-content">
                    <h6 class="result-title">${hotel.name}</h6>
                    <p class="result-location">
                        <i class="fas fa-map-marker-alt text-muted me-1"></i>
                        ${hotel.location}
                    </p>
                    <div class="result-amenities">
                        ${(hotel.amenities || []).slice(0, 3).map(amenity => 
                            `<span class="badge bg-light text-dark me-1">${amenity}</span>`
                        ).join('')}
                    </div>
                    <div class="result-pricing">
                        <div class="price-main">
                            <strong>${hotel.currency} ${this.formatPrice(hotel.price)}</strong>
                            <small class="text-muted">/night</small>
                        </div>
                        <div class="commission-info">
                            <small class="text-success">Commission: ${hotel.commission_rate}%</small>
                        </div>
                    </div>
                </div>
                <div class="result-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm select-provider" 
                            data-type="hotel" data-provider='${JSON.stringify(hotel).replace(/'/g, "&apos;")}'>
                        <i class="fas fa-plus me-1"></i> Select
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm view-details" 
                            onclick="viewProviderDetails('hotel', ${hotel.id})">
                        <i class="fas fa-eye me-1"></i> Details
                    </button>
                </div>
            </div>
        `;
    }
    createFlightResultCard(flight) {
        return `
            <div class="search-result-card flight-result" data-provider-id="${flight.id}">
                <div class="result-header">
                    <div class="airline-info">
                        <img src="${flight.airline_logo || '/images/airline-placeholder.png'}" 
                             alt="${flight.airline}" class="airline-logo">
                        <div class="flight-details">
                            <h6 class="flight-number">${flight.airline} ${flight.flight_number}</h6>
                            <p class="aircraft">${flight.aircraft || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="flight-price">
                        <strong>${flight.currency} ${this.formatPrice(flight.price)}</strong>
                        <small class="text-muted">per person</small>
                    </div>
                </div>
                <div class="flight-route">
                    <div class="route-segment">
                        <div class="departure">
                            <h6>${flight.departure_code}</h6>
                            <p class="time">${flight.departure_time}</p>
                            <small>${flight.departure_city}</small>
                        </div>
                        <div class="route-line">
                            <i class="fas fa-plane"></i>
                            <div class="duration">${flight.duration || 'N/A'}</div>
                        </div>
                        <div class="arrival">
                            <h6>${flight.arrival_code}</h6>
                            <p class="time">${flight.arrival_time}</p>
                            <small>${flight.arrival_city}</small>
                        </div>
                    </div>
                </div>
                <div class="flight-info">
                    <div class="flight-class">
                        <span class="badge bg-secondary">${flight.class || 'Economy'}</span>
                    </div>
                    <div class="baggage">
                        <small><i class="fas fa-suitcase me-1"></i> ${flight.baggage || 'Included'}</small>
                    </div>
                    <div class="commission">
                        <small class="text-success">Commission: ${flight.commission_rate}%</small>
                    </div>
                </div>
                <div class="result-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm select-provider" 
                            data-type="flight" data-provider='${JSON.stringify(flight).replace(/'/g, "&apos;")}'>
                        <i class="fas fa-plus me-1"></i> Select
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm check-availability" 
                            data-type="flight" data-id="${flight.id}">
                        <i class="fas fa-check me-1"></i> Check Availability
                    </button>
                </div>
            </div>
        `;
    }
    createTransportResultCard(transport) {
        return `
            <div class="search-result-card transport-result" data-provider-id="${transport.id}">
                <div class="result-header">
                    <div class="transport-info">
                        <img src="${transport.image || '/images/transport-placeholder.jpg'}" 
                             alt="${transport.company}" class="transport-image">
                        <div class="company-details">
                            <h6 class="company-name">${transport.company}</h6>
                            <p class="vehicle-type">
                                <span class="badge bg-info">${transport.type}</span>
                                <span class="capacity ms-2">${transport.capacity} seats</span>
                            </p>
                        </div>
                    </div>
                    <div class="transport-price">
                        <strong>${transport.currency} ${this.formatPrice(transport.price)}</strong>
                        <small class="text-muted">total</small>
                    </div>
                </div>
                <div class="transport-route">
                    <div class="route-details">
                        <div class="departure">
                            <h6>${transport.departure_location}</h6>
                            <p class="time">${transport.departure_time || 'TBD'}</p>
                        </div>
                        <div class="route-arrow">
                            <i class="fas fa-arrow-right"></i>
                            <small class="duration">${transport.duration || 'N/A'}</small>
                        </div>
                        <div class="arrival">
                            <h6>${transport.arrival_location}</h6>
                            <p class="time">${transport.arrival_time || 'TBD'}</p>
                        </div>
                    </div>
                </div>
                <div class="transport-features">
                    ${(transport.features || []).map(feature => 
                        `<span class="badge bg-light text-dark me-1">${feature}</span>`
                    ).join('')}
                </div>
                <div class="transport-info-footer">
                    <div class="commission">
                        <small class="text-success">Commission: ${transport.commission_rate}%</small>
                    </div>
                </div>
                <div class="result-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm select-provider" 
                            data-type="transport" data-provider='${JSON.stringify(transport).replace(/'/g, "&apos;")}'>
                        <i class="fas fa-plus me-1"></i> Select
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm view-details" 
                            onclick="viewProviderDetails('transport', ${transport.id})">
                        <i class="fas fa-info me-1"></i> Details
                    </button>
                </div>
            </div>
        `;
    }
    bindResultSelectionEvents(type, container) {
        const selectButtons = container.querySelectorAll('.select-provider');
        const availabilityButtons = container.querySelectorAll('.check-availability');
        selectButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const providerData = JSON.parse(e.target.dataset.provider.replace(/&apos;/g, "'"));
                this.selectProvider(type, providerData);
            });
        });
        availabilityButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const providerId = e.target.dataset.id;
                this.checkAvailability(type, providerId, e.target);
            });
        });
    }
    selectProvider(type, providerData) {
        // Add provider to the selected list (integrates with step2-providers.blade.php)
        if (typeof addProvider === 'function') {
            addProvider(type, providerData);
        }
        // Close the modal
        const modalElement = document.getElementById(`${type}SearchModal`);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
        // Show success message
        this.showSuccess(`${this.capitalizeFirst(type)} added successfully!`);
    }
    async checkAvailability(type, providerId, button) {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';
        button.disabled = true;
        try {
            const response = await fetch(this.options.apiRoutes.checkAvailability, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    type,
                    provider_id: providerId,
                    search_params: this.getSearchParams(type, '')
                })
            });
            const result = await response.json();
            if (result.success) {
                if (result.available) {
                    button.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Available';
                    button.classList.remove('btn-outline-info');
                    button.classList.add('btn-outline-success');
                } else {
                    button.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> Unavailable';
                    button.classList.remove('btn-outline-info');
                    button.classList.add('btn-outline-danger');
                }
            } else {
                throw new Error(result.message || 'Availability check failed');
            }
        } catch (error) {
            button.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-1"></i> Error';
            button.classList.remove('btn-outline-info');
            button.classList.add('btn-outline-warning');
        } finally {
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
                button.className = 'btn btn-outline-info btn-sm check-availability';
            }, 3000);
        }
    }
    // Search specific methods
    searchHotels() {
        const form = document.getElementById('hotelSearchForm');
        if (!form) return;
        const params = this.getSearchParams('hotels', '');
        this.performSearch('hotels', params.location || '');
    }
    searchFlights() {
        const form = document.getElementById('flightSearchForm');
        if (!form) return;
        const params = this.getSearchParams('flights', '');
        this.performSearch('flights', `${params.departure}-${params.arrival}`);
    }
    searchTransport() {
        const form = document.getElementById('transportSearchForm');
        if (!form) return;
        const params = this.getSearchParams('transport', '');
        this.performSearch('transport', `${params.from}-${params.to}`);
    }
    // UI Helper methods
    showLoading(type) {
        const loadingEl = document.getElementById(`${type}SearchLoading`);
        const resultsEl = document.getElementById(`${type}SearchResults`);
        if (loadingEl) loadingEl.style.display = 'block';
        if (resultsEl) resultsEl.style.display = 'none';
    }
    hideLoading(type) {
        const loadingEl = document.getElementById(`${type}SearchLoading`);
        const resultsEl = document.getElementById(`${type}SearchResults`);
        if (loadingEl) loadingEl.style.display = 'none';
        if (resultsEl) resultsEl.style.display = 'block';
    }
    showError(type, message) {
        const container = document.getElementById(`${type}SearchResults`);
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }
    }
    getNoResultsHTML(type) {
        return `
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No ${type} found</h6>
                <p class="text-muted">Try adjusting your search criteria</p>
            </div>
        `;
    }
    // Utility methods
    generateStarRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="fas fa-star ${i <= rating ? 'text-warning' : 'text-muted'}"></i>`;
        }
        return stars;
    }
    formatPrice(price) {
        return new Intl.NumberFormat('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(price);
    }
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }
}
// Global functions for external integration
window.viewProviderDetails = function(type, providerId) {
    // This would open a detailed view modal
    alert(`${type.charAt(0).toUpperCase() + type.slice(1)} details coming soon...`);
};
// Initialize provider search globally
window.providerSearch = new ProviderSearch();
// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.providerSearch.initialize();
    });
} else {
    window.providerSearch.initialize();
}
