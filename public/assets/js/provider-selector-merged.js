/**
 * Merged Provider Selection System - Complete Implementation
 * This file combines the working API integration with enhanced UI components
 * CREATED: 2025-01-20 18:30 - Merged from simple + enhanced versions
 */
// Initialize the MergedProviderSelector object
window.MergedProviderSelector = {
    // Core properties
    selectedProviders: {
        hotels: [],
        flights: [],
        transport: []
    },
    tempSelections: [],
    currentServiceType: 'hotels',
    currentSearchResults: [],
    currentPage: 1,
    itemsPerPage: 6,
    totalPages: 1,
    modalInstance: null, // Store modal instance to prevent multiple modal backdrops
    ownFlights: [], // Store user's own flights for selection
    // Initialize the system
    init() {
        this.currentExternalServiceType = null; // For external form tracking
        this.bindEvents();
        this.loadExistingSelections();
        // Fix any data inconsistencies (e.g., transports vs transport)
        this.fixDataInconsistency();
        // Load draft providers if available
        this.loadDraftProvidersIfAvailable();
        // Initial UI updates
        this.updateCountBadges();
        this.updateSummaryStats();
        this.renderAllProviders();
        this.updateOverallStatusSummary();
    },
    // Fix data inconsistency - migrate transports to transport
    fixDataInconsistency() {
        if (this.selectedProviders.transports) {
            if (!this.selectedProviders.transport) {
                this.selectedProviders.transport = [];
            }
            // Merge any existing transport data with transports data
            const allTransportProviders = [
                ...this.selectedProviders.transport,
                ...this.selectedProviders.transports
            ];
            // Remove duplicates based on ID
            this.selectedProviders.transport = allTransportProviders.filter((provider, index, self) => 
                index === self.findIndex((p) => p.id === provider.id)
            );
            // Remove the incorrect transports array
            delete this.selectedProviders.transports;
            // Update UI to reflect changes
            this.updateUI();
        }
    },
    // Bind all event listeners
    bindEvents() {
        this.bindSearchFormEvents();
        this.bindModalEvents();
        this.bindTabEvents();
    },
    // Bind modal events
    bindModalEvents() {
        const modal = document.getElementById('providerSelectionModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', () => {
                this.onModalShown();
            });
            modal.addEventListener('hidden.bs.modal', () => {
                this.onModalHidden();
            });
        }
        // Bind modal toggle buttons
        const browseBtn = document.getElementById('browseProvidersBtn');
        const externalBtn = document.getElementById('useExternalServiceBtn');
        if (browseBtn) {
            browseBtn.addEventListener('click', () => {
                this.showPlatformProviders();
            });
        }
        if (externalBtn) {
            externalBtn.addEventListener('click', () => {
                this.showExternalForm(this.currentServiceType);
            });
        }
    },
    // Bind tab events
    bindTabEvents() {
        const tabs = document.querySelectorAll('#providerTabs button[data-bs-toggle="pill"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', (event) => {
                const targetId = event.target.getAttribute('data-bs-target');
                this.onTabChange(targetId);
            });
        });
    },
    // Handle modal shown
    onModalShown() {
        // Load providers from real API for the requested service type
        setTimeout(() => {
            this.loadProvidersFromAPI(this.currentServiceType);
        }, 300);
    },
    // Handle modal hidden
    onModalHidden() {
        // Modal cleanup if needed
    },
    // Handle tab change
    onTabChange(targetId) {
        const previousServiceType = this.currentServiceType;
        if (targetId === '#hotels') {
            this.currentServiceType = 'hotels';
        } else if (targetId === '#flights') {
            this.currentServiceType = 'flights';
        } else if (targetId === '#transport') {
            this.currentServiceType = 'transport';
        }
        // If service type actually changed, reload providers for the new service type
        if (previousServiceType !== this.currentServiceType) {
            // Clear temporary selections when switching service types
            this.tempSelections = [];
            this.updateSelectionSummary();
            // Load providers for the new service type
            this.loadProvidersFromAPI(this.currentServiceType);
            // Update modal title
            const modalTitle = document.getElementById('modalServiceTitle');
            if (modalTitle) {
                const serviceNames = {
                    'hotels': 'Browse Hotels',
                    'flights': 'Browse Flights', 
                    'transport': 'Browse Transport'
                };
                modalTitle.textContent = serviceNames[this.currentServiceType] || 'Browse Providers';
            }
        }
    },
    // Load existing selections
    loadExistingSelections() {
        // Load any existing selections from storage if needed
    },
// Load draft providers if available
    loadDraftProvidersIfAvailable() {
        // Check if we have draft data available
        const draftData = window.draftData || null;
        if (draftData && draftData.data) {
            // Extract providers from draft data
            const draftProviders = draftData.data;
            // Function to convert legacy service request fields to request_info
            const ensureRequestInfo = (provider) => {
                if (!provider.request_info && provider.service_request_id) {
                    provider.request_info = {
                        request_id: provider.service_request_id,
                        request_status: provider.service_request_status || 'pending',
                        updated_at: new Date().toISOString()
                    };
                }
                return provider;
            };
            // Process and enrich providers for each service type
            const processServiceType = (key, pluralKey, singleKey) => {
                const list = draftProviders[key];
                if (list && Array.isArray(list)) {
                    if (list.length > 0) {
                        // Ensure each provider has request_info if it has service_request_id
                        const enrichedList = list.map(ensureRequestInfo);
                        this.loadAndEnrichProviders(enrichedList, pluralKey, singleKey);
                    } else {
                        this.selectedProviders[pluralKey] = [];
                    }
                } else {
                    this.selectedProviders[pluralKey] = [];
                }
            };
            // Process each service type
            processServiceType('selected_hotels', 'hotels', 'hotel');
            processServiceType('selected_flights', 'flights', 'flight');
            processServiceType('selected_transport', 'transport', 'transport');
            // Initial render with whatever data we have, then update as enrichment completes
            setTimeout(() => {
                this.renderAllProviders();
                this.updateCountBadges();
                this.updateSummaryStats();
                this.updateOverallStatusSummary();
            }, 100);
        }
    },
    // Load and enrich providers with full data from database using stored IDs
    async loadAndEnrichProviders(draftProviders, serviceTypeKey, serviceTypeSingle) {
        // Create loading placeholders first
        this.selectedProviders[serviceTypeKey] = draftProviders.map(draftProvider => ({
            ...draftProvider,
            name: 'Loading...',
            loading: true,
            enriching: true
        }));
        // Render loading state immediately
        this.renderServiceProviders(serviceTypeKey);
        this.updateCountBadges();
        // Fetch full provider data from database
        const enrichedProviders = await this.fetchProvidersFromDatabase(draftProviders, serviceTypeSingle);
        // Update with enriched data
        this.selectedProviders[serviceTypeKey] = enrichedProviders;
        // Update UI after enrichment
        this.renderServiceProviders(serviceTypeKey);
        this.updateCountBadges();
        this.updateSummaryStats();
    },
    // Fetch full provider data from database using stored IDs
    async fetchProvidersFromDatabase(draftProviders, serviceType) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const providerIds = draftProviders.map(p => p.id);
            const response = await fetch('/b2b/travel-agent/api/providers/batch-fetch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    provider_ids: providerIds,
                    service_type: serviceType,
                    include_service_requests: true
                }),
                credentials: 'same-origin'
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            if (data.success && data.providers) {
                // Merge database data with draft metadata
return draftProviders.map(draftProvider => {
                    const dbProvider = data.providers.find(p => p.id == draftProvider.id);
                    if (dbProvider) {
                        // Build service request from draft request_info or fallback fields, else use DB value
                        const reqInfo = draftProvider.request_info || null;
                        const serviceRequest = reqInfo ? {
                            id: reqInfo.request_id || reqInfo.id || draftProvider.service_request_id || null,
                            uuid: reqInfo.request_uuid || null,
                            status: reqInfo.request_status || draftProvider.service_request_status || 'pending',
                            created_at: reqInfo.request_created_at || null,
                            expires_at: reqInfo.request_expires_at || null,
                            requested_quantity: reqInfo.requested_quantity || draftProvider.requested_quantity || null
                        } : (draftProvider.service_request_id ? {
                            id: draftProvider.service_request_id,
                            status: draftProvider.service_request_status || 'pending'
                        } : (dbProvider.service_request || null));
                        // Merge database data with draft user customizations
                        return {
                            ...dbProvider,
                            ...draftProvider, // Override with draft customizations
                            type: serviceType,
                            from_draft: true,
                            enriching: false,
                            loading: false,
                            // Restore service request information if available
                            service_request: serviceRequest
                        };
                    } else {
                        // Provider not found in database - might be deleted
                        return {
                            ...draftProvider,
                            name: 'Provider Not Found',
                            status: 'deleted',
                            enriching: false,
                            loading: false,
                            error: 'Provider may have been deleted or is no longer available'
                        };
                    }
                });
            } else {
                console.error('Failed to fetch providers from database:', data.message);
                return this.createFallbackProviders(draftProviders, serviceType);
            }
        } catch (error) {
            console.error('Error fetching providers from database:', error);
            return this.createFallbackProviders(draftProviders, serviceType);
        }
    },
    // Create fallback providers when database fetch fails
    createFallbackProviders(draftProviders, serviceType) {
        return draftProviders.map(draftProvider => ({
            ...draftProvider,
            name: 'Provider (Offline)',
            type: serviceType,
            from_draft: true,
            enriching: false,
            loading: false,
            error: 'Could not load provider details. Check your connection.',
            status: 'unknown'
        }));
    },
    // Fetch detailed provider data from API
    async fetchProviderDetails(providerId, serviceType) {
        try {
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            // Convert service type for API call
            const apiServiceType = serviceType === 'transport' ? 'transport' : serviceType + 's';
            // Use the existing providers search API to get detailed provider information
            const formData = new FormData();
            formData.append('_token', token);
            formData.append('service_type', apiServiceType);
            formData.append('provider_id', providerId);
            const response = await fetch('/b2b/travel-agent/api/providers/search', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData,
                credentials: 'same-origin'
            });
            if (response.ok) {
                const data = await response.json();
                // Look for the specific provider in the results
                if (data.data && Array.isArray(data.data)) {
                    const foundProvider = data.data.find(p => 
                        p.id == providerId || 
                        String(p.id) === String(providerId)
                    );
                    if (foundProvider) {
                        return foundProvider;
                    }
                }
                // If not found in search results, return null
                console.warn(`Provider ${providerId} not found in search results`);
                return null;
            } else {
                console.warn(`Failed to fetch provider details for ${providerId}:`, response.status);
                return null;
            }
        } catch (error) {
            console.error('Error fetching provider details:', error);
            return null;
        }
    },
    // Create a minimal draft provider record with essential data only
createMinimalProviderRecord(provider, type) {
        // Normalize service request into request_info for persistence in draft data
        const requestInfo = provider.service_request ? {
            request_id: provider.service_request.id,
            request_uuid: provider.service_request.uuid || null,
            request_status: provider.service_request.status || null,
            request_created_at: provider.service_request.created_at || null,
            request_expires_at: provider.service_request.expires_at || null,
            requested_quantity: provider.service_request.requested_quantity || null,
            updated_at: new Date().toISOString()
        } : (provider.request_info || null);
        const record = {
            // Essential identifiers
            id: provider.id,
            type: type,
            provider_type: provider.provider_type || 'platform',
            // Service request tracking (both legacy flatten fields and structured info)
            service_request_id: provider.service_request?.id || (requestInfo ? requestInfo.request_id : null) || null,
            service_request_status: provider.service_request?.status || (requestInfo ? requestInfo.request_status : null) || null,
            request_info: requestInfo || undefined,
            // Selection metadata
            selected_at: new Date().toISOString(),
            from_draft: true,
            // Only store essential user-defined data that might not be in DB
            user_notes: provider.user_notes || null,
            markup_percentage: provider.markup_percentage || 0,
            special_requirements: provider.special_requirements || null,
            // Service-specific essential data that might be user-customized
            ...(type === 'hotel' && {
                nights: provider.nights || null,
                rooms_needed: provider.rooms_needed || null,
                room_type: provider.room_type || null,
                is_primary: provider.is_primary || false
            }),
            ...(type === 'flight' && {
                flight_type: provider.flight_type || null,
                seats_allocated: provider.seats_allocated || null,
                departure_date: provider.departure_date || null,
                return_date: provider.return_date || null
            }),
            ...(type === 'transport' && {
                pickup_location: provider.pickup_location || null,
                dropoff_location: provider.dropoff_location || null,
                day_of_itinerary: provider.day_of_itinerary || null,
                pickup_time: provider.pickup_time || null,
                transport_category: provider.transport_category || null
            })
        };
        return record;
    },
    // Get the service type from provider data
    getServiceTypeFromProvider(provider) {
        if (provider.type === 'hotel') return 'hotels';
        if (provider.type === 'flight') return 'flights';
        if (provider.type === 'transport') return 'transport';
        // Fallback: try to infer from provider properties
        if (provider.hotel_name || provider.room_types) return 'hotels';
        if (provider.airline || provider.flight_number) return 'flights';
        if (provider.transport_type || provider.vehicle_capacity) return 'transport';
        return 'hotels'; // Default fallback
    },
    // ***** API INTEGRATION METHODS (From Simple Version) *****
    // Open provider modal (main entry point)
    openProviderModal(serviceType) {
        // Ensure service type is properly set
        this.currentServiceType = serviceType;
        // Update modal title to reflect service type
        const modalTitle = document.getElementById('modalServiceTitle');
        if (modalTitle) {
            const serviceNames = {
                'hotels': 'Browse Hotels',
                'flights': 'Browse Flights', 
                'transport': 'Browse Transport'
            };
            modalTitle.textContent = serviceNames[serviceType] || 'Browse Providers';
        }
        // Show modal using helper method
        const modalInstance = this.getModalInstance();
        if (modalInstance) {
            modalInstance.show();
        }
    },
    // Load providers from backend (Real API Integration)
    loadProvidersFromAPI(serviceType) {
        // Show loading state
        this.showSearchLoading();
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        // Determine the API endpoint based on service type
        let apiEndpoint = '/b2b/travel-agent/api/providers/search';
        let requestBody = {
            type: serviceType,
            limit: 50
        };
        let method = 'POST';
        // Use specific endpoints for better functionality
        if (serviceType === 'flights') {
            apiEndpoint = '/b2b/travel-agent/api/providers/search-flights';
            requestBody = {
                ...requestBody,
                source: 'platform', // Exclude own flights when browsing platform
                limit: 50
            };
        } else if (serviceType === 'hotels') {
            apiEndpoint = '/b2b/travel-agent/api/providers/search-hotels';
            requestBody = {
                ...requestBody,
                source: 'platform',
                limit: 50
            };
        } else if (serviceType === 'transport') {
            apiEndpoint = '/b2b/travel-agent/api/providers/search-transport';
            requestBody = {
                ...requestBody,
                source: 'platform',
                limit: 50
            };
        }
        // Make API call to specific endpoint
        fetch(apiEndpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // Handle different response formats from service-specific APIs
            let serviceData = [];
            if (data.success) {
                if (serviceType === 'flights' && data.flights) {
                    serviceData = data.flights;
                } else if (serviceType === 'hotels' && data.hotels) {
                    serviceData = data.hotels;
                } else if (serviceType === 'transport' && data.transport_services) {
                    serviceData = data.transport_services;
                } else if (data.data) {
                    serviceData = data.data; // Generic endpoint response format
                }
            }
            if (serviceData && serviceData.length > 0) {
                // Store current search results for selection
                this.currentSearchResults = serviceData;
                // Reset pagination
                this.currentPage = 1;
                // Get paginated data for current page
                const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                const endIndex = startIndex + this.itemsPerPage;
                const paginatedData = serviceData.slice(startIndex, endIndex);
                // Create results object
                const results = {
                    data: paginatedData,
                    total: serviceData.length,
                    message: `Found ${serviceData.length} ${serviceType} providers (platform only)`
                };
                // Render with enhanced UI
                this.renderSearchResults(results);
                this.updateResultsInfo(results);
            } else {
                // Show no services available message
                this.showNoServicesAvailable(serviceType);
            }
        })
        .catch(error => {
            console.error('❌ FETCH ERROR occurred:', error);
            // Show error message instead of sample data
            this.showServiceLoadError(serviceType, error.message);
        });
    },
    // Show no services available message
    showNoServicesAvailable(serviceType) {
        const container = document.getElementById('providerSearchResults');
        const loadingState = document.getElementById('searchLoadingState');
        const emptyState = document.getElementById('searchEmptyState');
        const resultsInfo = document.getElementById('resultsInfo');
        // Hide loading state
        if (loadingState) loadingState.style.display = 'none';
        // Clear current search results
        this.currentSearchResults = [];
        // Show empty state
        if (emptyState) emptyState.style.display = 'block';
        // Update results info
        if (resultsInfo) {
            resultsInfo.textContent = `No ${serviceType} services available`;
        }
        // Show custom no services message
        if (container) {
            container.innerHTML = this.getNoServicesHTML(serviceType);
        }
    },
    // Show service load error message
    showServiceLoadError(serviceType, errorMessage) {
        const container = document.getElementById('providerSearchResults');
        const loadingState = document.getElementById('searchLoadingState');
        const emptyState = document.getElementById('searchEmptyState');
        const resultsInfo = document.getElementById('resultsInfo');
        // Hide loading state
        if (loadingState) loadingState.style.display = 'none';
        // Clear current search results
        this.currentSearchResults = [];
        // Show empty state
        if (emptyState) emptyState.style.display = 'block';
        // Update results info
        if (resultsInfo) {
            resultsInfo.textContent = `Error loading ${serviceType} services`;
        }
        // Show error message
        if (container) {
            container.innerHTML = this.getServiceErrorHTML(serviceType, errorMessage);
        }
    },
    // ***** ENHANCED UI RENDERING METHODS *****
    // Show search loading state
    showSearchLoading() {
        const loadingState = document.getElementById('searchLoadingState');
        const resultsContainer = document.getElementById('providerSearchResults');
        const emptyState = document.getElementById('searchEmptyState');
        if (loadingState) loadingState.style.display = 'block';
        if (resultsContainer) resultsContainer.innerHTML = '';
        if (emptyState) emptyState.style.display = 'none';
        const resultsInfo = document.getElementById('resultsInfo');
        if (resultsInfo) {
            resultsInfo.textContent = 'Searching...';
        }
    },
    // Render search results with enhanced UI
    renderSearchResults(results) {
        const container = document.getElementById('providerSearchResults');
        const loadingState = document.getElementById('searchLoadingState');
        const emptyState = document.getElementById('searchEmptyState');
        if (loadingState) loadingState.style.display = 'none';
        if (!results.data || results.data.length === 0) {
            if (container) container.innerHTML = this.getNoResultsHTML();
            if (emptyState) emptyState.style.display = 'block';
            return;
        }
        if (emptyState) emptyState.style.display = 'none';
        // Store current search results for selection
        this.currentSearchResults = results.data;
        const resultsHTML = results.data.map(provider => {
            return this.generateEnhancedProviderCard(provider);
        }).join('');
        if (container) {
            // Use existing provider-results-grid class from the blade file
            container.className = 'provider-results-grid';
            // Let existing CSS handle the styling, just add minimal grid layout
            container.style.display = 'grid';
            container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(400px, 1fr))';
            container.style.gap = '1.5rem';
            container.style.padding = '1rem';
            container.innerHTML = resultsHTML;
        }
        // Apply current view mode
        this.applyViewMode();
        // Update selection UI for any previously selected items
        this.updateSelectionUI();
    },
    // Generate enhanced provider card HTML using existing CSS classes
    generateEnhancedProviderCard(provider) {
        // Handle different data structures from real API
        const name = provider.name || provider.hotel_name || provider.service_name || provider.airline || 'Unknown Provider';
        const location = provider.location || provider.address || provider.city || provider.route || 'Location not specified';
        const rating = provider.rating || provider.star_rating || null;
        const isSelected = this.tempSelections.includes(provider.id);
        const selectedClass = isSelected ? 'selected' : '';
        const selectionOverlay = isSelected ? '<div class="selection-overlay"></div>' : '';
        const cardHTML = `
            <div class="provider-card-enhanced ${selectedClass}" 
                 data-provider-id="${provider.id}" 
                 onclick="MergedProviderSelector.toggleTempSelection('${provider.id}')">
                ${selectionOverlay}
                <!-- Selection Checkbox -->
                <div class="selection-checkbox-wrapper">
                    <input type="checkbox" id="select-${provider.id}" ${isSelected ? 'checked' : ''}>
                    <label for="select-${provider.id}" class="selection-label">
                        <i class="fas fa-check"></i>
                    </label>
                </div>
                <div class="card-content">
                    <!-- Provider Image/Avatar Section -->
                    <div class="provider-image-section">
                        <div class="provider-avatar">
                            <span class="avatar-letter">${name ? name.charAt(0).toUpperCase() : 'P'}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="card-header-section">
                                <h6 class="provider-name">${name}</h6>
                                ${provider.company_name || (provider.provider && provider.provider.company_name) ? `<div class="company-name">${provider.company_name || provider.provider.company_name}</div>` : ''}
                                <div class="location-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>${location}</span>
                                </div>
                            </div>
                        </div>
                        <div class="service-type-badge">${this.currentServiceType}</div>
                    </div>
                    <!-- Rating -->
                    ${rating ? `
                        <div class="provider-rating">
                            ${this.generateStarRating(rating)}
                            <span class="rating-text">${rating}</span>
                        </div>
                    ` : ''}
                    <!-- Features Section -->
                    <div class="features-section">
                        ${this.renderProviderFeatures(provider)}
                    </div>
                    <!-- Price Section -->
                    <div class="price-section">
                        <div class="price-label">Starting from</div>
                        <div class="price-amount">
                            ${this.formatPrice(provider)}
                        </div>
                    </div>
                    <!-- Room Pricing Info for Hotels -->
                    ${this.currentServiceType === 'hotels' ? this.generateRoomPricingInfo(provider) : ''}
                    <!-- Card Actions -->
                    <div class="card-actions">
                        <button type="button" class="btn-view-details" 
                                onclick="event.stopPropagation(); MergedProviderSelector.viewSearchResultDetails('${provider.id}')">
                            <i class="fas fa-eye"></i> Details
                        </button>
                        <button type="button" class="btn-quick-select ${isSelected ? 'selected' : ''}" 
                                onclick="event.stopPropagation(); MergedProviderSelector.toggleTempSelection('${provider.id}')">
                            <i class="fas fa-${isSelected ? 'check' : 'plus'}"></i> ${isSelected ? 'Selected' : 'Select'}
                        </button>
                    </div>
                </div>
            </div>
        `;
        return cardHTML;
    },
    // Render provider features using existing styles
    renderProviderFeatures(provider) {
        let features = this.getFeatures(provider);
        // If no features available, show appropriate message
        if (features.length === 0) {
            return '<div class="text-muted small"><i class="fas fa-info-circle me-1"></i>Features not specified</div>';
        }
        return features.slice(0, 3).map(feature => `
            <div class="feature-item">
                <i class="fas fa-check-circle text-success"></i>
                <span class="feature-text">${feature}</span>
            </div>
        `).join('');
    },
    // Generate room pricing information using existing CSS
    generateRoomPricingInfo(provider) {
        if (!provider.rooms || !Array.isArray(provider.rooms) || provider.rooms.length === 0) {
            return '';
        }
        const roomsToShow = provider.rooms.slice(0, 3);
        return `
            <div class="pricing-grid">
                ${roomsToShow.map(room => `
                    <div class="pricing-card">
                        <h6>${room.type || 'Standard Room'}</h6>
                        <div class="price">${room.price || 'On Request'}</div>
                    </div>
                `).join('')}
                ${provider.rooms.length > 3 ? `
                    <div class="pricing-card" style="opacity: 0.7;">
                        <h6>More Options</h6>
                        <div class="price">+${provider.rooms.length - 3}</div>
                    </div>
                ` : ''}
            </div>
        `;
    },
    // ***** SELECTION MANAGEMENT METHODS *****
    // Toggle temporary selection using existing CSS classes
    toggleTempSelection(providerId) {
        const index = this.tempSelections.indexOf(providerId);
        const card = document.querySelector(`[data-provider-id="${providerId}"]`);
        if (index > -1) {
            // Remove from selection
            this.tempSelections.splice(index, 1);
            if (card) {
                card.classList.remove('selected');
                // Update checkbox
                const checkbox = card.querySelector(`#select-${providerId}`);
                if (checkbox) checkbox.checked = false;
                // Update quick select button
                const button = card.querySelector('.btn-quick-select');
                if (button) {
                    button.classList.remove('selected');
                    button.innerHTML = '<i class="fas fa-plus"></i> Select';
                }
                // Remove selection feedback
                const feedback = card.querySelector('.selection-feedback');
                if (feedback) feedback.remove();
            }
        } else {
            // Add to selection
            this.tempSelections.push(providerId);
            if (card) {
                card.classList.add('selected');
                // Update checkbox
                const checkbox = card.querySelector(`#select-${providerId}`);
                if (checkbox) checkbox.checked = true;
                // Update quick select button
                const button = card.querySelector('.btn-quick-select');
                if (button) {
                    button.classList.add('selected');
                    button.innerHTML = '<i class="fas fa-check"></i> Selected';
                }
                // Add selection feedback
                const feedback = document.createElement('div');
                feedback.className = 'selection-feedback selected';
                feedback.innerHTML = '<i class="fas fa-check"></i>';
                card.appendChild(feedback);
                // Remove feedback after animation
                setTimeout(() => {
                    if (feedback.parentNode) feedback.remove();
                }, 1500);
            }
        }
        // Update selection summary
        this.updateSelectionSummary();
    },
    // Update selection summary
    updateSelectionSummary() {
        const summaryDiv = document.getElementById('selectionSummary');
        const selectedCount = document.getElementById('selectedCount');
        const selectionDetails = document.getElementById('selectionDetails');
        if (this.tempSelections.length > 0) {
            if (summaryDiv) summaryDiv.style.display = 'block';
            if (selectedCount) selectedCount.textContent = `${this.tempSelections.length} provider${this.tempSelections.length > 1 ? 's' : ''} selected`;
            if (selectionDetails) selectionDetails.textContent = `Ready to add to ${this.currentServiceType}`;
        } else {
            if (summaryDiv) summaryDiv.style.display = 'none';
        }
    },
    // Update selection UI
    updateSelectionUI() {
        document.querySelectorAll('.provider-card-enhanced').forEach(card => {
            const providerId = card.getAttribute('data-provider-id');
            const isSelected = this.tempSelections.includes(providerId);
            if (isSelected) {
                card.classList.add('selected');
                const checkbox = card.querySelector(`#select-${providerId}`);
                if (checkbox) checkbox.checked = true;
                const button = card.querySelector('.btn-quick-select');
                if (button) {
                    button.classList.add('selected');
                    button.innerHTML = '<i class="fas fa-check"></i> Selected';
                }
            } else {
                card.classList.remove('selected');
                const checkbox = card.querySelector(`#select-${providerId}`);
                if (checkbox) checkbox.checked = false;
                const button = card.querySelector('.btn-quick-select');
                if (button) {
                    button.classList.remove('selected');
                    button.innerHTML = '<i class="fas fa-plus"></i> Select';
                }
            }
        });
    },
    // Clear temporary selections using existing CSS classes
    clearTempSelections() {
        this.tempSelections = [];
        // Update all card selection states
        document.querySelectorAll('.provider-card-enhanced.selected').forEach(card => {
            card.classList.remove('selected');
            // Update checkbox
            const checkbox = card.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
            // Update quick select button
            const button = card.querySelector('.btn-quick-select');
            if (button) {
                button.classList.remove('selected');
                button.innerHTML = '<i class="fas fa-plus"></i> Select';
            }
            // Remove any selection feedback
            const feedback = card.querySelector('.selection-feedback');
            if (feedback) feedback.remove();
        });
        // Update selection summary
        this.updateSelectionSummary();
        // Show success notification
        this.showToast('All selections cleared', 'info');
    },
    // Confirm selections and add to main list
    confirmSelections() {
        // Prevent double processing
        if (this.processingSelections) {
            return;
        }
        this.processingSelections = true;
        try {
            if (this.tempSelections.length === 0) {
                this.showToast('No providers selected', 'warning');
                return;
            }
            const serviceType = this.currentServiceType;
            let addedCount = 0;
            const selectionsToProcess = [...this.tempSelections]; // Create a copy
            selectionsToProcess.forEach(providerId => {
                // Find provider in current search results - try both string and number comparison
                let provider = this.currentSearchResults?.find(p => 
                    p.id === providerId || 
                    p.id == providerId || 
                    String(p.id) === String(providerId)
                );
                if (provider) {
                    // Ensure provider has the correct type
                    provider.type = serviceType === 'transport' ? 'transport' : serviceType.slice(0, -1);
                    // Ensure provider has required fields for UI rendering
                    if (!provider.status) {
                        provider.status = 'approved'; // Default status for platform providers
                    }
                    if (!provider.provider_type) {
                        provider.provider_type = 'platform';
                    }
                    const success = this.addProvider(provider);
                    if (success) {
                        addedCount++;
                        // Remove successfully added provider from temp selections
                        const index = this.tempSelections.indexOf(providerId);
                        if (index > -1) {
                            this.tempSelections.splice(index, 1);
                        }
                    }
                }
            });
            // Only close modal and show success message if providers were actually added
            if (addedCount > 0) {
                // Close modal
                const modalInstance = this.getModalInstance();
                if (modalInstance) {
                    modalInstance.hide();
                }
                // Show success message
                this.showToast(`${addedCount} provider${addedCount > 1 ? 's' : ''} added successfully`, 'success');
                // Clear selection summary display
                this.updateSelectionSummary();
            } else {
                this.showToast('Failed to add providers. Please try again.', 'error');
            }
        } finally {
            // Always reset processing flag, even if there's an error
            this.processingSelections = false;
        }
    },
    // ***** PAGINATION METHODS *****
    // Update results info and pagination
    updateResultsInfo(results) {
        const resultsInfo = document.getElementById('resultsInfo');
        const paginationInfo = document.getElementById('paginationInfo');
        const total = results.total || results.data.length;
        const displaying = results.data.length;
        // Calculate pagination
        this.totalPages = Math.ceil(total / this.itemsPerPage);
        const startIndex = ((this.currentPage - 1) * this.itemsPerPage) + 1;
        const endIndex = Math.min(this.currentPage * this.itemsPerPage, total);
        if (resultsInfo) {
            resultsInfo.textContent = `Found ${total} ${this.currentServiceType}`;
        }
        if (paginationInfo) {
            paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${total} providers`;
        }
        // Update pagination UI
        this.updatePaginationUI();
    },
    // Update pagination UI
    updatePaginationUI() {
        const paginationNav = document.getElementById('paginationNav');
        if (!paginationNav) return;
        let paginationHTML = '';
        // Previous button
        paginationHTML += `
            <li class="page-item ${this.currentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" ${this.currentPage > 1 ? `onclick="MergedProviderSelector.goToPage(${this.currentPage - 1})"` : 'tabindex="-1" aria-disabled="true"'}>
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        // Page numbers (simplified)
        for (let i = 1; i <= this.totalPages; i++) {
            paginationHTML += `
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="MergedProviderSelector.goToPage(${i})">${i}</a>
                </li>
            `;
        }
        // Next button
        paginationHTML += `
            <li class="page-item ${this.currentPage >= this.totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" ${this.currentPage < this.totalPages ? `onclick="MergedProviderSelector.goToPage(${this.currentPage + 1})"` : 'tabindex="-1" aria-disabled="true"'}>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        paginationNav.innerHTML = paginationHTML;
    },
    // Go to specific page
    goToPage(pageNumber) {
        if (pageNumber < 1 || pageNumber > this.totalPages || pageNumber === this.currentPage) {
            return;
        }
        this.currentPage = pageNumber;
        // If we have current search results, paginate them
        if (this.currentSearchResults && this.currentSearchResults.length > 0) {
            this.paginateCurrentResults();
        }
    },
    // Paginate current results
    paginateCurrentResults() {
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const paginatedData = this.currentSearchResults.slice(startIndex, endIndex);
        const results = {
            data: paginatedData,
            total: this.currentSearchResults.length
        };
        this.renderSearchResults(results);
        this.updateResultsInfo(results);
    },
    // ***** HELPER METHODS (From Simple Version) *****
    // Format price from various data structures
    formatPrice(provider) {
        if (provider.price_range && provider.price_range.min) {
            return `From $${provider.price_range.min}`;
        }
        if (provider.base_price) {
            return `$${provider.base_price}`;
        }
        if (provider.estimated_price) {
            return `From $${provider.estimated_price}`;
        }
        if (provider.economy_price || (provider.pricing && provider.pricing.economy)) {
            return `From $${provider.economy_price || provider.pricing.economy}`;
        }
        if (provider.price) {
            return typeof provider.price === 'number' ? `$${provider.price}` : provider.price;
        }
        return 'Price on request';
    },
    // Get features from various data structures
    getFeatures(provider) {
        let features = [];
        // Check various feature fields
        if (provider.amenities && Array.isArray(provider.amenities)) {
            features = features.concat(provider.amenities);
        }
        if (provider.features && Array.isArray(provider.features)) {
            features = features.concat(provider.features);
        }
        if (provider.specifications && Array.isArray(provider.specifications)) {
            features = features.concat(provider.specifications);
        }
        // Add service-specific features
        if (provider.star_rating) {
            features.push(`${provider.star_rating} Star`);
        }
        if (provider.aircraft_type) {
            features.push(provider.aircraft_type);
        }
        if (provider.transport_type) {
            features.push(provider.transport_type);
        }
        if (provider.max_passengers) {
            features.push(`Up to ${provider.max_passengers} passengers`);
        }
        return features.filter(f => f && f.trim()).slice(0, 5);
    },
    // Generate star rating HTML
    generateStarRating(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        let html = '<div class="star-rating d-flex">';
        // Full stars
        for (let i = 0; i < fullStars; i++) {
            html += '<i class="fas fa-star text-warning"></i>';
        }
        // Half star
        if (hasHalfStar) {
            html += '<i class="fas fa-star-half-alt text-warning"></i>';
        }
        // Empty stars
        for (let i = 0; i < emptyStars; i++) {
            html += '<i class="far fa-star text-muted"></i>';
        }
        html += '</div>';
        return html;
    },
    // ***** UI HELPER METHODS *****
    // Apply view mode (grid/list)
    applyViewMode() {
        const container = document.getElementById('providerSearchResults');
        if (!container) return;
        const activeBtn = document.querySelector('#viewModeToggle button.active');
        const viewMode = activeBtn ? activeBtn.getAttribute('data-view') : 'grid';
        if (viewMode === 'list') {
            container.classList.add('list-view');
        } else {
            container.classList.remove('list-view');
        }
    },
    // Get no results HTML
    getNoResultsHTML() {
        return `
            <div class="text-center py-5 text-muted col-12">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h6>No Providers Found</h6>
                <p>Try adjusting your search criteria or check back later</p>
            </div>
        `;
    },
    // Get no services available HTML
    getNoServicesHTML(serviceType) {
        const serviceIcons = {
            hotels: 'fas fa-hotel',
            flights: 'fas fa-plane',
            transport: 'fas fa-bus'
        };
        const serviceNames = {
            hotels: 'Hotels',
            flights: 'Flights',
            transport: 'Transport Services'
        };
        const icon = serviceIcons[serviceType] || 'fas fa-exclamation-circle';
        const serviceName = serviceNames[serviceType] || serviceType;
        return `
            <div class="text-center py-5 col-12">
                <div class="mb-4">
                    <i class="${icon} fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No ${serviceName} Available</h4>
                    <p class="text-muted mb-4">
                        There are currently no ${serviceName.toLowerCase()} available in the platform.<br>
                        Please check back later or contact support for assistance.
                    </p>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-outline-primary" onclick="MergedProviderSelector.loadProvidersFromAPI('${serviceType}')">
                        <i class="fas fa-refresh me-1"></i>Refresh
                    </button>
                    <button class="btn btn-primary" onclick="MergedProviderSelector.showExternalForm('${serviceType}')">
                        <i class="fas fa-plus me-1"></i>Add Your Own
                    </button>
                </div>
            </div>
        `;
    },
    // Get service error HTML
    getServiceErrorHTML(serviceType, errorMessage) {
        const serviceNames = {
            hotels: 'Hotels',
            flights: 'Flights',
            transport: 'Transport Services'
        };
        const serviceName = serviceNames[serviceType] || serviceType;
        return `
            <div class="text-center py-5 col-12">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h4 class="text-warning">Error Loading ${serviceName}</h4>
                    <p class="text-muted mb-2">
                        We encountered an error while loading ${serviceName.toLowerCase()}.
                    </p>
                    <small class="text-muted d-block mb-4">
                        Error: ${errorMessage}
                    </small>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-outline-primary" onclick="MergedProviderSelector.loadProvidersFromAPI('${serviceType}')">
                        <i class="fas fa-refresh me-1"></i>Try Again
                    </button>
                    <button class="btn btn-secondary" onclick="MergedProviderSelector.showExternalForm('${serviceType}')">
                        <i class="fas fa-plus me-1"></i>Add Your Own
                    </button>
                </div>
            </div>
        `;
    },
    // Show toast notification
    showToast(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    },
    // ***** PROVIDER MANAGEMENT METHODS *****
    // Add provider to main selection
    addProvider(provider) {
        if (!provider || !provider.type) {
            return false;
        }
        // Handle service type conversion carefully
        let serviceType;
        if (provider.type === 'hotel') {
            serviceType = 'hotels';
        } else if (provider.type === 'flight') {
            serviceType = 'flights';
        } else if (provider.type === 'transport') {
            serviceType = 'transport'; // Keep as transport, not transports
        } else {
            // For any other types, use the type with 's' suffix
            serviceType = provider.type + 's';
        }
        if (!this.selectedProviders[serviceType]) {
            this.selectedProviders[serviceType] = [];
        }
        // Check if already exists
        const existingIndex = this.selectedProviders[serviceType].findIndex(p => p.id === provider.id);
        if (existingIndex === -1) {
            this.selectedProviders[serviceType].push(provider);
            this.updateUI();
            // Also create minimal record for draft saving
            provider._draftRecord = this.createMinimalProviderRecord(provider, provider.type);
            return true;
        } else {
            this.showToast(`${provider.name} is already selected`, 'warning');
            return false;
        }
    },
    // Remove provider from selections (with legacy system sync)
    removeProvider(providerId, serviceType) {
        if (!this.selectedProviders[serviceType]) {
            return false;
        }
        const index = this.selectedProviders[serviceType].findIndex(p => p.id == providerId); // Use == for flexible comparison
        if (index > -1) {
            const provider = this.selectedProviders[serviceType][index];
            this.selectedProviders[serviceType].splice(index, 1);
            // SYNC: Also remove from legacy window.selectedProviders if it exists
            if (window.selectedProviders && window.selectedProviders[serviceType]) {
                const legacyIndex = window.selectedProviders[serviceType].findIndex(p => p.id == providerId);
                if (legacyIndex > -1) {
                    window.selectedProviders[serviceType].splice(legacyIndex, 1);
                    // Update legacy UI functions
                    if (typeof window.updateHiddenFormFields === 'function') {
                        window.updateHiddenFormFields();
                    }
                    if (typeof window.updateProviderBadges === 'function') {
                        window.updateProviderBadges();
                    }
                }
            }
            // Update our UI
            this.updateUI();
            this.showToast(`${provider.name || 'Provider'} removed`, 'info');
            return true;
        }
        return false;
    },
    // Update main UI and ensure legacy system sync
    updateUI() {
        this.updateCountBadges();
        this.updateSummaryStats();
        this.renderAllProviders();
        // CRITICAL: Sync with legacy system to prevent data conflicts
        this.syncWithLegacySystem();
    },
    // Sync current state with legacy system
    syncWithLegacySystem() {
        if (window.selectedProviders) {
            // Overwrite legacy system with our current state
            window.selectedProviders.hotels = [...this.selectedProviders.hotels];
            window.selectedProviders.flights = [...this.selectedProviders.flights];
            window.selectedProviders.transport = [...this.selectedProviders.transport];
            // Update legacy UI functions if available
            if (typeof window.updateHiddenFormFields === 'function') {
                window.updateHiddenFormFields();
            }
            if (typeof window.updateProviderBadges === 'function') {
                window.updateProviderBadges();
            }
        }
    },
    // Get minimal provider records for draft saving
    getMinimalProvidersForDraft() {
        const minimalData = {};
        // Extract minimal records for each service type
        ['hotels', 'flights', 'transport'].forEach(serviceType => {
            const providers = this.selectedProviders[serviceType] || [];
            const serviceTypeSingle = serviceType === 'transport' ? 'transport' : serviceType.slice(0, -1);
            const minimalRecords = providers.map(provider => {
                return this.createMinimalProviderRecord(provider, serviceTypeSingle);
            });
            minimalData[`selected_${serviceType}`] = minimalRecords;
        });
        return minimalData;
    },
    // Update count badges
    updateCountBadges() {
        const hotelCount = document.getElementById('hotelCountBadge');
        const flightCount = document.getElementById('flightCountBadge');
        const transportCount = document.getElementById('transportCountBadge');
        if (hotelCount) hotelCount.textContent = this.selectedProviders.hotels.length;
        if (flightCount) flightCount.textContent = this.selectedProviders.flights.length;
        if (transportCount) transportCount.textContent = this.selectedProviders.transport.length;
    },
    // Update summary statistics
    updateSummaryStats() {
        const hotelSummary = document.getElementById('hotelSummaryCount');
        const flightSummary = document.getElementById('flightSummaryCount');
        const transportSummary = document.getElementById('transportSummaryCount');
        if (hotelSummary) hotelSummary.textContent = `${this.selectedProviders.hotels.length} selected`;
        if (flightSummary) flightSummary.textContent = `${this.selectedProviders.flights.length} selected`;
        if (transportSummary) transportSummary.textContent = `${this.selectedProviders.transport.length} selected`;
    },
    // Render all providers in their respective sections
    renderAllProviders() {
        // Render each service type
        this.renderServiceProviders('hotels');
        this.renderServiceProviders('flights');
        this.renderServiceProviders('transport');
        // Update overall status summary
        this.updateOverallStatusSummary();
    },
    // Render providers for a specific service type
    renderServiceProviders(serviceType) {
        const providers = this.selectedProviders[serviceType] || [];
        const containerId = this.getProviderContainerId(serviceType);
        const emptyStateId = this.getEmptyStateId(serviceType);
        const container = document.getElementById(containerId);
        const emptyState = document.getElementById(emptyStateId);
        if (!container) return;
        if (providers.length === 0) {
            // Show empty state
            container.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
        } else {
            // Hide empty state and show providers
            if (emptyState) emptyState.style.display = 'none';
            container.style.display = 'block';
            // Generate provider cards HTML
            const providerHTML = providers.map(provider => this.generateProviderCard(provider, serviceType)).join('');
            container.innerHTML = providerHTML;
        }
    },
    // Get container ID for service type
    getProviderContainerId(serviceType) {
        const containerMap = {
            'hotels': 'selectedHotels',
            'flights': 'selectedFlights',
            'transport': 'selectedTransport'
        };
        return containerMap[serviceType];
    },
    // Get empty state ID for service type
    getEmptyStateId(serviceType) {
        const emptyStateMap = {
            'hotels': 'noHotelsSelected',
            'flights': 'noFlightsSelected',
            'transport': 'noTransportSelected'
        };
        return emptyStateMap[serviceType];
    },
    // Generate provider card HTML
    generateProviderCard(provider, serviceType) {
        const statusClass = this.getStatusClass(provider.status);
        const statusIcon = this.getStatusIcon(provider.status);
        const serviceIcon = this.getServiceIcon(serviceType);
        return `
            <div class="card mb-3 provider-card" data-provider-id="${provider.id}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <!-- Header Row -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="provider-icon me-3">
                                    <div class="p-2 rounded-circle bg-light">
                                        <i class="${serviceIcon} fa-lg text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="card-title mb-1 d-flex align-items-center">
                                                ${provider.name || provider.airline || provider.service_name || 'Unknown Provider'}
                                                ${this.generateProviderStatusBadges(provider)}
                                            </h6>
                                            ${provider.company_name || (provider.provider && provider.provider.company_name) ? `
                                                <div class="text-muted small mb-1">
                                                    <i class="fas fa-building me-1"></i>${provider.company_name || provider.provider.company_name}
                                                </div>
                                            ` : ''}
                                        </div>
                                        <div class="text-end">
                                            ${this.generatePricingInfo(provider, serviceType)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Details Row - Always render structure for consistent layout -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="provider-contact-info mb-2">
                                        ${provider.email ? `
                                            <div class="mb-1">
                                                <i class="fas fa-envelope text-muted me-2"></i>
                                                <span class="small">${provider.email}</span>
                                            </div>
                                        ` : ''}
                                        ${provider.phone ? `
                                            <div class="mb-1">
                                                <i class="fas fa-phone text-muted me-2"></i>
                                                <span class="small">${provider.phone}</span>
                                            </div>
                                        ` : ''}
                                        ${(provider.city || provider.country) ? `
                                            <div class="mb-1">
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <span class="small">${[provider.city, provider.country].filter(Boolean).join(', ')}</span>
                                            </div>
                                        ` : ''}
                                        ${provider.website ? `
                                            <div class="mb-1">
                                                <i class="fas fa-globe text-muted me-2"></i>
                                                <a href="${provider.website}" target="_blank" class="small text-decoration-none">Website</a>
                                            </div>
                                        ` : ''}
                                        <!-- Ensure minimum content to maintain card structure -->
                                        ${!provider.email && !provider.phone && !provider.city && !provider.country && !provider.website ? `
                                            <div class="mb-1 text-muted small">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Contact details not provided
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="service-info-container">
                                        ${this.generateEnhancedServiceInfo(provider, serviceType)}
                                    </div>
                                </div>
                            </div>
                            <!-- Features/Amenities Row -->
                            ${this.getFeatures(provider).length > 0 ? `
                                <div class="features-row mt-2 mb-3">
                                    <div class="d-flex flex-wrap gap-1">
                                        ${this.generateFeatureBadges(provider)}
                                    </div>
                                </div>
                            ` : ''}
            <!-- Service Request Status Row -->
            ${provider.service_request ? `
                <div class="service-request-status mt-3 p-3 border rounded">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">
                            <i class="fas fa-handshake text-info me-1"></i>
                            Service Request Details
                        </h6>
                    </div>
                                    <div class="row g-2 small">
                                        <div class="col-sm-6">
                                            <strong>Request ID:</strong> #${provider.service_request.id}
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Quantity:</strong> <span>${provider.service_request.requested_quantity || 'Auto-calculated'}</span>
                                        </div>
                                        ${provider.service_request.expires_at ? `
                                            <div class="col-sm-6">
                                                <strong>Expires:</strong> <span class="${this.getExpirationClass(provider.service_request.expires_at)}">
                                                    ${this.formatRelativeTime(provider.service_request.expires_at)}
                                                </span>
                                            </div>
                                        ` : ''}
                                        ${provider.service_request.status === 'rejected' && provider.service_request.rejection_reason ? `
                                            <div class="col-12 mt-2">
                                                <strong class="text-danger">Rejection Reason:</strong><br>
                                                <small class="text-muted">${provider.service_request.rejection_reason}</small>
                                            </div>
                                        ` : ''}
                                    </div>
                                    <div class="mt-2">
                                        ${this.generateServiceRequestActions(provider, serviceType)}
                                    </div>
                                </div>
                            ` : ''}
                            <!-- Service Request Creation Row (if no request exists) -->
                            ${!provider.service_request && !provider.own_service ? `
                                <div class="service-request-creation mt-3 p-3 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <h6 class="mb-1 text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Approval Required
                                            </h6>
                                            <small class="text-muted">This provider requires approval before use in your package.</small>
                                        </div>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="ServiceRequestManager.createServiceRequest('${provider.id}', '${serviceType}')">
                                            <i class="fas fa-paper-plane me-1"></i> Request Approval
                                        </button>
                                    </div>
                                </div>
                            ` : ''}
                            <!-- Own Service Notice -->
                            ${provider.own_service ? `
                                <div class="own-service-notice mt-3 p-2 border rounded bg-success bg-opacity-10">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <small class="text-success mb-0">
                                            <strong>Your Own Service</strong> - No approval required
                                        </small>
                                    </div>
                                </div>
                            ` : ''}
                            <!-- Actions Row -->
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-3">
                                <div class="provider-meta small text-muted">
                                    ${provider.created_at ? `
                                        <span><i class="fas fa-calendar-plus me-1"></i>Added ${new Date(provider.created_at).toLocaleDateString()}</span>
                                    ` : ''}
                                    ${provider.commission_rate ? `
                                        <span class="ms-3"><i class="fas fa-percentage me-1"></i>Commission: ${provider.commission_rate}%</span>
                                    ` : ''}
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" onclick="MergedProviderSelector.viewProviderDetails('${provider.id}', '${serviceType}')" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${provider.service_request ? `
                                        <button type="button" class="btn btn-outline-info" onclick="ServiceRequestManager.viewServiceRequest('${provider.service_request.id}')" title="View Request Details">
                                            <i class="fas fa-handshake"></i>
                                        </button>
                                    ` : ''}
                                    ${provider.provider_type === 'external' ? `
                                        <button type="button" class="btn btn-outline-warning" onclick="MergedProviderSelector.editExternalProvider('${provider.id}', '${serviceType}')" title="Edit Provider">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    ` : ''}
                                    <button type="button" class="btn btn-outline-danger" onclick="MergedProviderSelector.removeProvider('${provider.id}', '${serviceType}')" title="Remove Provider">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },
    // Generate pricing information for the card
    generatePricingInfo(provider, serviceType) {
        let priceInfo = '';
        // Handle different pricing structures
        if (serviceType === 'flights') {
            if (provider.economy_price) {
                priceInfo = `
                    <div class="price-display">
                        <div class="h6 mb-0 text-success">From ${provider.currency || 'USD'} ${provider.economy_price}</div>
                        <small class="text-muted">Economy Class</small>
                    </div>
                `;
            }
        } else if (serviceType === 'hotels') {
            if (provider.estimated_price || provider.base_price) {
                const price = provider.estimated_price || provider.base_price;
                priceInfo = `
                    <div class="price-display">
                        <div class="h6 mb-0 text-success">From ${provider.currency || 'USD'} ${price}</div>
                        <small class="text-muted">per night</small>
                    </div>
                `;
            }
        } else if (serviceType === 'transport') {
            if (provider.price) {
                priceInfo = `
                    <div class="price-display">
                        <div class="h6 mb-0 text-success">${typeof provider.price === 'string' ? provider.price : (provider.currency || 'USD') + ' ' + provider.price}</div>
                        <small class="text-muted">per trip</small>
                    </div>
                `;
            }
        } else if (provider.base_price > 0) {
            priceInfo = `
                <div class="price-display">
                    <div class="h6 mb-0 text-success">${provider.currency || 'USD'} ${provider.base_price}</div>
                    <small class="text-muted">base price</small>
                </div>
            `;
        }
        return priceInfo;
    },
    // Generate enhanced service-specific information
    generateEnhancedServiceInfo(provider, serviceType) {
        let serviceInfo = '';
        if (serviceType === 'hotels') {
            serviceInfo = `<div class="service-specific-info">`;
            if (provider.star_rating) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-star text-warning me-2"></i>
                        <span class="small">${'★'.repeat(provider.star_rating)} ${provider.star_rating} Star Hotel</span>
                    </div>
                `;
            }
            if (provider.total_rooms) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-bed text-muted me-2"></i>
                        <span class="small">${provider.total_rooms} Rooms</span>
                    </div>
                `;
            }
            if (provider.room_types) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-door-open text-muted me-2"></i>
                        <span class="small">${provider.room_types}</span>
                    </div>
                `;
            }
            serviceInfo += `</div>`;
        } else if (serviceType === 'flights') {
            serviceInfo = `<div class="service-specific-info">`;
            if (provider.flight_number) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-plane text-muted me-2"></i>
                        <span class="small">${provider.flight_number}</span>
                    </div>
                `;
            }
            if (provider.departure_airport && provider.arrival_airport) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-route text-muted me-2"></i>
                        <span class="small">${provider.departure_airport} → ${provider.arrival_airport}</span>
                    </div>
                `;
            }
            if (provider.aircraft_type) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-cogs text-muted me-2"></i>
                        <span class="small">${provider.aircraft_type}</span>
                    </div>
                `;
            }
            if (provider.available_seats && provider.total_seats) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-users text-muted me-2"></i>
                        <span class="small">${provider.available_seats}/${provider.total_seats} seats available</span>
                    </div>
                `;
            }
            serviceInfo += `</div>`;
        } else if (serviceType === 'transport') {
            serviceInfo = `<div class="service-specific-info">`;
            if (provider.transport_type) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-bus text-muted me-2"></i>
                        <span class="small">${this.formatTransportType(provider.transport_type)}</span>
                    </div>
                `;
            }
            if (provider.max_passengers || provider.vehicle_capacity) {
                const capacity = provider.max_passengers || provider.vehicle_capacity;
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-users text-muted me-2"></i>
                        <span class="small">Up to ${capacity} passengers</span>
                    </div>
                `;
            }
            if (provider.fleet_size) {
                serviceInfo += `
                    <div class="mb-1">
                        <i class="fas fa-car text-muted me-2"></i>
                        <span class="small">${provider.fleet_size} vehicles in fleet</span>
                    </div>
                `;
            }
            serviceInfo += `</div>`;
        }
        return serviceInfo;
    },
    // Generate feature badges
    generateFeatureBadges(provider) {
        const features = this.getFeatures(provider);
        return features.slice(0, 5).map(feature => `
            <span class="badge bg-light text-dark me-1 mb-1">
                <i class="fas fa-check text-success me-1"></i>${feature}
            </span>
        `).join('');
    },
    // Generate service-specific information (legacy method - keeping for compatibility)
    generateServiceSpecificInfo(provider, serviceType) {
        if (serviceType === 'hotels' && provider.star_rating) {
            return `<div class="mt-1"><small class="text-muted">${'★'.repeat(provider.star_rating)} ${provider.star_rating} Star Hotel</small></div>`;
        } else if (serviceType === 'flights' && provider.airline_code) {
            return `<div class="mt-1"><small class="text-muted">Airline: ${provider.airline_code}</small></div>`;
        } else if (serviceType === 'transport' && provider.transport_type) {
            return `<div class="mt-1"><small class="text-muted">${this.formatTransportType(provider.transport_type)}</small></div>`;
        }
        return '';
    },
    // Get status CSS class
    getStatusClass(status) {
        const statusClasses = {
            'approved': 'bg-success',
            'pending': 'bg-warning text-dark',
            'rejected': 'bg-danger',
            'active': 'bg-success',
            'inactive': 'bg-secondary'
        };
        return statusClasses[status] || 'bg-secondary';
    },
    // Get status icon
    getStatusIcon(status) {
        const statusIcons = {
            'approved': 'fas fa-check',
            'pending': 'fas fa-clock',
            'rejected': 'fas fa-times',
            'active': 'fas fa-check',
            'inactive': 'fas fa-pause'
        };
        return statusIcons[status] || 'fas fa-question';
    },
    // Get service icon
    getServiceIcon(serviceType) {
        const serviceIcons = {
            'hotels': 'fas fa-bed',
            'flights': 'fas fa-plane',
            'transport': 'fas fa-bus'
        };
        return serviceIcons[serviceType] || 'fas fa-cog';
    },
    // Format status text
    formatStatus(status) {
        if (!status || typeof status !== 'string') {
            return 'Unknown';
        }
        return status.charAt(0).toUpperCase() + status.slice(1);
    },
    // Format transport type
    formatTransportType(type) {
        const typeMap = {
            'bus': 'Bus Service',
            'train': 'Train Service',
            'taxi': 'Taxi/Car Service',
            'shuttle': 'Airport Shuttle',
            'rental': 'Car Rental',
            'boat': 'Boat/Ferry',
            'other': 'Other Transport'
        };
        return typeMap[type] || type;
    },
    // Update overall status summary
    updateOverallStatusSummary() {
        const totalProviders = Object.values(this.selectedProviders).reduce((total, providers) => total + providers.length, 0);
        const overallStatusElement = document.getElementById('overallStatusSummary');
        if (!overallStatusElement) return;
        let statusText = 'No providers selected';
        let statusColor = 'text-muted';
        if (totalProviders > 0) {
            // Count providers by status
            let approvedCount = 0;
            let pendingCount = 0;
            let rejectedCount = 0;
            Object.values(this.selectedProviders).forEach(providers => {
                providers.forEach(provider => {
                    switch (provider.status) {
                        case 'approved':
                        case 'active':
                            approvedCount++;
                            break;
                        case 'pending':
                            pendingCount++;
                            break;
                        case 'rejected':
                        case 'inactive':
                            rejectedCount++;
                            break;
                    }
                });
            });
            if (rejectedCount > 0) {
                statusText = `${rejectedCount} provider(s) rejected`;
                statusColor = 'text-danger';
            } else if (pendingCount > 0) {
                statusText = `${pendingCount} provider(s) pending approval`;
                statusColor = 'text-warning';
            } else if (approvedCount === totalProviders) {
                statusText = 'All providers approved';
                statusColor = 'text-success';
            } else {
                statusText = `${totalProviders} provider(s) selected`;
                statusColor = 'text-primary';
            }
        }
        overallStatusElement.textContent = statusText;
        overallStatusElement.className = `summary-value fw-bold d-block ${statusColor}`;
    },
    // View provider details
    viewProviderDetails(providerId, serviceType) {
        let provider = null;
        // Convert providerId to both string and number for comparison
        const providerIdStr = String(providerId);
        const providerIdNum = parseInt(providerId);
        // First, try to find the provider in selected providers
        if (this.selectedProviders[serviceType]) {
            provider = this.selectedProviders[serviceType].find(p => {
                return String(p.id) === providerIdStr || p.id === providerIdNum;
            });
        }
        // If not found in selected providers, try to find in search results
        if (!provider && this.currentSearchResults) {
            provider = this.currentSearchResults.find(p => {
                return String(p.id) === providerIdStr || p.id === providerIdNum;
            });
        }
        // If still not found, try to find in own flights if available
        if (!provider && serviceType === 'flights' && this.ownFlights) {
            provider = this.ownFlights.find(f => {
                return String(f.id) === providerIdStr || f.id === providerIdNum;
            });
        }
        if (!provider) {
            this.showToast('Provider not found', 'error');
            return;
        }
        // Create and show the details modal
        if (typeof this.showProviderDetailsModal === 'function') {
            this.showProviderDetailsModal(provider, serviceType);
        } else {
            this.showToast('Details modal not available', 'error');
        }
    },
    // Show provider details modal
    showProviderDetailsModal(provider, serviceType) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('providerDetailsModal');
        if (!modal) {
            this.createProviderDetailsModal();
            modal = document.getElementById('providerDetailsModal');
        }
        // Update modal content
        this.updateProviderDetailsModal(provider, serviceType);
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    },
    // Create provider details modal structure
    createProviderDetailsModal() {
        const modalHTML = `
            <div class="modal fade" id="providerDetailsModal" tabindex="-1" aria-labelledby="providerDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="providerDetailsModalLabel">
                                <i class="fas fa-info-circle me-2"></i>
                                Provider Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="providerDetailsModalBody">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <div id="providerDetailsActions">
                                <!-- Dynamic action buttons will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    },
    // Update provider details modal content
    updateProviderDetailsModal(provider, serviceType) {
        const modalTitle = document.getElementById('providerDetailsModalLabel');
        const modalBody = document.getElementById('providerDetailsModalBody');
        const modalActions = document.getElementById('providerDetailsActions');
        if (!modalBody) return;
        // Update title
        if (modalTitle) {
            const serviceIcon = this.getServiceIcon(serviceType);
            modalTitle.innerHTML = `
                <i class="${serviceIcon} me-2"></i>
                ${provider.name || provider.airline || provider.service_name || 'Provider'} Details
            `;
        }
        // Generate detailed content
        modalBody.innerHTML = this.generateDetailedProviderContent(provider, serviceType);
        // Update action buttons
        if (modalActions) {
            modalActions.innerHTML = this.generateDetailedProviderActions(provider, serviceType);
        }
    },
    // Generate detailed provider content
    generateDetailedProviderContent(provider, serviceType) {
        const statusClass = this.getStatusClass(provider.status);
        const statusIcon = this.getStatusIcon(provider.status);
        return `
            <div class="container-fluid">
                <!-- Provider Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="provider-avatar me-3">
                                <div class="p-3 rounded-circle bg-light">
                                    <i class="${this.getServiceIcon(serviceType)} fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-1">${provider.name || provider.airline || provider.service_name || 'Unknown Provider'}</h4>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    ${this.generateProviderStatusBadges(provider)}
                                </div>
                                ${provider.company_name || (provider.provider && provider.provider.company_name) ? `
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-building me-2"></i>${provider.company_name || provider.provider.company_name}
                                    </p>
                                ` : ''}
                            </div>
                            <div class="text-end">
                                ${this.generateDetailedPricingInfo(provider, serviceType)}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Contact Information -->
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
                            </div>
                            <div class="card-body">
                                ${this.generateContactInfo(provider)}
                            </div>
                        </div>
                        <!-- Features & Amenities -->
                        ${this.generateFeaturesSection(provider)}
                    </div>
                    <!-- Service Specific Information -->
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="${this.getServiceIcon(serviceType)} me-2"></i>
                                    ${this.getServiceDisplayName(serviceType)} Details
                                </h6>
                            </div>
                            <div class="card-body">
                                ${this.generateServiceSpecificDetails(provider, serviceType)}
                            </div>
                        </div>
                        <!-- Additional Information -->
                        ${this.generateAdditionalInfoSection(provider)}
                    </div>
                </div>
                <!-- Notes Section -->
                ${provider.notes || provider.description ? `
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes & Description</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">${provider.notes || provider.description}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    },
    // Generate detailed pricing information
    generateDetailedPricingInfo(provider, serviceType) {
        let pricingHTML = '<div class="pricing-details">';
        if (serviceType === 'flights') {
            if (provider.economy_price) {
                pricingHTML += `<div class="price-item mb-1"><strong>Economy:</strong> ${provider.currency || 'USD'} ${provider.economy_price}</div>`;
            }
            if (provider.business_price) {
                pricingHTML += `<div class="price-item mb-1"><strong>Business:</strong> ${provider.currency || 'USD'} ${provider.business_price}</div>`;
            }
            if (provider.first_class_price) {
                pricingHTML += `<div class="price-item mb-1"><strong>First Class:</strong> ${provider.currency || 'USD'} ${provider.first_class_price}</div>`;
            }
        } else if (provider.estimated_price || provider.base_price) {
            const price = provider.estimated_price || provider.base_price;
            pricingHTML += `<div class="price-item mb-1"><strong>Starting from:</strong> ${provider.currency || 'USD'} ${price}</div>`;
        }
        if (provider.commission_rate) {
            pricingHTML += `<div class="commission-info text-success"><i class="fas fa-percentage me-1"></i>Commission: ${provider.commission_rate}%</div>`;
        }
        pricingHTML += '</div>';
        return pricingHTML;
    },
    // Generate contact information
    generateContactInfo(provider) {
        let contactHTML = '';
        if (provider.email) {
            contactHTML += `
                <div class="contact-item mb-2">
                    <i class="fas fa-envelope text-primary me-2"></i>
                    <a href="mailto:${provider.email}" class="text-decoration-none">${provider.email}</a>
                </div>
            `;
        }
        if (provider.phone) {
            contactHTML += `
                <div class="contact-item mb-2">
                    <i class="fas fa-phone text-primary me-2"></i>
                    <a href="tel:${provider.phone}" class="text-decoration-none">${provider.phone}</a>
                </div>
            `;
        }
        if (provider.website) {
            contactHTML += `
                <div class="contact-item mb-2">
                    <i class="fas fa-globe text-primary me-2"></i>
                    <a href="${provider.website}" target="_blank" class="text-decoration-none">${provider.website}</a>
                </div>
            `;
        }
        if (provider.city || provider.country) {
            contactHTML += `
                <div class="contact-item mb-2">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                    <span>${[provider.city, provider.country].filter(Boolean).join(', ')}</span>
                </div>
            `;
        }
        if (!contactHTML) {
            contactHTML = '<p class="text-muted mb-0">No contact information available</p>';
        }
        return contactHTML;
    },
    // Generate features section
    generateFeaturesSection(provider) {
        const features = this.getFeatures(provider);
        if (features.length === 0) {
            return '';
        }
        return `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-star me-2"></i>Features & Amenities</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        ${features.map(feature => `
                            <div class="col-6 col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="small">${feature}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    },
    // Generate service specific details
    generateServiceSpecificDetails(provider, serviceType) {
        let detailsHTML = '';
        if (serviceType === 'hotels') {
            detailsHTML += this.generateHotelDetails(provider);
        } else if (serviceType === 'flights') {
            detailsHTML += this.generateFlightDetails(provider);
        } else if (serviceType === 'transport') {
            detailsHTML += this.generateTransportDetails(provider);
        }
        return detailsHTML || '<p class="text-muted mb-0">No specific details available</p>';
    },
    // Generate hotel-specific details
    generateHotelDetails(provider) {
        let html = '';
        if (provider.star_rating) {
            html += `
                <div class="detail-item mb-3">
                    <strong>Rating:</strong>
                    <div class="mt-1">
                        ${'★'.repeat(provider.star_rating)}
                        <span class="ms-2">${provider.star_rating} Star Hotel</span>
                    </div>
                </div>
            `;
        }
        if (provider.total_rooms) {
            html += `<div class="detail-item mb-2"><strong>Total Rooms:</strong> ${provider.total_rooms}</div>`;
        }
        if (provider.room_types) {
            html += `<div class="detail-item mb-2"><strong>Room Types:</strong> ${provider.room_types}</div>`;
        }
        if (provider.check_in || provider.check_out) {
            html += `
                <div class="detail-item mb-2">
                    <strong>Check-in/Check-out:</strong> 
                    ${provider.check_in || 'N/A'} / ${provider.check_out || 'N/A'}
                </div>
            `;
        }
        return html;
    },
    // Generate flight-specific details
    generateFlightDetails(provider) {
        let html = '';
        if (provider.airline) {
            html += `<div class="detail-item mb-2"><strong>Airline:</strong> ${provider.airline}</div>`;
        }
        if (provider.flight_number) {
            html += `<div class="detail-item mb-2"><strong>Flight Number:</strong> ${provider.flight_number}</div>`;
        }
        if (provider.departure_airport && provider.arrival_airport) {
            html += `<div class="detail-item mb-2"><strong>Route:</strong> ${provider.departure_airport} → ${provider.arrival_airport}</div>`;
        }
        if (provider.departure_datetime) {
            html += `<div class="detail-item mb-2"><strong>Departure:</strong> ${new Date(provider.departure_datetime).toLocaleString()}</div>`;
        }
        if (provider.arrival_datetime) {
            html += `<div class="detail-item mb-2"><strong>Arrival:</strong> ${new Date(provider.arrival_datetime).toLocaleString()}</div>`;
        }
        if (provider.duration) {
            html += `<div class="detail-item mb-2"><strong>Duration:</strong> ${provider.duration}</div>`;
        }
        if (provider.aircraft_type) {
            html += `<div class="detail-item mb-2"><strong>Aircraft:</strong> ${provider.aircraft_type}</div>`;
        }
        if (provider.total_seats) {
            html += `<div class="detail-item mb-2"><strong>Total Seats:</strong> ${provider.total_seats}</div>`;
        }
        if (provider.available_seats) {
            html += `<div class="detail-item mb-2"><strong>Available Seats:</strong> ${provider.available_seats}</div>`;
        }
        return html;
    },
    // Generate transport-specific details
    generateTransportDetails(provider) {
        let html = '';
        if (provider.transport_type) {
            html += `<div class="detail-item mb-2"><strong>Transport Type:</strong> ${this.formatTransportType(provider.transport_type)}</div>`;
        }
        if (provider.max_passengers || provider.vehicle_capacity) {
            const capacity = provider.max_passengers || provider.vehicle_capacity;
            html += `<div class="detail-item mb-2"><strong>Passenger Capacity:</strong> ${capacity} passengers</div>`;
        }
        if (provider.fleet_size) {
            html += `<div class="detail-item mb-2"><strong>Fleet Size:</strong> ${provider.fleet_size} vehicles</div>`;
        }
        if (provider.service_areas) {
            html += `<div class="detail-item mb-2"><strong>Service Areas:</strong> ${provider.service_areas}</div>`;
        }
        if (provider.routes && Array.isArray(provider.routes)) {
            html += `<div class="detail-item mb-2"><strong>Available Routes:</strong> ${provider.routes.length} routes</div>`;
        }
        return html;
    },
    // Generate additional information section
    generateAdditionalInfoSection(provider) {
        let additionalInfo = '';
        if (provider.created_at) {
            additionalInfo += `<div class="info-item mb-2"><strong>Added:</strong> ${new Date(provider.created_at).toLocaleDateString()}</div>`;
        }
        if (provider.updated_at && provider.updated_at !== provider.created_at) {
            additionalInfo += `<div class="info-item mb-2"><strong>Last Updated:</strong> ${new Date(provider.updated_at).toLocaleDateString()}</div>`;
        }
        if (provider.provider_id) {
            additionalInfo += `<div class="info-item mb-2"><strong>Provider ID:</strong> ${provider.provider_id}</div>`;
        }
        if (!additionalInfo) {
            return '';
        }
        return `
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info me-2"></i>Additional Information</h6>
                </div>
                <div class="card-body">
                    ${additionalInfo}
                </div>
            </div>
        `;
    },
    // Generate detailed provider actions
    generateDetailedProviderActions(provider, serviceType) {
        let actionsHTML = '';
        if (provider.provider_type === 'external') {
            actionsHTML += `
                <button type="button" class="btn btn-warning me-2" 
                        onclick="MergedProviderSelector.editExternalProvider('${provider.id}', '${serviceType}')">
                    <i class="fas fa-edit me-1"></i>Edit Provider
                </button>
            `;
        }
        if (provider.website) {
            actionsHTML += `
                <a href="${provider.website}" target="_blank" class="btn btn-info me-2">
                    <i class="fas fa-external-link-alt me-1"></i>Visit Website
                </a>
            `;
        }
        actionsHTML += `
            <button type="button" class="btn btn-danger" 
                    onclick="MergedProviderSelector.removeProvider('${provider.id}', '${serviceType}'); bootstrap.Modal.getInstance(document.getElementById('providerDetailsModal')).hide();">
                <i class="fas fa-trash me-1"></i>Remove Provider
            </button>
        `;
        return actionsHTML;
    },
    // Get service display name
    getServiceDisplayName(serviceType) {
        const serviceNames = {
            'hotels': 'Hotel',
            'flights': 'Flight',
            'transport': 'Transport'
        };
        return serviceNames[serviceType] || serviceType;
    },
    // Edit external provider (placeholder)
    editExternalProvider(providerId, serviceType) {
        const provider = this.selectedProviders[serviceType]?.find(p => p.id === providerId);
        if (!provider) {
            this.showToast('Provider not found', 'error');
            return;
        }
        if (provider.provider_type !== 'external') {
            this.showToast('Only external providers can be edited', 'warning');
            return;
        }
        // TODO: Implement edit functionality
        // This could pre-populate the external form with existing data
        alert('Edit external provider functionality coming soon!');
    },
    // Generate provider details modal content
    generateProviderDetailsModal(provider, serviceType) {
        // This will be used when implementing the actual modal
        return {
            title: `${provider.name} Details`,
            content: `
                <div class="provider-details-modal">
                    <!-- Provider details content will go here -->
                </div>
            `
        };
    },
    // ***** MODAL AND FORM METHODS *****
    // Get or create modal instance
    getModalInstance() {
        const modal = document.getElementById('providerSelectionModal');
        if (!modal) return null;
        // Get existing instance or create new one
        if (!this.modalInstance) {
            this.modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        }
        return this.modalInstance;
    },
    // Show platform providers section
    showPlatformProviders() {
        const platformSection = document.getElementById('platformProvidersSection');
        const externalSection = document.getElementById('externalServiceSection');
        if (platformSection) platformSection.style.display = 'flex';
        if (externalSection) externalSection.style.display = 'none';
        // Update modal title if in modal context
        const modalTitle = document.getElementById('modalServiceTitle');
        if (modalTitle) {
            const serviceTitles = {
                'hotels': 'Browse Hotels',
                'flights': 'Browse Flights',
                'transport': 'Browse Transport'
            };
            modalTitle.textContent = serviceTitles[this.currentServiceType] || 'Browse Providers';
        }
        // Update button states
        this.updateModalButtonStates('platform');
    },
    // Update modal button states
    updateModalButtonStates(mode) {
        const browseBtn = document.getElementById('browseProvidersBtn');
        const externalBtn = document.getElementById('useExternalServiceBtn');
        if (!browseBtn || !externalBtn) return;
        if (mode === 'platform') {
            browseBtn.classList.add('active');
            externalBtn.classList.remove('active');
        } else if (mode === 'external') {
            browseBtn.classList.remove('active');
            externalBtn.classList.add('active');
        }
    },
    // Show external service form
    showExternalForm(serviceType) {
        // Set current service type first
        this.currentServiceType = serviceType;
        // Open the modal using helper method
        const modalInstance = this.getModalInstance();
        if (modalInstance) {
            modalInstance.show();
        }
        // Then set up the sections based on service type
        setTimeout(() => {
            // Hide platform providers section and show external service section
            const platformSection = document.getElementById('platformProvidersSection');
            const externalSection = document.getElementById('externalServiceSection');
            if (platformSection) platformSection.style.display = 'none';
            if (externalSection) externalSection.style.display = 'flex';
            // Update modal title
            const modalTitle = document.getElementById('modalServiceTitle');
            if (modalTitle) {
                const serviceTitles = {
                    'hotels': 'Add External Hotel',
                    'flights': 'Select Your Flight',
                    'transport': 'Add External Transport'
                };
                modalTitle.textContent = serviceTitles[serviceType] || 'Add External Service';
            }
            // Special handling for flights - show existing flights instead of form
            if (serviceType === 'flights') {
                this.showOwnFlightsList();
            } else {
                // Generate dynamic form for other service types
                this.generateExternalServiceForm(serviceType);
            }
            // Set current service type for form submission
            this.currentExternalServiceType = serviceType;
            // Update button states
            this.updateModalButtonStates('external');
        }, 100);
    },
    // Show user's own flights list (for flight service type)
    showOwnFlightsList() {
        const formContainer = document.getElementById('externalServiceForm');
        if (!formContainer) return;
        // Show loading state
        formContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading your flights...</span>
                </div>
                <p class="mt-2">Loading your flights...</p>
            </div>
        `;
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        // Fetch user's own flights
        fetch('/b2b/travel-agent/api/providers/own-flights', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.flights && data.flights.length > 0) {
                // Store flights for selection
                this.ownFlights = data.flights;
                this.renderOwnFlightsList(data.flights);
            } else {
                this.ownFlights = [];
                this.showNoOwnFlightsMessage();
            }
        })
        .catch(error => {
            console.error('Error loading own flights:', error);
            this.showOwnFlightsError(error.message);
        });
    },
    // Render the list of user's own flights
    renderOwnFlightsList(flights) {
        const formContainer = document.getElementById('externalServiceForm');
        if (!formContainer) return;
        let flightListHTML = `
            <div class="own-flights-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fas fa-plane me-2"></i>Select from Your Flights</h6>
                    <small class="text-muted">${flights.length} flight(s) available</small>
                </div>
                <div class="own-flights-list" style="max-height: 400px; overflow-y: auto;">
        `;
        flights.forEach(flight => {
            const departureDate = new Date(flight.departure_datetime).toLocaleDateString();
            const departureTime = new Date(flight.departure_datetime).toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            const arrivalDate = new Date(flight.arrival_datetime).toLocaleDateString();
            const arrivalTime = new Date(flight.arrival_datetime).toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            flightListHTML += `
                <div class="flight-selection-card mb-3 p-3 border rounded" 
                     data-flight-id="${flight.id}" 
                     style="cursor: pointer; transition: all 0.2s;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flight-info flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="mb-0 me-3">
                                    <i class="fas fa-plane text-primary me-1"></i>
                                    ${flight.airline} ${flight.flight_number}
                                </h6>
                                <span class="badge bg-${this.getFlightStatusColor(flight.status)}">
                                    ${flight.status}
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="flight-route mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="text-center">
                                                <div class="fw-bold">${flight.departure_airport}</div>
                                                <small class="text-muted">${departureTime}</small>
                                                <small class="text-muted d-block">${departureDate}</small>
                                            </div>
                                            <div class="px-3">
                                                <i class="fas fa-arrow-right text-primary"></i>
                                            </div>
                                            <div class="text-center">
                                                <div class="fw-bold">${flight.arrival_airport}</div>
                                                <small class="text-muted">${arrivalTime}</small>
                                                <small class="text-muted d-block">${arrivalDate}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="flight-details">
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>Duration: ${flight.duration}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-users me-1"></i>Available: ${flight.available_seats}/${flight.total_seats} seats
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-dollar-sign me-1"></i>From ${flight.economy_price} ${flight.currency}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flight-actions ml-3">
                            <button class="btn btn-primary btn-sm select-flight-btn" 
                                    onclick="MergedProviderSelector.selectOwnFlight(${flight.id})">
                                <i class="fas fa-check me-1"></i>Select
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        flightListHTML += `
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        These are flights from your flight management. No approval required.
                    </small>
                </div>
            </div>
        `;
        formContainer.innerHTML = flightListHTML;
        // Add click handlers for flight selection
        formContainer.querySelectorAll('.flight-selection-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.select-flight-btn')) {
                    // Toggle selection visual feedback
                    formContainer.querySelectorAll('.flight-selection-card').forEach(c => {
                        c.classList.remove('border-primary', 'selected');
                        c.style.backgroundColor = '';
                    });
                    card.classList.add('border-primary', 'selected');
                    card.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
                }
            });
        });
    },
    // Show message when user has no flights
    showNoOwnFlightsMessage() {
        const formContainer = document.getElementById('externalServiceForm');
        if (!formContainer) return;
        formContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-plane-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Flights Found</h5>
                <p class="text-muted mb-4">
                    You don't have any flights in your flight management yet.
                </p>
                <a href="/b2b/travel-agent/flights/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Your First Flight
                </a>
            </div>
        `;
    },
    // Show error message when loading flights fails
    showOwnFlightsError(errorMessage) {
        const formContainer = document.getElementById('externalServiceForm');
        if (!formContainer) return;
        formContainer.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                <h6 class="text-warning">Error Loading Flights</h6>
                <p class="text-muted small">${errorMessage}</p>
                <button class="btn btn-outline-primary btn-sm" onclick="MergedProviderSelector.showOwnFlightsList()">
                    <i class="fas fa-refresh me-1"></i>Retry
                </button>
            </div>
        `;
    },
    // Get flight status color for badges
    getFlightStatusColor(status) {
        const statusColors = {
            'scheduled': 'primary',
            'boarding': 'info',
            'departed': 'success',
            'arrived': 'success',
            'cancelled': 'danger',
            'delayed': 'warning'
        };
        return statusColors[status] || 'secondary';
    },
    // Select own flight
    selectOwnFlight(flightId) {
        // Find the flight in stored own flights
        const flight = this.ownFlights?.find(f => f.id === flightId);
        if (!flight) {
            this.showToast('Flight not found', 'error');
            return;
        }
        // Add flight to selected providers
        const success = this.addProvider({
            ...flight,
            type: 'flight',
            provider_type: 'own',
            service_type: 'flights',
            status: 'approved' // Own flights are automatically approved
        });
        if (success) {
            // Close modal
            const modalInstance = this.getModalInstance();
            if (modalInstance) {
                modalInstance.hide();
            }
            this.showToast('Flight added successfully', 'success');
        }
    },
    // Generate dynamic external service form based on service type
    generateExternalServiceForm(serviceType) {
        const formContainer = document.getElementById('externalServiceForm');
        if (!formContainer) return;
        const serviceConfig = this.getServiceFormConfig(serviceType);
        let formHTML = `
            <form id="externalProviderForm" onsubmit="return false;">
                <input type="hidden" name="service_type" value="${serviceType}">
                <!-- Basic Information -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="provider_name" class="form-label">
                            <i class="${serviceConfig.icon} me-1"></i>${serviceConfig.name} Name *
                        </label>
                        <input type="text" class="form-control" id="provider_name" name="provider_name" required 
                               placeholder="Enter ${serviceConfig.name.toLowerCase()} provider name">
                    </div>
                    <div class="col-md-6">
                        <label for="contact_email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Contact Email *
                        </label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" required 
                               placeholder="provider@example.com">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="contact_phone" class="form-label">
                            <i class="fas fa-phone me-1"></i>Contact Phone
                        </label>
                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                               placeholder="+1 (555) 000-0000">
                    </div>
                    <div class="col-md-6">
                        <label for="website_url" class="form-label">
                            <i class="fas fa-globe me-1"></i>Website URL
                        </label>
                        <input type="url" class="form-control" id="website_url" name="website_url" 
                               placeholder="https://provider-website.com">
                    </div>
                </div>
`;
        // Add service-specific fields
        formHTML += serviceConfig.specificFields;
        // Add common fields
        formHTML += `
                <!-- Location Information -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="location_city" class="form-label">
                            <i class="fas fa-map-marker-alt me-1"></i>City
                        </label>
                        <input type="text" class="form-control" id="location_city" name="location_city" 
                               placeholder="Enter city">
                    </div>
                    <div class="col-md-6">
                        <label for="location_country" class="form-label">
                            <i class="fas fa-flag me-1"></i>Country
                        </label>
                        <input type="text" class="form-control" id="location_country" name="location_country" 
                               placeholder="Enter country">
                    </div>
                </div>
                <!-- Pricing Information -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="base_price" class="form-label">
                            <i class="fas fa-dollar-sign me-1"></i>Base Price
                        </label>
                        <input type="number" class="form-control" id="base_price" name="base_price" 
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label for="currency" class="form-label">
                            <i class="fas fa-coins me-1"></i>Currency
                        </label>
                        <select class="form-select" id="currency" name="currency">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="CAD">CAD ($)</option>
                            <option value="AUD">AUD ($)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="commission_rate" class="form-label">
                            <i class="fas fa-percentage me-1"></i>Commission (%)
                        </label>
                        <input type="number" class="form-control" id="commission_rate" name="commission_rate" 
                               min="0" max="100" step="0.1" placeholder="10.0">
                    </div>
                </div>
                <!-- Additional Notes -->
                <div class="mb-4">
                    <label for="notes" class="form-label">
                        <i class="fas fa-sticky-note me-1"></i>Additional Notes
                    </label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="Any additional information about this provider..."></textarea>
                </div>
                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="MergedProviderSelector.showPlatformProviders()">
                        <i class="fas fa-arrow-left me-1"></i>Back to Search
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="MergedProviderSelector.resetExternalForm()">
                            <i class="fas fa-undo me-1"></i>Reset Form
                        </button>
                        <button type="submit" class="btn btn-success" onclick="MergedProviderSelector.submitExternalProvider()">
                            <i class="fas fa-plus me-1"></i>Add ${serviceConfig.name} Provider
                        </button>
                    </div>
                </div>
            </form>
        `;
        formContainer.innerHTML = formHTML;
    },
    // Get service-specific form configuration
    getServiceFormConfig(serviceType) {
        const configs = {
            hotels: {
                name: 'Hotel',
                icon: 'fas fa-bed',
                specificFields: `
                    <!-- Hotel-Specific Fields -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-bed me-1"></i>Hotel-Specific Information
                            </h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="star_rating" class="form-label">
                                <i class="fas fa-star me-1"></i>Star Rating
                            </label>
                            <select class="form-select" id="star_rating" name="star_rating">
                                <option value="">Select Rating</option>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="room_types" class="form-label">
                                <i class="fas fa-door-open me-1"></i>Room Types
                            </label>
                            <input type="text" class="form-control" id="room_types" name="room_types" 
                                   placeholder="Standard, Deluxe, Suite...">
                        </div>
                        <div class="col-md-4">
                            <label for="total_rooms" class="form-label">
                                <i class="fas fa-building me-1"></i>Total Rooms
                            </label>
                            <input type="number" class="form-control" id="total_rooms" name="total_rooms" 
                                   min="1" placeholder="50">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="amenities" class="form-label">
                                <i class="fas fa-swimming-pool me-1"></i>Amenities
                            </label>
                            <input type="text" class="form-control" id="amenities" name="amenities" 
                                   placeholder="WiFi, Pool, Spa, Restaurant, Gym...">
                        </div>
                    </div>
                `
            },
            flights: {
                name: 'Flight',
                icon: 'fas fa-plane',
                specificFields: `
                    <!-- Flight-Specific Fields -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-plane me-1"></i>Flight-Specific Information
                            </h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="airline_code" class="form-label">
                                <i class="fas fa-tag me-1"></i>Airline Code
                            </label>
                            <input type="text" class="form-control" id="airline_code" name="airline_code" 
                                   placeholder="AA, BA, LH..." maxlength="3">
                        </div>
                        <div class="col-md-4">
                            <label for="aircraft_types" class="form-label">
                                <i class="fas fa-plane me-1"></i>Aircraft Types
                            </label>
                            <input type="text" class="form-control" id="aircraft_types" name="aircraft_types" 
                                   placeholder="Boeing 737, Airbus A320...">
                        </div>
                        <div class="col-md-4">
                            <label for="class_types" class="form-label">
                                <i class="fas fa-chair me-1"></i>Class Types
                            </label>
                            <input type="text" class="form-control" id="class_types" name="class_types" 
                                   placeholder="Economy, Business, First...">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="routes" class="form-label">
                                <i class="fas fa-route me-1"></i>Available Routes
                            </label>
                            <textarea class="form-control" id="routes" name="routes" rows="2" 
                                      placeholder="JFK-LAX, LHR-CDG, etc."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="baggage_policy" class="form-label">
                                <i class="fas fa-suitcase me-1"></i>Baggage Policy
                            </label>
                            <textarea class="form-control" id="baggage_policy" name="baggage_policy" rows="2" 
                                      placeholder="Carry-on: 7kg, Checked: 23kg..."></textarea>
                        </div>
                    </div>
                `
            },
            transport: {
                name: 'Transport',
                icon: 'fas fa-bus',
                specificFields: `
                    <!-- Transport-Specific Fields -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-bus me-1"></i>Transport-Specific Information
                            </h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="transport_type" class="form-label">
                                <i class="fas fa-bus me-1"></i>Transport Type *
                            </label>
                            <select class="form-select" id="transport_type" name="transport_type" required>
                                <option value="">Select Type</option>
                                <option value="bus">Bus</option>
                                <option value="train">Train</option>
                                <option value="taxi">Taxi/Car Service</option>
                                <option value="shuttle">Airport Shuttle</option>
                                <option value="rental">Car Rental</option>
                                <option value="boat">Boat/Ferry</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_capacity" class="form-label">
                                <i class="fas fa-users me-1"></i>Vehicle Capacity
                            </label>
                            <input type="number" class="form-control" id="vehicle_capacity" name="vehicle_capacity" 
                                   min="1" placeholder="4, 12, 50...">
                        </div>
                        <div class="col-md-4">
                            <label for="fleet_size" class="form-label">
                                <i class="fas fa-car me-1"></i>Fleet Size
                            </label>
                            <input type="number" class="form-control" id="fleet_size" name="fleet_size" 
                                   min="1" placeholder="5">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="service_areas" class="form-label">
                                <i class="fas fa-map me-1"></i>Service Areas
                            </label>
                            <textarea class="form-control" id="service_areas" name="service_areas" rows="2" 
                                      placeholder="Cities or regions served..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="special_features" class="form-label">
                                <i class="fas fa-star me-1"></i>Special Features
                            </label>
                            <textarea class="form-control" id="special_features" name="special_features" rows="2" 
                                      placeholder="WiFi, AC, Wheelchair accessible..."></textarea>
                        </div>
                    </div>
                `
            }
        };
        return configs[serviceType] || configs.hotels;
    },
    // Submit external provider form
    submitExternalProvider() {
        const form = document.getElementById('externalProviderForm');
        if (!form) {
            this.showToast('Form not found', 'error');
            return;
        }
        // Validate required fields
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        if (!isValid) {
            this.showToast('Please fill in all required fields', 'error');
            return;
        }
        // Collect form data
        const formData = new FormData(form);
        const providerData = Object.fromEntries(formData.entries());
        // Add some metadata
        providerData.provider_type = 'external';
        providerData.status = 'pending';
        providerData.created_at = new Date().toISOString();
        providerData.id = 'ext_' + Date.now(); // Temporary ID
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding Provider...';
        submitBtn.disabled = true;
        // Simulate API call (replace with actual API endpoint)
        this.submitExternalProviderToAPI(providerData)
            .then(response => {
                // Transform data to match expected provider format
                const provider = this.transformExternalProviderData(providerData);
                // Use the existing addProvider method to ensure consistency
                const success = this.addProvider(provider);
                if (success) {
                } else {
                }
                // Reset form
                this.resetExternalForm();
                // Go back to platform providers view
                this.showPlatformProviders();
            })
            .catch(error => {
                console.error('Error adding external provider:', error);
                this.showToast('Error adding provider. Please try again.', 'error');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    },
    // Submit external provider to API (placeholder for now)
    async submitExternalProviderToAPI(providerData) {
        // TODO: Replace with actual API endpoint
        // For now, simulate API call
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simulate success (90% of the time)
                if (Math.random() > 0.1) {
                    resolve({ success: true, id: 'ext_' + Date.now() });
                } else {
                    reject(new Error('Simulated API error'));
                }
            }, 1500);
        });
    },
    // Transform external provider data to match expected format
    transformExternalProviderData(formData) {
        const serviceType = formData.service_type;
        const baseProvider = {
            id: formData.id,
            name: formData.provider_name,
            email: formData.contact_email,
            phone: formData.contact_phone || '',
            website: formData.website_url || '',
            city: formData.location_city || '',
            country: formData.location_country || '',
            base_price: parseFloat(formData.base_price) || 0,
            currency: formData.currency || 'USD',
            commission_rate: parseFloat(formData.commission_rate) || 0,
            notes: formData.notes || '',
            provider_type: 'external',
            status: 'pending',
            created_at: formData.created_at,
            type: serviceType === 'transport' ? 'transport' : serviceType.slice(0, -1) // hotels -> hotel, flights -> flight, transport -> transport
        };
        // Add service-specific fields
        if (serviceType === 'hotels') {
            return {
                ...baseProvider,
                star_rating: parseInt(formData.star_rating) || 0,
                room_types: formData.room_types || '',
                total_rooms: parseInt(formData.total_rooms) || 0,
                amenities: formData.amenities || '',
                service_type: 'hotels'
            };
        } else if (serviceType === 'flights') {
            return {
                ...baseProvider,
                airline_code: formData.airline_code || '',
                aircraft_types: formData.aircraft_types || '',
                class_types: formData.class_types || '',
                routes: formData.routes || '',
                baggage_policy: formData.baggage_policy || '',
                service_type: 'flights'
            };
        } else if (serviceType === 'transport') {
            return {
                ...baseProvider,
                transport_type: formData.transport_type || '',
                vehicle_capacity: parseInt(formData.vehicle_capacity) || 0,
                fleet_size: parseInt(formData.fleet_size) || 0,
                service_areas: formData.service_areas || '',
                special_features: formData.special_features || '',
                service_type: 'transport'
            };
        }
        return baseProvider;
    },
    // Reset external form
    resetExternalForm() {
        const form = document.getElementById('externalProviderForm');
        if (form) {
            form.reset();
            // Remove validation classes
            form.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
        }
    },
    showBulkImport(serviceType) {
        alert('Bulk import feature coming soon!');
    },
    refreshProviderStatus() {
        alert('Provider status refreshed!');
    },
    // View search result details
    viewSearchResultDetails(providerId) {
        // Convert providerId to both string and number for comparison
        const providerIdStr = String(providerId);
        const providerIdNum = parseInt(providerId);
        let provider = this.currentSearchResults?.find(p => {
            return String(p.id) === providerIdStr || p.id === providerIdNum;
        });
        if (!provider) {
            this.showToast('Provider details not found', 'error');
            return;
        }
        // Determine service type from provider data
        let serviceType = 'hotels'; // default
        if (provider.airline || provider.flight_number || provider.service_type === 'flights') {
            serviceType = 'flights';
        } else if (provider.transport_type || provider.service_type === 'transport') {
            serviceType = 'transport';
        } else if (provider.service_type) {
            serviceType = provider.service_type;
        }
        // Use the same modal system as viewProviderDetails
        if (typeof this.showProviderDetailsModal === 'function') {
            this.showProviderDetailsModal(provider, serviceType);
        } else {
            this.showToast('Details modal not available', 'error');
        }
    },
    // Clear all selections (both temp and permanent)
    clearAllSelections() {
        // Clear temporary selections
        this.clearTempSelections();
        // Clear all selected providers
        this.selectedProviders = {
            hotels: [],
            flights: [],
            transport: []
        };
        // Update UI
        this.updateUI();
    },
    // Bind search form events (placeholder)
    bindSearchFormEvents() {
        // Bind confirm selection button
        const confirmBtn = document.getElementById('confirmSelectionBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                this.confirmSelections();
            });
        }
        // Bind clear all button (use event delegation)
        document.addEventListener('click', (e) => {
            if (e.target.matches('button[onclick*="clearAllSelections"]') || e.target.closest('button[onclick*="clearAllSelections"]')) {
                e.preventDefault();
                this.clearTempSelections();
            }
        });
    },
    // ***** SERVICE REQUEST HELPER METHODS *****
    // Get service request status CSS class
    getServiceRequestStatusClass(status) {
        const statusClasses = {
            'pending': 'bg-warning',
            'approved': 'bg-success', 
            'rejected': 'bg-danger',
            'expired': 'bg-secondary',
            'cancelled': 'bg-dark'
        };
        return statusClasses[status] || 'bg-light';
    },
    // Format service request status for display
    formatServiceRequestStatus(status) {
        const statusLabels = {
            'pending': 'Pending Approval',
            'approved': 'Approved',
            'rejected': 'Rejected', 
            'expired': 'Expired',
            'cancelled': 'Cancelled'
        };
        return statusLabels[status] || status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown';
    },
    // Get expiration CSS class based on time remaining
    getExpirationClass(expiresAt) {
        if (!expiresAt) return '';
        const now = new Date();
        const expires = new Date(expiresAt);
        const diffHours = (expires - now) / (1000 * 60 * 60);
        if (expires < now) return 'text-danger';
        if (diffHours <= 1) return 'text-danger';
        if (diffHours <= 4) return 'text-warning';
        return 'text-muted';
    },
    // Format relative time for expiration
    formatRelativeTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const now = new Date();
        if (date < now) {
            return 'Expired';
        }
        const diffMs = date - now;
        const diffMins = Math.floor(diffMs / (1000 * 60));
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        if (diffDays > 0) {
            return `${diffDays}d ${diffHours % 24}h`;
        } else if (diffHours > 0) {
            return `${diffHours}h ${diffMins % 60}m`;
        } else {
            return `${diffMins}m`;
        }
    },
    // Generate provider status badges (prevents duplicates)
    generateProviderStatusBadges(provider) {
        const badges = [];
        const addedStatuses = new Set();
        // Priority order: service_request status > provider type > provider status > source
        // 1. Service request status has highest priority
        if (provider.service_request && provider.service_request.status) {
            const status = provider.service_request.status;
            const statusClass = this.getServiceRequestStatusClass(status);
            badges.push(`<span class="badge ${statusClass} ms-2"><i class="fas fa-handshake me-1"></i>${this.formatServiceRequestStatus(status)}</span>`);
            addedStatuses.add('service_request');
            addedStatuses.add(status); // Prevent duplicate status rendering
        }
        // 2. Provider type badges (external/own/platform)
        else if (provider.provider_type === 'external' && !addedStatuses.has('external')) {
            badges.push(`<span class="badge bg-info ms-2"><i class="fas fa-external-link-alt me-1"></i>External</span>`);
            addedStatuses.add('external');
        }
        else if (provider.provider_type === 'own' && !addedStatuses.has('own')) {
            badges.push(`<span class="badge bg-success ms-2"><i class="fas fa-user me-1"></i>Your Service</span>`);
            addedStatuses.add('own');
        }
        else if (provider.provider_type === 'platform' && !addedStatuses.has('platform')) {
            badges.push(`<span class="badge bg-primary ms-2"><i class="fas fa-globe me-1"></i>Platform</span>`);
            addedStatuses.add('platform');
        }
        // 3. Provider status (only if no service request status and not already shown)
        else if (provider.status && provider.status !== 'active' && provider.status !== 'approved' && !addedStatuses.has('provider_status') && !addedStatuses.has(provider.status)) {
            const statusClass = this.getStatusClass(provider.status);
            const statusIcon = this.getStatusIcon(provider.status);
            badges.push(`<span class="badge ${statusClass} ms-2"><i class="${statusIcon} me-1"></i>${this.formatStatus(provider.status)}</span>`);
            addedStatuses.add('provider_status');
            addedStatuses.add(provider.status);
        }
        // 4. Source badge (from draft) - only show if no other important status is shown
        if ((provider.source === 'draft' || provider.from_draft) && !addedStatuses.has('from_draft') && badges.length === 0) {
            badges.push(`<span class="badge bg-secondary ms-1"><i class="fas fa-file-alt me-1"></i>From Draft</span>`);
            addedStatuses.add('from_draft');
        }
        // 5. Loading state badge - always show if applicable (highest visual priority)
        if (provider.enriching && !addedStatuses.has('loading')) {
            badges.push(`<span class="badge bg-warning ms-1"><i class="fas fa-spinner fa-spin me-1"></i>Loading Details...</span>`);
            addedStatuses.add('loading');
        }
        return badges.join('');
    },
    // Generate service request action buttons
    generateServiceRequestActions(provider, serviceType) {
        const request = provider.service_request;
        if (!request) return '';
        let actions = '';
        if (request.status === 'pending') {
            actions += `
                <div class="btn-group btn-group-sm me-2">
                    <button type="button" class="btn btn-outline-info" onclick="ServiceRequestManager.viewServiceRequest('${request.id}')">
                        <i class="fas fa-eye me-1"></i> View Details
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="ServiceRequestManager.cancelServiceRequest('${request.id}')">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                </div>
            `;
        } else if (request.status === 'approved') {
            actions += `
                <div class="btn-group btn-group-sm me-2">
                    <button type="button" class="btn btn-outline-success" onclick="ServiceRequestManager.viewServiceRequest('${request.id}')">
                        <i class="fas fa-check-circle me-1"></i> View Approval
                    </button>
                </div>
            `;
        } else if (request.status === 'rejected') {
            actions += `
                <div class="btn-group btn-group-sm me-2">
                    <button type="button" class="btn btn-outline-danger" onclick="ServiceRequestManager.viewServiceRequest('${request.id}')">
                        <i class="fas fa-times-circle me-1"></i> View Rejection
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="ServiceRequestManager.createServiceRequest('${provider.id}', '${serviceType}')">
                        <i class="fas fa-redo me-1"></i> Request Again
                    </button>
                </div>
            `;
        } else if (request.status === 'expired') {
            actions += `
                <div class="btn-group btn-group-sm me-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="ServiceRequestManager.viewServiceRequest('${request.id}')">
                        <i class="fas fa-clock me-1"></i> View Expired
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="ServiceRequestManager.createServiceRequest('${provider.id}', '${serviceType}')">
                        <i class="fas fa-paper-plane me-1"></i> Request Again
                    </button>
                </div>
            `;
        }
        return actions;
    }
};
// ***** SERVICE REQUEST MANAGER *****
window.ServiceRequestManager = {
    // Flag to track if a request is currently being submitted
    isSubmitting: false,
    // Create a service request for a provider
    async createServiceRequest(providerId, providerType) {
        try {
            // Get current package ID from form
            const packageDraftId = document.getElementById('packageDraftId')?.value;
            if (!packageDraftId) {
                this.showToast('Package ID not found. Please save your package first.', 'error');
                return;
            }
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                this.showToast('CSRF token not found', 'error');
                return;
            }
            // Show loading state
            this.showCreateRequestModal(providerId, providerType);
        } catch (error) {
            console.error('Error creating service request:', error);
            this.showToast('Failed to create service request', 'error');
        }
    },
    // Show service request creation modal
    showCreateRequestModal(providerId, providerType) {
        // Create modal HTML
        const modalHTML = `
            <div class="modal fade" id="createServiceRequestModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-handshake me-2"></i>
                                Request Service Approval
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Smart Request:</strong> Dates, quantities, and guest count will be automatically calculated from your package data. You can override them if needed.
                            </div>
                            <form id="serviceRequestForm">
                                <div class="mb-3">
                                    <label for="requestQuantity" class="form-label">Quantity (Optional)</label>
                                    <input type="number" class="form-control" id="requestQuantity" name="requested_quantity" 
                                           min="1" placeholder="Auto-calculated from package data">
                                    <div class="form-text">
                                        Leave empty to auto-calculate: 
                                        <span class="text-muted">
                                            Hotels = rooms needed, Flights = seat count, Transport = vehicle count
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="specialRequirements" class="form-label">Special Requirements</label>
                                    <textarea class="form-control" id="specialRequirements" name="special_requirements" 
                                              rows="3" placeholder="Any special requirements or notes..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="expiresInHours" class="form-label">Request Expires In</label>
                                    <select class="form-select" id="expiresInHours" name="expires_in_hours">
                                        <option value="24">24 hours</option>
                                        <option value="48">48 hours</option>
                                        <option value="72">72 hours</option>
                                        <option value="168">1 week</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="submitServiceRequestBtn" onclick="ServiceRequestManager.submitServiceRequest('${providerId}', '${providerType}')">
                                <i class="fas fa-paper-plane me-1"></i> Send Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Remove existing modal if any
        const existingModal = document.getElementById('createServiceRequestModal');
        if (existingModal) {
            existingModal.remove();
        }
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('createServiceRequestModal'));
        modal.show();
    },
    // Submit service request
    async submitServiceRequest(providerId, providerType) {
        // Prevent multiple concurrent submissions
        if (this.isSubmitting) {
            this.showToast('Request is already being submitted, please wait...', 'warning');
            return;
        }
        const submitBtn = document.getElementById('submitServiceRequestBtn');
        const originalBtnContent = submitBtn.innerHTML;
        try {
            // Validate form first
            const form = document.getElementById('serviceRequestForm');
            if (!this.validateServiceRequestForm(form)) {
                return; // Stop if validation fails
            }
            // Set submission flag and disable button
            this.isSubmitting = true;
            this.setButtonLoadingState(submitBtn, true);
            const formData = new FormData(form);
            // Add additional data
            const packageDraftId = document.getElementById('packageDraftId')?.value;
            formData.append('package_id', packageDraftId);
            formData.append('provider_id', providerId);
            formData.append('provider_type', providerType);
            formData.append('item_id', providerId); // For now, use provider ID as item ID
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            // Submit request
            const response = await fetch('/api/v1/service-requests', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.showToast('Service request created successfully!', 'success');
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('createServiceRequestModal'));
                if (modal) modal.hide();
                // Refresh provider status
                if (window.MergedProviderSelector) {
                    await this.refreshProviderServiceRequest(providerId, providerType, data.data);
                }
                // Clear submission flag (modal closes, so no need to re-enable button)
                this.isSubmitting = false;
            } else {
                this.showToast(data.message || 'Failed to create service request', 'error');
                // Re-enable button and clear flag on error
                this.isSubmitting = false;
                this.setButtonLoadingState(submitBtn, false, originalBtnContent);
            }
        } catch (error) {
            console.error('Error submitting service request:', error);
            this.showToast('Network error occurred', 'error');
            // Re-enable button and clear flag on error
            this.isSubmitting = false;
            this.setButtonLoadingState(submitBtn, false, originalBtnContent);
        }
    },
    // Validate service request form
    validateServiceRequestForm(form) {
        // Clear any existing validation errors
        const errorElements = form.querySelectorAll('.is-invalid');
        errorElements.forEach(el => el.classList.remove('is-invalid'));
        let isValid = true;
        // Check if package ID exists
        const packageDraftId = document.getElementById('packageDraftId')?.value;
        if (!packageDraftId) {
            this.showToast('Package ID not found. Please save your package first.', 'error');
            return false;
        }
        // Validate quantity if provided
        const quantityInput = form.querySelector('#requestQuantity');
        if (quantityInput && quantityInput.value && quantityInput.value < 1) {
            quantityInput.classList.add('is-invalid');
            isValid = false;
        }
        // Validate special requirements length
        const specialReqInput = form.querySelector('#specialRequirements');
        if (specialReqInput && specialReqInput.value.length > 1000) {
            specialReqInput.classList.add('is-invalid');
            isValid = false;
        }
        if (!isValid) {
            this.showToast('Please fix the form errors before submitting', 'error');
        }
        return isValid;
    },
    // Set button loading state
    setButtonLoadingState(button, isLoading, originalContent = null) {
        if (isLoading) {
            button.disabled = true;
            button.classList.add('disabled');
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Sending...
            `;
            // Also disable modal dismiss buttons during loading
            const modal = document.getElementById('createServiceRequestModal');
            if (modal) {
                const cancelBtn = modal.querySelector('[data-bs-dismiss="modal"]');
                const closeBtn = modal.querySelector('.btn-close');
                if (cancelBtn) cancelBtn.disabled = true;
                if (closeBtn) closeBtn.disabled = true;
            }
        } else {
            button.disabled = false;
            button.classList.remove('disabled');
            button.innerHTML = originalContent || `<i class="fas fa-paper-plane me-1"></i> Send Request`;
            // Re-enable modal dismiss buttons
            const modal = document.getElementById('createServiceRequestModal');
            if (modal) {
                const cancelBtn = modal.querySelector('[data-bs-dismiss="modal"]');
                const closeBtn = modal.querySelector('.btn-close');
                if (cancelBtn) cancelBtn.disabled = false;
                if (closeBtn) closeBtn.disabled = false;
            }
        }
    },
    // View service request details
    async viewServiceRequest(requestId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(`/api/v1/service-requests/${requestId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await response.json();
            if (data.success) {
                this.showServiceRequestModal(data.data);
            } else {
                this.showToast('Failed to load service request details', 'error');
            }
        } catch (error) {
            console.error('Error loading service request:', error);
            this.showToast('Network error occurred', 'error');
        }
    },
    // Show service request details modal
    showServiceRequestModal(request) {
        const statusClass = this.getStatusClass(request.status);
        const statusLabel = this.getStatusLabel(request.status);
        const modalHTML = `
            <div class="modal fade" id="serviceRequestModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-handshake me-2"></i>
                                Service Request #${request.id}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Request Details</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Status:</strong></td><td><span class="badge ${statusClass}">${statusLabel}</span></td></tr>
                                        <tr><td><strong>Provider Type:</strong></td><td class="text-capitalize">${request.provider_type}</td></tr>
                                        <tr><td><strong>Quantity:</strong></td><td>${request.requested_quantity}</td></tr>
                                        <tr><td><strong>Guest Count:</strong></td><td>${request.guest_count || 'N/A'}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Timeline</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Start Date:</strong></td><td>${new Date(request.start_date).toLocaleDateString()}</td></tr>
                                        <tr><td><strong>End Date:</strong></td><td>${new Date(request.end_date).toLocaleDateString()}</td></tr>
                                        <tr><td><strong>Created:</strong></td><td>${new Date(request.created_at).toLocaleString()}</td></tr>
                                        <tr><td><strong>Expires:</strong></td><td>${request.expires_at ? new Date(request.expires_at).toLocaleString() : 'N/A'}</td></tr>
                                    </table>
                                </div>
                            </div>
                            ${request.special_requirements ? `
                                <div class="mt-3">
                                    <h6>Special Requirements</h6>
                                    <div class="alert alert-info">${request.special_requirements}</div>
                                </div>
                            ` : ''}
                            ${request.rejection_reason ? `
                                <div class="mt-3">
                                    <h6>Rejection Reason</h6>
                                    <div class="alert alert-danger">${request.rejection_reason}</div>
                                </div>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            ${request.status === 'pending' ? `
                                <button type="button" class="btn btn-danger" onclick="ServiceRequestManager.cancelServiceRequest('${request.id}')">
                                    <i class="fas fa-times me-1"></i> Cancel Request
                                </button>
                            ` : ''}
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Remove existing modal
        const existingModal = document.getElementById('serviceRequestModal');
        if (existingModal) {
            existingModal.remove();
        }
        // Add and show modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('serviceRequestModal'));
        modal.show();
    },
    // Cancel service request
    async cancelServiceRequest(requestId) {
        if (!confirm('Are you sure you want to cancel this service request?')) {
            return;
        }
        const reason = prompt('Please provide a reason for cancellation:');
        if (!reason) {
            return;
        }
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(`/api/v1/service-requests/${requestId}/cancel`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ cancellation_reason: reason })
            });
            const data = await response.json();
            if (data.success) {
                this.showToast('Service request cancelled successfully', 'success');
                // Close any open modals
                const modals = ['serviceRequestModal', 'createServiceRequestModal'];
                modals.forEach(modalId => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modal) modal.hide();
                });
                // Refresh UI
                if (window.MergedProviderSelector) {
                    window.MergedProviderSelector.renderAllProviders();
                }
            } else {
                this.showToast(data.message || 'Failed to cancel request', 'error');
            }
        } catch (error) {
            console.error('Error cancelling request:', error);
            this.showToast('Network error occurred', 'error');
        }
    },
    // Refresh provider with service request data
    async refreshProviderServiceRequest(providerId, providerType, requestData) {
        // Find and update the provider in selected providers
        let key = providerType;
        const selectedProviders = window.MergedProviderSelector.selectedProviders;
        const providers = selectedProviders[key] || [];
        const providerIndex = providers.findIndex(p => p.id == providerId);
        if (providerIndex !== -1) {
            providers[providerIndex].service_request = requestData;
            window.MergedProviderSelector.renderAllProviders();
        }
    },
    // Helper methods
    getStatusClass(status) {
        const classes = {
            'pending': 'bg-warning',
            'approved': 'bg-success',
            'rejected': 'bg-danger',
            'expired': 'bg-secondary',
            'cancelled': 'bg-dark'
        };
        return classes[status] || 'bg-light';
    },
    getStatusLabel(status) {
        const labels = {
            'pending': 'Pending Approval',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'expired': 'Expired', 
            'cancelled': 'Cancelled'
        };
        return labels[status] || status;
    },
    showToast(message, type = 'info') {
        if (window.MergedProviderSelector && window.MergedProviderSelector.showToast) {
            window.MergedProviderSelector.showToast(message, type);
        } else {
            alert(message);
        }
    }
};
// ***** GLOBAL FUNCTIONS FOR HTML ONCLICK HANDLERS *****
function openProviderModal(serviceType) {
    window.MergedProviderSelector.openProviderModal(serviceType);
}
function showExternalForm(serviceType) {
    window.MergedProviderSelector.showExternalForm(serviceType);
}
function showBulkImport(serviceType) {
    window.MergedProviderSelector.showBulkImport(serviceType);
}
function refreshProviderStatus() {
    window.MergedProviderSelector.refreshProviderStatus();
}
function clearAllSelections() {
    if (window.MergedProviderSelector && typeof window.MergedProviderSelector.clearTempSelections === 'function') {
        window.MergedProviderSelector.clearTempSelections();
    }
}
function confirmSelections() {
    window.MergedProviderSelector.confirmSelections();
}
function goToPage(pageNumber) {
    if (window.MergedProviderSelector && typeof window.MergedProviderSelector.goToPage === 'function') {
        window.MergedProviderSelector.goToPage(pageNumber);
    }
}
// Service Request Functions
function refreshServiceRequestStatus() {
    if (window.MergedProviderSelector) {
        window.MergedProviderSelector.renderAllProviders();
    }
}
function viewAllServiceRequests() {
    alert('Service requests management page is coming soon!');
}
function toggleAutoApproval(serviceType) {
    alert('Auto-approval for own services is enabled by default!');
}
// ***** BACKWARD COMPATIBILITY ALIASES *****
// Create aliases for both naming conventions
window.SimpleProviderSelector = window.MergedProviderSelector;
window.EnhancedProviderSelector = window.MergedProviderSelector;
// ***** INITIALIZATION *****
// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (window.MergedProviderSelector && typeof window.MergedProviderSelector.init === 'function') {
                window.MergedProviderSelector.init();
            }
        }, 100);
    });
} else {
    setTimeout(() => {
        if (window.MergedProviderSelector && typeof window.MergedProviderSelector.init === 'function') {
            window.MergedProviderSelector.init();
        }
    }, 100);
}
