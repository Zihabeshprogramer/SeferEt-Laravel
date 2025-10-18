/**
 * Package Creation Wizard - Core JavaScript
 * Handles step navigation, validation, auto-save, and form management
 */
class PackageWizard {
    constructor(options) {
        this.options = {
            formId: 'packageWizardForm',
            stepsCount: 5,
            autoSave: true,
            autoSaveInterval: 30000,
            validationRules: {},
            apiRoutes: {},
            ...options
        };
        this.currentStep = parseInt(1);
        this.maxCompletedStep = parseInt(1);
        this.form = document.getElementById(this.options.formId);
        this.autoSaveTimer = null;
        this.saveTimeout = null;
        this.isDirty = false;
        this.eventCallbacks = {};
        this.init();
    }
    init() {
        this.bindEvents();
        this.updateStepUI();
        if (this.options.autoSave) {
            this.startAutoSave();
        }
        // Mark form as dirty on any input change
        this.form.addEventListener('change', () => this.markDirty());
        this.form.addEventListener('input', () => this.markDirty());
        // Additional event listeners for critical fields that should trigger immediate save
        this.setupCriticalFieldListeners();
        this.emit('initialized');
    }
    setupCriticalFieldListeners() {
        // Fields that should trigger an immediate draft save when changed
        const criticalFields = [
            'package_name', 'start_date', 'end_date', 'base_price', 
            'commission_rate', 'payment_terms', 'cancellation_policy'
        ];
        criticalFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('blur', () => {
                    if (this.isDirty) {
                        // Debounce the save to avoid too many requests
                        clearTimeout(this.saveTimeout);
                        this.saveTimeout = setTimeout(() => {
                            this.saveDraft();
                        }, 1000);
                    }
                });
            }
        });
        // Listen for dynamic content changes (like adding optional extras)
        document.addEventListener('DOMNodeInserted', () => {
            if (this.form.contains(event.target)) {
                this.markDirty();
            }
        });
    }
    bindEvents() {
        // Step navigation buttons
        const nextBtn = document.getElementById('nextStepBtn');
        const prevBtn = document.getElementById('prevStepBtn');
        const submitBtn = document.getElementById('submitPackageBtn');
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        const exitWizardBtn = document.getElementById('exitWizardBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.nextStep();
            });
        } else {
            console.log('\u26a0\ufe0f Next button not found (nextStepBtn)');
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prevStep());
        }
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', () => this.saveDraft());
        }
        if (exitWizardBtn) {
            exitWizardBtn.addEventListener('click', () => this.exitWizard());
        }
        // Step indicator clicks
        const stepIndicators = document.querySelectorAll('.wizard-step');
        stepIndicators.forEach((step, index) => {
            step.addEventListener('click', () => {
                const stepNum = index + 1;
                if (stepNum <= this.maxCompletedStep || stepNum === this.currentStep) {
                    this.goToStep(stepNum);
                }
            });
        });
        // Form submission prevention
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.currentStep === this.options.stepsCount) {
                this.submitForm();
            }
        });
        // Warn user before leaving if form is dirty
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                const message = 'You have unsaved changes. Are you sure you want to leave?';
                e.returnValue = message;
                return message;
            }
        });
    }
    nextStep() {
        console.log('🔄 PackageWizard.nextStep() called', {
            currentStep: this.currentStep,
            maxCompletedStep: this.maxCompletedStep,
            stepsCount: this.options.stepsCount
        });
        // BUGFIX: Validate and correct maxCompletedStep if it's corrupted
        if (this.maxCompletedStep > this.options.stepsCount) {
            this.maxCompletedStep = this.currentStep;
        }
        if (this.currentStep < this.options.stepsCount) {
            const nextStep = this.currentStep + 1;
            this.validateCurrentStep()
                .then(isValid => {
                    if (isValid) {
                        // BUGFIX: Ensure all values are integers
                        this.maxCompletedStep = Math.max(parseInt(this.maxCompletedStep), parseInt(nextStep));
                        this.goToStep(parseInt(nextStep));
                    } else {
                    }
                })
                .catch(error => {
                    console.error('❌ Step validation error:', error);
                    this.showError('Validation failed. Please check your inputs.');
                });
        } else {
        }
    }
    prevStep() {
        if (this.currentStep > 1) {
            this.goToStep(this.currentStep - 1);
        }
    }
    goToStep(stepNumber) {
        // BUGFIX: Ensure stepNumber is a valid integer
        stepNumber = parseInt(stepNumber);
        if (isNaN(stepNumber)) {
            console.log(`\u274c Invalid step number (not a number):`, stepNumber);
            return;
        }
        // BUGFIX: Additional validation and correction
        if (stepNumber < 1 || stepNumber > this.options.stepsCount) {
            // If trying to go to an invalid step > stepsCount, go to final step instead
            if (stepNumber > this.options.stepsCount) {
                stepNumber = this.options.stepsCount;
            } else {
                return;
            }
        }
        // Hide current step
        const currentStepEl = document.getElementById(`step${this.currentStep}`);
        if (currentStepEl) {
            currentStepEl.style.display = 'none';
        } else {
        }
        // Show target step
        const targetStepEl = document.getElementById(`step${stepNumber}`);
        if (targetStepEl) {
            targetStepEl.style.display = 'block';
        } else {
        }
        this.currentStep = parseInt(stepNumber);
        this.updateStepUI();
        this.updateFormCurrentStep();
        // Handle pending optional extras loading when step 4 becomes visible
        if (stepNumber === 4 && this.pendingOptionalExtras) {
            setTimeout(() => {
                // Check if optional extras already exist in DOM (from Blade template)
                const container = document.getElementById('optionalExtras');
                const existingExtras = container ? container.querySelectorAll('.optional-extra-item') : [];
                if (existingExtras.length === 0) {
                    this.loadOptionalExtras(this.pendingOptionalExtras);
                } else {
                }
                this.pendingOptionalExtras = null; // Clear after checking
            }, 200); // Delay to ensure DOM is fully ready
        }
        // Scroll to top
        window.scrollTo(0, 0);
        this.emit('stepChanged', stepNumber);
    }
    updateStepUI() {
        // Update step indicators
        const stepIndicators = document.querySelectorAll('.wizard-step');
        stepIndicators.forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');
            if (stepNum === this.currentStep) {
                step.classList.add('active');
            } else if (stepNum < this.currentStep || stepNum <= this.maxCompletedStep) {
                step.classList.add('completed');
            }
        });
        // Update navigation buttons
        const nextBtn = document.getElementById('nextStepBtn');
        const prevBtn = document.getElementById('prevStepBtn');
        const submitBtn = document.getElementById('submitPackageBtn');
        if (prevBtn) {
            prevBtn.style.display = this.currentStep > 1 ? 'block' : 'none';
        }
        if (nextBtn && submitBtn) {
            if (this.currentStep < this.options.stepsCount) {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            }
        }
        // Update step counter
        const stepCounter = document.getElementById('currentStepNumber');
        if (stepCounter) {
            stepCounter.textContent = this.currentStep;
        }
        // Update progress line
        const progressLine = document.querySelector('.progress-line');
        if (progressLine) {
            const progressPercent = ((this.currentStep - 1) / (this.options.stepsCount - 1)) * 100;
            progressLine.style.width = `${progressPercent}%`;
        }
    }
    updateFormCurrentStep() {
        const currentStepInput = document.getElementById('currentStepInput');
        if (currentStepInput) {
            currentStepInput.value = this.currentStep;
        }
    }
    async validateCurrentStep() {
        console.log(`🔍 PackageWizard.validateCurrentStep() called for step ${this.currentStep}`);
        const stepElement = document.getElementById(`step${this.currentStep}`);
        if (!stepElement) {
            return true;
        }
        // Clear previous validation errors
        this.clearValidationErrors(stepElement);
        // Get all form elements in current step, but exclude modal forms
        const allFormElements = stepElement.querySelectorAll('input, select, textarea');
        // Filter out elements that are inside modals (they shouldn't be validated when modal is closed)
        const formElements = Array.from(allFormElements).filter(element => {
            // Check if element is inside a modal
            const modal = element.closest('.modal');
            if (modal) {
                // Only include modal elements if the modal is currently shown
                const isModalVisible = modal.classList.contains('show') || 
                                     modal.style.display === 'block' || 
                                     window.getComputedStyle(modal).display !== 'none';
                if (!isModalVisible) {
                    console.log(`🚫 Excluding modal element from validation:`, {
                        id: element.id || '(no id)',
                        name: element.name || '(no name)',
                        modalId: modal.id || '(no modal id)'
                    });
                    return false;
                }
            }
            return true;
        });
        let isValid = true;
        // Debug: Log all form elements for troubleshooting
        formElements.forEach((element, index) => {
            console.log(`  [${index}] ${element.tagName}:`, {
                name: element.name || '(no name)',
                type: element.type,
                value: element.value,
                required: element.required,
                id: element.id || '(no id)',
                className: element.className
            });
        });
        // Basic HTML5 validation (excluding hidden modal forms)
        let invalidCount = 0;
        formElements.forEach((element, index) => {
            if (!element.checkValidity()) {
                console.log(`❌ HTML5 validation failed for element ${index}:`, {
                    name: element.name || '(no name)',
                    type: element.type,
                    value: element.value,
                    validationMessage: element.validationMessage,
                    id: element.id || '(no id)',
                    className: element.className,
                    innerHTML: element.outerHTML.substring(0, 100) + '...'
                });
                this.showFieldError(element, element.validationMessage);
                isValid = false;
                invalidCount++;
            }
        });
        // Custom validation rules
        if (this.options.validationRules[this.currentStep]) {
            try {
                const customValidation = await this.runCustomValidation(this.currentStep);
                isValid = isValid && customValidation;
            } catch (error) {
                console.error('❌ Custom validation error:', error);
                isValid = false;
            }
        } else {
        }
        // Server-side validation
        if (isValid && this.options.apiRoutes.validateStep) {
            try {
                const serverValidation = await this.validateStepOnServer();
                isValid = isValid && serverValidation;
            } catch (error) {
                console.error('❌ Server validation error:', error);
                // Don't fail on server validation errors to allow offline use
            }
        } else {
            console.log(`ℹ️ Server-side validation skipped for step ${this.currentStep}`, {
                isValidSoFar: isValid,
                hasValidateStepRoute: !!this.options.apiRoutes.validateStep
            });
        }
        return isValid;
    }
    async runCustomValidation(step) {
        const rules = this.options.validationRules[step];
        let isValid = true;
        switch (step) {
            case 1:
                // Basic information validation
                if (rules.requireDestinations) {
                    const destinations = document.querySelectorAll('input[name="destinations[]"]');
                    if (!destinations || destinations.length === 0) {
                        this.showError('At least one destination is required');
                        isValid = false;
                    }
                }
                break;
            case 2:
                // Itinerary validation - check both DOM elements AND global variables
                const activities = document.querySelectorAll('input[name*="[activity_name]"]');
                let activityCount = activities ? activities.length : 0;
                // If no DOM elements found, check global variables (for draft continuation scenarios)
                if (activityCount === 0) {
                    if (window.itineraryActivities && Array.isArray(window.itineraryActivities)) {
                        activityCount = window.itineraryActivities.length;
                    } else if (window.draftActivitiesData && Array.isArray(window.draftActivitiesData)) {
                        activityCount = window.draftActivitiesData.length;
                    } else if (window.draftActivitiesForItinerary && Array.isArray(window.draftActivitiesForItinerary)) {
                        activityCount = window.draftActivitiesForItinerary.length;
                    }
                } else {
                }
                if (rules.requireActivities && activityCount === 0) {
                    this.showError('At least one activity must be planned');
                    isValid = false;
                } else {
                }
                break;
            case 3:
                // Provider validation
                if (rules.requireProviders) {
                    const hotels = document.querySelectorAll('input[name^="selected_hotels"]');
                    const flights = document.querySelectorAll('input[name^="selected_flights"]');
                    const transport = document.querySelectorAll('input[name^="selected_transport"]');
                    const hotelCount = hotels ? hotels.length : 0;
                    const flightCount = flights ? flights.length : 0;
                    const transportCount = transport ? transport.length : 0;
                    if (hotelCount === 0 && flightCount === 0 && transportCount === 0) {
                        this.showError('At least one provider (hotel, flight, or transport) must be selected');
                        isValid = false;
                    }
                }
                break;
        }
        return isValid;
    }
    async validateStepOnServer() {
        // Always use comprehensive data gathering - let the server decide what's needed
        const formData = this.gatherAllFormData();
        formData.append('step', this.currentStep);
        // DEBUGGING: Log what we're actually sending for step 4
        if (this.currentStep === 4) {
            const debugData = {};
            for (let [key, value] of formData.entries()) {
                if (key.includes('base_price') || key.includes('commission_rate') || key.includes('payment_terms') || key.includes('optional_extras')) {
                    debugData[key] = value;
                }
            }
        }
        // Log what's being sent for debugging
        const debugData = {};
        for (let [key, value] of formData.entries()) {
            debugData[key] = value;
        }
        const response = await fetch(this.options.apiRoutes.validateStep, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        if (!result.success && result.errors) {
            this.showValidationErrors(result.errors);
            return false;
        }
        return result.success;
    }
    showValidationErrors(errors) {
        Object.keys(errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.showFieldError(field, errors[fieldName][0]);
            }
        });
    }
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        let feedback = field.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
    clearValidationErrors(container = this.form) {
        const invalidFields = container.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => {
            field.classList.remove('is-invalid');
        });
        const feedbacks = container.querySelectorAll('.invalid-feedback');
        feedbacks.forEach(feedback => {
            feedback.style.display = 'none';
        });
    }
    async saveDraft() {
        if (!this.options.apiRoutes.saveDraft) return;
        try {
            this.showSaving();
            const formData = this.gatherAllFormData();
            // Add current step information
            formData.append('current_step', this.currentStep);
            // Add draft ID if it exists
            const draftIdInput = document.getElementById('packageDraftId');
            if (draftIdInput && draftIdInput.value) {
                formData.append('draft_id', draftIdInput.value);
            }
            const response = await fetch(this.options.apiRoutes.saveDraft, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();
            if (result.success) {
                this.isDirty = false;
                this.showSuccess(result.message || 'Draft saved successfully');
                // Update draft ID if returned
                if (result.draft_id) {
                    const draftInput = document.getElementById('packageDraftId');
                    if (draftInput) {
                        draftInput.value = result.draft_id;
                    }
                }
                this.emit('draftSaved', result);
            } else {
                this.showError(result.message || 'Failed to save draft');
            }
        } catch (error) {
            console.error('Draft save error:', error);
            this.showError('Network error occurred while saving draft');
        } finally {
            this.hideSaving();
        }
    }
    async loadDraftData(draftData) {
        try {
            // Populate form fields with draft data
            Object.keys(draftData).forEach(key => {
                if (key === 'destinations' && Array.isArray(draftData[key])) {
                    // Handle destinations array specially
                    this.loadDestinations(draftData[key]);
                } else if (key === 'categories' && Array.isArray(draftData[key])) {
                    // Handle categories array
                    this.loadCategories(draftData[key]);
                } else if (key === 'optional_extras' && Array.isArray(draftData[key])) {
                    // Handle optional extras array specially - delay loading until step is visible
                    // Store optional extras data for delayed loading
                    this.pendingOptionalExtras = draftData[key];
                    // Load immediately if step 4 is visible, otherwise wait
                    const step4 = document.getElementById('step4');
                    if (step4 && step4.style.display !== 'none') {
                        this.loadOptionalExtras(draftData[key]);
                    } else {
                    }
                } else {
                    // Handle regular form fields
                    const field = this.form.querySelector(`[name="${key}"]`);
                    if (field) {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = !!draftData[key];
                        } else {
                            field.value = draftData[key];
                        }
                        // Handle disabled state for smart pricing fields
                        if (key === 'child_price' || key === 'child_discount_percent') {
                            const disabledKey = `${key}_disabled`;
                            if (draftData[disabledKey] === '1') {
                                field.disabled = true;
                                field.style.backgroundColor = '#f8f9fa';
                                field.style.cursor = 'not-allowed';
                                // Re-add the calculated indicator if it was calculated
                                setTimeout(() => {
                                    if (typeof addCalculatedIndicator === 'function') {
                                        const tooltip = key === 'child_price' ? 
                                            'Auto-calculated from discount percentage' : 
                                            'Auto-calculated from child price';
                                        addCalculatedIndicator(field, tooltip);
                                    }
                                }, 100);
                            } else {
                                field.disabled = false;
                                field.style.backgroundColor = '';
                                field.style.cursor = '';
                            }
                        }
                        // Trigger change event to update dependent UI elements
                        if (field.type !== 'hidden') {
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                }
            });
            // Update provider approval status if on step 3 and data exists
            if (draftData.provider_approvals && typeof updateProviderApprovalStatus === 'function') {
                setTimeout(() => {
                    updateProviderApprovalStatus(draftData);
                }, 500); // Small delay to ensure DOM is ready
            }
            // Update pricing preview if on step 4
            if (this.currentStep === 4) {
                setTimeout(() => {
                    // Update child pricing logic first
                    if (typeof updateChildPricingLogic === 'function') {
                        updateChildPricingLogic();
                    }
                    // Then update pricing preview
                    if (typeof updatePricingPreview === 'function') {
                        updatePricingPreview();
                    }
                }, 300);
            }
            this.isDirty = false;
            this.emit('draftLoaded', draftData);
        } catch (error) {
            console.error('Draft load error:', error);
            this.showError('Failed to load draft data');
        }
    }
    loadDestinations(destinations) {
        // This would integrate with the destination autocomplete component
        destinations.forEach(destination => {
            if (typeof addDestination === 'function') {
                addDestination(destination);
            }
        });
    }
    loadCategories(categories) {
        categories.forEach(category => {
            const checkbox = this.form.querySelector(`input[name="categories[]"][value="${category}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    loadOptionalExtras(extras) {
        console.log('🔧 extras type:', typeof extras, 'is array:', Array.isArray(extras), 'length:', extras ? extras.length : 'N/A');
        if (!Array.isArray(extras) || extras.length === 0) {
            return;
        }
        const container = document.getElementById('optionalExtras');
        if (!container) {
            return;
        }
        console.log('📦 Container display style:', getComputedStyle(container).display);
        // CHECK: Do extras already exist from server-side rendering?
        const existingExtras = container.querySelectorAll('.optional-extra-item');
        if (existingExtras.length > 0) {
            console.log('📊 Existing extras HTML preview:', container.innerHTML.substring(0, 200) + '...');
            // Validate that existing extras match the expected count
            if (existingExtras.length === extras.length) {
                return;
            } else {
                console.log(`⚠️ Existing extras count (${existingExtras.length}) doesn't match expected (${extras.length}). Will recreate.`);
            }
        }
        // Only clear if we need to recreate
        container.innerHTML = '';
        // Set the global extraIndex to match the number of extras we're loading
        if (typeof window !== 'undefined') {
            if (typeof window.extraIndex !== 'undefined') {
                window.extraIndex = extras.length;
            } else if (typeof extraIndex !== 'undefined') {
                extraIndex = extras.length;
            } else {
                // Create global extraIndex if it doesn't exist
                window.extraIndex = extras.length;
            }
        }
        // Recreate extras from draft data
        extras.forEach((extra, index) => {
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
            console.log(`✅ Added extra ${index}: ${extra.name} - ${extra.price} (${extra.type})`);
            // Debug: Check if HTML was actually inserted
            const insertedElements = container.querySelectorAll('.optional-extra-item');
        });
        console.log(`✅ Completed loading ${extras.length} optional extras. Current extraIndex:`, 
                    typeof extraIndex !== 'undefined' ? extraIndex : (typeof window.extraIndex !== 'undefined' ? window.extraIndex : 'undefined'));
        // Final debugging - check final state
        const finalElements = container.querySelectorAll('.optional-extra-item');
        const finalInputs = container.querySelectorAll('input[name*="optional_extras"]');
        console.log(`🎯 Container final innerHTML preview:`, container.innerHTML.substring(0, 200) + '...');
        // Check if the container is inside a hidden step or something
        let parent = container;
        while (parent && parent !== document.body) {
            const style = getComputedStyle(parent);
            if (style.display === 'none') {
                break;
            }
            parent = parent.parentElement;
        }
    }
    startAutoSave() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
        this.autoSaveTimer = setInterval(() => {
            if (this.isDirty) {
                this.saveDraft();
            }
        }, this.options.autoSaveInterval);
    }
    stopAutoSave() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
            this.autoSaveTimer = null;
        }
    }
    markDirty() {
        this.isDirty = true;
    }
    // Gather all form data from all steps, including dynamic data
    gatherAllFormData() {
        const formData = new FormData(this.form);
        // Ensure activities data is included from step 2
        this.ensureActivitiesDataIncluded(formData);
        // Ensure provider data is included from step 3
        this.ensureProviderDataIncluded(formData);
        // Ensure pricing data is included from step 4
        this.ensurePricingDataIncluded(formData);
        // Ensure step 5 checkbox data is included
        this.ensureStep5DataIncluded(formData);
        return formData;
    }
    // Ensure activities data from step 2 itinerary is included
    ensureActivitiesDataIncluded(formData) {
        try {
            // Check if activities data exists globally (from step 2)
            let activitiesData = null;
            if (window.itineraryActivities && Array.isArray(window.itineraryActivities)) {
                activitiesData = window.itineraryActivities;
            } else if (window.draftActivitiesData && Array.isArray(window.draftActivitiesData)) {
                activitiesData = window.draftActivitiesData;
            } else if (window.draftActivitiesForItinerary && Array.isArray(window.draftActivitiesForItinerary)) {
                activitiesData = window.draftActivitiesForItinerary;
            } else {
                // Fallback: Check if activities data already exists in hidden form inputs
                const existingActivityInputs = document.querySelectorAll('input[name^="activities["]');
                if (existingActivityInputs.length > 0) {
                    return; // Let the form data collection handle it naturally
                }
            }
            if (activitiesData && activitiesData.length > 0) {
                // Remove any existing activities data to avoid duplicates
                const keysToRemove = [];
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('activities[')) {
                        keysToRemove.push(key);
                    }
                }
                keysToRemove.forEach(key => formData.delete(key));
                // Add activities data to form data
                activitiesData.forEach((activity, index) => {
                    Object.keys(activity).forEach(key => {
                        if (activity[key] !== null && activity[key] !== undefined) {
                            formData.append(`activities[${index}][${key}]`, activity[key]);
                        }
                    });
                });
            }
        } catch (error) {
            console.error('Error including activities data:', error);
        }
    }
    // Ensure provider data from step 3 is included (using minimal records)
    ensureProviderDataIncluded(formData) {
        try {
            // Use minimal provider records if MergedProviderSelector is available
            if (window.MergedProviderSelector && typeof window.MergedProviderSelector.getMinimalProvidersForDraft === 'function') {
                const minimalProviders = window.MergedProviderSelector.getMinimalProvidersForDraft();
                const minimalDataSize = JSON.stringify(minimalProviders).length;
                // Remove existing provider data to avoid duplicates
                const keysToRemove = [];
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('selected_hotels[') || 
                        key.startsWith('selected_flights[') || 
                        key.startsWith('selected_transport[')) {
                        keysToRemove.push(key);
                    }
                }
                keysToRemove.forEach(key => formData.delete(key));
                // Add minimal provider data (ALWAYS include arrays, even if empty)
                let totalFieldsAdded = 0;
                Object.keys(minimalProviders).forEach(serviceKey => {
                    const providers = minimalProviders[serviceKey];
                    if (Array.isArray(providers)) {
                        if (providers.length > 0) {
                            providers.forEach((provider, index) => {
                                Object.keys(provider).forEach(key => {
                                    if (provider[key] !== null && provider[key] !== undefined) {
                                        formData.append(`${serviceKey}[${index}][${key}]`, provider[key]);
                                        totalFieldsAdded++;
                                    }
                                });
                            });
                        } else {
                            // Add empty array indicator to ensure old data is cleared
                            formData.append(`${serviceKey}_empty`, 'true');
                        }
                    }
                });
                return; // Exit early when using minimal records
            }
            // Fallback: Check if provider data exists globally (from step 3) - LEGACY
            if (window.selectedProviders) {
                const providers = window.selectedProviders;
                // Include hotels (full data - legacy) - ALWAYS process, even if empty
                // Remove existing hotel data first
                const hotelKeysToRemove = [];
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('selected_hotels[')) {
                        hotelKeysToRemove.push(key);
                    }
                }
                hotelKeysToRemove.forEach(key => formData.delete(key));
                if (providers.hotels && Array.isArray(providers.hotels)) {
                    if (providers.hotels.length > 0) {
                        // Add hotel data (limited to essential fields for size optimization)
                        providers.hotels.forEach((hotel, index) => {
                            // Only include essential fields to reduce draft size
                            const essentialFields = ['id', 'name', 'type', 'provider_type', 'service_request_id', 'service_request_status', 'markup_percentage'];
                            essentialFields.forEach(key => {
                                if (hotel[key] !== null && hotel[key] !== undefined) {
                                    formData.append(`selected_hotels[${index}][${key}]`, hotel[key]);
                                }
                            });
                        });
                    } else {
                        formData.append('selected_hotels_empty', 'true');
                    }
                }
                // Include flights (minimal fields) - ALWAYS process, even if empty
                // Remove existing flight data first
                const flightKeysToRemove = [];
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('selected_flights[')) {
                        flightKeysToRemove.push(key);
                    }
                }
                flightKeysToRemove.forEach(key => formData.delete(key));
                if (providers.flights && Array.isArray(providers.flights)) {
                    if (providers.flights.length > 0) {
                        // Add flight data (essential fields only)
                        providers.flights.forEach((flight, index) => {
                            const essentialFields = ['id', 'name', 'airline', 'type', 'provider_type', 'service_request_id', 'service_request_status', 'markup_percentage'];
                            essentialFields.forEach(key => {
                                if (flight[key] !== null && flight[key] !== undefined) {
                                    formData.append(`selected_flights[${index}][${key}]`, flight[key]);
                                }
                            });
                        });
                    } else {
                        formData.append('selected_flights_empty', 'true');
                    }
                }
                // Include transport (minimal fields) - ALWAYS process, even if empty
                // Remove existing transport data first
                const transportKeysToRemove = [];
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('selected_transport[')) {
                        transportKeysToRemove.push(key);
                    }
                }
                transportKeysToRemove.forEach(key => formData.delete(key));
                if (providers.transport && Array.isArray(providers.transport)) {
                    if (providers.transport.length > 0) {
                        // Add transport data (essential fields only)
                        providers.transport.forEach((transport, index) => {
                            const essentialFields = ['id', 'name', 'service_name', 'type', 'provider_type', 'service_request_id', 'service_request_status', 'markup_percentage'];
                            essentialFields.forEach(key => {
                                if (transport[key] !== null && transport[key] !== undefined) {
                                    formData.append(`selected_transport[${index}][${key}]`, transport[key]);
                                }
                            });
                        });
                    } else {
                        formData.append('selected_transport_empty', 'true');
                    }
                }
            }
        } catch (error) {
            console.error('Error including provider data:', error);
        }
    }
    // Ensure pricing data from step 4 is properly captured
    ensurePricingDataIncluded(formData) {
        try {
            // Check if step 4 pricing container exists (means we're on step 4 or have been there)
            const pricingContainer = document.getElementById('step4');
            if (!pricingContainer) {
                return;
            }
            // Capture all pricing-related form fields from step 4 specifically
            const pricingFields = [
                'base_price', 'child_price', 'child_discount_percent', 'infant_price',
                'single_supplement', 'commission_rate', 'payment_terms',
                'cancellation_policy', 'min_booking_days', 'requires_deposit',
                'deposit_amount', 'total_price'
            ];
            let capturedFields = 0;
            pricingFields.forEach(fieldName => {
                // Look for fields within step 4 container OR entire form (for fields like currency)
                let field = pricingContainer.querySelector(`[name="${fieldName}"]`) || 
                           this.form.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    // Remove existing entry first to avoid duplicates
                    if (formData.has(fieldName)) {
                        formData.delete(fieldName);
                    }
                    if (field.type === 'checkbox') {
                        const value = field.checked ? '1' : '0';
                        formData.append(fieldName, value);
                        capturedFields++;
                    } else if (field.value !== '' && field.value !== null) {
                        formData.append(fieldName, field.value);
                        capturedFields++;
                    }
                    // Also save the disabled state for smart child pricing
                    if (fieldName === 'child_price' || fieldName === 'child_discount_percent') {
                        formData.append(`${fieldName}_disabled`, field.disabled ? '1' : '0');
                    }
                } else if (this.currentStep === 4) {
                    // Only log missing fields when we're actually on step 4
                }
            });
            // Handle optional extras array
            const optionalExtras = [];
            // Debug: Check what's actually in the DOM
            // Try different possible containers
            const step4Container = document.getElementById('step4');
            const optionalExtrasContainer = document.getElementById('optionalExtras');
            // Try different selectors
            const extraItems1 = this.form.querySelectorAll('.extra-item');
            const extraItems2 = this.form.querySelectorAll('[data-index]');
            const extraItems3 = this.form.querySelectorAll('.row.mb-3');
            const extraItems4 = optionalExtrasContainer ? optionalExtrasContainer.querySelectorAll('.optional-extra-item') : [];
            const extraItems5 = optionalExtrasContainer ? optionalExtrasContainer.querySelectorAll('.extra-item') : [];
            const extraItems6a = optionalExtrasContainer ? optionalExtrasContainer.querySelectorAll('.row.mb-3') : [];
            const extraItems7 = this.form.querySelectorAll('input[name*="optional_extras"]');
            console.log('  .extra-item (form):', extraItems1.length, extraItems1);
            console.log('  [data-index] (form):', extraItems2.length, extraItems2);
            console.log('  .row.mb-3 (form):', extraItems3.length, extraItems3);
            console.log('  .optional-extra-item (container):', extraItems4.length, extraItems4);
            console.log('  .extra-item (container):', extraItems5.length, extraItems5);
            console.log('  .row.mb-3 (container):', extraItems6a.length, extraItems6a);
            // Use the most promising selector (prefer .optional-extra-item which matches Blade template)
            const extraItems = extraItems4.length > 0 ? extraItems4 : 
                              extraItems5.length > 0 ? extraItems5 :
                              extraItems1.length > 0 ? extraItems1 : 
                              extraItems2;
            extraItems.forEach((item, index) => {
                const nameField = item.querySelector('[name*="[name]"]');
                const priceField = item.querySelector('[name*="[price]"]');
                const typeField = item.querySelector('[name*="[type]"]');
                console.log(`Processing extra item ${index}:`, {
                    item: item,
                    nameField: nameField,
                    priceField: priceField,
                    typeField: typeField,
                    nameValue: nameField?.value,
                    priceValue: priceField?.value,
                    typeValue: typeField?.value
                });
                const name = nameField?.value;
                const price = priceField?.value;
                const type = typeField?.value;
                if (name && price) {
                    optionalExtras.push({ name, price, type });
                    console.log(`✅ Added optional extra: ${name} - ${price} (${type})`);
                } else {
                }
            });
            // Fallback: If no items found with container approach, try direct input field approach
            if (optionalExtras.length === 0) {
                const nameInputs = this.form.querySelectorAll('input[name*="optional_extras"][name*="[name]"]');
                nameInputs.forEach((nameInput, index) => {
                    const name = nameInput.value;
                    const nameAttr = nameInput.getAttribute('name');
                    const indexMatch = nameAttr.match(/optional_extras\[(\d+)\]\[name\]/);
                    if (indexMatch && name) {
                        const extraIndex = indexMatch[1];
                        const priceInput = this.form.querySelector(`input[name="optional_extras[${extraIndex}][price]"]`);
                        const typeInput = this.form.querySelector(`select[name="optional_extras[${extraIndex}][type]"]`);
                        const price = priceInput ? priceInput.value : null;
                        const type = typeInput ? typeInput.value : 'per_person';
                        if (name && price) {
                            optionalExtras.push({ name, price, type });
                            console.log(`✅ Fallback added optional extra: ${name} - ${price} (${type})`);
                        }
                    }
                });
            }
            if (optionalExtras.length > 0) {
                // Remove existing optional_extras entries
                const keysToRemove = [];
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('optional_extras[')) {
                        keysToRemove.push(key);
                    }
                }
                keysToRemove.forEach(key => formData.delete(key));
                // Add structured optional extras
                optionalExtras.forEach((extra, index) => {
                    formData.append(`optional_extras[${index}][name]`, extra.name);
                    formData.append(`optional_extras[${index}][price]`, extra.price);
                    formData.append(`optional_extras[${index}][type]`, extra.type);
                });
            } else {
            }
            // Debug: Show what pricing-related data is actually in FormData
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('optional_extras[') || 
                    key === 'base_price' || 
                    key === 'child_price' || 
                    key === 'commission_rate') {
                }
            }
        } catch (error) {
            console.error('Error including pricing data:', error);
        }
    }
    // Ensure step 5 checkboxes and final confirmation data is included
    ensureStep5DataIncluded(formData) {
        try {
            // List of step 5 checkboxes and their corresponding form field names
            const step5Fields = [
                { elementId: 'agreeTerms', fieldName: 'terms_accepted' },
                { elementId: 'agreeCommission', fieldName: 'final_confirmation' },
                { elementId: 'agreeMarketing', fieldName: 'marketing_consent' }
            ];
            step5Fields.forEach(field => {
                const element = document.getElementById(field.elementId);
                if (element) {
                    // Remove existing entry to avoid duplicates
                    if (formData.has(field.fieldName)) {
                        formData.delete(field.fieldName);
                    }
                    if (element.checked) {
                        formData.append(field.fieldName, '1');
                        console.log(`✅ ${field.fieldName}: 1 (checked)`);
                    } else {
                    }
                } else {
                    console.warn(`⚠️ Element not found: ${field.elementId}`);
                }
            });
            // Also ensure publish status is included
            const publishStatusRadio = document.querySelector('input[name="publish_status"]:checked');
            if (publishStatusRadio) {
                if (formData.has('publish_status')) {
                    formData.delete('publish_status');
                }
                formData.append('publish_status', publishStatusRadio.value);
            }
        } catch (error) {
            console.error('Error including step 5 data:', error);
        }
    }
    async submitForm() {
        // Validate all steps
        for (let step = 1; step <= this.options.stepsCount; step++) {
            const stepElement = document.getElementById(`step${step}`);
            if (stepElement) {
                const isValid = await this.validateStep(step);
                if (!isValid) {
                    this.goToStep(step);
                    this.showError(`Please fix errors in Step ${step} before submitting`);
                    return;
                }
            }
        }
        // Submit the form with all data from all steps (including activities, providers, pricing)
        const formData = this.gatherAllFormData();
        // Debug: Check what's actually being submitted
        for (let [key, value] of formData.entries()) {
            if (key === 'terms_accepted' || key === 'final_confirmation' || key === 'marketing_consent') {
            } else if (key.startsWith('activities[')) {
            } else {
            }
        }
        this.emit('submit', formData);
    }
    async validateStep(stepNumber) {
        const currentStep = this.currentStep;
        this.currentStep = stepNumber;
        const isValid = await this.validateCurrentStep();
        this.currentStep = currentStep;
        return isValid;
    }
    exitWizard() {
        if (this.isDirty) {
            if (confirm('You have unsaved changes. Do you want to save them before exiting?')) {
                this.saveDraft().then(() => {
                    this.performExit();
                });
            } else if (confirm('Are you sure you want to exit without saving?')) {
                this.performExit();
            }
        } else {
            this.performExit();
        }
    }
    performExit() {
        this.stopAutoSave();
        // Navigate back to packages list or dashboard
        window.location.href = '/b2b/travel-agent/packages';
    }
    // Event system
    on(event, callback) {
        if (!this.eventCallbacks[event]) {
            this.eventCallbacks[event] = [];
        }
        this.eventCallbacks[event].push(callback);
    }
    emit(event, data) {
        if (this.eventCallbacks[event]) {
            this.eventCallbacks[event].forEach(callback => {
                callback(data);
            });
        }
    }
    // Utility methods for UI feedback
    showSaving() {
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.disabled = true;
            saveDraftBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        }
    }
    hideSaving() {
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.disabled = false;
            saveDraftBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Draft';
        }
    }
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    showError(message) {
        this.showNotification(message, 'error');
    }
    showNotification(message, type) {
        // Use toastr if available, otherwise create simple alert
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            // Create simple notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.parentElement.removeChild(notification);
                }
            }, 5000);
        }
    }
}
// Make PackageWizard globally available
window.PackageWizard = PackageWizard;
