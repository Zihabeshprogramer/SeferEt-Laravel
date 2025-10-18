<!-- Enhanced Step 3: Provider Selection with Advanced Features -->
<div class="card shadow-sm border-0" id="step3ProvidersCard">
    <div class="card-header bg-gradient-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-handshake me-2"></i>
            Provider Selection & Management
        </h5>
        <p class="mb-0 small opacity-75">Search platform providers or add external services for your package</p>
    </div>
    
    <!-- Simple Service Request Info (hidden by default, shown only when needed) -->
    <div id="serviceRequestStatusBanner" class="alert alert-info border-0 rounded-0 mb-0" style="display: none;">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div class="flex-grow-1">
                <span id="statusMessage">Service requests have been sent to selected providers.</span>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4">
        <!-- Provider Type Tabs with Enhanced Badges -->
        <ul class="nav nav-pills nav-justified mb-4" id="providerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active position-relative" id="hotels-tab" data-bs-toggle="pill" 
                        data-bs-target="#hotels" type="button" role="tab">
                    <i class="fas fa-bed me-1"></i> Hotels
                    <span class="badge bg-light text-dark ms-1" id="hotelCountBadge">0</span>
                    <span class="status-indicator position-absolute" id="hotelStatusIndicator" style="display: none;"></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative" id="flights-tab" data-bs-toggle="pill" 
                        data-bs-target="#flights" type="button" role="tab">
                    <i class="fas fa-plane me-1"></i> Flights
                    <span class="badge bg-light text-dark ms-1" id="flightCountBadge">0</span>
                    <span class="status-indicator position-absolute" id="flightStatusIndicator" style="display: none;"></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative" id="transport-tab" data-bs-toggle="pill" 
                        data-bs-target="#transport" type="button" role="tab">
                    <i class="fas fa-bus me-1"></i> Transport
                    <span class="badge bg-light text-dark ms-1" id="transportCountBadge">0</span>
                    <span class="status-indicator position-absolute" id="transportStatusIndicator" style="display: none;"></span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="providerTabsContent">
            <!-- Hotels Tab -->
            <div class="tab-pane fade show active" id="hotels" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-bed text-primary me-1"></i>
                        Hotel Accommodations
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openProviderModal('hotels')">
                            <i class="fas fa-search me-1"></i> Browse Hotels
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-1"></i> Add External
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showExternalForm('hotels')">
                                    <i class="fas fa-building me-1"></i> Add Hotel Partner
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="showBulkImport('hotels')">
                                    <i class="fas fa-upload me-1"></i> Bulk Import
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Selected Hotels -->
                <div id="selectedHotels" class="selected-providers-list">
                    <!-- Hotels will be populated here -->
                </div>
                
                <!-- Hidden form fields for selected hotels -->
                <div id="selectedHotelsData" style="display: none;"></div>

                <div id="noHotelsSelected" class="empty-state text-center py-5">
                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Hotels Selected</h6>
                    <p class="text-muted">Browse platform hotels or add your own external hotel providers</p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary me-2" onclick="openProviderModal('hotels')">
                            <i class="fas fa-search me-1"></i> Browse Hotels
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="showExternalForm('hotels')">
                            <i class="fas fa-plus me-1"></i> Add External
                        </button>
                    </div>
                </div>
            </div>

            <!-- Flights Tab -->
            <div class="tab-pane fade" id="flights" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-plane text-primary me-1"></i>
                        Flight Services
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openProviderModal('flights')">
                            <i class="fas fa-search me-1"></i> Browse Flights
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-1"></i> Add External
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showExternalForm('flights')">
                                    <i class="fas fa-plane me-1"></i> Add Flight Partner
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="showBulkImport('flights')">
                                    <i class="fas fa-upload me-1"></i> Bulk Import
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Selected Flights -->
                <div id="selectedFlights" class="selected-providers-list">
                    <!-- Flights will be populated here -->
                </div>
                
                <!-- Hidden form fields for selected flights -->
                <div id="selectedFlightsData" style="display: none;"></div>

                <div id="noFlightsSelected" class="empty-state text-center py-5">
                    <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Flights Selected</h6>
                    <p class="text-muted">Browse platform flights or add your own external flight providers</p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary me-2" onclick="openProviderModal('flights')">
                            <i class="fas fa-search me-1"></i> Browse Flights
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="showExternalForm('flights')">
                            <i class="fas fa-plus me-1"></i> Add External
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transport Tab -->
            <div class="tab-pane fade" id="transport" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-bus text-primary me-1"></i>
                        Transport Services
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openProviderModal('transport')">
                            <i class="fas fa-search me-1"></i> Browse Transport
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-1"></i> Add External
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showExternalForm('transport')">
                                    <i class="fas fa-bus me-1"></i> Add Transport Partner
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="showBulkImport('transport')">
                                    <i class="fas fa-upload me-1"></i> Bulk Import
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Selected Transport -->
                <div id="selectedTransport" class="selected-providers-list">
                    <!-- Transport will be populated here -->
                </div>
                
                <!-- Hidden form fields for selected transport -->
                <div id="selectedTransportData" style="display: none;"></div>

                <div id="noTransportSelected" class="empty-state text-center py-5">
                    <i class="fas fa-bus fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Transport Selected</h6>
                    <p class="text-muted">Browse platform transport or add your own external transport providers</p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary me-2" onclick="openProviderModal('transport')">
                            <i class="fas fa-search me-1"></i> Browse Transport
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="showExternalForm('transport')">
                            <i class="fas fa-plus me-1"></i> Add External
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Provider Summary Statistics -->
        <div class="mt-4 pt-3 border-top">
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-item d-flex align-items-center">
                        <i class="fas fa-bed text-primary fa-lg me-2"></i>
                        <div>
                            <span class="summary-label small text-muted">Hotels:</span>
                            <span class="summary-value fw-bold d-block" id="hotelSummaryCount">0 selected</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-item d-flex align-items-center">
                        <i class="fas fa-plane text-primary fa-lg me-2"></i>
                        <div>
                            <span class="summary-label small text-muted">Flights:</span>
                            <span class="summary-value fw-bold d-block" id="flightSummaryCount">0 selected</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-item d-flex align-items-center">
                        <i class="fas fa-bus text-primary fa-lg me-2"></i>
                        <div>
                            <span class="summary-label small text-muted">Transport:</span>
                            <span class="summary-value fw-bold d-block" id="transportSummaryCount">0 selected</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-item d-flex align-items-center">
                        <i class="fas fa-chart-line text-success fa-lg me-2"></i>
                        <div>
                            <span class="summary-label small text-muted">Status:</span>
                            <span class="summary-value fw-bold d-block" id="overallStatusSummary">Ready to proceed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Step Footer with Progress Indicator -->
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <div id="progressNotification" class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                <span id="progressText">Select at least one provider to continue.</span>
            </div>
            <div class="ms-auto d-flex align-items-center">
                <div id="validationStatus" class="me-2"></div>
                <span id="canProceedIndicator" class="badge badge-secondary" style="display: none;">
                    <i class="fas fa-clock me-1"></i> Validation in progress
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Provider Selection Modal -->
<div class="modal fade" id="providerSelectionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog" style="max-width: 95vw; width: 95vw; height: 95vh; margin: 2.5vh auto;">
        <div class="modal-content" style="height: 100%; display: flex; flex-direction: column;">
            <div class="modal-header bg-primary text-white flex-shrink-0 py-3">
                <h4 class="modal-title d-flex align-items-center mb-0">
                    <i class="fas fa-search me-3"></i>
                    <span id="modalServiceTitle">Browse Providers</span>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body flex-grow-1 p-0 d-flex flex-column">
                <div class="row g-0 flex-grow-1">
                    <!-- Search & Filters Sidebar -->
                    <div class="col-lg-4 col-xl-3 border-end bg-light d-flex flex-column" style="max-height: 100%; overflow: hidden;">
                        <div class="p-4 flex-grow-1 provider-modal-sidebar">
                             <!-- Service Type Toggle -->
                            <div class="row border-bottom mb-3 pb-2">
                                <div class="col-6">
                                    <button type="button" class="btn btn-primary btn-sm" id="browseProvidersBtn">
                                        <i class="fas fa-search me-1"></i> Browse Platform Providers
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="useExternalServiceBtn">
                                        <i class="fas fa-plus me-1"></i> Use My Own Service
                                    </button>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-filter me-1"></i>
                                Search & Filters
                            </h6>
                            
                            <!-- Search Form -->
                            <div id="providerSearchForm">
                                <!-- Dynamic search form will be loaded here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Content Area -->
                    <div class="col-lg-8 col-xl-9 d-flex flex-column">
                        <!-- Results Header -->
                        <div class="px-4 py-3 border-bottom bg-white flex-shrink-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1" id="resultsTitle">Available Services</h6>
                                    <span class="text-muted small" id="resultsInfo">Browse and select from our available providers</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="btn-group" id="viewModeToggle">
                                        <button type="button" class="btn btn-outline-secondary active" data-view="grid" title="Grid View">
                                            <i class="fas fa-th"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" data-view="list" title="List View">
                                            <i class="fas fa-list"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-sort me-2"></i> Sort By
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-sort="relevance">
                                                <i class="fas fa-star me-2"></i> Relevance
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" data-sort="price_low">
                                                <i class="fas fa-sort-amount-up me-2"></i> Price: Low to High
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" data-sort="price_high">
                                                <i class="fas fa-sort-amount-down me-2"></i> Price: High to Low
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" data-sort="rating">
                                                <i class="fas fa-thumbs-up me-2"></i> Rating
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" data-sort="distance">
                                                <i class="fas fa-map-marker-alt me-2"></i> Distance
                                            </a></li>
                                        </ul>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="EnhancedProviderSelector.performSearch()">
                                        <i class="fas fa-sync me-1"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search Results / External Form Container -->
                        <div class="flex-grow-1 d-flex flex-column" style="min-height: 0;" id="SearchExternalContainer">
                            <!-- Platform Provider Results -->
                            <div id="platformProvidersSection" class="flex-grow-1 d-flex flex-column" style="min-height: 0; display: block;">
                                <!-- Available Services List -->
                                <div class="provider-results-container p-4">
                                    <div id="providerSearchResults" class="provider-results-grid">
                                        <!-- Real provider data will be loaded here via JavaScript -->
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary mb-3"></div>
                                            <p class="text-muted">Loading available providers...</p>
                                        </div>
                                    </div>
                                    
                                    <
                                    
                                    <div id="searchLoadingState" class="text-center py-5" style="display: none;">
                                        <div class="spinner-border text-primary mb-3"></div>
                                        <p class="text-muted">Loading providers...</p>
                                    </div>
                                    
                                    <!-- Empty State -->
                                    <div id="searchEmptyState" class="text-center py-5 text-muted" style="display: none;">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h6>No Providers Found</h6>
                                        <p>Try adjusting your search criteria or check back later</p>
                                    </div>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="border-top bg-light px-4 py-3 flex-shrink-0" id="paginationContainer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small" id="paginationInfo">
                                            Showing 1-6 of 6 providers
                                        </div>
                                        <nav aria-label="Provider pagination">
                                            <ul class="pagination pagination-sm mb-0" id="paginationNav">
                                                <li class="page-item disabled">
                                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item active">
                                                    <a class="page-link" href="#">1</a>
                                                </li>
                                                <li class="page-item disabled">
                                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- External Service Form -->
                            <div id="externalServiceSection" class="p-4 flex-grow-1 d-flex flex-column overflow-auto" style="display: none;">
                                <div class="container-fluid">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-10 col-xl-8">
                                            <div class="card shadow-sm">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-plus-circle me-1"></i>
                                                        Add External Service
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="externalServiceForm">
                                                        <!-- Dynamic external service form will be loaded here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Selection Summary (shows when providers are selected) -->
                        <div id="selectionSummary" class="border-top bg-light p-3 flex-shrink-0" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <span class="fw-bold" id="selectedCount">0 providers selected</span>
                                    <span class="text-muted ms-2" id="selectionDetails"></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllSelections()">
                                        <i class="fas fa-times me-1"></i> Clear All
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="confirmSelectionBtn">
                                        <i class="fas fa-check me-1"></i> Add Selected Providers
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Modals -->
@include('b2b.travel-agent.packages.modals.provider-comparison')
@include('b2b.travel-agent.packages.modals.bulk-import')

<!-- Legacy Modal Support (kept for compatibility) -->
@include('b2b.travel-agent.packages.modals.hotel-search')
@include('b2b.travel-agent.packages.modals.flight-search')
@include('b2b.travel-agent.packages.modals.transport-search')

<style>
/* Enhanced Styles for Provider Step */

/* Section toggle styles */
#platformProvidersSection,
#externalServiceSection {
    position: relative;
    width: 100%;
    flex: 1 1 auto;
    min-height: 0;
}

/* Force hide when display:none is set */
#platformProvidersSection[style*="display: none"],
#externalServiceSection[style*="display: none"] {
    display: none !important;
}

/* Force show when display:flex is set */
#platformProvidersSection[style*="display: flex"],
#externalServiceSection[style*="display: flex"] {
    display: flex !important;
    flex-direction: column;
}

/* External service section scrolling */
#externalServiceSection {
    max-height: calc(95vh - 200px);
    overflow-y: auto;
    overflow-x: hidden;
}

#externalServiceSection::-webkit-scrollbar {
    width: 8px;
}

#externalServiceSection::-webkit-scrollbar-track {
    background: #f8f9fa;
}

#externalServiceSection::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

#externalServiceSection::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

.status-indicator {
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #28a745;
    border: 2px solid #fff;
}

.status-indicator.pending {
    background: #ffc107;
}

.status-indicator.rejected {
    background: #dc3545;
}

/* Legacy provider card styles */
.provider-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    position: relative;
}

.provider-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.provider-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}

/* Enhanced Provider Card Styles */
.provider-card-enhanced {
    position: relative;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
}

.provider-card-enhanced:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    border-color: #e3f2fd;
}

.provider-card-enhanced.selected {
    border-color: #2196f3;
    box-shadow: 0 8px 24px rgba(33, 150, 243, 0.2);
    transform: translateY(-2px);
}

.provider-card-enhanced.quick-selected {
    animation: quickSelectPulse 1s ease-in-out;
}

@keyframes quickSelectPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(76, 175, 80, 0.3); }
    100% { transform: scale(1); }
}

/* Selection Overlay */
.selection-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(33, 150, 243, 0.08);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 10;
    pointer-events: none;
}

.provider-card-enhanced.selected .selection-overlay {
    opacity: 1;
}

.selection-checkbox-wrapper {
    position: absolute;
    top: 16px;
    right: 16px;
    pointer-events: all;
}

.selection-checkbox-wrapper input[type="checkbox"] {
    display: none;
}

.selection-label {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.selection-label:hover {
    border-color: #2196f3;
    transform: scale(1.1);
}

.selection-checkbox-wrapper input[type="checkbox"]:checked + .selection-label {
    background: #2196f3;
    border-color: #2196f3;
    color: white;
}

.selection-checkbox-wrapper input[type="checkbox"]:checked + .selection-label i {
    transform: scale(1);
    opacity: 1;
}

.selection-label i {
    font-size: 14px;
    transform: scale(0);
    opacity: 0;
    transition: all 0.2s ease;
}

/* Card Content */
.card-content {
    padding: 20px;
    position: relative;
    z-index: 2;
}

/* Image Section */
.provider-image-section {
    position: relative;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.provider-image {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.provider-avatar {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.avatar-letter {
    font-size: 24px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
}

.service-type-badge {
    background: rgba(33, 150, 243, 0.1);
    color: #1976d2;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: auto;
}

/* Header Section */
.card-header-section {
    margin-bottom: 20px;
}

.provider-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.rating-text {
    font-size: 14px;
    font-weight: 600;
    color: #424242;
    margin-left: 4px;
}

.provider-name {
    font-size: 18px;
    font-weight: 700;
    color: #212121;
    margin-bottom: 4px;
    line-height: 1.3;
}

.company-name {
    font-size: 14px;
    color: #757575;
    margin-bottom: 8px;
    font-weight: 500;
}

.location-info {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #616161;
    gap: 4px;
}

.location-info i {
    font-size: 12px;
    color: #9e9e9e;
}

/* Features Section */
.features-section {
    margin-bottom: 20px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 6px 0;
}

.feature-item:last-child {
    margin-bottom: 0;
}

.feature-item i {
    font-size: 14px;
    width: 16px;
    text-align: center;
}

.feature-text {
    font-size: 13px;
    color: #424242;
    font-weight: 500;
}

/* Price Section */
.price-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.price-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #4caf50 0%, #81c784 100%);
}

.price-label {
    font-size: 12px;
    color: #757575;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    font-weight: 600;
}

.price-amount {
    font-size: 24px;
    font-weight: 700;
    color: #2e7d32;
    line-height: 1;
}

/* Actions Section */
.card-actions {
    display: flex;
    gap: 8px;
}

.btn-view-details,
.btn-quick-select {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
}

.btn-view-details {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #bbdefb;
}

.btn-view-details:hover {
    background: #1976d2;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
}

.btn-quick-select {
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.btn-quick-select:hover {
    background: #4caf50;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

/* Status Indicators */
.status-indicators {
    position: absolute;
    top: 16px;
    left: 16px;
    display: flex;
    gap: 6px;
}

.availability-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.availability-indicator.available {
    background: #4caf50;
}

.availability-indicator.busy {
    background: #ff9800;
}

.availability-indicator.unavailable {
    background: #f44336;
}

.availability-indicator i {
    font-size: 6px;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .provider-card-enhanced {
        margin-bottom: 16px;
    }
    
    .card-content {
        padding: 16px;
    }
    
    .provider-image-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .service-type-badge {
        margin-left: 0;
        margin-top: 8px;
    }
    
    .card-actions {
        flex-direction: column;
    }
}

/* Loading animation for cards */
.provider-card-enhanced.loading {
    opacity: 0.7;
    pointer-events: none;
}

.provider-card-enhanced.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Advanced Interactive Features and Animations */

/* Hover Effects for Cards */
.provider-card-enhanced {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.provider-card-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
    );
    transition: left 0.5s;
    z-index: 1;
    pointer-events: none;
}

.provider-card-enhanced:hover::before {
    left: 100%;
}

.provider-card-enhanced:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.provider-card-enhanced:hover .card-content {
    transform: translateZ(0);
}

/* Button Hover Effects - Simplified */
.btn-view-details,
.btn-quick-select {
    transition: all 0.3s ease;
}

/* Floating Action Button */
.floating-actions {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.fab-main {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #007bff;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.fab-main:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

.fab-menu {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.fab-menu.show {
    opacity: 1;
    transform: translateY(0);
    pointer-events: all;
}

.fab-item {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: white;
    border: none;
    color: #333;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.fab-item:hover {
    transform: scale(1.1);
    background: #007bff;
    color: white;
}

.fab-item::after {
    content: attr(data-tooltip);
    position: absolute;
    right: 60px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}

.fab-item:hover::after {
    opacity: 1;
}

/* Loading States */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(2px);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Pulse Animation for Updates */
.pulse {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

/* Success/Error Feedback Animations */
.feedback-success {
    animation: successBounce 0.6s ease;
}

.feedback-error {
    animation: errorShake 0.6s ease;
}

@keyframes successBounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0, 0, 0);
    }
    40%, 43% {
        transform: translate3d(0, -10px, 0);
    }
    70% {
        transform: translate3d(0, -5px, 0);
    }
    90% {
        transform: translate3d(0, -2px, 0);
    }
}

@keyframes errorShake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-5px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(5px);
    }
}

/* Smooth Tab Transitions */
.tab-content .tab-pane {
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s ease;
}

.tab-content .tab-pane.active {
    opacity: 1;
    transform: translateX(0);
}

/* Enhanced Modal Animations */
.modal.fade .modal-dialog {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    transform: scale(0.8) translateY(-100px);
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Progress Indicators */
.progress-ring {
    width: 60px;
    height: 60px;
    position: relative;
}

.progress-ring svg {
    width: 60px;
    height: 60px;
    transform: rotate(-90deg);
}

.progress-ring circle {
    fill: transparent;
    stroke-width: 3;
    r: 26;
    cx: 30;
    cy: 30;
}

.progress-ring .background {
    stroke: #e6e6e6;
}

.progress-ring .progress {
    stroke: #007bff;
    stroke-linecap: round;
    stroke-dasharray: 163.36;
    stroke-dashoffset: 163.36;
    transition: stroke-dashoffset 0.5s ease;
}

/* Card Stack Effect */
.cards-stack {
    position: relative;
}

.cards-stack .provider-card-enhanced:nth-child(n+2) {
    transform: translateY(-2px) scale(0.98);
    opacity: 0.9;
}

.cards-stack .provider-card-enhanced:nth-child(n+3) {
    transform: translateY(-4px) scale(0.96);
    opacity: 0.8;
}

/* Micro-interactions */
.micro-bounce {
    animation: microBounce 0.3s ease;
}

@keyframes microBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.micro-glow {
    animation: microGlow 1s ease-in-out;
}

@keyframes microGlow {
    0%, 100% { box-shadow: 0 0 0 rgba(0, 123, 255, 0); }
    50% { box-shadow: 0 0 20px rgba(0, 123, 255, 0.5); }
}

/* Typography Animations */
.typewriter {
    overflow: hidden;
    border-right: 2px solid;
    animation: typewriter 2s steps(40) 1s both, blink 1s step-end infinite;
}

@keyframes typewriter {
    from { width: 0; }
    to { width: 100%; }
}

@keyframes blink {
    50% { border-color: transparent; }
}

/* Responsive Animations */
@media (max-width: 768px) {
    .provider-card-enhanced:hover {
        transform: translateY(-4px) scale(1.02);
    }
    
    .floating-actions {
        bottom: 20px;
        right: 20px;
    }
    
    .fab-main {
        width: 48px;
        height: 48px;
        font-size: 16px;
    }
}

/* Accessibility - Reduced motion */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Dark mode support - Removed to keep original styling */

/* Provider Details Modal Styles */
#providerDetailsModal .modal-dialog {
    max-width: 1200px;
}

#providerDetailsModal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

#providerDetailsModal .modal-header {
    border-bottom: none;
    border-radius: 16px 16px 0 0;
    padding: 24px 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

#providerDetailsModal .rating-display {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

/* Gallery Styles */
.provider-gallery {
    height: 300px;
    overflow: hidden;
    position: relative;
}

.gallery-image {
    height: 300px;
    object-fit: cover;
    width: 100%;
}

.carousel-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
    color: white;
    padding: 32px;
}

.carousel-overlay h5 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
}

/* Tab Content Styles */
.overview-content,
.services-content,
.policies-content,
.contact-content {
    min-height: 400px;
}

.info-card,
.quick-info-card,
.action-card,
.trust-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e9ecef;
}

.stat-box {
    text-align: center;
    padding: 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Amenities and Features */
.amenities-grid,
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-top: 16px;
}

.amenity-item,
.feature-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

/* Room Cards */
.room-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    transition: all 0.3s ease;
}

.room-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.1);
}

.price-tag {
    display: flex;
    align-items: baseline;
    gap: 4px;
    margin: 12px 0;
}

.price-tag .price {
    font-size: 20px;
    font-weight: 700;
    color: #28a745;
}

.price-tag small {
    color: #6c757d;
    font-size: 12px;
}

.room-amenities {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

/* Flight Info */
.flight-info-grid {
    display: grid;
    gap: 12px;
    margin-top: 16px;
}

.info-row {
    padding: 12px 16px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Pricing Grid */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.pricing-card {
    text-align: center;
    padding: 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.pricing-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.1);
}

.pricing-card h6 {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pricing-card .price {
    font-size: 24px;
    font-weight: 700;
    color: #007bff;
}

/* Routes List */
.routes-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 16px;
}

.route-item {
    padding: 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.route-path {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 16px;
}

.route-details {
    display: flex;
    gap: 16px;
}

/* Policy Sections */
.policy-section {
    margin-bottom: 24px;
    padding: 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.policy-section h6 {
    margin-bottom: 12px;
}

/* Contact Grid */
.contact-grid {
    display: grid;
    gap: 20px;
    margin-top: 16px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.contact-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f8f9fa;
}

.contact-icon i {
    font-size: 18px;
}

.contact-details h6 {
    font-size: 14px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Sidebar Styles */
.quick-info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item .label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
}

.info-item .value {
    font-weight: 600;
    color: #212529;
}

.trust-indicators {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.trust-item {
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
}

/* Modal Footer */
#providerDetailsModal .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 20px 32px;
    background: #f8f9fa;
    border-radius: 0 0 16px 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    #providerDetailsModal .modal-dialog {
        margin: 0;
        max-width: 100%;
        height: 100vh;
    }
    
    #providerDetailsModal .modal-content {
        height: 100vh;
        border-radius: 0;
    }
    
    .provider-gallery {
        height: 200px;
    }
    
    .gallery-image {
        height: 200px;
    }
    
    #providerDetailsModal .modal-header {
        padding: 16px 20px;
    }
    
    .amenities-grid,
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .pricing-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

/* Animation for modal appearance */
#providerDetailsModal.fade .modal-dialog {
    transition: transform 0.4s ease-out;
    transform: scale(0.9) translateY(-50px);
}

#providerDetailsModal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Selection Feedback and Status Styles */
.provider-card-enhanced.selecting {
    transform: scale(0.95) !important;
    transition: transform 0.2s ease;
}

.provider-card-enhanced.deselecting {
    transform: scale(1.05) !important;
    transition: transform 0.15s ease;
}

.selection-feedback {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    z-index: 1000;
    pointer-events: none;
    animation: selectionPulse 1.5s ease-out;
}

.selection-feedback.selected {
    background: rgba(40, 167, 69, 0.9);
    color: white;
}

.selection-feedback.deselected {
    background: rgba(108, 117, 125, 0.9);
    color: white;
}

@keyframes selectionPulse {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0;
    }
}

/* Status Indicators */
.selection-status-indicator {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 15;
}

/* Ensure consistent provider card layout */
.provider-card {
    min-height: 200px; /* Minimum height to prevent layout shifts */
    display: flex;
    flex-direction: column;
}

.provider-card .card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.provider-card .card-body .row {
    flex-grow: 1;
}

/* Ensure action buttons are always at bottom */
.provider-card .provider-actions-row,
.provider-card .d-flex.justify-content-between {
    margin-top: auto;
}

/* Service info container for consistent height */
.service-info-container {
    min-height: 60px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Provider contact info consistent height */
.provider-contact-info {
    min-height: 60px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Status-based card styling */
.provider-card-enhanced.status-approved {
    border-color: #28a745 !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2) !important;
}

.provider-card-enhanced.status-rejected {
    border-color: #dc3545 !important;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2) !important;
    opacity: 0.8;
}

.provider-card-enhanced.status-pending {
    border-color: #ffc107 !important;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2) !important;
}

/* Toast Container Positioning */
.toast-container {
    max-width: 350px;
}

.toast {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Enhanced selection states with better visual feedback */
.provider-card-enhanced.selected {
    border-color: #007bff !important;
    box-shadow: 0 8px 24px rgba(0, 123, 255, 0.3) !important;
    transform: translateY(-2px);
}

.provider-card-enhanced.selected .selection-overlay {
    opacity: 1;
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 123, 255, 0.05));
}

/* Bulk selection buttons */
.bulk-selection-controls {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.bulk-selection-controls .btn {
    font-size: 12px;
    padding: 4px 12px;
}

/* Selection count badge animation */
.selection-count-badge {
    display: inline-block;
    animation: countUpdate 0.3s ease;
}

@keyframes countUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Disabled state for action buttons */
.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Loading state for cards */
.provider-card-enhanced.loading-selection {
    opacity: 0.6;
    pointer-events: none;
}

.provider-card-enhanced.loading-selection::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #007bff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    z-index: 1000;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Selection summary enhancements */
#selectionSummary {
    border-top: 2px solid #007bff;
    background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced Provider Card Styles */
.enhanced-provider-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0, 0, 0, 0.05) !important;
    border-radius: 16px !important;
    overflow: hidden;
}

.enhanced-provider-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12) !important;
    border-color: rgba(0, 123, 255, 0.2) !important;
}

/* Provider Image Enhanced */
.provider-image-enhanced {
    width: 80px;
    height: 80px;
    object-fit: cover;
    transition: all 0.3s ease;
}

.provider-image-wrapper:hover .provider-image-enhanced {
    transform: scale(1.05);
}

/* Availability Indicators */
.availability-indicator {
    top: -4px;
    right: -4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    z-index: 10;
}

.availability-indicator.available {
    background: #28a745;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
}

.availability-indicator.limited {
    background: #ffc107;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}

.availability-indicator.unavailable {
    background: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.3);
}

/* Info Badges */
.info-badge {
    background: rgba(0, 123, 255, 0.1);
    border: 1px solid rgba(0, 123, 255, 0.2);
    border-radius: 20px;
    padding: 4px 8px;
    font-size: 12px;
    transition: all 0.3s ease;
}

.info-badge:hover {
    background: rgba(0, 123, 255, 0.15);
    transform: translateY(-1px);
}

/* Service Specific Info Sections */
.service-specific-info {
    background: rgba(248, 249, 250, 0.6);
    border-radius: 12px;
    padding: 12px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.hotel-info .info-badge {
    background: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.flight-info .info-badge {
    background: rgba(0, 123, 255, 0.1);
    border-color: rgba(0, 123, 255, 0.2);
    color: #007bff;
}

.transport-info .info-badge {
    background: rgba(40, 167, 69, 0.1);
    border-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

/* Pricing Display Enhancements */
.pricing-display {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.02));
    border-radius: 12px;
    padding: 12px;
    border: 1px solid rgba(40, 167, 69, 0.1);
}

.price-request {
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.05), rgba(0, 123, 255, 0.02));
    border-radius: 12px;
    padding: 12px;
    border: 1px solid rgba(0, 123, 255, 0.1);
}

/* Features Display */
.features-display {
    background: rgba(248, 249, 250, 0.8);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.feature-title {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.feature-tag {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 16px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.feature-tag:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Contact Information */
.contact-info {
    background: rgba(248, 249, 250, 0.6);
    border-radius: 8px;
    padding: 8px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

/* Provider Actions Enhanced */
.provider-actions {
    background: rgba(248, 249, 250, 0.5);
    border-radius: 12px;
    padding: 12px;
}

.provider-actions .btn-group .btn {
    border-radius: 8px;
    margin: 0 2px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 12px;
    padding: 6px 12px;
}

.provider-actions .btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Provider Title and Subtitle */
.provider-title {
    font-size: 18px;
    color: #212529;
    line-height: 1.3;
    margin-bottom: 4px;
}

.provider-subtitle {
    font-size: 13px;
    color: #6c757d;
    line-height: 1.2;
}

.provider-location {
    font-size: 12px;
    color: #6c757d;
}

/* Rating Display */
.provider-rating {
    font-size: 13px;
}

.rating-text {
    font-weight: 600;
    color: #495057;
}

/* Badge Enhancements */
.badge {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    border-radius: 6px;
}

/* Soft Badge Colors */
.bg-success-soft {
    background-color: rgba(40, 167, 69, 0.1) !important;
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.bg-warning-soft {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border: 1px solid rgba(255, 193, 7, 0.2);
}

.bg-danger-soft {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.bg-primary-soft {
    background-color: rgba(0, 123, 255, 0.1) !important;
    border: 1px solid rgba(0, 123, 255, 0.2);
}

/* Modal Enhancements for Provider Details */
.modal-header.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.provider-gallery {
    max-height: 300px;
    overflow: hidden;
}

.gallery-image {
    height: 300px;
    object-fit: cover;
}

.section-title {
    font-weight: 700;
    color: #495057;
    border-bottom: 2px solid rgba(0, 123, 255, 0.1);
    padding-bottom: 8px;
    margin-bottom: 16px;
}

.detail-card {
    transition: all 0.3s ease;
    background: #ffffff;
}

.detail-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.info-item {
    padding: 8px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.info-item:last-child {
    border-bottom: none;
}

.contact-item {
    padding: 8px 0;
    transition: all 0.2s ease;
}

.contact-item:hover {
    transform: translateX(4px);
}

.feature-item {
    padding: 6px 0;
    font-size: 14px;
    transition: all 0.2s ease;
}

.feature-item:hover {
    color: #007bff;
    transform: translateX(4px);
}

/* Additional Information Styling */
.additional-info {
    background: rgba(248, 249, 250, 0.7);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.provider-description {
    font-style: italic;
    color: #6c757d;
}

.provider-website a:hover {
    color: #0056b3 !important;
    transform: translateX(2px);
}

/* Accordion Enhancements for Policies */
.accordion-item {
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 8px !important;
    margin-bottom: 8px;
}

.accordion-button {
    border-radius: 8px !important;
    font-weight: 600;
    padding: 12px 16px;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(0, 123, 255, 0.05);
    border-color: rgba(0, 123, 255, 0.2);
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .enhanced-provider-card {
        margin-bottom: 20px;
    }
    
    .enhanced-provider-card:hover {
        transform: translateY(-4px);
    }
    
    .provider-image-enhanced {
        width: 60px;
        height: 60px;
    }
    
    .provider-title {
        font-size: 16px;
    }
    
    .info-badge {
        font-size: 10px;
        padding: 2px 6px;
    }
    
    .provider-actions .btn-group .btn {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .selection-feedback {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .bulk-selection-controls {
        justify-content: center;
    }
    
    .toast-container {
        left: 0;
        right: 0;
        max-width: calc(100% - 20px);
        margin: 0 10px;
    }
    
    .modal-xl {
        max-width: 100%;
        margin: 0;
    }
    
    .gallery-image {
        height: 200px;
    }
    
    .detail-card {
        margin-bottom: 8px;
    }
}

.provider-card .status-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    z-index: 10;
}

.provider-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.provider-results-grid.list-view {
    grid-template-columns: 1fr;
}

.provider-search-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
}

.provider-search-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.provider-search-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}

.provider-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 0.375rem;
}

.provider-rating {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
}

.empty-state {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    color: #6c757d;
}

.summary-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    border-left: 3px solid #0d6efd;
}

/* Modal Enhancements */
.modal-fullscreen .modal-content {
    height: 100vh;
    max-height: 100vh;
    overflow: hidden;
}

.modal-fullscreen .modal-body {
    height: calc(100vh - 120px);
    max-height: calc(100vh - 120px);
    overflow: hidden;
}

.modal-fullscreen .modal-dialog {
    margin: 0;
    max-width: 100%;
    height: 100vh;
}

/* Fix form button positioning */
#externalServiceForm form {
    display: flex;
    flex-direction: column;
}

#externalServiceForm .row {
    flex-grow: 1;
}

#externalServiceForm .d-flex.justify-content-end {
    flex-shrink: 0;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .provider-results-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-fullscreen .modal-dialog {
        margin: 0;
    }
}

/* Loading and Error States */
.provider-card-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.error-state {
    border: 1px solid #dc3545;
    background: #f8d7da;
    color: #721c24;
}

/* Enhanced Provider Card Styles */
.service-card,
.provider-card {
    border: 2px solid transparent !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    background: #fff;
    position: relative;
    overflow: hidden;
}

/* Selected Provider Cards */
.selected-provider-card {
    transition: all 0.3s ease;
    border: 1px solid #e3f2fd !important;
    background: #f8f9ff !important;
}

.selected-provider-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    border-color: #2196f3 !important;
}

/* Soft badge colors */
.bg-danger-soft {
    background-color: rgba(220, 53, 69, 0.1) !important;
    color: #dc3545 !important;
}

.bg-primary-soft {
    background-color: rgba(13, 110, 253, 0.1) !important;
    color: #0d6efd !important;
}

.bg-success-soft {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #198754 !important;
}

.service-card:hover,
.provider-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12) !important;
}

/* Search Results Card Enhancements */
.provider-search-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.provider-search-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.provider-search-card.selected {
    border-color: #0d6efd !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(13, 110, 253, 0.2);
}

.provider-search-card .selection-overlay {
    border-radius: inherit;
}

.provider-avatar {
    transition: all 0.3s ease;
}

.provider-search-card:hover .provider-avatar {
    transform: scale(1.05);
}

.star-rating i {
    font-size: 14px;
    margin-right: 1px;
}

.service-info {
    line-height: 1.4;
}

.features-section .badge {
    font-size: 11px;
    padding: 4px 8px;
}

.card-footer .btn {
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
}

/* Simple Provider Card Styles */
.provider-card {
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.provider-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.provider-card .card-body {
    padding: 1.25rem;
}

.provider-card .card-title {
    font-weight: 600;
    color: #2c3e50;
}

.provider-card .btn {
    transition: all 0.2s ease;
}

.provider-card .btn:hover {
    transform: translateY(-1px);
}

.provider-card.selected {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1) !important;
    background: #f8f9fa;
}

.provider-card .card-header {
    border-bottom: none;
    padding: 1rem;
    position: relative;
    overflow: hidden;
}

.provider-card .card-body {
    padding: 1.25rem;
}

.provider-card .form-check-input {
    transform: scale(1.2);
    margin: 0;
}

.provider-card .badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

/* Text sizing */
.text-sm {
    font-size: 0.875rem;
}

/* Provider results grid responsive */
.provider-results-grid {
    min-height: 400px;
}

.provider-results-grid .row {
    margin: 0;
}

/* Modal enhancements */
.modal-dialog {
    margin: 0;
}

.modal-content {
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    height: 100%;
    max-height: 100%;
    overflow: hidden;
}

.modal-body {
    overflow: hidden;
    padding: 0;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    flex-shrink: 0;
}

/* Sidebar scrolling improvements */
.provider-modal-sidebar {
    overflow-y: auto;
    overflow-x: hidden;
    max-height: calc(95vh - 120px);
}

.provider-modal-sidebar::-webkit-scrollbar {
    width: 6px;
}

.provider-modal-sidebar::-webkit-scrollbar-track {
    background: #f8f9fa;
}

.provider-modal-sidebar::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.provider-modal-sidebar::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Provider results scrolling */
.provider-results-container {
    max-height: calc(95vh - 287px);
    overflow-y: auto;
    overflow-x: hidden;
}

.provider-results-container::-webkit-scrollbar {
    width: 8px;
}

.provider-results-container::-webkit-scrollbar-track {
    background: #f8f9fa;
}

.provider-results-container::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.provider-results-container::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Modal height fixes */
#providerSelectionModal .modal-dialog {
    height: 95vh !important;
    max-height: 95vh !important;
    margin: 2.5vh auto !important;
}

#providerSelectionModal .modal-content {
    height: 100% !important;
    max-height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

#providerSelectionModal .modal-body {
    flex: 1 1 auto !important;
    overflow: hidden !important;
    padding: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
}

/* Form spacing in sidebar */
.provider-modal-sidebar .form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.provider-modal-sidebar .mb-3 {
    margin-bottom: 1.5rem !important;
}

.provider-modal-sidebar .btn {
    font-size: 0.875rem;
}

/* Ensure proper spacing for search form */
#providerSearchForm .form-control,
#providerSearchForm .form-select {
    margin-bottom: 0.75rem;
}

/* Fix for full height content */
.flex-grow-1 {
    flex: 1 1 auto;
    min-height: 0;
}

.overflow-auto {
    overflow: auto !important;
}

/* Provider results container */
.provider-results-grid {
    min-height: 500px;
    max-height: calc(95vh - 200px);
    overflow-y: auto;
}

/* Ensure cards display properly */
.provider-results-grid .row {
    margin: 0;
    padding: 1rem;
}

/* Selection summary */
#selectionSummary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 2px solid #0d6efd !important;
}

/* Search form enhancements */
#providerSearchForm .form-label {
    color: #495057;
    font-size: 0.875rem;
}

#providerSearchForm .form-control,
#providerSearchForm .form-select {
    border: 2px solid #e9ecef;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#providerSearchForm .form-control:focus,
#providerSearchForm .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}

/* Pagination */
.pagination .page-link {
    border: none;
    margin: 0 2px;
    border-radius: 6px;
    color: #6c757d;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Header improvements */
.modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
    border-bottom: none;
}

/* Loading and empty states */
#searchLoadingState,
#searchEmptyState {
    color: #6c757d;
}

/* Results header */
#resultsTitle {
    color: #343a40;
    font-weight: 600;
}

/* Bootstrap 5 utility classes compatibility */
.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}

.overflow-auto {
    overflow: auto;
}

.flex-grow-1 {
    flex-grow: 1;
}

.h-100 {
    height: 100%;
}

/* Button hover effects */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Enhanced provider cards grid layout */
.provider-results-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)) !important;
    gap: 1.5rem !important;
    padding: 1rem !important;
    min-height: 500px;
    max-height: calc(95vh - 200px);
    overflow-y: auto;
}


/* Ensure provider cards are visible and styled */
.provider-card-enhanced {
    display: block !important;
    min-height: 300px !important;
}

/* Force proper display of card elements */
.provider-image-section,
.card-header-section,
.features-section,
.price-section {
    display: block !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-fullscreen .col-lg-3 {
        display: none;
    }
    
    .modal-fullscreen .col-lg-9 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    #providerSelectionModal .modal-dialog {
        width: 100vw !important;
        max-width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
    }
    
    #externalServiceSection {
        max-height: calc(100vh - 150px);
    }
    
    .provider-results-container {
        max-height: calc(100vh - 200px);
    }
    
    .provider-modal-sidebar {
        max-height: calc(100vh - 150px);
    }
    
    .provider-results-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- Merged Provider Selector JavaScript -->
<script src="{{ asset('assets/js/provider-selector-merged.js') }}?v={{ time() }}"></script>

<!-- Initialize Merged Provider Selector -->
<script>
// Global variables to store selected providers (ensure it exists globally)
if (!window.selectedProviders) {
    window.selectedProviders = {
        hotels: [],
        flights: [],
        transport: []
    };
}

/**
 * Update provider cards with approval status from draft data
 * This function should be called when loading a draft to show current approval status
 */
function updateProviderApprovalStatus(draftData) {

    
    if (!draftData || !draftData.provider_approvals) {

        return;
    }
    
    const providerApprovals = draftData.provider_approvals;
    
    // Update each provider card with its approval status
    Object.keys(providerApprovals).forEach(serviceRequestId => {
        const approval = providerApprovals[serviceRequestId];

        
        updateProviderCardApprovalStatus(
            approval.item_id,
            approval.provider_type,
            approval.status,
            {
                approved_at: approval.approved_at,
                offered_price: approval.offered_price,
                currency: approval.currency,
                service_request_id: serviceRequestId
            }
        );
    });
}

/**
 * Update a specific provider card with approval status
 */
function updateProviderCardApprovalStatus(itemId, providerType, status, details = {}) {

    
    // Find the provider card
    const selectors = [
        `[data-provider-id="${itemId}"]`,
        `[data-service-id="${itemId}"]`,
        `[data-hotel-id="${itemId}"]`,
        `[data-flight-id="${itemId}"]`,
        `[data-transport-id="${itemId}"]`,
        `.provider-card[data-id="${itemId}"]`,
        `.${providerType}-card[data-id="${itemId}"]`
    ];
    
    let providerCard = null;
    for (const selector of selectors) {
        providerCard = document.querySelector(selector);
        if (providerCard) {

            break;
        }
    }
    
    if (!providerCard) {
        console.warn('Provider card not found for:', { itemId, providerType });
        return;
    }
    
    // Remove existing status classes
    providerCard.classList.remove('status-approved', 'status-rejected', 'status-pending');
    
    // Add new status class
    providerCard.classList.add(`status-${status}`);
    
    // Update or create status badge
    let statusBadge = providerCard.querySelector('.approval-status-badge');
    if (!statusBadge) {
        statusBadge = document.createElement('div');
        statusBadge.className = 'approval-status-badge position-absolute top-0 end-0 m-2';
        providerCard.style.position = 'relative';
        providerCard.appendChild(statusBadge);
    }
    
    // Set badge content based on status
    let badgeClass = 'badge ';
    let badgeText = '';
    let badgeIcon = '';
    
    switch (status) {
        case 'approved':
            badgeClass += 'bg-success-soft text-success';
            badgeText = 'Approved';
            badgeIcon = '<i class="fas fa-check-circle me-1"></i>';
            break;
        case 'rejected':
            badgeClass += 'bg-danger-soft text-danger';
            badgeText = 'Rejected';
            badgeIcon = '<i class="fas fa-times-circle me-1"></i>';
            break;
        case 'pending':
        default:
            badgeClass += 'bg-warning-soft text-warning';
            badgeText = 'Pending';
            badgeIcon = '<i class="fas fa-clock me-1"></i>';
            break;
    }
    
    statusBadge.className = `approval-status-badge position-absolute ${badgeClass}`;
    statusBadge.style.cssText = 'top: 0.5rem; right: 0.5rem; z-index: 10; font-size: 0.75rem;';
    statusBadge.innerHTML = badgeIcon + badgeText;
    
    // Update request approval action buttons
    updateProviderCardActions(providerCard, status, details);
    
    // Update tab indicators
    updateTabStatusIndicators();
}

/**
 * Update provider card action buttons based on status
 */
function updateProviderCardActions(providerCard, status, details) {
    const cardBody = providerCard.querySelector('.card-body');
    if (!cardBody) return;
    
    // Find or create action section
    let actionSection = cardBody.querySelector('.provider-approval-actions');
    if (!actionSection) {
        actionSection = document.createElement('div');
        actionSection.className = 'provider-approval-actions mt-3 pt-2 border-top';
        cardBody.appendChild(actionSection);
    }
    
    // Update actions based on status
    switch (status) {
        case 'approved':
            actionSection.innerHTML = `
                <div class="alert alert-success border-0 rounded p-2 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <div class="flex-grow-1">
                            <strong class="small">Request Approved</strong>
                            ${details.offered_price ? `<div class="text-muted small">Price: ${details.currency || 'USD'} ${details.offered_price}</div>` : ''}
                            ${details.approved_at ? `<div class="text-muted small">Approved: ${new Date(details.approved_at).toLocaleDateString()}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'rejected':
            actionSection.innerHTML = `
                <div class="alert alert-danger border-0 rounded p-2 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <div class="flex-grow-1">
                            <strong class="small">Request Rejected</strong>
                            <div class="text-muted small">Provider declined this request</div>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'pending':
        default:
            actionSection.innerHTML = `
                <div class="alert alert-warning border-0 rounded p-2 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock text-warning me-2"></i>
                        <div class="flex-grow-1">
                            <strong class="small">Awaiting Response</strong>
                            <div class="text-muted small">Request sent to provider</div>
                        </div>
                    </div>
                </div>
            `;
            break;
    }
}

/**
 * Update tab status indicators based on approval status
 */
function updateTabStatusIndicators() {
    const tabs = ['hotels', 'flights', 'transport'];
    
    tabs.forEach(tabType => {
        const tabButton = document.getElementById(`${tabType}-tab`);
        const statusIndicator = document.getElementById(`${tabType}StatusIndicator`);
        
        if (!tabButton || !statusIndicator) return;
        
        // Count statuses for this tab
        const tabCards = document.querySelectorAll(`#${tabType} .provider-card, #${tabType} .selected-provider-card`);
        let approvedCount = 0;
        let rejectedCount = 0;
        let pendingCount = 0;
        let totalCount = tabCards.length;
        
        tabCards.forEach(card => {
            if (card.classList.contains('status-approved')) approvedCount++;
            else if (card.classList.contains('status-rejected')) rejectedCount++;
            else if (card.classList.contains('status-pending')) pendingCount++;
        });
        
        // Update status indicator
        if (totalCount > 0) {
            statusIndicator.style.display = 'block';
            
            if (approvedCount === totalCount) {
                // All approved
                statusIndicator.className = 'status-indicator position-absolute';
                statusIndicator.style.background = '#28a745';
            } else if (rejectedCount > 0) {
                // Some rejected
                statusIndicator.className = 'status-indicator position-absolute rejected';
                statusIndicator.style.background = '#dc3545';
            } else if (pendingCount > 0) {
                // Some pending
                statusIndicator.className = 'status-indicator position-absolute pending';
                statusIndicator.style.background = '#ffc107';
            }
        } else {
            statusIndicator.style.display = 'none';
        }
    });
}

// Function to update hidden form fields for selected providers
function updateHiddenFormFields() {

    
    // Update hotels
    updateProviderFormFields('hotels', window.selectedProviders.hotels);
    
    // Update flights
    updateProviderFormFields('flights', window.selectedProviders.flights);
    
    // Update transport
    updateProviderFormFields('transport', window.selectedProviders.transport);
    
    // Update badges
    updateProviderBadges();
}

// Function to update form fields for a specific provider type
function updateProviderFormFields(type, providers) {
    const container = document.getElementById(`selected${type.charAt(0).toUpperCase() + type.slice(1)}Data`);
    if (!container) return;
    
    // Clear existing fields
    container.innerHTML = '';
    
    // Add hidden fields for each selected provider
    providers.forEach((provider, index) => {
        Object.keys(provider).forEach(key => {
            if (provider[key] !== null && provider[key] !== undefined) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `selected_${type}[${index}][${key}]`;
                input.value = provider[key];
                container.appendChild(input);
            }
        });
    });
}

// Function to update provider badges
function updateProviderBadges() {
    const hotelBadge = document.getElementById('hotelCountBadge');
    const flightBadge = document.getElementById('flightCountBadge');
    const transportBadge = document.getElementById('transportCountBadge');
    
    if (hotelBadge) hotelBadge.textContent = window.selectedProviders.hotels.length;
    if (flightBadge) flightBadge.textContent = window.selectedProviders.flights.length;
    if (transportBadge) transportBadge.textContent = window.selectedProviders.transport.length;
    
    // Update summary counts
    const hotelSummary = document.getElementById('hotelSummaryCount');
    const flightSummary = document.getElementById('flightSummaryCount');
    const transportSummary = document.getElementById('transportSummaryCount');
    
    if (hotelSummary) hotelSummary.textContent = `${window.selectedProviders.hotels.length} selected`;
    if (flightSummary) flightSummary.textContent = `${window.selectedProviders.flights.length} selected`;
    if (transportSummary) transportSummary.textContent = `${window.selectedProviders.transport.length} selected`;
    
    // Update overall status
    const totalSelected = window.selectedProviders.hotels.length + 
                         window.selectedProviders.flights.length + 
                         window.selectedProviders.transport.length;
    
    const overallStatus = document.getElementById('overallStatusSummary');
    if (overallStatus) {
        if (totalSelected === 0) {
            overallStatus.textContent = 'No providers selected';
            overallStatus.className = 'summary-value fw-bold d-block text-muted';
        } else {
            overallStatus.textContent = 'Ready to proceed';
            overallStatus.className = 'summary-value fw-bold d-block text-success';
        }
    }
}

// Function to add a provider to selections
function addProviderToSelection(type, provider) {


    
    // Ensure the selectedProviders object exists
    if (!window.selectedProviders) {

        window.selectedProviders = { hotels: [], flights: [], transport: [] };
    }
    
    // Ensure the type array exists
    if (!window.selectedProviders[type]) {

        window.selectedProviders[type] = [];
    }
    
    // Check if provider already exists
    const existingIndex = window.selectedProviders[type].findIndex(p => p.id === provider.id);
    if (existingIndex === -1) {
        window.selectedProviders[type].push(provider);


        
        updateHiddenFormFields();
        // Note: Display is handled by existing provider system
        
        if (typeof toastr !== 'undefined') {
            toastr.success(`${provider.name || provider.company || provider.airline} added to selection`);
        }
    } else {

    }
}

// Function to remove a provider from selections
function removeProviderFromSelection(type, providerId) {

    
    const index = window.selectedProviders[type].findIndex(p => p.id == providerId);
    if (index !== -1) {
        const removed = window.selectedProviders[type].splice(index, 1)[0];
        updateHiddenFormFields();
        // Note: Display is handled by existing provider system
        // We just ensure data consistency
        
        if (typeof toastr !== 'undefined') {
            toastr.info(`${removed.name || removed.company || removed.airline} removed from selection`);
        }
    }
}

// Function to update provider display (removed - using existing system)
// The existing provider selection system already handles display
// We just need to ensure data consistency

// Function to restore providers to the existing system format
function restoreProvidersToExistingSystem(type, providers) {

    
    const containerName = type.charAt(0).toUpperCase() + type.slice(1);
    const container = document.getElementById(`selected${containerName}`);
    const emptyState = document.getElementById(`no${containerName}Selected`);
    
    if (!container) {
        console.error(`Container selected${containerName} not found`);
        return;
    }
    
    if (providers.length === 0) {
        container.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
        return;
    }
    
    // Show container and hide empty state
    container.style.display = 'block';
    if (emptyState) emptyState.style.display = 'none';
    
    // Generate cards using the existing system format
    container.innerHTML = providers.map((provider, index) => {
        const name = provider.name || provider.company || provider.airline || 'Restored Provider';
        const providerId = provider.id || (Date.now() + index);
        
        return `
            <div class="card mb-3 provider-card" data-provider-id="${providerId}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <!-- Header Row -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="provider-icon me-3">
                                    <div class="icon-circle bg-primary text-white">
                                        <i class="fas ${getProviderIcon(type)}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">${name}</h5>
                                    ${getProviderSubtitle(type, provider)}
                                </div>
                                <div class="provider-actions">
                                    ${getProviderPrice(provider)}
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                            onclick="removeProviderFromDraft('${type}', ${providerId})" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            ${getProviderDetails(type, provider)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    

}

// Helper functions for provider card generation
function getProviderIcon(type) {
    switch(type) {
        case 'hotels': return 'fa-bed';
        case 'flights': return 'fa-plane';
        case 'transport': return 'fa-bus';
        default: return 'fa-building';
    }
}

function getProviderSubtitle(type, provider) {
    switch(type) {
        case 'hotels':
            return `<small class="text-muted">${provider.location || 'Location TBD'}</small>`;
        case 'flights':
            return `<small class="text-muted">${provider.departure || 'TBD'} → ${provider.arrival || 'TBD'}</small>`;
        case 'transport':
            return `<small class="text-muted">${provider.pickup_location || 'TBD'} → ${provider.dropoff_location || 'TBD'}</small>`;
        default:
            return '<small class="text-muted">Service Provider</small>';
    }
}

function getProviderPrice(provider) {
    if (provider.price && provider.price > 0) {
        return `<span class="badge bg-success">${provider.price} ${provider.currency || 'USD'}</span>`;
    }
    return '<span class="badge bg-secondary">Price on request</span>';
}

function getProviderDetails(type, provider) {
    const details = [];
    
    switch(type) {
        case 'hotels':
            if (provider.nights) details.push(`${provider.nights} nights`);
            if (provider.room_type) details.push(`${provider.room_type} rooms`);
            if (provider.rooms_needed) details.push(`${provider.rooms_needed} rooms needed`);
            break;
        case 'flights':
            if (provider.flight_type) details.push(`${provider.flight_type} flight`);
            if (provider.seats_allocated) details.push(`${provider.seats_allocated} seats`);
            break;
        case 'transport':
            if (provider.vehicle_type) details.push(`${provider.vehicle_type}`);
            if (provider.capacity) details.push(`${provider.capacity} capacity`);
            break;
    }
    
    if (details.length > 0) {
        return `
            <div class="provider-details mt-2">
                <small class="text-muted">${details.join(' • ')}</small>
            </div>
        `;
    }
    
    return '';
}

// Function to remove provider from draft (used by the remove buttons) - ENHANCED
function removeProviderFromDraft(type, providerId) {

    
    // IMPORTANT: Also remove from MergedProviderSelector if it exists
    if (window.MergedProviderSelector && typeof window.MergedProviderSelector.removeProvider === 'function') {

        const success = window.MergedProviderSelector.removeProvider(providerId, type);
        if (success) {

            return; // MergedProviderSelector.removeProvider handles all the cleanup
        } else {

        }
    }
    
    // Fallback: Legacy removal system

    
    // Remove from global object
    const index = window.selectedProviders[type].findIndex(p => p.id == providerId);
    if (index !== -1) {
        const removedProvider = window.selectedProviders[type][index];
        window.selectedProviders[type].splice(index, 1);

    }
    
    // Remove the card from DOM
    const card = document.querySelector(`[data-provider-id="${providerId}"]`);
    if (card) {
        card.remove();
    }
    
    // Update display
    const containerName = type.charAt(0).toUpperCase() + type.slice(1);
    const container = document.getElementById(`selected${containerName}`);
    const emptyState = document.getElementById(`no${containerName}Selected`);
    
    if (container && window.selectedProviders[type].length === 0) {
        container.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
    }
    
    // Update hidden fields and badges
    updateHiddenFormFields();
    updateProviderBadges();
    

}

// Debug function to compare old vs new provider data formats
function debugProviderDataFormats() {
    console.group('🔍 Provider Data Format Comparison');
    
    if (window.selectedProviders && Object.keys(window.selectedProviders).length > 0) {
        console.log('📊 Current provider data (OLD FORMAT):');



        
        // Show size of current data
        const currentDataSize = JSON.stringify(window.selectedProviders).length;

        
        // Show sample of current data
        if (window.selectedProviders.hotels?.[0]) {
            console.log('📄 Sample hotel data (OLD):', window.selectedProviders.hotels[0]);
        }
        
        // Test new minimal format if available
        if (window.MergedProviderSelector && typeof window.MergedProviderSelector.getMinimalProvidersForDraft === 'function') {
            const minimalData = window.MergedProviderSelector.getMinimalProvidersForDraft();
            const minimalDataSize = JSON.stringify(minimalData).length;
            


            console.log('Size reduction:', ((currentDataSize - minimalDataSize) / currentDataSize * 100).toFixed(1) + '%');

        }
    } else {

    }
    
    console.groupEnd();
}

// Initialize MergedProviderSelector when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if MergedProviderSelector exists
    if (window.MergedProviderSelector) {
        window.MergedProviderSelector.init();
    } else {

    }
    
    // Listen for step changes to update provider approval status
    if (window.packageWizard) {
        window.packageWizard.on('stepChanged', function(stepNumber) {
            if (stepNumber === 3) {

                
                // Check if we have draft data with provider approvals
                const draftDataInput = document.querySelector('input[name="draft_data"]');
                if (draftDataInput && draftDataInput.value) {
                    try {
                        const draftData = JSON.parse(draftDataInput.value);
                        if (draftData.provider_approvals) {
                            setTimeout(() => {
                                updateProviderApprovalStatus(draftData);
                            }, 1000); // Delay to ensure provider cards are loaded
                        }
                    } catch (e) {
                        console.warn('Failed to parse draft data:', e);
                    }
                }
                
                // Also check window.draftData
                if (window.draftData && window.draftData.data && window.draftData.data.provider_approvals) {
                    setTimeout(() => {
                        updateProviderApprovalStatus(window.draftData.data);
                    }, 1000);
                }
            }
        });
    }
});

// Function to load selected providers from draft data (ENHANCED)
function loadProvidersFromDraft(draftData) {
    
    // Try using the new MergedProviderSelector system first
    if (window.MergedProviderSelector && typeof window.MergedProviderSelector.loadDraftProvidersIfAvailable === 'function') {

        // Make draft data available to MergedProviderSelector
        window.draftData = { data: draftData };
        
        // Trigger the new loading system
        window.MergedProviderSelector.loadDraftProvidersIfAvailable();
        return;
    }
    
    if (draftData.selected_hotels && Array.isArray(draftData.selected_hotels)) {
        window.selectedProviders.hotels = [...draftData.selected_hotels];
        // Trigger existing system to display hotels
        restoreProvidersToExistingSystem('hotels', draftData.selected_hotels);
    }
    
    if (draftData.selected_flights && Array.isArray(draftData.selected_flights)) {
        window.selectedProviders.flights = [...draftData.selected_flights];
        // Trigger existing system to display flights
        restoreProvidersToExistingSystem('flights', draftData.selected_flights);
    }
    
    if (draftData.selected_transport && Array.isArray(draftData.selected_transport)) {
        window.selectedProviders.transport = [...draftData.selected_transport];
        // Trigger existing system to display transport
        restoreProvidersToExistingSystem('transport', draftData.selected_transport);
    }
    
    updateHiddenFormFields();
}

// Function to integrate with existing provider selection modal
function setupProviderModalIntegration() {
    
    // Override or enhance the confirmSelectionBtn functionality
    const confirmBtn = document.getElementById('confirmSelectionBtn');
    if (confirmBtn) {
        
        confirmBtn.addEventListener('click', function(e) {
            // Prevent default behavior
            e.preventDefault();
            e.stopPropagation();
            
            // Collect selected providers from the modal
            const selectedProviders = collectSelectedProvidersFromModal();
            
            // Add them to our system
            if (selectedProviders.length > 0) {
                selectedProviders.forEach(provider => {
                    addProviderToSelection(provider.type, provider.data);
                });
                
                // Close the modal
                const modal = document.getElementById('providerSelectionModal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            } else {

            }
        });
    } else {
        // Retry after a delay in case the modal isn't loaded yet
        setTimeout(setupProviderModalIntegration, 1000);
    }
}

// Function to collect selected providers from the modal AND existing selections
function collectSelectedProvidersFromModal() {
    const providers = [];
    
    // First, try to collect from modal selections
    const selectedCards = document.querySelectorAll('.provider-card.selected, .provider-search-card.selected, input[type="checkbox"]:checked[data-provider-id]');

    
    selectedCards.forEach(element => {
        try {
            let providerData = null;
            let providerType = null;
            
            // Try different methods to extract provider data
            if (element.dataset.providerData) {
                // Data stored in data attribute
                providerData = JSON.parse(element.dataset.providerData);
                providerType = element.dataset.providerType || determineProviderType(providerData);
            } else if (element.dataset.providerId) {
                // Extract from data attributes
                providerData = {
                    id: parseInt(element.dataset.providerId),
                    name: element.dataset.providerName || element.querySelector('.provider-name')?.textContent || 'Unknown Provider',
                    price: parseFloat(element.dataset.providerPrice) || 0,
                    currency: element.dataset.providerCurrency || 'USD'
                };
                providerType = element.dataset.providerType || 'hotels';
            } else {
                // Try to extract from the card content
                const card = element.closest('.provider-card, .provider-search-card');
                if (card) {
                    providerData = extractProviderDataFromCard(card);
                    providerType = card.dataset.providerType || determineProviderType(providerData);
                }
            }
            
            if (providerData && providerType) {
                providers.push({
                    type: providerType,
                    data: providerData
                });
            }
        } catch (error) {
            console.error('❌ Error extracting provider data from modal:', error, element);
        }
    });
    
    // If no providers found in modal, try to collect from existing selected-providers-list containers
    if (providers.length === 0) {

        
        // Check each provider type container
        const providerTypes = ['Hotels', 'Flights', 'Transport'];
        providerTypes.forEach(type => {
            const containerName = type.toLowerCase();
            const container = document.getElementById(`selected${type}`);
            
            if (container) {

                // Updated selector to match the actual structure
                const existingItems = container.querySelectorAll('.provider-card[data-provider-id], .card[data-provider-id], [data-provider-id]');


                
                existingItems.forEach(item => {
                    try {
                        const providerData = extractProviderDataFromExistingItem(item, containerName);
                        if (providerData) {
                            providers.push({
                                type: containerName,
                                data: providerData
                            });
                        }
                    } catch (error) {
                        console.error(`❌ Error extracting existing ${type} provider:`, error, item);
                    }
                });
            }
        });
    }
    

    return providers;
}

// Function to extract provider data from a card element
function extractProviderDataFromCard(card) {
    const data = {
        id: parseInt(card.dataset.providerId || card.id || Math.random() * 1000),
        name: card.querySelector('.provider-name, .card-title, h6')?.textContent?.trim() || 'Unknown Provider'
    };
    
    // Try to get price
    const priceElement = card.querySelector('[data-price], .price, .price-amount');
    if (priceElement) {
        data.price = parseFloat(priceElement.dataset.price || priceElement.textContent.replace(/[^\d.]/g, '')) || 0;
    }
    
    // Try to get currency
    const currencyElement = card.querySelector('[data-currency]');
    if (currencyElement) {
        data.currency = currencyElement.dataset.currency || 'USD';
    }
    
    // Try to get location
    const locationElement = card.querySelector('.location, .provider-location');
    if (locationElement) {
        data.location = locationElement.textContent.trim();
    }
    
    return data;
}

// Function to extract provider data from existing selected items
function extractProviderDataFromExistingItem(item, type) {

    
    // Try to get basic data
    const data = {
        id: parseInt(item.dataset.providerId || item.id || Date.now() + Math.random()),
        name: null,
        price: 0,
        currency: 'USD',
        type: type
    };
    
    // Extract name from various possible locations
    const nameSelectors = [
        '.provider-name', 
        '.card-title', 
        'h6', 
        'h5', 
        '[data-provider-name]',
        '.name',
        '.title'
    ];
    
    for (const selector of nameSelectors) {
        const nameElement = item.querySelector(selector);
        if (nameElement) {
            data.name = nameElement.textContent.trim();
            break;
        }
    }
    
    // If no name found, try data attributes
    if (!data.name) {
        data.name = item.dataset.providerName || item.dataset.name || 'Unknown Provider';
    }
    
    // Extract price
    const priceSelectors = [
        '.price', 
        '.price-amount', 
        '.cost', 
        '[data-price]',
        '.badge:contains("$")',
        '.badge:contains("€")',
        '.badge:contains("£")'
    ];
    
    for (const selector of priceSelectors) {
        const priceElement = item.querySelector(selector);
        if (priceElement) {
            const priceText = priceElement.dataset.price || priceElement.textContent;
            const priceMatch = priceText.match(/([\d.,]+)/);
            if (priceMatch) {
                data.price = parseFloat(priceMatch[1].replace(',', ''));
            }
            break;
        }
    }
    
    // Extract currency
    const currencyElement = item.querySelector('[data-currency]');
    if (currencyElement) {
        data.currency = currencyElement.dataset.currency;
    } else {
        // Try to extract from price text
        const allText = item.textContent;
        if (allText.includes('USD') || allText.includes('$')) data.currency = 'USD';
        else if (allText.includes('EUR') || allText.includes('€')) data.currency = 'EUR';
        else if (allText.includes('GBP') || allText.includes('£')) data.currency = 'GBP';
        else if (allText.includes('TRY') || allText.includes('₺')) data.currency = 'TRY';
    }
    
    // Add type-specific data
    if (type === 'hotels') {
        data.location = item.querySelector('.location, .address, .city')?.textContent.trim() || '';
        data.nights = parseInt(item.dataset.nights || '1');
        data.room_type = item.dataset.roomType || 'standard';
    } else if (type === 'flights') {
        data.airline = data.name; // For flights, name is usually the airline
        data.departure = item.querySelector('.departure, .from')?.textContent.trim() || '';
        data.arrival = item.querySelector('.arrival, .to')?.textContent.trim() || '';
        data.flight_type = item.dataset.flightType || 'departure';
    } else if (type === 'transport') {
        data.company = data.name; // For transport, name is usually the company
        data.vehicle_type = item.dataset.vehicleType || 'bus';
        data.pickup_location = item.querySelector('.pickup, .from')?.textContent.trim() || '';
        data.dropoff_location = item.querySelector('.dropoff, .to')?.textContent.trim() || '';
    }
    

    return data;
}

// Function to determine provider type based on data
function determineProviderType(data) {
    if (data.airline || data.flight_number) return 'flights';
    if (data.vehicle_type || data.capacity) return 'transport';
    return 'hotels'; // default
}

// Make functions globally accessible
window.updateHiddenFormFields = updateHiddenFormFields;
window.addProviderToSelection = addProviderToSelection;
window.removeProviderFromSelection = removeProviderFromSelection;
window.restoreProvidersToExistingSystem = restoreProvidersToExistingSystem;
window.removeProviderFromDraft = removeProviderFromDraft;
window.loadProvidersFromDraft = loadProvidersFromDraft;
window.updateProviderBadges = updateProviderBadges;
window.setupProviderModalIntegration = setupProviderModalIntegration;
window.collectSelectedProvidersFromModal = collectSelectedProvidersFromModal;
window.extractProviderDataFromExistingItem = extractProviderDataFromExistingItem;

// Debug function to inspect existing selected providers
window.debugExistingProviders = function() {

    
    const providerTypes = ['Hotels', 'Flights', 'Transport'];
    
    providerTypes.forEach(type => {
        console.log(`\n🔍 --- ${type.toUpperCase()} ---`);
        const container = document.getElementById(`selected${type}`);
        
        if (container) {

            console.log(`📋 Container HTML:`, container.outerHTML.substring(0, 500) + '...');
            
            // Check all possible child elements
            const allChildren = container.children;

            
            for (let i = 0; i < allChildren.length; i++) {
                const child = allChildren[i];
                console.log(`  Child ${i}:`, {
                    tagName: child.tagName,
                    className: child.className,
                    id: child.id,
                    textContent: child.textContent.trim().substring(0, 100),
                    dataset: child.dataset
                });
            }
            
            // Try various selectors
            const selectors = [
                '.selected-provider-card',
                '.provider-item', 
                '.selected-item',
                '[data-provider-id]',
                '.provider-card',
                '.card',
                'div',
                '*'
            ];
            
            selectors.forEach(selector => {
                const found = container.querySelectorAll(selector);
                if (found.length > 0) {

                    found.forEach((item, index) => {
                        if (index < 3) { // Show first 3 items only
                            console.log(`    Item ${index}:`, {
                                element: item,
                                className: item.className,
                                textContent: item.textContent.trim().substring(0, 50),
                                innerHTML: item.innerHTML.substring(0, 100)
                            });
                        }
                    });
                }
            });
        } else {

        }
    });
    

};

// Debug function to inspect the modal
// Function to sync existing selected providers with our selectedProviders object
window.syncExistingProvidersToGlobal = function() {

    
    // Ensure our global object exists
    if (!window.selectedProviders) {
        window.selectedProviders = { hotels: [], flights: [], transport: [] };
    }
    
    const providerTypes = [{ name: 'Hotels', key: 'hotels' }, { name: 'Flights', key: 'flights' }, { name: 'Transport', key: 'transport' }];
    
    providerTypes.forEach(({ name, key }) => {
        const container = document.getElementById(`selected${name}`);
        if (container) {
            // Clear existing data for this type
            window.selectedProviders[key] = [];
            
            // Find all provider cards in this container
            const cards = container.querySelectorAll('.provider-card[data-provider-id], .card[data-provider-id]');

            
            cards.forEach((card, index) => {
                try {
                    const providerData = {
                        id: parseInt(card.dataset.providerId) || (Date.now() + index),
                        name: card.textContent.trim().split('\n')[0].trim(), // Get first line as name
                        price: 0,
                        currency: 'USD',
                        type: key
                    };
                    
                    // Try to extract more details from the card
                    const nameElement = card.querySelector('h5, h6, .provider-name, .card-title');
                    if (nameElement) {
                        providerData.name = nameElement.textContent.trim();
                    }
                    
                    // Try to extract price
                    const priceElement = card.querySelector('.price, .cost, .badge');
                    if (priceElement) {
                        const priceMatch = priceElement.textContent.match(/([\d,]+(?:\.\d{2})?)/);
                        if (priceMatch) {
                            providerData.price = parseFloat(priceMatch[1].replace(',', ''));
                        }
                    }
                    
                    // Add type-specific data
                    if (key === 'hotels') {
                        providerData.location = 'TBD';
                        providerData.nights = 1;
                        providerData.room_type = 'standard';
                    } else if (key === 'flights') {
                        providerData.airline = providerData.name;
                        providerData.flight_type = 'departure';
                    } else if (key === 'transport') {
                        providerData.company = providerData.name;
                        providerData.vehicle_type = 'bus';
                    }
                    
                    window.selectedProviders[key].push(providerData);

                    
                } catch (error) {
                    console.error(`❌ Error processing ${key} provider:`, error, card);
                }
            });
        }
    });
    

    
    // Update hidden form fields
    if (typeof updateHiddenFormFields === 'function') {
        updateHiddenFormFields();
    }
    
    // Update badges
    if (typeof updateProviderBadges === 'function') {
        updateProviderBadges();
    }
    
    return window.selectedProviders;
};

window.debugProviderModal = function() {

    
    const modal = document.getElementById('providerSelectionModal');
    if (modal) {

        
        const confirmBtn = document.getElementById('confirmSelectionBtn');

        
        const selectedInModal = modal.querySelectorAll('.selected, :checked, .active');

        
        const allCards = modal.querySelectorAll('.provider-card, .provider-search-card, .card');

        
    } else {

    }
    

};



document.addEventListener('DOMContentLoaded', function() {

    
    // Integrate with existing provider selection modal
    setupProviderModalIntegration();
    
    if (typeof window.MergedProviderSelector !== 'undefined') {  
        // Initialize the merged system
        if (typeof window.MergedProviderSelector.init === 'function') {

            window.MergedProviderSelector.init();

        } else {
            console.error('❌ Init function not found!');
        }
        

        
    } else {
        console.error('❌ Merged Provider Selector not loaded! Available providers:');
        console.error('window.MergedProviderSelector:', typeof window.MergedProviderSelector);
        console.error('window.SimpleProviderSelector:', typeof window.SimpleProviderSelector);
        console.error('window.EnhancedProviderSelector:', typeof window.EnhancedProviderSelector);
        console.error('All window Provider objects:', Object.keys(window).filter(k => k.includes('Provider')));
    }
});

// Also try immediate initialization if DOM is already ready
if (document.readyState === 'complete' || document.readyState === 'interactive') {

    setTimeout(() => {
        if (typeof window.MergedProviderSelector !== 'undefined' && window.MergedProviderSelector.init) {
            window.MergedProviderSelector.init();
        }
    }, 100);
}

/**
 * Simple service request status refresh (lightweight version)
 */
function refreshServiceRequestStatus() {

    // This is a lightweight refresh that doesn't do complex progress tracking
    // Just logs that a refresh was requested - the card updates are handled immediately after request
}

// Make function globally available for compatibility
window.refreshServiceRequestStatus = refreshServiceRequestStatus;
window.refreshApprovalStatus = refreshServiceRequestStatus; // Alias for compatibility

</script>
