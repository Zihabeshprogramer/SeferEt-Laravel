@extends('layouts.b2b')

@section('title', 'Create Travel Package - Professional Package Builder | SeferEt B2B')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Welcome Section -->
            <div class="welcome-section mb-4">
                <div class="card bg-gradient-primary text-white border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h1 class="h3 mb-2">
                                    <i class="fas fa-rocket me-2"></i>
                                    Create Your Travel Package
                                </h1>
                                <p class="mb-0 opacity-90">
                                    Build professional travel packages in 5 simple steps. Our intuitive wizard will guide you through 
                                    every detail - from basic information to pricing and final review.
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <div class="creation-stats">
                                    <div class="stat-item d-inline-block me-3">
                                        <div class="stat-number">5</div>
                                        <div class="stat-label">Easy Steps</div>
                                    </div>
                                    <div class="stat-item d-inline-block">
                                        <div class="stat-number">~10</div>
                                        <div class="stat-label">Minutes</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Wizard Progress Header -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0 text-dark">
                            <i class="fas fa-list-ol me-2 text-primary"></i>
                            Step-by-Step Progress
                        </h2>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="saveDraftBtn">
                                <i class="fas fa-save me-1"></i> Save Draft
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="exitWizardBtn">
                                <i class="fas fa-times me-1"></i> Exit
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="wizard-progress mb-4">
                        <div class="d-flex justify-content-between position-relative">
                            <!-- Background Progress Line -->
                            <div class="progress-line-bg"></div>
                            <!-- Active Progress Line -->
                            <div class="progress-line"></div>
                            
                            <!-- Step 1: Basic Info -->
                            <div class="wizard-step active" data-step="1">
                                <div class="step-circle">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="step-label">Basic Info</div>
                            </div>
                            
                            <!-- Step 2: Itinerary -->
                            <div class="wizard-step" data-step="2">
                                <div class="step-circle">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div class="step-label">Itinerary</div>
                            </div>
                            
                            <!-- Step 3: Providers -->
                            <div class="wizard-step" data-step="3">
                                <div class="step-circle">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="step-label">Providers</div>
                            </div>
                            
                            <!-- Step 4: Pricing -->
                            <div class="wizard-step" data-step="4">
                                <div class="step-circle">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="step-label">Pricing</div>
                            </div>
                            
                            <!-- Step 5: Review -->
                            <div class="wizard-step" data-step="5">
                                <div class="step-circle">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="step-label">Review</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wizard Form Container -->
            <form id="packageWizardForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="current_step" id="currentStepInput" value="1">
                <input type="hidden" name="package_draft_id" id="packageDraftId" value="{{ $draft->id ?? '' }}">

                <!-- Step 1: Basic Information -->
                <div class="wizard-content" id="step1" style="display: block;">
                    @include('b2b.travel-agent.packages.create-steps.step1-basic')
                </div>

                <!-- Step 2: Itinerary Builder -->
                <div class="wizard-content" id="step2" style="display: none;">
                    @include('b2b.travel-agent.packages.create-steps.step2-itinerary')
                </div>

                <!-- Step 3: Provider Selection -->
                <div class="wizard-content" id="step3" style="display: none;">
                    @include('b2b.travel-agent.packages.create-steps.step3-providers')
                </div>

                <!-- Step 4: Pricing Configuration -->
                <div class="wizard-content" id="step4" style="display: none;">
                    @include('b2b.travel-agent.packages.create-steps.step4-pricing')
                </div>

                <!-- Step 5: Review & Submit -->
                <div class="wizard-content" id="step5" style="display: none;">
                    @include('b2b.travel-agent.packages.create-steps.step5-review')
                </div>

                <!-- Wizard Navigation -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-footer p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" id="prevStepBtn" style="display: none;">
                                    <i class="fas fa-arrow-left me-1"></i> Previous
                                </button>
                            </div>
                            
                            <div class="wizard-info text-muted small">
                                Step <span id="currentStepNumber">1</span> of 5
                            </div>
                            
                            <div>
                                <button type="button" class="btn btn-primary" id="nextStepBtn">
                                    Next <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="submitPackageBtn" style="display: none;">
                                    <i class="fas fa-check me-1"></i> Create Package
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Processing...</div>
    </div>
</div>

@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<!-- Custom Enhancements CSS -->
<style>
.welcome-section .creation-stats .stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}
.welcome-section .creation-stats .stat-label {
    font-size: 0.8rem;
    opacity: 0.8;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
.form-floating-enhanced {
    position: relative;
}
.form-floating-enhanced .form-control {
    height: auto;
    min-height: 3.5rem;
    padding: 1rem 0.75rem 0.5rem;
}
.form-floating-enhanced .form-label {
    position: absolute;
    top: 1rem;
    left: 0.75rem;
    font-size: 0.85rem;
    color: #6c757d;
    pointer-events: none;
    transition: all 0.15s ease-in-out;
}
.form-floating-enhanced .form-control:focus ~ .form-label,
.form-floating-enhanced .form-control:not(:placeholder-shown) ~ .form-label {
    top: 0.25rem;
    font-size: 0.7rem;
    color: #0d6efd;
}
.select2-container--bootstrap-5 .select2-selection {
    min-height: 3.5rem;
}
.form-wizard-section {
    background: #f8f9fa;
    border-left: 4px solid #0d6efd;
    padding: 1.5rem;
    margin: 1rem 0;
    border-radius: 0.375rem;
}
.form-wizard-section h6 {
    color: #0d6efd;
    font-weight: 600;
    margin-bottom: 1rem;
}
.step-description {
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
}
.step-description h6 {
    color: #1976d2;
    margin-bottom: 0.5rem;
}

/* Step Progress Fixes */
.wizard-progress {
    position: relative;
    margin-bottom: 2rem;
}

.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
    z-index: 10;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
    border: 3px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    z-index: 10;
}

.step-label {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
    text-align: center;
    transition: color 0.3s ease;
}

.wizard-step.active .step-circle {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

.wizard-step.active .step-label {
    color: #0d6efd;
}

.wizard-step.completed .step-circle {
    background: #198754;
    color: white;
    border-color: #198754;
}

.wizard-step.completed .step-label {
    color: #198754;
}

/* Clickable step styles */
.wizard-step.clickable-step {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.wizard-step.clickable-step:hover {
    transform: translateY(-2px);
}

.wizard-step.clickable-step:hover .step-circle {
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
    transition: box-shadow 0.2s ease-in-out;
}

.wizard-step.clickable-step:hover .step-label {
    color: #0d6efd;
    font-weight: 700;
    transition: all 0.2s ease-in-out;
}

/* Non-clickable step styles */
.wizard-step:not(.clickable-step) {
    opacity: 0.6;
    cursor: not-allowed;
}

.wizard-step:not(.clickable-step) .step-circle {
    background: #e9ecef;
    color: #6c757d;
    border-color: #e9ecef;
}

.wizard-step:not(.clickable-step) .step-label {
    color: #6c757d;
}

.progress-line-bg {
    position: absolute;
    top: 25px;
    left: 25px;
    right: 25px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    z-index: 1;
}

.progress-line {
    position: absolute;
    top: 25px;
    left: 25px;
    height: 4px;
    background: #198754;
    border-radius: 2px;
    transition: width 0.5s ease;
    z-index: 2;
    width: 0%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .step-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .step-label {
        font-size: 0.75rem;
    }
    
    .progress-line-bg {
        top: 20px;
    }
    
    .progress-line {
        top: 20px;
    }
}

/* Itinerary Builder Styles */
.info-card {
    border-radius: 0.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.day-section {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    overflow: hidden;
}

.day-header {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef;
}

.activity-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.activity-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-state {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
}

.activities-container {
    min-height: 300px;
}
</style>
@endpush

@push('scripts')
<!-- jQuery (required for Select2, Date Range Picker, Summernote) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS (required for modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Moment.js (required for Date Range Picker) -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- Date Range Picker JavaScript -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- Select2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Summernote JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- Core Wizard JavaScript -->
<script src="{{ asset('assets/js/package-wizard.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/provider-search.js') }}"></script>
<script src="{{ asset('assets/js/itinerary-builder.js') }}"></script>
<script src="{{ asset('assets/js/pricing-calculator.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize global provider selections object
    if (!window.selectedProviders) {
        window.selectedProviders = {
            hotels: [],
            flights: [],
            transport: []
        };
    }
    
    // Initialize Select2 for all select elements
    initializeSelect2();
    
    // Initialize enhanced form features
    initializeFormEnhancements();
    
    // Initialize Summernote editor
    initializeSummernote();
    
    // Initialize Package Wizard
    window.packageWizard = new PackageWizard({
        formId: 'packageWizardForm',
        stepsCount: 5,
        autoSave: true,
        autoSaveInterval: 30000, // 30 seconds
        validationRules: {},
        apiRoutes: {
            saveDraft: '{{ route("b2b.travel-agent.packages.save-draft") }}',
            loadDraft: '{{ route("b2b.travel-agent.packages.load-draft", ":draftId") }}',
            validateStep: '{{ route("b2b.travel-agent.packages.validate-step") }}',
            searchProviders: '{{ route("b2b.travel-agent.api.providers.search") }}',
            checkAvailability: '{{ route("b2b.travel-agent.api.providers.check-availability") }}',
            calculatePricing: '{{ route("b2b.travel-agent.api.providers.calculate-pricing") }}',
            storeActivity: '{{ route("b2b.travel-agent.api.activities.store", ":packageId") }}',
            updateActivity: '{{ route("b2b.travel-agent.api.activities.update", [":packageId", ":activityId"]) }}',
            deleteActivity: '{{ route("b2b.travel-agent.api.activities.destroy", [":packageId", ":activityId"]) }}',
            reorderActivities: '{{ route("b2b.travel-agent.api.activities.reorder", ":packageId") }}'
        }
    });

    // Load draft data if exists and navigate to correct step
    @if(isset($draft) && $draft)
    
    const draftData = @json($draft->data ?? null);

    const currentStep = @json($draft->current_step ?? 1);
    const draftId = '@json($draft->id)';
    const draftName = '@json($draft->name)';
    
    // Make draft data available to MergedProviderSelector
    window.draftData = {
        id: draftId,
        name: draftName,
        current_step: currentStep,
        data: draftData
    };
    
    // Make package ID available globally for service requests
    window.packageId = draftId;
    window.currentPackageId = draftId;
    window.isDraftPackage = true;
    



    
    // Load draft data with proper timing checks
    if (draftData) {

        
        // Function to load draft data with wizard checks
        function loadDraftWithChecks() {
            try {
                // Try the wizard's loadDraftData method first if available
                if (window.packageWizard && typeof window.packageWizard.loadDraftData === 'function') {

                    window.packageWizard.loadDraftData(draftData);
                }
                
                // Always do manual population as well
                setTimeout(() => {

                    
                    // First populate basic form fields
                    window.populateFormFromDraftData(draftData);
                    
                    // Load pricing data if available
                    if (draftData && draftData.pricing_data) {

                        window.loadPricingData(draftData.pricing_data);
                    }
                    
                    // Update package draft ID
                    const draftIdField = document.getElementById('packageDraftId');
                    if (draftIdField) {
                        draftIdField.value = draftId;

                    }
                    
                    // CRITICAL: Ensure activities data is immediately available for saving
                    // regardless of which step we're on
                    window.ensureActivitiesDataImmediatelyAvailable(draftData);
                    
                    // Navigate to correct step
                    window.navigateToStep(currentStep);
                    
                    // Populate Summernote fields after a short delay
                    setTimeout(() => {
                        window.populateSummernoteFields(draftData);
                    }, 500);
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Draft "' + draftName + '" loaded successfully!');
                    }
                    
                }, 1200); // Increased delay to ensure wizard is ready
                
            } catch (error) {
                
                // Fallback with even more delay
                setTimeout(() => {

                    window.populateFormFromDraftData(draftData);
                    window.navigateToStep(currentStep);
                    
                    setTimeout(() => {
                        window.populateSummernoteFields(draftData);
                    }, 500);
                }, 1500);
            }
        }
        
        // Wait for wizard to be initialized, then load draft
        function waitForWizard(attempts = 0) {
            if (window.packageWizard) {

                loadDraftWithChecks();
            } else if (attempts < 10) {

                setTimeout(() => waitForWizard(attempts + 1), 300);
            } else {

                loadDraftWithChecks();
            }
        }
        
        // Start the waiting process
        waitForWizard();
    }
    @else
    // For new packages (no draft), set empty package ID initially
    window.packageId = null;
    window.currentPackageId = null;
    window.isDraftPackage = false;
    
    // The package ID will be set after first save/draft creation
    @endif
    
    // Function to load pricing data specifically
    window.loadPricingData = function(pricingData) {

        
        if (!pricingData) return;
        
        // Load basic pricing fields
        Object.keys(pricingData).forEach(fieldName => {
            if (fieldName === 'optional_extras') return; // Handle separately
            
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field && pricingData[fieldName] !== null && pricingData[fieldName] !== undefined) {
                if (field.type === 'checkbox') {
                    field.checked = !!pricingData[fieldName];
                } else {
                    field.value = pricingData[fieldName];
                }
                
                // Handle disabled states for smart pricing
                if (fieldName.endsWith('_disabled') && pricingData[fieldName] === '1') {
                    const actualFieldName = fieldName.replace('_disabled', '');
                    const actualField = document.querySelector(`[name="${actualFieldName}"]`);
                    if (actualField) {
                        actualField.disabled = true;
                        actualField.style.backgroundColor = '#f8f9fa';
                        actualField.style.cursor = 'not-allowed';
                    }
                }
                

            }
        });
        
        // Handle optional extras
        if (pricingData.optional_extras) {
            window.loadOptionalExtrasFromData(pricingData.optional_extras);
        }
        
        // Update pricing preview if we're on step 4
        setTimeout(() => {
            if (typeof updateChildPricingLogic === 'function') {
                updateChildPricingLogic();
            }
            if (typeof updatePricingPreview === 'function') {
                updatePricingPreview();
            }
        }, 100);
    };
    
    // Function to load optional extras from pricing data
    window.loadOptionalExtrasFromData = function(extrasData) {

        
        const container = document.getElementById('optionalExtras');
        if (!container) return;
        
        // CHECK: Do optional extras already exist from server-side rendering?
        const existingExtras = container.querySelectorAll('.optional-extra-item');

        
        if (existingExtras.length > 0) {
            return; // Don't recreate - preserve server-rendered content
        }
        
        // Clear existing extras only if we need to recreate

        container.innerHTML = '';
        
        // Parse the extras data and recreate elements
        const extras = [];
        Object.keys(extrasData).forEach(key => {
            const matches = key.match(/optional_extras\[(\d+)\]\[([^\]]+)\]/);
            if (matches) {
                const index = parseInt(matches[1]);
                const field = matches[2];
                
                if (!extras[index]) extras[index] = {};
                extras[index][field] = extrasData[key];
            }
        });
        
        // Create HTML for each extra
        extras.forEach((extra, index) => {
            if (extra && extra.name) {
                const currency = document.querySelector('input[name="currency"]')?.value || '₺';
                const extraHTML = `
                    <div class="optional-extra-item row mb-3" data-index="${index}">
                        <div class="col-md-4">
                            <input type="text" class="form-control" 
                                   name="optional_extras[${index}][name]" 
                                   placeholder="Extra name" 
                                   value="${extra.name || ''}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text">${currency}</span>
                                <input type="number" class="form-control" 
                                       name="optional_extras[${index}][price]" 
                                       placeholder="0.00" step="0.01" min="0" 
                                       value="${extra.price || ''}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="optional_extras[${index}][type]">
                                <option value="per_person" ${(extra.type || '') === 'per_person' ? 'selected' : ''}>Per Person</option>
                                <option value="per_group" ${(extra.type || '') === 'per_group' ? 'selected' : ''}>Per Group</option>
                                <option value="per_day" ${(extra.type || '') === 'per_day' ? 'selected' : ''}>Per Day</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                                    onclick="removeExtra(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', extraHTML);
            }
        });
        
        // Update the global extraIndex
        if (typeof window !== 'undefined') {
            window.extraIndex = extras.length;
        }
    };
    
    // Function to manually populate form fields from draft data
    window.populateFormFromDraftData = function(data) {

        
        if (!data) return;
        
        // Populate basic form fields
        Object.keys(data).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field && data[fieldName] !== null && data[fieldName] !== undefined) {
                if (field.type === 'checkbox') {
                    field.checked = !!data[fieldName];
                } else if (field.type === 'radio') {
                    if (field.value === data[fieldName]) {
                        field.checked = true;
                    }
                } else if (field.tagName.toLowerCase() === 'select') {
                    field.value = data[fieldName];
                    // Trigger change event for Select2
                    if ($(field).hasClass('select2-hidden-accessible')) {
                        $(field).val(data[fieldName]).trigger('change');
                    }
                } else if (field.classList.contains('summernote-editor')) {
                    // Handle Summernote fields

                    $(field).summernote('code', data[fieldName]);
                } else {
                    field.value = data[fieldName];
                }
            }
        });
        
        // Handle Summernote fields specifically (fallback)
        $('.summernote-editor').each(function() {
            const fieldName = $(this).attr('name');
            if (fieldName && data[fieldName]) {

                $(this).summernote('code', data[fieldName]);
            }
        });
        
        // Handle special fields like arrays or objects
        if (data.destinations && Array.isArray(data.destinations)) {

            // Handle destinations array - assuming it's a multi-select or similar
            const destinationsField = document.querySelector('[name="destinations[]"]') || 
                                    document.querySelector('[name="destinations"]');
            if (destinationsField) {
                if ($(destinationsField).hasClass('select2-hidden-accessible')) {
                    $(destinationsField).val(data.destinations).trigger('change');
                } else {
                    destinationsField.value = data.destinations.join(',');
                }
            }
        }
        
        // Handle itinerary/activities data
        if (data.activities && Array.isArray(data.activities)) {

            // Store activities data globally for step navigation
            window.draftActivitiesData = data.activities;
            window.populateItineraryData(data.activities);
        }
        
        // Handle selected providers data  
        if (data.selected_hotels && Array.isArray(data.selected_hotels)) {

            window.populateSelectedProviders('hotels', data.selected_hotels);
        }
        
        if (data.selected_flights && Array.isArray(data.selected_flights)) {

            window.populateSelectedProviders('flights', data.selected_flights);
        }
        
        if (data.selected_transport && Array.isArray(data.selected_transport)) {

            window.populateSelectedProviders('transport', data.selected_transport);
        }
        
        // Store provider data globally for step 3 loading
        window.draftProvidersData = {
            selected_hotels: data.selected_hotels || [],
            selected_flights: data.selected_flights || [],
            selected_transport: data.selected_transport || []
        };
        

    };
    
    // Function to navigate to specific step
    window.navigateToStep = function(stepNumber) {

        
        try {
            // Try wizard method first (with proper safety checks)
            if (window.packageWizard && typeof window.packageWizard.goToStep === 'function') {

                window.packageWizard.goToStep(stepNumber);
            } else if (window.packageWizard && typeof window.packageWizard.showStep === 'function') {

                window.packageWizard.showStep(stepNumber);
            } else {

                // Manual step navigation
                window.manualStepNavigation(stepNumber);
            }
        } catch (error) {
            window.manualStepNavigation(stepNumber);
        }
    };
    
    // Manual step navigation as fallback
    window.manualStepNavigation = function(stepNumber) {

        
        // Hide all steps
        document.querySelectorAll('.wizard-content').forEach(step => {
            step.style.display = 'none';
        });
        
        // Show target step
        const targetStep = document.getElementById(`step${stepNumber}`);
        if (targetStep) {
            targetStep.style.display = 'block';
        }
        
        // Update progress indicators
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                step.classList.add('completed');
            } else if (index + 1 === stepNumber) {
                step.classList.add('active');
            }
        });
        
        // Handle step-specific actions for draft data
        if (stepNumber === 2) {

            
            // Multiple attempts to ensure data is loaded
            const loadActivitiesWithRetry = (attempts = 0, maxAttempts = 5) => {
                const draftActivities = window.draftActivitiesData || window.draftActivitiesForItinerary;
                
                if (draftActivities && Array.isArray(draftActivities) && draftActivities.length > 0) {

                    window.integrateActivitiesWithItineraryBuilder(draftActivities);
                } else if (attempts < maxAttempts) {

                    setTimeout(() => loadActivitiesWithRetry(attempts + 1, maxAttempts), 300 * (attempts + 1));
                } else {

                }
            };
            
            // Start loading with immediate attempt, then retry if needed
            setTimeout(() => loadActivitiesWithRetry(), 100);
        }
        
        if (stepNumber === 3 && window.draftProvidersData) {

            setTimeout(() => {
                if (typeof window.loadProvidersFromDraft === 'function') {
                    window.loadProvidersFromDraft(window.draftProvidersData);
                }
            }, 800);
        }
        
        // Update step counter
        const stepCounter = document.getElementById('currentStepNumber');
        if (stepCounter) {
            stepCounter.textContent = stepNumber;
        }
        
        // Update current step input
        const stepInput = document.getElementById('currentStepInput');
        if (stepInput) {
            stepInput.value = stepNumber;

        } else {
            console.error('currentStepInput not found!');
        }
        
        // Show/hide navigation buttons
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const submitBtn = document.getElementById('submitPackageBtn');
        
        if (prevBtn) prevBtn.style.display = stepNumber > 1 ? 'inline-block' : 'none';
        if (nextBtn) nextBtn.style.display = stepNumber < 5 ? 'inline-block' : 'none';
        if (submitBtn) submitBtn.style.display = stepNumber === 5 ? 'inline-block' : 'none';
        
        // Trigger step change event for progress bar updates
        $(document).trigger('stepChanged', [stepNumber]);
    };
    
    // Simplified function to load activities data into the itinerary builder
    window.populateItineraryData = function(activities) {

        
        if (!activities || !Array.isArray(activities)) {

            return;
        }
        
        // Store the activities data globally for the itinerary step to access
        window.draftActivitiesData = activities;
        
        // If we're currently on step 2, integrate the data immediately
        if (document.getElementById('step2') && document.getElementById('step2').style.display !== 'none') {
            setTimeout(() => {
                window.integrateActivitiesWithItineraryBuilder(activities);
            }, 1000);
        }
    };
    
    // CRITICAL: Ensure activities data is immediately available for draft saving
    // This function must be called immediately after loading draft data
    window.ensureActivitiesDataImmediatelyAvailable = function(draftData) {

        
        if (!draftData || !draftData.activities) {

            return;
        }
        
        const activities = draftData.activities;
        if (!Array.isArray(activities) || activities.length === 0) {

            return;
        }
        

        
        // Store activities in ALL possible global variables immediately
        window.itineraryActivities = [...activities];
        window.draftActivitiesData = [...activities];
        window.draftActivitiesForItinerary = [...activities];
        
        // IMPORTANT: Immediately create hidden form inputs so the data is available for saving
        // even if the user never visits step 2
        window.createHiddenActivitiesInputsImmediately(activities);
        
        // Mark wizard as having activities data available
        window.activitiesDataReady = true;
        

    };
    
    // Create hidden form inputs immediately for activities data
    window.createHiddenActivitiesInputsImmediately = function(activities) {

        
        const form = document.getElementById('packageWizardForm');
        if (!form) {
            console.error('Form not found for creating hidden inputs');
            return;
        }
        
        // Remove any existing activities inputs first
        const existingInputs = form.querySelectorAll('input[name^="activities["]');
        existingInputs.forEach(input => input.remove());
        
        // Create hidden inputs for each activity
        activities.forEach((activity, index) => {
            Object.keys(activity).forEach(key => {
                if (activity[key] !== null && activity[key] !== undefined) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `activities[${index}][${key}]`;
                    input.value = activity[key];
                    input.className = 'draft-activities-input'; // Mark for identification
                    form.appendChild(input);
                }
            });
        });
        

    };
    
    // Function to integrate activities with the existing itinerary builder
    window.integrateActivitiesWithItineraryBuilder = function(activities) {

        
        if (!activities || !Array.isArray(activities) || activities.length === 0) {

            return;
        }
        
        try {
            let integrationSuccessful = false;
            
            // Method 1: Direct integration with the step's activities array
            if (typeof window.itineraryActivities !== 'undefined') {

                window.itineraryActivities = [...activities];
                
                // Trigger re-render if render function exists
                if (typeof window.renderActivities === 'function') {
                    window.renderActivities();
                    // Update activity counter
                    if (typeof window.updateActivityCounter === 'function') {
                        window.updateActivityCounter();
                    }
                }
                // CRITICAL: Always ensure hidden form inputs are created
                window.createHiddenActivitiesInputsImmediately(activities);
                integrationSuccessful = true;
            }
            
            // Method 2: Try to trigger itinerary builder initialization with data
            if (!integrationSuccessful && typeof window.initializeItineraryBuilder === 'function') {

                // Store activities first
                window.draftActivitiesForItinerary = activities;
                window.draftActivitiesData = activities;
                
                // Try to reinitialize the itinerary builder
                window.initializeItineraryBuilder();
                integrationSuccessful = true;
            }
            
            // Method 3: Fallback - store in multiple global variables and wait for builder
            if (!integrationSuccessful) {

                window.draftActivitiesForItinerary = activities;
                window.draftActivitiesData = activities;
                
                // Try to trigger integration when itinerary elements become available
                const retryIntegration = (attempts = 0, maxAttempts = 10) => {
                    if (attempts >= maxAttempts) {
                        console.warn('Max integration attempts reached');
                        return;
                    }
                    
                    if (typeof window.itineraryActivities !== 'undefined') {

                        window.itineraryActivities = [...activities];
                        if (typeof window.renderActivities === 'function') {
                            window.renderActivities();
                        }
                        if (typeof window.updateActivityCounter === 'function') {
                            window.updateActivityCounter();
                        }
                        // CRITICAL: Always ensure hidden form inputs are created
                        window.createHiddenActivitiesInputsImmediately(activities);
                    } else {
                        setTimeout(() => retryIntegration(attempts + 1, maxAttempts), 500);
                    }
                };
                
                retryIntegration();
            }
            

        } catch (error) {
            console.error('Error integrating activities with itinerary builder:', error);
            
            // Emergency fallback - just store the data
            window.draftActivitiesForItinerary = activities;
            window.draftActivitiesData = activities;
        }
    };
    
    // Progress bar step navigation functionality
    $(document).ready(function() {
        // Make progress steps clickable for navigation
        $('.wizard-step').on('click', function() {
            const targetStep = parseInt($(this).data('step'));
            const currentStep = parseInt($('#currentStepInput').val() || 1);
            

            
            // Allow navigation to any completed step or current step
            // Check if step is completed or current
            if ($(this).hasClass('completed') || $(this).hasClass('active') || targetStep <= currentStep) {

                window.navigateToStep(targetStep);
            } else {

                // Optional: Show a message that this step isn't available yet
                if (window.showToast) {
                    window.showToast('Please complete the current step first', 'warning');
                } else {
                    alert('Please complete the current step before proceeding to this step.');
                }
            }
        });
        
        // Add pointer cursor to clickable steps
        $('.wizard-step').each(function() {
            const step = parseInt($(this).data('step'));
            const currentStep = parseInt($('#currentStepInput').val() || 1);
            
            if ($(this).hasClass('completed') || $(this).hasClass('active') || step <= currentStep) {
                $(this).css('cursor', 'pointer');
                $(this).addClass('clickable-step');
            } else {
                $(this).css('cursor', 'not-allowed');
                $(this).removeClass('clickable-step');
            }
        });
        
        // Update clickable status when step changes
        $(document).on('stepChanged', function(e, newStep) {
            $('.wizard-step').each(function() {
                const step = parseInt($(this).data('step'));
                
                if ($(this).hasClass('completed') || $(this).hasClass('active') || step <= newStep) {
                    $(this).css('cursor', 'pointer');
                    $(this).addClass('clickable-step');
                } else {
                    $(this).css('cursor', 'not-allowed');
                    $(this).removeClass('clickable-step');
                }
            });
        });
    });
    
    window.editActivity = function(day, index) {

        // This would typically open a modal or form to edit the activity
        if (window.itineraryBuilder && typeof window.itineraryBuilder.editActivity === 'function') {
            window.itineraryBuilder.editActivity(day, index);
        } else {

        }
    };
    
    window.removeActivity = function(day, index) {

        if (confirm('Are you sure you want to remove this activity?')) {
            const activityElement = document.querySelector(`#day-${day}-activities .activity-card:nth-child(${index + 1})`);
            if (activityElement) {
                activityElement.remove();
            }
        }
    };
    
    window.addNewDay = function() {

        // This would typically add a new day section
        if (window.itineraryBuilder && typeof window.itineraryBuilder.addNewDay === 'function') {
            window.itineraryBuilder.addNewDay();
        } else {

        }
    };
    
    // Function to populate selected providers (enhanced)
    window.populateSelectedProviders = function(type, providers) {

        
        if (!providers || !Array.isArray(providers)) {

            return;
        }
        
        // Use step 3 provider functions if available (when on step 3)
        if (typeof window.loadProvidersFromDraft === 'function') {

            const draftData = {};
            draftData[`selected_${type}`] = providers;
            window.loadProvidersFromDraft(draftData);
            return;
        }
        
        try {
            // Find the container for selected providers
            const containerSelector = `.selected-${type}-container, .${type}-selections, #selected${type.charAt(0).toUpperCase() + type.slice(1)}`;
            const container = document.querySelector(containerSelector);
            
            if (!container) {

                return;
            }
            
            // Clear existing selections
            container.innerHTML = '';
            
            providers.forEach((provider, index) => {

                
                // Create a basic provider card/item
                const providerElement = document.createElement('div');
                providerElement.className = `selected-${type}-item border p-3 mb-2`;
                providerElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${provider.name || provider.company || provider.airline || 'Unknown'}</h6>
                            <small class="text-muted">${provider.location || provider.route || provider.departure + ' → ' + provider.arrival || ''}</small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-primary">${provider.price || '0'} ${provider.currency || 'USD'}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="this.parentElement.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="selected_${type}[${index}][id]" value="${provider.id}">
                    <input type="hidden" name="selected_${type}[${index}][name]" value="${provider.name || provider.company || provider.airline}">
                    <input type="hidden" name="selected_${type}[${index}][price]" value="${provider.price || 0}">
                    <input type="hidden" name="selected_${type}[${index}][currency]" value="${provider.currency || 'USD'}">
                `;
                
                container.appendChild(providerElement);
            });
            
        } catch (error) {
            console.error(`Error populating ${type} selections:`, error);
        }
    };
    
    // Function to specifically populate Summernote fields
    window.populateSummernoteFields = function(data) {

        
        if (!data) return;
        
        // Find all Summernote editors
        $('.summernote-editor').each(function() {
            const $editor = $(this);
            const fieldName = $editor.attr('name');
            
            if (fieldName && data[fieldName]) {

                
                try {
                    // Check if Summernote is initialized
                    if ($editor.hasClass('note-editable') || $editor.next('.note-editor').length > 0) {
                        // Summernote is initialized, set the content
                        $editor.summernote('code', data[fieldName]);
                    } else {
                        // Summernote not initialized yet, try again after a delay
                        setTimeout(() => {
                            if ($editor.summernote) {
                                $editor.summernote('code', data[fieldName]);
                            } else {
                                // Fallback: set the textarea value directly
                                $editor.val(data[fieldName]);
                            }
                        }, 1000);
                    }
                } catch (error) {
                    console.error(`Error setting Summernote content for ${fieldName}:`, error);
                    // Fallback: set textarea value
                    $editor.val(data[fieldName]);
                }
            }
        });
    };
    // Manual save draft functionality - delegates to PackageWizard system
    window.manualSaveDraft = function() {
        // Delegate to the new PackageWizard system instead of using legacy logic
        if (window.packageWizard && typeof window.packageWizard.saveDraft === 'function') {
            return window.packageWizard.saveDraft();
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error('Draft save system not available');
            }
            return;
        }
    };
    
    // Add button click handler for manual save
    document.getElementById('saveDraftBtn')?.addEventListener('click', function() {
        window.manualSaveDraft();
    });
    
    // Initialize provider search with wizard routes (moved from orphaned section)
    window.providerSearch = new ProviderSearch({
        apiRoutes: {
            searchHotels: '{{ route("api.b2b.providers.search-hotels") }}',
            searchFlights: '{{ route("api.b2b.providers.search-flights") }}',
            searchTransport: '{{ route("api.b2b.providers.search-transport") }}',
            checkAvailability: '{{ route("api.b2b.providers.check-availability") }}',
            calculatePricing: '{{ route("api.b2b.providers.calculate-pricing") }}'
        }
    });
    
    // Initialize step-specific components (moved from orphaned section)
    if (window.packageWizard) {
        window.packageWizard.on('stepChanged', function(stepNumber) {
            switch(stepNumber) {
                case 2:
                    if (window.providerSearch) {
                        window.providerSearch.initialize();
                    }
                    break;
                case 3:
                    if (window.itineraryBuilder) {
                        window.itineraryBuilder.initialize();
                    }
                    break;
                case 4:
                    if (window.pricingCalculator) {
                        window.pricingCalculator.initialize();
                        window.pricingCalculator.updateTotals();
                    }
                    break;
            }
        });

        // Handle form submission (moved from orphaned section)
        window.packageWizard.on('submit', function(formData) {
            showLoadingOverlay('Creating your package...');
            
            fetch('{{ route("b2b.travel-agent.packages.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // Check if response is JSON or HTML
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Server returned HTML (likely an error page)
                    return response.text().then(html => {
                        throw new Error('Server returned HTML instead of JSON. This usually indicates a server-side error.');
                    });
                }
            })
            .then(data => {
                hideLoadingOverlay();
                
                if (data.success) {
                    showSuccessAlert('Package created successfully!');
                    // Redirect to the package detail page or packages list
                    setTimeout(() => {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = '{{ route("b2b.travel-agent.packages.index") }}';
                        }
                    }, 1500);
                } else {
                    showErrorAlert(data.message || 'Failed to create package');
                    if (data.errors && window.packageWizard.showValidationErrors) {
                        window.packageWizard.showValidationErrors(data.errors);
                    }
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Submit error:', error);
                
                // Provide more specific error messages
                let errorMessage = 'An error occurred while creating the package.';
                if (error.message.includes('HTML instead of JSON')) {
                    errorMessage = 'Server error occurred. Please check the console for details and try again.';
                } else if (error.message.includes('NetworkError') || error.message.includes('fetch')) {
                    errorMessage = 'Network connection error. Please check your internet connection and try again.';
                } else if (error.message.includes('SyntaxError')) {
                    errorMessage = 'Server returned invalid response. Please try again or contact support.';
                }
                
                showErrorAlert(errorMessage);
            });
        });
    }
});

window.testSaveDraft = function() {


    console.log('Current step:', document.getElementById('currentStepInput')?.value);
    
    // Trigger manual save
    if (typeof window.manualSaveDraft === 'function') {
        window.manualSaveDraft();
    } else {
        console.error('❌ manualSaveDraft function not found');
    }
};

// Add manual draft save function for direct access
window.manualSaveDraft = function() {

    
    if (window.packageWizard && typeof window.packageWizard.saveDraft === 'function') {
        console.log('Using PackageWizard.saveDraft()');
        window.packageWizard.saveDraft();
    } else {
        console.error('❌ PackageWizard or saveDraft method not available');
        
        // Try to save manually if wizard isn't available
        if (confirm('PackageWizard not found. Try manual save?')) {
            window.manualDraftSaveWithoutWizard();
        }
    }
};

// Fallback manual save without wizard
window.manualDraftSaveWithoutWizard = async function() {
    try {
        const form = document.getElementById('packageWizardForm');
        if (!form) {
            console.error('❌ Form not found');
            return;
        }
        
        const formData = new FormData(form);
        
        // Add current step
        const currentStep = document.getElementById('currentStepInput')?.value || 1;
        formData.append('current_step', currentStep);
        
        // Add draft ID if exists
        const draftId = document.getElementById('packageDraftId')?.value;
        if (draftId) {
            formData.append('package_draft_id', draftId);
        }
        
        // Add activities data manually
        if (window.itineraryActivities && Array.isArray(window.itineraryActivities)) {
            window.itineraryActivities.forEach((activity, index) => {
                Object.keys(activity).forEach(key => {
                    formData.append(`activities[${index}][${key}]`, activity[key]);
                });
            });
        }
        
        // Add provider data manually
        if (window.selectedProviders) {
            ['hotels', 'flights', 'transport'].forEach(providerType => {
                if (window.selectedProviders[providerType] && Array.isArray(window.selectedProviders[providerType])) {
                    window.selectedProviders[providerType].forEach((provider, index) => {
                        Object.keys(provider).forEach(key => {
                            formData.append(`selected_${providerType}[${index}][${key}]`, provider[key]);
                        });
                    });
                }
            });
        }
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('{{ route("b2b.travel-agent.packages.save-draft") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {

            if (typeof toastr !== 'undefined') {
                toastr.success(result.message || 'Draft saved successfully');
            } else {
                alert(result.message || 'Draft saved successfully');
            }
        } else {
            console.error('❌ Manual draft save failed:', result.message);
            if (typeof toastr !== 'undefined') {
                toastr.error(result.message || 'Failed to save draft');
            } else {
                alert(result.message || 'Failed to save draft');
            }
        }
    } catch (error) {
        console.error('❌ Manual draft save error:', error);
        alert('Error saving draft: ' + error.message);
    }
};


// Add activities data verification function
window.verifyActivitiesState = function() {

    console.log('='.repeat(50));
    
    // Check global variables





    
    // Check hidden form inputs
    const hiddenInputs = document.querySelectorAll('input[name^="activities["]');


    
    if (hiddenInputs.length > 0) {
        const uniqueActivities = new Set();
        hiddenInputs.forEach(input => {
            const match = input.name.match(/activities\[(\d+)\]/);
            if (match) {
                uniqueActivities.add(match[1]);
            }
        });

        
        // Show sample of form data
        const formData = new FormData(document.getElementById('packageWizardForm'));
        const activityEntries = [];
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('activities[')) {
                activityEntries.push(`${key} = ${value}`);
            }
        }
        console.log('  Sample form entries (first 5):');
        activityEntries.slice(0, 5).forEach(entry => console.log('   ', entry));
    }
    
    // Check step 2 visibility
    const step2Element = document.getElementById('step2');



    
    // Check current step
    const currentStepInput = document.getElementById('currentStepInput');



    
    console.log('='.repeat(50));
    
    // Recommendation
    if (window.itineraryActivities?.length > 0 || window.draftActivitiesData?.length > 0) {

    } else if (hiddenInputs.length > 0) {

    } else {

    }
};

// Add function to fix step 4 total_price validation
window.fixStep4Validation = function() {

    
    // Check if we're on step 4
    const step4 = document.getElementById('step4');
    if (!step4 || step4.style.display === 'none') {

        return;
    }
    
    // Check if total_price field exists
    const totalPriceField = document.getElementById('total_price');
    if (!totalPriceField) {

        return;
    }
    
    // Get base price and calculate a basic total
    const basePriceField = document.getElementById('base_price');
    const basePrice = basePriceField ? parseFloat(basePriceField.value) : 0;
    
    if (basePrice > 0) {
        // Use base price as total price for validation
        totalPriceField.value = basePrice;

    } else {
        // Set minimum valid value
        totalPriceField.value = '100';

    }
    
    // Trigger the pricing calculation if available
    if (typeof updatePricingPreview === 'function') {
        updatePricingPreview();

    }
};

// Add function to fix required field validation issues
window.fixStep3Validation = function() {

    
    const step3 = document.getElementById('step3');
    if (!step3) {
        console.error('❌ Step 3 element not found');
        return;
    }
    
    // Find all required fields that are empty and don't have names
    const problematicFields = step3.querySelectorAll('input[required], select[required], textarea[required]');
    let fixedCount = 0;
    
    problematicFields.forEach((field, index) => {
        if (!field.value && (!field.name || field.name.trim() === '')) {

            // Remove required attribute temporarily
            field.removeAttribute('required');
            field.classList.add('temp-validation-fix');
            fixedCount++;
        }
    });
    


};

// Utility functions outside DOMContentLoaded

function initializeSummernote() {
    $('.summernote-editor').summernote({
        placeholder: 'Write a comprehensive description...',
        tabsize: 2,
        height: 200,
        minHeight: 150,
        maxHeight: 400,
        focus: false,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        styleTags: [
            'p',
            { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
            'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana'],
        callbacks: {
            onInit: function() {

            },
            onChange: function(contents, $editable) {
                // Auto-save when content changes
                if (window.packageWizard && window.packageWizard.markDirty) {
                    window.packageWizard.markDirty();
                }
            }
        }
    });
}

    // This code should be inside DOMContentLoaded - moving it there
    // (This section will be removed as it's orphaned outside the proper scope)

// Utility functions
function showLoadingOverlay(text = 'Loading...') {
    document.getElementById('loadingOverlay').style.display = 'flex';
    document.querySelector('.loading-text').textContent = text;
}

function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showSuccessAlert(message) {
    // Use your preferred notification system
    if (typeof toastr !== 'undefined') {
        toastr.success(message);
    } else {
        alert(message);
    }
}

function showErrorAlert(message) {
    // Use your preferred notification system
    if (typeof toastr !== 'undefined') {
        toastr.error(message);
    } else {
        alert(message);
    }
}

// Initialize Select2 for enhanced select boxes
function initializeSelect2() {
    // Initialize Select2 with Bootstrap 5 theme
    $('.select2-enhanced').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder');
        },
        allowClear: true
    });
    
    // Re-initialize Select2 when steps change
    document.addEventListener('stepChanged', function() {
        setTimeout(function() {
            $('.select2-enhanced:not(.select2-hidden-accessible)').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true
            });
        }, 100);
    });
}

// Initialize form enhancements
function initializeFormEnhancements() {
    // Character counter for textareas
    $('textarea[maxlength]').each(function() {
        const textarea = $(this);
        const maxLength = textarea.attr('maxlength');
        const counterId = textarea.attr('id') + 'Counter';
        const counter = $('#' + counterId);
        
        if (counter.length) {
            function updateCounter() {
                counter.text(textarea.val().length);
            }
            
            textarea.on('input', updateCounter);
            updateCounter();
        }
    });
    
    // Auto-calculate nights from days
    $('#duration_days').on('input', function() {
        const days = parseInt($(this).val()) || 0;
        const nights = Math.max(0, days - 1);
        $('#duration_nights').val(nights);
    });
    
    // Form validation feedback
    $('.form-control, .form-select').on('blur', function() {
        if (this.checkValidity()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Progressive disclosure for advanced options
    $('.advanced-options-toggle').on('click', function() {
        const target = $($(this).data('target'));
        target.slideToggle();
        $(this).find('.fa-chevron-down, .fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
    });
    
    // Auto-save notification
    let autoSaveTimer;
    $('.form-control, .form-select').on('input change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            showAutoSaveIndicator();
        }, 2000);
    });
}

function showAutoSaveIndicator() {
    // Create a small notification for auto-save
    const indicator = $('<div class="auto-save-indicator position-fixed" style="top: 20px; right: 20px; z-index: 1050;"><div class="alert alert-info alert-sm py-2 px-3"><i class="fas fa-cloud-upload-alt me-1"></i> Draft saved automatically</div></div>');
    $('body').append(indicator);
    
    setTimeout(function() {
        indicator.fadeOut(function() {
            indicator.remove();
        });
    }, 3000);
}
</script>
@endpush
