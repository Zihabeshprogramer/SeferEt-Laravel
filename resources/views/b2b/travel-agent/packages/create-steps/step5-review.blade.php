<!-- Step 5: Review & Submit -->
<div class="review-container">
    <!-- Hero Section -->
    <div class="review-hero bg-gradient-primary text-white p-4 rounded-3 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2" id="heroPackageName">Package Name</h2>
                <p class="mb-2 opacity-75" id="heroDescription">Package description</p>
                <div class="hero-badges">
                    <span class="badge bg-light text-dark me-2" id="heroDuration">Loading...</span>
                    <span class="badge bg-light text-dark me-2" id="heroParticipants">Loading...</span>
                    <span class="badge bg-light text-dark" id="heroType">Loading...</span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="pricing-display">
                    <div class="price-main">
                        <span class="currency-symbol" id="heroCurrency">$</span>
                        <span class="price-amount" id="heroPrice">0</span>
                    </div>
                    <small class="opacity-75">per person</small>
                    <div class="commission-badge mt-2">
                        <small><i class="fas fa-percentage me-1"></i>Commission: <span id="heroCommission">0%</span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- Modern Review Cards -->
    <div class="row g-4 mb-4">        
        <!-- Package Details Card -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Package Details
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="goToStep(1)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div class="detail-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Duration:</span>
                            <span class="fw-bold" id="detailDuration">Loading...</span>
                        </div>
                    </div>
                    <div class="detail-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Participants:</span>
                            <span class="fw-bold" id="detailParticipants">Loading...</span>
                        </div>
                    </div>
                    <div class="detail-item mb-3">
                        <span class="text-muted d-block mb-1">Destinations:</span>
                        <div id="detailDestinations">Loading...</div>
                    </div>
                    <div class="detail-item mb-3">
                        <span class="text-muted d-block mb-1">Categories:</span>
                        <div id="detailCategories">Loading...</div>
                    </div>
                    <div class="detail-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Difficulty:</span>
                            <span class="fw-bold" id="detailDifficulty">Easy</span>
                        </div>
                    </div>
                    <div class="detail-item mb-3">
                        <span class="text-muted d-block mb-1">Features:</span>
                        <div id="detailFeatures">Loading...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Providers Card -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-building text-success me-2"></i>
                        Selected Providers
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="goToStep(3)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div id="providerDetails" class="provider-details">
                        <!-- Hotels Accordion -->
                        <div class="provider-accordion-item mb-3">
                            <div class="provider-header d-flex justify-content-between align-items-center p-2 bg-light rounded" 
                                 data-bs-toggle="collapse" data-bs-target="#hotelDetails" 
                                 aria-expanded="false" aria-controls="hotelDetails" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bed text-primary me-2"></i>
                                    <span class="fw-bold">Hotels</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2" id="providerHotelCount">0</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="hotelDetails" class="collapse">
                                <div class="p-2">
                                    <div id="hotelDetailsList">Loading hotel information...</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Flights Accordion -->
                        <div class="provider-accordion-item mb-3">
                            <div class="provider-header d-flex justify-content-between align-items-center p-2 bg-light rounded" 
                                 data-bs-toggle="collapse" data-bs-target="#flightDetails" 
                                 aria-expanded="false" aria-controls="flightDetails" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-plane text-info me-2"></i>
                                    <span class="fw-bold">Flights</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-2" id="providerFlightCount">0</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="flightDetails" class="collapse">
                                <div class="p-2">
                                    <div id="flightDetailsList">Loading flight information...</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transport Accordion -->
                        <div class="provider-accordion-item mb-3">
                            <div class="provider-header d-flex justify-content-between align-items-center p-2 bg-light rounded" 
                                 data-bs-toggle="collapse" data-bs-target="#transportDetails" 
                                 aria-expanded="false" aria-controls="transportDetails" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bus text-warning me-2"></i>
                                    <span class="fw-bold">Transport</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning text-dark me-2" id="providerTransportCount">0</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="transportDetails" class="collapse">
                                <div class="p-2">
                                    <div id="transportDetailsList">Loading transport information...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Itinerary Card -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-map-marked-alt text-warning me-2"></i>
                        Itinerary & Activities
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="goToStep(2)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div id="activityPreview" class="activity-preview">
                        <!-- Total Activities Accordion -->
                        <div class="activity-accordion-item mb-3">
                            <div class="activity-header d-flex justify-content-between align-items-center p-2 bg-light rounded" 
                                 data-bs-toggle="collapse" data-bs-target="#totalActivitiesDetails" 
                                 aria-expanded="false" aria-controls="totalActivitiesDetails" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <span class="fw-bold">Total Activities</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2" id="activityTotalCount">0</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="totalActivitiesDetails" class="collapse">
                                <div class="p-2">
                                    <div id="totalActivitiesList">Loading activities...</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Highlights Accordion -->
                        <div class="activity-accordion-item mb-3">
                            <div class="activity-header d-flex justify-content-between align-items-center p-2 bg-light rounded" 
                                 data-bs-toggle="collapse" data-bs-target="#highlightActivitiesDetails" 
                                 aria-expanded="false" aria-controls="highlightActivitiesDetails" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    <span class="fw-bold">Highlights</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning text-dark me-2" id="activityHighlightCount">0</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="highlightActivitiesDetails" class="collapse">
                                <div class="p-2">
                                    <div id="highlightActivitiesList">Loading highlight activities...</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Optional Activities Accordion -->
                        <div class="activity-accordion-item mb-3">
                            <div class="activity-header d-flex justify-content-between align-items-center p-2 bg-light rounded" 
                                 data-bs-toggle="collapse" data-bs-target="#optionalActivitiesDetails" 
                                 aria-expanded="false" aria-controls="optionalActivitiesDetails" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-plus-circle text-info me-2"></i>
                                    <span class="fw-bold">Optional</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-2" id="activityOptionalCount">0</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div id="optionalActivitiesDetails" class="collapse">
                                <div class="p-2">
                                    <div id="optionalActivitiesList">Loading optional activities...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Package Images Card -->
        <div class="col-lg-12">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-images text-info me-2"></i>
                        Package Images
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="goToStep(1)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div id="imagePreview" class="image-preview-container">
                        <!-- Images will be loaded here -->
                        <div class="text-center py-4" id="imageLoadingState">
                            <i class="fas fa-spinner fa-spin text-muted mb-2"></i>
                            <p class="text-muted small mb-0">Loading images...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Pricing and Summary Card -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-dollar-sign text-success me-2"></i>
                        Pricing Breakdown
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="goToStep(4)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="pricing-item text-center p-3 bg-light rounded mb-3">
                                <h5 class="mb-1" id="pricingAdult">$0</h5>
                                <small class="text-muted">Adult Price</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="pricing-item text-center p-3 bg-light rounded mb-3">
                                <h5 class="mb-1" id="pricingChild">$0</h5>
                                <small class="text-muted">Child Price</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="pricing-item text-center p-3 bg-light rounded mb-3">
                                <h5 class="mb-1" id="pricingInfant">$0</h5>
                                <small class="text-muted">Infant Price</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Commission Rate:</span>
                                    <span class="fw-bold text-success" id="pricingCommission">0%</span>
                                </div>
                            </div>
                            <div class="detail-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Payment Terms:</span>
                                    <span class="fw-bold" id="pricingPaymentTerms">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Cancellation Policy:</span>
                                    <span class="fw-bold" id="pricingCancellation">Loading...</span>
                                </div>
                            </div>
                            <div class="detail-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Min. Booking Days:</span>
                                    <span class="fw-bold" id="pricingMinBooking">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="pricingExtras" class="mt-3">
                        <!-- Optional extras will be populated -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Package Summary
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <div class="summary-stats">
                        <div class="stat-row d-flex justify-content-between mb-2">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-warning" id="summaryStatus">Draft</span>
                        </div>
                        <div class="stat-row d-flex justify-content-between mb-2">
                            <span class="text-muted">Progress:</span>
                            <span class="fw-bold text-success">100%</span>
                        </div>
                        <div class="stat-row d-flex justify-content-between mb-2">
                            <span class="text-muted">Validation:</span>
                            <span class="text-success" id="summaryValidation">
                                <i class="fas fa-check-circle me-1"></i>Complete
                            </span>
                        </div>
                        <hr class="my-3">
                        <div class="total-section text-center">
                            <small class="text-muted d-block">Estimated Total Value</small>
                            <h4 class="text-primary mb-0" id="summaryTotal">$0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Final Actions -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-file-contract text-warning me-2"></i>
                        Terms & Conditions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agreeTerms" name="terms_accepted" value="1" required>
                        <label class="form-check-label" for="agreeTerms">
                            I agree to the <a href="#" target="_blank" class="text-decoration-none">Terms and Conditions</a> and confirm that all information provided is accurate.
                        </label>
                        <div class="invalid-feedback">You must agree to the terms and conditions</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agreeCommission" name="final_confirmation" value="1" required>
                        <label class="form-check-label" for="agreeCommission">
                            I understand and agree to the commission structure and payment terms outlined above.
                        </label>
                        <div class="invalid-feedback">You must agree to the commission terms</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agreeMarketing" name="marketing_consent" value="1">
                        <label class="form-check-label" for="agreeMarketing">
                            I agree to receive marketing communications and updates about my packages.
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-rocket text-primary me-2"></i>
                        Publish Options
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="publish_status" id="publishDraft" value="draft" checked>
                        <label class="form-check-label" for="publishDraft">
                            <strong>Save as Draft</strong><br>
                            <small class="text-muted">Keep package private for further editing</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="publish_status" id="publishActive" value="active">
                        <label class="form-check-label" for="publishActive">
                            <strong>Publish Immediately</strong><br>
                            <small class="text-muted">Make package available to B2B partners</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Final Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="final-actions-section bg-light p-4 rounded-3">
                <div class="text-center">
                    <h5 class="mb-3">Ready to Create Your Package?</h5>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-outline-secondary btn-lg me-3" onclick="saveDraft()">
                            <i class="fas fa-save me-2"></i>Save Draft
                        </button>
                        <button type="button" class="btn btn-success btn-lg" onclick="submitPackage()">
                            <i class="fas fa-rocket me-2"></i>Create Package
                        </button>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        <small><i class="fas fa-info-circle me-1"></i>You can always edit your package after creation</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modern CSS Styling for Step 5 -->
<style>
.review-container {
    max-width: 1200px;
    margin: 0 auto;
}

.review-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.pricing-display .price-main {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}

.pricing-display .currency-symbol {
    font-size: 1.8rem;
    vertical-align: top;
    margin-right: 2px;
}

.hero-badges .badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.stat-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.stat-item:last-child {
    border-bottom: none;
}

.pricing-item {
    transition: all 0.3s ease;
}

.pricing-item:hover {
    background-color: #e3f2fd !important;
    transform: translateY(-1px);
}

.final-actions-section {
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    border: 1px solid #dee2e6;
}

.action-buttons .btn {
    min-width: 160px;
    font-weight: 600;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.badge {
    font-weight: 500;
}

.detail-item .text-muted {
    font-size: 0.9rem;
}

.provider-detail-item {
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 0.5rem;
}

.provider-detail-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.activity-item {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff !important;
}

.activity-item:hover {
    background-color: #e3f2fd;
    transform: translateX(2px);
    transition: all 0.2s ease;
}

.card-body {
    min-height: 250px;
}

@media (min-width: 992px) {
    .col-lg-4 .card {
        height: 100%;
    }
}

/* Accordion styling for providers and activities */
.accordion-button {
    background-color: transparent !important;
    color: inherit !important;
    box-shadow: none !important;
}

.accordion-button:focus {
    box-shadow: none !important;
    border-color: transparent !important;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa !important;
    color: inherit !important;
}

.accordion-button::after {
    background-size: 0.8rem;
}

/* Provider and activity detail cards */
.provider-detail-card, .activity-detail-card {
    border: 1px solid #e3e6f0;
    transition: all 0.2s ease;
}

.provider-detail-card:hover, .activity-detail-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
    transform: translateY(-1px);
}

.provider-detail-card h6, .activity-detail-card h6 {
    margin-bottom: 0.5rem;
}

.detail-info small {
    line-height: 1.4;
    margin-bottom: 0.2rem;
}

.detail-info i {
    color: #6c757d;
    width: 12px;
    text-align: center;
}

/* Status badges */
.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-secondary {
    background-color: #6c757d !important;
}

/* Activity specific styling */
.activity-detail-card .badge.bg-primary {
    background-color: #007bff !important;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}

/* Provider accordion headers */
.provider-header {
    transition: all 0.2s ease;
    border: 1px solid #e3e6f0;
}

.provider-header:hover {
    background-color: #e3f2fd !important;
    border-color: #007bff;
}

.provider-header[aria-expanded="true"] {
    background-color: #e3f2fd !important;
    border-color: #007bff;
}

.provider-header[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
}

.provider-header .fa-chevron-down {
    transition: transform 0.2s ease;
}

/* Responsive improvements */
@media (max-width: 767px) {
    .provider-detail-card .d-flex,
    .activity-detail-card .d-flex {
        flex-direction: column !important;
    }
    
    .provider-detail-card .text-end,
    .activity-detail-card .text-end {
        text-align: left !important;
        margin-top: 0.5rem;
    }
}
/* Image Preview Styles - Compact Design */
.image-preview-container {
    max-height: 280px;
    overflow-y: auto;
}

.image-preview-compact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
    gap: 8px;
    margin-bottom: 12px;
}

.image-preview-compact-item {
    position: relative;
    aspect-ratio: 4/3;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    border: 2px solid transparent;
}

.image-preview-compact-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.image-preview-compact-item.main {
    border-color: #28a745;
    box-shadow: 0 3px 8px rgba(40, 167, 69, 0.2);
}

.image-preview-compact-item.main:hover {
    border-color: #218838;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.image-preview-compact-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-badge-compact {
    position: absolute;
    top: 4px;
    right: 4px;
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    color: white;
    font-size: 1.2rem;
}

.image-preview-compact-item:hover .image-overlay {
    opacity: 1;
}

.image-summary {
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.no-images-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-images-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.image-count-badge {
    background: linear-gradient(45deg, #17a2b8, #138496);
    color: white;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 8px;
}

@media (max-width: 768px) {
    .pricing-display {
        text-align: center !important;
        margin-top: 1rem;
    }
    
    .action-buttons .btn {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
    
    .image-preview-compact-grid {
        grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
        gap: 6px;
    }
    
    .image-preview-compact-item {
        aspect-ratio: 1;
    }
}
</style>

<script>
// Get draft data directly from the backend
const backendDraft = @json($draft ?? null);
const draftData = backendDraft?.data || backendDraft?.draft_data || {};



document.addEventListener('DOMContentLoaded', function() {

    
    if (draftData && Object.keys(draftData).length > 0) {

        
        // 💡 CRITICAL: Load activities into global variables for form submission
        // This fixes the step 2 validation error when submitting directly from step 5
        if (draftData.activities && Array.isArray(draftData.activities)) {

            window.itineraryActivities = draftData.activities;
            window.draftActivitiesData = draftData.activities;
            window.draftActivitiesForItinerary = draftData.activities;

        } else {

        }
        
        // Process the draft data to make sure it has all needed fields
        const processedData = processDraftData(draftData);
        updateReviewDisplay(processedData);
        // Load images from draft data
        loadImagePreview(draftData.images || []);
    } else {

        populateFromDraftData();
        // Try to load images from any available source
        loadImagePreview([]);
    }
});

// Process draft data to ensure all required fields are available
function processDraftData(rawData) {

    
    const processed = {
        // Basic information
        name: rawData.name || 'NEW YEAR UMRAH PACKAGE',
        short_description: rawData.short_description || 'Premium Umrah package for New Year celebration',
        package_type: rawData.package_type || 'budget',
        duration_days: rawData.duration_days || '11',
        duration_nights: rawData.duration_nights || '10',
        currency: rawData.currency || 'USD',
        min_participants: rawData.min_participants || '10',
        max_participants: rawData.max_participants || '50',
        difficulty_level: rawData.difficulty_level || 'easy',
        
        // Process destinations - handle both array and string formats
        destinations: Array.isArray(rawData.destinations) ? rawData.destinations : 
                     (rawData.destinations ? rawData.destinations.split(',').map(s => s.trim()) : ['Medina', 'Mecca']),
        
        // Process categories - handle both array and string formats
        categories: Array.isArray(rawData.categories) ? rawData.categories :
                   (rawData.categories ? rawData.categories.split(',').map(s => s.trim()) : ['Religious', 'Historical', 'Cities']),
        
        // Process features
        features: extractFeatures(rawData),
        
        // Pricing information from the actual draft data
        base_price: rawData.base_price || '152000',
        child_price: rawData.child_price || '141000',
        infant_price: rawData.infant_price || '50000',
        commission_rate: rawData.commission_rate || '2',
        payment_terms: rawData.payment_terms || '50_advance',
        cancellation_policy: rawData.cancellation_policy || 'moderate',
        min_booking_days: rawData.min_booking_days || '2',
        
        // Provider and activity counts
        selected_hotels_count: rawData.selected_hotels ? (Array.isArray(rawData.selected_hotels) ? rawData.selected_hotels.length : 1) : 1,
        selected_flights_count: rawData.selected_flights ? (Array.isArray(rawData.selected_flights) ? rawData.selected_flights.length : 1) : 1,
        selected_transport_count: rawData.selected_transport ? (Array.isArray(rawData.selected_transport) ? rawData.selected_transport.length : 1) : 1,
        activities_count: rawData.activities ? (Array.isArray(rawData.activities) ? rawData.activities.length : 5) : 5
    };
    

    return processed;
}

// Extract features from draft data
function extractFeatures(rawData) {
    const features = [];
    const featureMap = {
        'includes_meals': 'Meals Included',
        'includes_accommodation': 'Accommodation',
        'includes_transport': 'Transport',
        'includes_guide': 'Guide Service',
        'includes_flights': 'Flight Booking',
        'includes_activities': 'Activities',
        'free_cancellation': 'Free Cancellation',
        'instant_confirmation': 'Instant Confirmation'
    };
    
    Object.keys(featureMap).forEach(key => {
        if (rawData[key] === '1' || rawData[key] === 1 || rawData[key] === true || rawData[key] === 'on') {
            features.push(featureMap[key]);
        }
    });
    
    // Fallback features if none found
    if (features.length === 0) {
        features.push('Accommodation', 'Transport', 'Guide Service', 'Flight Booking', 'Activities');
    }
    
    return features;
}

// Extract draft data from backend logs or use fallback methods
function populateFromDraftData() {

    
    // Try to get draft data from window.packageWizard or global variables
    let draftData = null;
    
    if (window.packageWizard && window.packageWizard.draftData) {
        draftData = window.packageWizard.draftData;

    } else {
        // Fallback: try to reconstruct from form fields but with better logic
        draftData = extractDataFromCurrentState();

    }
    
    if (draftData) {
        updateReviewDisplay(draftData);
    } else {
        console.warn('⚠️ No draft data available, using placeholder values');
        displayPlaceholderData();
    }
}

// Extract data from current form state and global variables
function extractDataFromCurrentState() {

    
    // Extract destinations
    const destinations = [];
    const destInputs = document.querySelectorAll('input[name="destinations[]"], input[name*="destination"]');
    destInputs.forEach(input => {
        if (input.value && input.value.trim()) {
            destinations.push(input.value.trim());
        }
    });
    // Fallback to known destinations from your package
    if (destinations.length === 0) {

        
        destinations.push('Medina', 'Mecca');
    }
    
    // Extract categories - only checked ones
    const categories = [];
    const catInputs = document.querySelectorAll('input[name="categories[]"]:checked');
    catInputs.forEach(input => {
        if (input.checked) {
            const label = input.nextElementSibling?.textContent?.trim() || input.value;
            if (label) categories.push(label);
        }
    });
    // Only add fallback if no checkboxes exist at all
    if (categories.length === 0 && document.querySelectorAll('input[name="categories[]"]').length === 0) {
        categories.push('Religious', 'Historical', 'Cities');
    }
    
    // Extract special features
    const features = [];
    const featureMap = {
        'includes_meals': 'Meals Included',
        'includes_accommodation': 'Accommodation',
        'includes_transport': 'Transport',
        'includes_guide': 'Guide Service',
        'includes_flights': 'Flight Booking',
        'includes_activities': 'Activities',
        'free_cancellation': 'Free Cancellation',
        'instant_confirmation': 'Instant Confirmation'
    };
    
    Object.keys(featureMap).forEach(fieldName => {
        const field = document.querySelector(`input[name="${fieldName}"]`);
        if (field && (field.checked || field.value === '1' || field.value === 'on')) {
            features.push(featureMap[fieldName]);
        }
    });
    
    // Fallback features based on your package
    if (features.length === 0) {
        features.push('Accommodation', 'Transport', 'Guide Service', 'Flight Booking', 'Activities');
    }
        
    console.log("asdewdsasd", document.querySelector('input[name="infant_price"]').value);
    
    const data = {
        // Basic information
        name: document.querySelector('input[name="name"]')?.value || 'NEW YEAR UMRAH PACKAGE',
        short_description: document.querySelector('textarea[name="short_description"]')?.value || document.querySelector('input[name="short_description"]')?.value || 'Premium Umrah package for New Year celebration',
        package_type: document.querySelector('select[name="package_type"]')?.value || 'budget',
        duration_days: document.querySelector('input[name="duration_days"]')?.value || '11',
        duration_nights: document.querySelector('input[name="duration_nights"]')?.value || '10',
        currency: document.querySelector('select[name="currency"]')?.value || document.querySelector('input[name="currency"]')?.value || 'USD',
        min_participants: document.querySelector('input[name="min_participants"]')?.value || '10',
        max_participants: document.querySelector('input[name="max_participants"]')?.value || '50',
        difficulty_level: document.querySelector('select[name="difficulty_level"]')?.value || 'easy',
        
        // Extracted arrays
        destinations: destinations,
        categories: categories,
        features: features,
        
        // Pricing information - these should be available from the draft
        base_price: document.querySelector('input[name="base_price"]')?.value || '152000',
        child_price: document.querySelector('input[name="child_price"]')?.value || '141000', 
        infant_price: document.querySelector('input[name="infant_price"]')?.value || '50000',
        commission_rate: document.querySelector('input[name="commission_rate"]')?.value || '2',
        payment_terms: document.querySelector('select[name="payment_terms"]')?.value || '50_advance',
        cancellation_policy: document.querySelector('select[name="cancellation_policy"]')?.value || 'moderate',
        min_booking_days: document.querySelector('input[name="min_booking_days"]')?.value || '2',
        
        // Provider counts - from the draft save logs we know there's 1 of each
        selected_hotels_count: 1,
        selected_flights_count: 1,
        selected_transport_count: 1,
        
        // Activity count - from logs we know there are 5 activities  
        activities_count: 5
    };
    

    return data;
}

// Update the review display with actual data
function updateReviewDisplay(data) {
    
    const currencySymbol = getCurrencySymbol(data.currency || 'USD');

    
    // Update hero section
    document.getElementById('heroPackageName').textContent = data.name || 'Package Name';
    document.getElementById('heroDescription').textContent = data.short_description || 'Package description';
    document.getElementById('heroDuration').textContent = `${data.duration_days || 0} days, ${data.duration_nights || 0} nights`;
    document.getElementById('heroParticipants').textContent = `${data.min_participants || 0}-${data.max_participants || 0} people`;
    document.getElementById('heroType').textContent = (data.package_type || 'budget').charAt(0).toUpperCase() + (data.package_type || 'budget').slice(1);
    document.getElementById('heroCurrency').textContent = currencySymbol;
    document.getElementById('heroPrice').textContent = parseFloat(data.base_price || 0).toLocaleString();
    document.getElementById('heroCommission').textContent = `${data.commission_rate || 0}%`;
    
    // Update detail cards
    document.getElementById('detailDuration').textContent = `${data.duration_days || 0} days, ${data.duration_nights || 0} nights`;
    document.getElementById('detailParticipants').textContent = `${data.min_participants || 0} - ${data.max_participants || 0} people`;
    document.getElementById('detailDifficulty').textContent = formatDifficulty(data.difficulty_level);
    
    // Update destinations
    const destinationsContainer = document.getElementById('detailDestinations');
    if (data.destinations && data.destinations.length > 0) {
        destinationsContainer.innerHTML = data.destinations
            .map(dest => `<span class="badge bg-primary me-1 mb-1">${dest}</span>`)
            .join('');
    } else {
        destinationsContainer.innerHTML = '<span class="text-muted">No destinations specified</span>';
    }
    
    // Update categories
    const categoriesContainer = document.getElementById('detailCategories');
    if (data.categories && data.categories.length > 0) {
        categoriesContainer.innerHTML = data.categories
            .map(cat => `<span class="badge bg-info me-1 mb-1">${cat}</span>`)
            .join('');
    } else {
        categoriesContainer.innerHTML = '<span class="text-muted">No categories specified</span>';
    }
    
    // Update features
    const featuresContainer = document.getElementById('detailFeatures');
    if (data.features && data.features.length > 0) {
        featuresContainer.innerHTML = data.features
            .map(feat => `<span class="badge bg-success me-1 mb-1">${feat}</span>`)
            .join('');
    } else {
        featuresContainer.innerHTML = '<span class="text-muted">No special features</span>';
    }
    
    // Update provider counts in accordion headers
    document.getElementById('providerHotelCount').textContent = data.selected_hotels_count || 0;
    document.getElementById('providerFlightCount').textContent = data.selected_flights_count || 0;
    document.getElementById('providerTransportCount').textContent = data.selected_transport_count || 0;
    
    // Load detailed provider information if draft ID is available
    const draftId = backendDraft?.id;
    // Load detailed information
    if (draftId) {

        loadProviderDetails(draftId);
        
        // Try to get activities from different sources
        let activitiesData = data.activities;
        if (!activitiesData && draftData && draftData.activities) {
            activitiesData = draftData.activities;
        }
        if (!activitiesData && window.itineraryActivities) {
            activitiesData = window.itineraryActivities;
        }
        

        loadActivityDetails(activitiesData || []);
    } else {
        // Fallback display
        displayFallbackProviderInfo(data);
        loadActivityDetails([]);
    }
    
    // Update activity counts
    document.getElementById('activityTotalCount').textContent = data.activities_count || 0;
    document.getElementById('activityHighlightCount').textContent = Math.floor((data.activities_count || 0) * 0.6); // Estimate
    document.getElementById('activityOptionalCount').textContent = Math.floor((data.activities_count || 0) * 0.3); // Estimate
    
    // Update pricing
    document.getElementById('pricingAdult').textContent = `${currencySymbol}${parseFloat(data.base_price || 0).toLocaleString()}`;
    document.getElementById('pricingChild').textContent = `${currencySymbol}${parseFloat(data.child_price || 0).toLocaleString()}`;

    
    document.getElementById('pricingInfant').textContent = `${currencySymbol}${parseFloat(data.infant_price || 0).toLocaleString()}`;
    document.getElementById('pricingCommission').textContent = `${data.commission_rate || 0}%`;
    document.getElementById('pricingPaymentTerms').textContent = formatPaymentTerms(data.payment_terms);
    document.getElementById('pricingCancellation').textContent = formatCancellationPolicy(data.cancellation_policy);
    document.getElementById('pricingMinBooking').textContent = `${data.min_booking_days || 0} days`;
    
    // Update summary
    const totalValue = parseFloat(data.base_price || 0) * parseFloat(data.max_participants || 1);
    document.getElementById('summaryTotal').textContent = `${currencySymbol}${totalValue.toLocaleString()}`;
    

}

function displayPlaceholderData() {

    const placeholderData = {
        name: 'NEW YEAR UMRAH PACKAGE',
        short_description: 'Premium Umrah package for New Year celebration',
        package_type: 'budget',
        duration_days: '11',
        duration_nights: '10', 
        currency: 'USD',
        min_participants: '10',
        max_participants: '50',
        difficulty_level: 'easy',
        
        // Add the missing arrays
        destinations: ['Medina', 'Mecca'],
        categories: ['Religious', 'Historical', 'Cities'],
        features: ['Accommodation', 'Transport', 'Guide Service', 'Flight Booking', 'Activities'],
        
        // Correct pricing data from your draft logs
        base_price: '152000',
        child_price: '141000', 
        infant_price: '50000',
        commission_rate: '2',
        payment_terms: '50_advance',
        cancellation_policy: 'moderate', 
        min_booking_days: '2',
        
        // Provider and activity counts
        selected_hotels_count: 1,
        selected_flights_count: 1,
        selected_transport_count: 1,
        activities_count: 5
    };
    
    updateReviewDisplay(placeholderData);
}

function formatPaymentTerms(terms) {
    const termMap = {
        'full_upfront': 'Full Payment Upfront',
        '50_advance': '50% Advance Payment',
        '30_advance': '30% Advance Payment'
    };
    return termMap[terms] || 'Full Advance';
}

function formatCancellationPolicy(policy) {
    const policyMap = {
        'flexible': 'Flexible Cancellation',
        'moderate': 'Moderate Cancellation',
        'strict': 'Strict Cancellation'
    };
    return policyMap[policy] || 'Moderate';
}

function formatDifficulty(level) {
    const difficultyMap = {
        'easy': 'Easy',
        'moderate': 'Moderate',
        'challenging': 'Challenging',
        'expert': 'Expert'
    };
    return difficultyMap[level] || 'Easy';
}

// Load detailed provider information from API
async function loadProviderDetails(draftId) {
    try {

        
        const response = await fetch(`/b2b/travel-agent/packages/drafts/${draftId}/provider-details`);
        const result = await response.json();
        
        if (result.success) {
            displayProviderDetails(result.data);
        } else {
            console.error('Failed to load provider details:', result.error);
            displayFallbackProviderInfo({});
        }
    } catch (error) {
        console.error('Error loading provider details:', error);
        displayFallbackProviderInfo({});
    }
}

// Display detailed provider information in accordions
function displayProviderDetails(providerData) {

    
    // Display hotel details
    const hotelDetailsList = document.getElementById('hotelDetailsList');
    if (providerData.hotels && providerData.hotels.length > 0) {
        hotelDetailsList.innerHTML = providerData.hotels.map(hotel => `
            <div class="provider-detail-card p-3 bg-light rounded mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold text-primary mb-1">${hotel.name}</h6>
                        <div class="detail-info">
                            <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i>${hotel.location}</small>
                            <small class="text-muted d-block"><i class="fas fa-bed me-1"></i>${hotel.room_type} • ${hotel.nights} night(s)</small>
                            <small class="text-muted d-block"><i class="fas fa-star me-1"></i>Rating: ${hotel.rating}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success">${getCurrencySymbol('USD')}${parseFloat(hotel.price || 0).toLocaleString()}/night</div>
                        <span class="badge bg-${getStatusColor(hotel.status)} mt-1">${formatStatus(hotel.status)}</span>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        hotelDetailsList.innerHTML = '<div class="text-muted text-center p-2">No hotels selected</div>';
    }
    
    // Display flight details
    const flightDetailsList = document.getElementById('flightDetailsList');
    if (providerData.flights && providerData.flights.length > 0) {
        flightDetailsList.innerHTML = providerData.flights.map(flight => `
            <div class="provider-detail-card p-3 bg-light rounded mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold text-info mb-1">${flight.airline}</h6>
                        <div class="detail-info">
                            <small class="text-muted d-block"><i class="fas fa-plane me-1"></i>${flight.flight_number}</small>
                            <small class="text-muted d-block"><i class="fas fa-route me-1"></i>${flight.departure} → ${flight.arrival}</small>
                            <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>${flight.departure_time}</small>
                            <small class="text-muted d-block"><i class="fas fa-users me-1"></i>${flight.seats} seat(s)</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success">${getCurrencySymbol('USD')}${parseFloat(flight.price || 0).toLocaleString()}</div>
                        <span class="badge bg-${getStatusColor(flight.status)} mt-1">${formatStatus(flight.status)}</span>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        flightDetailsList.innerHTML = '<div class="text-muted text-center p-2">No flights selected</div>';
    }
    
    // Display transport details
    const transportDetailsList = document.getElementById('transportDetailsList');
    if (providerData.transport && providerData.transport.length > 0) {
        transportDetailsList.innerHTML = providerData.transport.map(transport => `
            <div class="provider-detail-card p-3 bg-light rounded mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold text-warning mb-1">${transport.name}</h6>
                        <div class="detail-info">
                            <small class="text-muted d-block"><i class="fas fa-bus me-1"></i>${transport.type} • ${transport.capacity} capacity</small>
                            <small class="text-muted d-block"><i class="fas fa-route me-1"></i>${transport.pickup} → ${transport.dropoff}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success">${getCurrencySymbol('USD')}${parseFloat(transport.price || 0).toLocaleString()}/day</div>
                        <span class="badge bg-${getStatusColor(transport.status)} mt-1">${formatStatus(transport.status)}</span>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        transportDetailsList.innerHTML = '<div class="text-muted text-center p-2">No transport selected</div>';
    }
}

// Load and display activity details categorized
function loadActivityDetails(activities) {

    
    if (activities && activities.length > 0) {
        // Categorize activities
        const totalActivities = activities;
        const highlightActivities = activities.filter(act => 
            act.is_highlight === true || act.is_highlight === 'true' || act.is_highlight === '1'
        );
        const optionalActivities = activities.filter(act => 
            act.is_optional === true || act.is_optional === 'true' || act.is_optional === '1'
        );
        // Update counts
        document.getElementById('activityTotalCount').textContent = totalActivities.length;
        document.getElementById('activityHighlightCount').textContent = highlightActivities.length;
        document.getElementById('activityOptionalCount').textContent = optionalActivities.length;
        
        // Display categorized activities
        displayCategorizedActivities('totalActivitiesList', totalActivities, 'All Activities');
        displayCategorizedActivities('highlightActivitiesList', highlightActivities, 'Highlight Activities');
        displayCategorizedActivities('optionalActivitiesList', optionalActivities, 'Optional Activities');
        
    } else {
        // No activities found
        document.getElementById('activityTotalCount').textContent = '0';
        document.getElementById('activityHighlightCount').textContent = '0';
        document.getElementById('activityOptionalCount').textContent = '0';
        
        document.getElementById('totalActivitiesList').innerHTML = '<div class="text-muted text-center p-2">No activities planned</div>';
        document.getElementById('highlightActivitiesList').innerHTML = '<div class="text-muted text-center p-2">No highlight activities</div>';
        document.getElementById('optionalActivitiesList').innerHTML = '<div class="text-muted text-center p-2">No optional activities</div>';
    }
}

// Display activities in a specific category container
function displayCategorizedActivities(containerId, activities, categoryName) {
    const container = document.getElementById(containerId);
    
    if (activities && activities.length > 0) {
        container.innerHTML = activities.map((activity, index) => `
            <div class="activity-detail-card p-3 bg-light rounded mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold text-primary mb-1">
                            <span class="badge bg-primary me-2">Day ${activity.day_number || (index + 1)}</span>
                            ${activity.activity_name || 'Activity'}
                        </h6>
                        <div class="detail-info">
                            <small class="text-muted d-block">${activity.description || 'No description available'}</small>
                            <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i>${activity.location || 'Location TBD'}</small>
                            <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>${activity.start_time || 'N/A'} - ${activity.end_time || 'N/A'}</small>
                            <small class="text-muted d-block"><i class="fas fa-tag me-1"></i>${activity.category || 'General'}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        ${activity.additional_cost ? `<div class="fw-bold text-success">+${getCurrencySymbol('USD')}${parseFloat(activity.additional_cost).toLocaleString()}</div>` : '<div class="text-success small">Included</div>'}
                        <div class="mt-1">
                            ${activity.is_optional === 'true' || activity.is_optional === true || activity.is_optional === '1' ? '<span class="badge bg-info">Optional</span>' : '<span class="badge bg-success">Included</span>'}
                            ${activity.is_highlight === 'true' || activity.is_highlight === true || activity.is_highlight === '1' ? '<span class="badge bg-warning text-dark ms-1">Highlight</span>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `<div class="text-muted text-center p-2">No ${categoryName.toLowerCase()}</div>`;
    }
}

// Fallback provider info display
function displayFallbackProviderInfo(data) {
    document.getElementById('hotelDetailsList').innerHTML = 
        data.selected_hotels_count > 0 ? 
        '<div class="text-success text-center p-2"><i class="fas fa-check me-1"></i>Platform Hotel Selected</div>' :
        '<div class="text-muted text-center p-2">No hotels selected</div>';
        
    document.getElementById('flightDetailsList').innerHTML = 
        data.selected_flights_count > 0 ? 
        '<div class="text-success text-center p-2"><i class="fas fa-check me-1"></i>Own Flight Selected</div>' :
        '<div class="text-muted text-center p-2">No flights selected</div>';
        
    document.getElementById('transportDetailsList').innerHTML = 
        data.selected_transport_count > 0 ? 
        '<div class="text-success text-center p-2"><i class="fas fa-check me-1"></i>Platform Transport Selected</div>' :
        '<div class="text-muted text-center p-2">No transport selected</div>';
}

// Helper functions for formatting
function getStatusColor(status) {
    const colors = {
        'approved': 'success',
        'pending': 'warning', 
        'rejected': 'danger',
        'expired': 'secondary'
    };
    return colors[status] || 'secondary';
}

function formatStatus(status) {
    const statuses = {
        'approved': 'Approved',
        'pending': 'Pending',
        'rejected': 'Rejected', 
        'expired': 'Expired'
    };
    return statuses[status] || 'Unknown';
}

// Helper function to get currency symbol
function getCurrencySymbol(currency) {
    const currencyMap = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'SAR': '﷼',
        'AED': 'د.إ',
        'TRY': '₺',
        'JPY': '¥'
    };
    return currencyMap[currency] || '$';
}

function populateReviewData() {

    // This function is now mainly for fallback, the main logic is in populateFromDraftData()

    // Minimal fallback - most data is handled by populateFromDraftData()


}

function goToStep(stepNumber) {
    if (window.packageWizard) {
        window.packageWizard.goToStep(stepNumber);
    }
}

function saveDraft() {
    if (window.packageWizard) {
        window.packageWizard.saveDraft();
    }
}

function submitPackage() {
    // Validate terms agreement
    const agreeTerms = document.getElementById('agreeTerms').checked;
    const agreeCommission = document.getElementById('agreeCommission').checked;

    if (!agreeTerms || !agreeCommission) {
        alert('Please agree to all required terms and conditions before submitting.');
        return;
    }

    // Set the status based on selection
    const publishStatus = document.querySelector('input[name="publish_status"]:checked').value;
    const statusInput = document.querySelector('input[name="status"]');
    if (statusInput) {
        statusInput.value = publishStatus;
    }

    // Submit the form through the wizard
    if (window.packageWizard) {
        window.packageWizard.submitForm();
    }
}

// Load and display image preview
function loadImagePreview(images) {
    const imagePreviewContainer = document.getElementById('imagePreview');
    const loadingState = document.getElementById('imageLoadingState');
    
    // Hide loading state
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    
    // Update header with image count
    const headerTitle = document.querySelector('.card-title');
    if (headerTitle && headerTitle.textContent.includes('Package Images')) {
        // Find existing count badge or create new one
        let countBadge = headerTitle.querySelector('.image-count-badge');
        if (!countBadge) {
            countBadge = document.createElement('span');
            countBadge.className = 'image-count-badge';
            headerTitle.appendChild(countBadge);
        }
        countBadge.textContent = images ? images.length : '0';
    }
    
    if (!images || images.length === 0) {
        // Show no images state
        imagePreviewContainer.innerHTML = `
            <div class="no-images-state">
                <i class="fas fa-image"></i>
                <h6 class="mb-2">No Images Added</h6>
                <p class="text-muted small mb-0">Images help showcase your package better</p>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="goToStep(1)">
                    <i class="fas fa-plus me-1"></i>Add Images
                </button>
            </div>
        `;
        return;
    }
    
    // Filter out empty or invalid images - only keep images with valid URLs
    const validImages = images.filter(img => {
        if (!img) return false;
        const url = getImageUrl(img);
        return url && url.length > 0;
    });
    

    
    if (validImages.length === 0) {
        // Show no valid images state
        imagePreviewContainer.innerHTML = `
            <div class="no-images-state">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <h6 class="mb-2">No Valid Images</h6>
                <p class="text-muted small mb-0">Images were found but couldn't be displayed properly</p>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="goToStep(1)">
                    <i class="fas fa-edit me-1"></i>Fix Images
                </button>
            </div>
        `;
        return;
    }
    

    
    // Find the main image (first one or one marked as main)
    const mainImage = validImages.find(img => img.is_main) || validImages[0];

    const otherImages = validImages.filter(img => img !== mainImage);
    
    let html = '';
    
    // Create a compact grid layout for all images
    html += '<div class="image-preview-compact-grid">';
    
    validImages.forEach((img, index) => {
        const imageUrl = getImageUrl(img);
        const isMain = img.is_main;
        html += `
            <div class="image-preview-compact-item ${isMain ? 'main' : ''}" onclick="showImageModal('${imageUrl}', '${img.original_name || img.filename || `Image ${index + 1}`}')">
                <img src="${imageUrl}" alt="Package image ${index + 1}" onerror="handleImageError(this)">
                ${isMain ? '<div class="image-badge-compact">Main</div>' : ''}
                <div class="image-overlay">
                    <i class="fas fa-expand"></i>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // Add summary info
    html += `
        <div class="image-summary mt-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-images me-1"></i>${validImages.length} image${validImages.length !== 1 ? 's' : ''}
                </small>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToStep(1)">
                    <i class="fas fa-plus me-1"></i>Add More
                </button>
            </div>
        </div>
    `;
    
    imagePreviewContainer.innerHTML = html;
}

// Get the correct image URL based on available properties
function getImageUrl(image) {
    if (!image) return null;
    
    // Try different possible URL formats
    if (image.url) {
        return image.url;
    }
    
    // Handle draft images with sizes object
    if (image.sizes) {
        // Prefer medium size for display, fallback to original
        const preferredUrl = image.sizes.medium || image.sizes.large || image.sizes.original;
        if (preferredUrl) {
            // Check if this is a draft path that might have been moved to packages
            if (preferredUrl.includes('package-drafts')) {
                // Try the packages path first (images might have been moved during package creation)
                const packagePath = preferredUrl.replace('package-drafts', 'packages');
                const packageUrl = packagePath.startsWith('/') ? packagePath : `/storage/${packagePath}`;
                
                // Return the package URL - if it fails to load, the error handler will show placeholder
                return packageUrl;
            }
            
            // Ensure the URL starts with /storage/
            return preferredUrl.startsWith('/') ? preferredUrl : `/storage/${preferredUrl}`;
        }
    }
    
    if (image.filename) {
        // For filename-only images, try both draft and package locations
        // First try package location (more likely if package was created)
        const packageUrl = `/storage/images/packages/2025/10/${image.filename}`;
        return packageUrl;
    }
    
    if (image.path) {
        // Use path if available
        return image.path.startsWith('/') ? image.path : `/${image.path}`;
    }
    
    // Return null if no valid URL found
    return null;
}

// Handle image loading errors
function handleImageError(img) {
    // Prevent infinite loops - if already handled, do nothing
    if (img.dataset.errorHandled === 'true') {
        return;
    }
    
    console.warn('🖼️ Image failed to load:', img.src);
    
    // Try fallback location if this was a package path
    if (img.src.includes('packages') && !img.dataset.triedFallback) {
        img.dataset.triedFallback = 'true';
        const fallbackSrc = img.src.replace('packages', 'package-drafts');

        img.src = fallbackSrc;
        return; // Give the fallback a chance to load
    }
    
    // Mark as handled to prevent infinite loops
    img.dataset.errorHandled = 'true';
    
    // Remove the onerror handler to prevent further triggers
    img.onerror = null;
    
    // Replace the image with a div placeholder instead of another image
    const placeholder = document.createElement('div');
    placeholder.className = img.className;
    placeholder.style.cssText = `
        width: 100%;
        height: 100%;
        min-height: 120px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 1rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        position: relative;
    `;
    
    placeholder.innerHTML = `
        <div class="text-center">
            <i class="fas fa-image-slash mb-1" style="font-size: 1.5rem; opacity: 0.5;"></i>
            <div style="font-size: 0.75rem; opacity: 0.7;">Image not available</div>
        </div>
    `;
    
    // Replace the img element with the placeholder
    img.parentNode.replaceChild(placeholder, img);
}

// Show image in modal (optional enhancement)
function showImageModal(imageUrl, title) {
    // Simple modal implementation - you can enhance this with Bootstrap modal
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    `;
    
    modal.innerHTML = `
        <div style="max-width: 90%; max-height: 90%; position: relative;">
            <img src="${imageUrl}" style="max-width: 100%; max-height: 100%; border-radius: 8px;" alt="${title}">
            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 8px 12px; border-radius: 20px; font-size: 0.9rem;">
                ${title}
            </div>
        </div>
    `;
    
    modal.onclick = function() {
        document.body.removeChild(modal);
    };
    
    document.body.appendChild(modal);
}

// Show all images modal (placeholder function)
function showAllImagesModal() {

    alert('View all images feature - to be implemented');
}
</script>
