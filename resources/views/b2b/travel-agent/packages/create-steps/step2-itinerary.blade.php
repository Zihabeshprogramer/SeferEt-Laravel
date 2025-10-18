<!-- Step 2: Itinerary Builder -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-map-marked-alt text-success me-2"></i>
            Itinerary Builder
        </h5>
        <p class="text-muted mb-0 small">Plan your day-by-day activities and create a detailed itinerary</p>
    </div>
    
    <!-- Step Description -->
    <div class="step-description mx-4 mt-4">
        <h6><i class="fas fa-route me-1"></i> Build Your Itinerary</h6>
        <p class="mb-0 small">Create activities for each day of your trip. The duration is automatically calculated from the dates you selected in Step 1.</p>
    </div>
    
    <div class="card-body p-4">
        <!-- Itinerary Header Info -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-card bg-primary text-white p-3 rounded">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Duration</h6>
                            <span id="itineraryDuration">{{ old('duration_days', $draft->duration_days ?? 7) }} Days</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card bg-success text-white p-3 rounded">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list-check fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Activities</h6>
                            <span id="totalActivities">0 Planned</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card bg-warning text-white p-3 rounded">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Status</h6>
                            <span id="itineraryStatus">Planning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Activity Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Daily Activities</h6>
            <div>
                <button type="button" class="btn btn-outline-info btn-sm me-2" onclick="debugFormDetection()">
                    <i class="fas fa-bug me-1"></i> Debug Form
                </button>
                <button type="button" class="btn btn-primary" id="addActivityBtn">
                    <i class="fas fa-plus me-1"></i> Add Activity
                </button>
            </div>
        </div>

        <!-- Activities List -->
        <div id="activitiesList" class="activities-container">
            <!-- Activities will be populated here -->
            <div class="empty-state text-center py-5" id="emptyState">
                <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No Activities Planned Yet</h6>
                <p class="text-muted">Click "Add Activity" to start building your itinerary</p>
            </div>
        </div>

        <!-- Help Text -->
        <div class="alert alert-info border-0 mt-4" role="alert">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Itinerary Tips</h6>
            <ul class="mb-0 small">
                <li>Plan activities for each day to create a comprehensive travel experience</li>
                <li>Include timing, locations, and descriptions for each activity</li>
                <li>You can edit, delete, or reorder activities anytime</li>
                <li>Consider travel time between locations when planning</li>
            </ul>
        </div>
    </div>
</div>

<!-- Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    <span id="modalTitle">Add New Activity</span>
                </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModalDirectly()"></button>
            </div>
            <div class="modal-body">
                <form id="activityForm">
                    <input type="hidden" id="activityId" name="activity_id">
                    <input type="hidden" id="activityIndex" name="activity_index">
                    
                    <div class="row">
                        <!-- Day Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="dayNumber" class="form-label fw-bold">
                                <i class="fas fa-calendar-day me-1"></i> Day <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="dayNumber" name="day_number" required>
                                <!-- Days will be populated by JavaScript based on duration -->
                            </select>
                            <small class="form-text text-muted">Select which day this activity takes place</small>
                        </div>
                        
                        <!-- Activity Category -->
                        <div class="col-md-6 mb-3">
                            <label for="activityCategory" class="form-label fw-bold">
                                <i class="fas fa-tag me-1"></i> Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="activityCategory" name="category" required>
                                <option value="">Select category...</option>
                                <option value="religious">🕌 Religious</option>
                                <option value="cultural">🏛️ Cultural</option>
                                <option value="educational">📚 Educational</option>
                                <option value="recreational">🎯 Recreational</option>
                                <option value="shopping">🛍️ Shopping</option>
                                <option value="dining">🍽️ Dining</option>
                                <option value="transport">🚌 Transport</option>
                                <option value="accommodation">🏨 Accommodation</option>
                                <option value="free_time">⏰ Free Time</option>
                                <option value="group">👥 Group Activity</option>
                                <option value="individual">👤 Individual Activity</option>
                                <option value="optional">❓ Optional</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Activity Name -->
                    <div class="mb-3">
                        <label for="activityName" class="form-label fw-bold">
                            <i class="fas fa-edit me-1"></i> Activity Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="activityName" name="activity_name" 
                               placeholder="e.g., Perform Umrah with Guide, Visit Masjid al-Haram, Historical Tour..." required>
                        <small class="form-text text-muted">Enter a clear, descriptive name for this activity</small>
                    </div>
                    
                    <!-- Activity Description -->
                    <div class="mb-3">
                        <label for="activityDescription" class="form-label fw-bold">
                            <i class="fas fa-align-left me-1"></i> Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="activityDescription" name="description" rows="4" 
                                  placeholder="Describe the activity in detail - what will participants do, see, or experience..." required></textarea>
                        <small class="form-text text-muted">Provide detailed information about what this activity involves</small>
                    </div>
                    
                    <div class="row">
                        <!-- Location -->
                        <div class="col-md-6 mb-3">
                            <label for="activityLocation" class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt me-1"></i> Location
                            </label>
                            <input type="text" class="form-control" id="activityLocation" name="location" 
                                   placeholder="e.g., Masjid al-Haram, Old City, Hotel Lobby...">
                            <small class="form-text text-muted">Where will this activity take place?</small>
                        </div>
                        
                        <!-- Duration -->
                        <div class="col-md-6 mb-3">
                            <label for="activityDuration" class="form-label fw-bold">
                                <i class="fas fa-clock me-1"></i> Duration (Hours)
                            </label>
                            <input type="number" class="form-control" id="activityDuration" name="duration_hours" 
                                   min="0.5" max="24" step="0.5" placeholder="2.5">
                            <small class="form-text text-muted">How long will this activity take?</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Start Time -->
                        <div class="col-md-6 mb-3">
                            <label for="startTime" class="form-label fw-bold">
                                <i class="fas fa-play-circle me-1"></i> Start Time
                            </label>
                            <input type="time" class="form-control" id="startTime" name="start_time">
                            <small class="form-text text-muted">When does this activity start?</small>
                        </div>
                        
                        <!-- End Time -->
                        <div class="col-md-6 mb-3">
                            <label for="endTime" class="form-label fw-bold">
                                <i class="fas fa-stop-circle me-1"></i> End Time
                            </label>
                            <input type="time" class="form-control" id="endTime" name="end_time">
                            <small class="form-text text-muted">When does this activity end?</small>
                        </div>
                    </div>
                    
                    <!-- Options -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isIncluded" name="is_included" checked>
                                <label class="form-check-label fw-bold" for="isIncluded">
                                    <i class="fas fa-check-circle text-success me-1"></i> Included in Package
                                </label>
                                <small class="form-text text-muted d-block">Is this activity included in the base price?</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isOptional" name="is_optional">
                                <label class="form-check-label fw-bold" for="isOptional">
                                    <i class="fas fa-question-circle text-warning me-1"></i> Optional Activity
                                </label>
                                <small class="form-text text-muted d-block">Can participants choose to skip this activity?</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Cost (shown when not included) -->
                    <div class="mb-3" id="additionalCostSection" style="display: none;">
                        <label for="additionalCost" class="form-label fw-bold">
                            <i class="fas fa-dollar-sign me-1"></i> Additional Cost
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="additionalCost" name="additional_cost" 
                                   min="0" step="0.01" placeholder="0.00">
                            <span class="input-group-text">{{ old('currency', $draft->currency ?? 'USD') }}</span>
                        </div>
                        <small class="form-text text-muted">Cost per person for this activity</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModalDirectly()">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveActivityBtn">
                    <i class="fas fa-save me-1"></i> Save Activity
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize itinerary builder if we're on step 2 or the elements exist
    const step2Content = document.getElementById('step2');
    const addActivityBtn = document.getElementById('addActivityBtn');
    
    // Check if we're actually on step 2 and the elements exist
    if (step2Content && addActivityBtn && (step2Content.style.display !== 'none' || addActivityBtn.offsetParent !== null)) {
        initializeItineraryBuilder();
    } else {

        
        // Set up observer to initialize when step 2 becomes visible
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const step2 = document.getElementById('step2');
                    if (step2 && step2.style.display !== 'none') {
                        const addBtn = document.getElementById('addActivityBtn');
                        if (addBtn && !addBtn.hasAttribute('data-initialized')) {

                            initializeItineraryBuilder();
                            observer.disconnect(); // Stop observing once initialized
                        }
                    }
                }
            });
        });
        
        // Observe step 2 container for visibility changes
        if (step2Content) {
            observer.observe(step2Content, { attributes: true, attributeFilter: ['style'] });
        }
    }
});

function initializeItineraryBuilder() {
    // Check if already initialized
    const addActivityBtn = document.getElementById('addActivityBtn');
    if (addActivityBtn && addActivityBtn.hasAttribute('data-initialized')) {

        return;
    }
    
    let activities = [];
    let editingIndex = -1;
    
    const activityModalElement = document.getElementById('activityModal');
    const saveActivityBtn = document.getElementById('saveActivityBtn');
    
    // Try multiple ways to find the activity form
    let activityForm = document.getElementById('activityForm');
    if (!activityForm) {
        // Try finding by form element inside modal
        activityForm = document.querySelector('#activityModal form');
    }
    if (!activityForm) {
        // Try finding by form element anywhere
        activityForm = document.querySelector('form[id="activityForm"]');
    }
    if (!activityForm) {
        // Try finding any form with the name attribute
        activityForm = document.querySelector('form[name="activityForm"]');
    }
    if (!activityForm && activityModalElement) {
        // Try searching within the modal element directly
        activityForm = activityModalElement.querySelector('form');
    }
    
    console.log('Comprehensive form search:', {
        byId: document.getElementById('activityForm'),
        byQuerySelector: document.querySelector('#activityModal form'),
        byFormTag: document.querySelector('form'),
        allForms: document.querySelectorAll('form').length,
        modalExists: !!activityModalElement,
        modalStyle: activityModalElement ? window.getComputedStyle(activityModalElement).display : 'null',
        modalChildren: activityModalElement ? activityModalElement.children.length : 0,
        step2Display: document.getElementById('step2') ? document.getElementById('step2').style.display : 'null'
    });
    
    console.log('Itinerary builder initialization:', {
        addActivityBtn: !!addActivityBtn,
        activityModalElement: !!activityModalElement,
        saveActivityBtn: !!saveActivityBtn,
        activityForm: !!activityForm,
        bootstrap: typeof bootstrap !== 'undefined',
        jquery: typeof $ !== 'undefined'
    });
    
    // Check if required elements exist
    if (!addActivityBtn || !activityModalElement || !saveActivityBtn) {
        console.error('Basic required elements for itinerary builder not found:', {
            addActivityBtn: !!addActivityBtn,
            activityModalElement: !!activityModalElement,
            saveActivityBtn: !!saveActivityBtn
        });
        return;
    }
    
    // Special handling for activityForm - retry if not found
    if (!activityForm) {
        console.warn('Activity form not found on first attempt, retrying with delays...');
        
        let retryCount = 0;
        const maxRetries = 5;
        const retryDelays = [100, 250, 500, 1000, 2000]; // Progressive delays
        
        function attemptFormFind() {

            
            const retryForm = document.getElementById('activityForm') || 
                            document.querySelector('#activityModal form') || 
                            document.querySelector('form[id="activityForm"]') ||
                            document.querySelector('form[name="activityForm"]') ||
                            (activityModalElement && activityModalElement.querySelector('form'));
            
            // Detailed diagnostic logging
            const modal = document.getElementById('activityModal');
            const step2 = document.getElementById('step2');
            
            console.log('Retry search results:', {
                attempt: retryCount + 1,
                getElementById: !!document.getElementById('activityForm'),
                querySelector: !!document.querySelector('#activityModal form'),
                modalExists: !!activityModalElement,
                step2Visible: step2 && step2.style.display !== 'none',
                modalInnerHTML: modal ? modal.innerHTML.includes('activityForm') : 'no modal',
                allFormsCount: document.querySelectorAll('form').length,
                allFormIds: Array.from(document.querySelectorAll('form')).map(f => f.id || 'no-id'),
                modalBodyExists: !!document.querySelector('#activityModal .modal-body')
            });
            
            if (retryForm) {

                activityForm = retryForm;
                completeInitialization();
                return;
            }
            
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(attemptFormFind, retryDelays[retryCount - 1]);
            } else {
                console.error('Activity form still not found after all retry attempts. Proceeding without form functionality.');
                // Proceed without form - limited functionality
                activityForm = null;
                completeInitialization();
            }
        }
        
        setTimeout(attemptFormFind, retryDelays[0]);
        return; // Exit here and wait for retry
    }
    
    // Complete initialization immediately if form was found
    completeInitialization();
    
    function completeInitialization() {
        console.log('Starting complete initialization with activityForm:', {
            formExists: !!activityForm,
            formId: activityForm ? activityForm.id : 'null',
            formTagName: activityForm ? activityForm.tagName : 'null'
        });
    
    // Check for draft activities and load them
    if (window.draftActivitiesData && Array.isArray(window.draftActivitiesData)) {

        activities = [...window.draftActivitiesData];
        
        // Debug the loaded activities and normalize boolean values
        activities.forEach((activity, index) => {
            console.log(`Draft Activity ${index} (before normalization):`, {
                name: activity.activity_name,
                is_optional: activity.is_optional,
                is_optional_type: typeof activity.is_optional,
                is_included: activity.is_included,
                is_included_type: typeof activity.is_included
            });
            
            // Normalize boolean values (in case they come as strings from database)
            activity.is_optional = activity.is_optional === true || activity.is_optional === 'true' || activity.is_optional === '1';
            activity.is_included = activity.is_included !== false && activity.is_included !== 'false' && activity.is_included !== '0';
            
            console.log(`Draft Activity ${index} (after normalization):`, {
                name: activity.activity_name,
                is_optional: activity.is_optional,
                is_optional_type: typeof activity.is_optional,
                is_included: activity.is_included,
                is_included_type: typeof activity.is_included
            });
        });
        
        // Clear the draft data to prevent double loading
        window.draftActivitiesData = null;
    } else if (window.draftActivitiesForItinerary && Array.isArray(window.draftActivitiesForItinerary)) {

        activities = [...window.draftActivitiesForItinerary];
        
        // Normalize boolean values for fallback case too
        activities.forEach((activity, index) => {
            activity.is_optional = activity.is_optional === true || activity.is_optional === 'true' || activity.is_optional === '1';
            activity.is_included = activity.is_included !== false && activity.is_included !== 'false' && activity.is_included !== '0';
        });
        
        window.draftActivitiesForItinerary = null;
    }
    
    // Make activities array available globally for draft integration
    window.itineraryActivities = activities;
    window.renderActivities = renderActivities;
    
    // Initialize modal with Bootstrap 5
    let activityModal;
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            activityModal = new bootstrap.Modal(activityModalElement);
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            // Fallback to jQuery Bootstrap modal
            activityModal = {
                show: () => $(activityModalElement).modal('show'),
                hide: () => $(activityModalElement).modal('hide')
            };
        } else {
            console.error('Bootstrap Modal not available');
            return;
        }
    } catch (error) {
        console.error('Error initializing modal:', error);
        return;
    }
    
    // Get duration from the previous step - check multiple sources
    let durationDays = 7; // default
    const durationElement = document.getElementById('itineraryDuration');
    if (durationElement) {
        const durationText = durationElement.textContent;
        const match = durationText.match(/\d+/);
        if (match) {
            durationDays = parseInt(match[0]);
        }
    }
    
    // Also check for form inputs
    const durationInput = document.getElementById('duration_days');
    if (durationInput && durationInput.value) {
        durationDays = parseInt(durationInput.value);
    }
    
    // Populate day selection dropdown
    populateDayOptions(durationDays);
    
    // Add Activity button click
    addActivityBtn.addEventListener('click', function() {

        openActivityModal();
    });
    
    // Save Activity button click
    saveActivityBtn.addEventListener('click', function() {
        saveActivity();
    });
    
    // Handle included checkbox change
    const isIncludedCheckbox = document.getElementById('isIncluded');
    if (isIncludedCheckbox) {
        isIncludedCheckbox.addEventListener('change', function() {
            const additionalCostSection = document.getElementById('additionalCostSection');
            const additionalCostInput = document.getElementById('additionalCost');
            
            if (additionalCostSection && additionalCostSection.style) {
                if (this.checked) {
                    additionalCostSection.style.display = 'none';
                    if (additionalCostInput) {
                        additionalCostInput.value = '';
                    }
                } else {
                    additionalCostSection.style.display = 'block';
                }
            }
        });
    } else {
        console.warn('isIncluded checkbox not found during initialization');
    }
    
    function populateDayOptions(days) {
        const daySelect = document.getElementById('dayNumber');
        daySelect.innerHTML = '';
        
        for (let i = 1; i <= days; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Day ${i}`;
            daySelect.appendChild(option);
        }
    }
    
    function openActivityModal(activity = null, index = -1) {

        
        // Try to find the form again if it wasn't found during initialization
        if (!activityForm) {

            activityForm = document.getElementById('activityForm') || 
                          document.querySelector('#activityModal form') || 
                          (activityModalElement && activityModalElement.querySelector('form'));
            
            if (activityForm) {

            } else {
                console.warn('Activity form still not found during modal open');
            }
        }
        
        editingIndex = index;
        const modalTitle = document.getElementById('modalTitle');
        
        // Always reset the form first to ensure it's empty
        if (activityForm) {
            activityForm.reset();
        }
        
        // Reset all form elements manually as well
        const formElements = {
            'dayNumber': '',
            'activityCategory': '',
            'activityName': '',
            'activityDescription': '',
            'activityLocation': '',
            'activityDuration': '',
            'startTime': '',
            'endTime': '',
            'additionalCost': ''
        };
        
        Object.keys(formElements).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = formElements[id];
            }
        });
        
        // Reset checkboxes
        const isIncludedCheckbox = document.getElementById('isIncluded');
        const isOptionalCheckbox = document.getElementById('isOptional');
        const additionalCostSection = document.getElementById('additionalCostSection');
        
        if (isIncludedCheckbox) {
            isIncludedCheckbox.checked = true;
        }
        if (isOptionalCheckbox) {
            isOptionalCheckbox.checked = false;
        }
        if (additionalCostSection && additionalCostSection.style) {
            additionalCostSection.style.display = 'none';
        }
        
        // Only populate if editing existing activity
        if (activity && index >= 0) {
            // Editing existing activity
            if (modalTitle) modalTitle.textContent = 'Edit Activity';
            populateForm(activity);
        } else {
            // Adding new activity - form is already empty
            if (modalTitle) modalTitle.textContent = 'Add New Activity';
        }
        

        if (activityModal) {
            try {
                activityModal.show();
                console.log('Modal show() called successfully');
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback: try direct DOM manipulation
                tryDirectModalShow();
            }
        } else {
            console.error('activityModal is null or undefined');
            // Fallback: try direct DOM manipulation
            tryDirectModalShow();
        }
    }
    
    function tryDirectModalShow() {

        const modal = document.getElementById('activityModal');
        if (modal && modal.style) {
            try {
                modal.style.display = 'block';
                modal.classList.add('show');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                modal.removeAttribute('aria-hidden');

            } catch (error) {
                console.error('Error setting modal styles:', error);
                return;
            }
            
            // Add backdrop
            try {
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    if (document.body) {
                        document.body.appendChild(backdrop);

                    }
                }
            } catch (error) {
                console.error('Error adding backdrop:', error);
            }
            
            // Add body class
            try {
                if (document.body && document.body.classList) {
                    document.body.classList.add('modal-open');

                }
            } catch (error) {
                console.error('Error adding body class:', error);
            }
            

        } else {
            console.error('Modal element not found or missing style property for direct show');
        }
    }
    
    function closeModalDirectly() {

        const modal = document.getElementById('activityModal');
        if (modal && modal.style) {
            try {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                modal.removeAttribute('aria-modal');
                modal.removeAttribute('role');

            } catch (error) {
                console.error('Error updating modal styles:', error);
            }
            
            // Remove backdrop
            try {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop && backdrop.remove) {
                    backdrop.remove();

                }
            } catch (error) {
                console.error('Error removing backdrop:', error);
            }
            
            // Remove body class
            try {
                if (document.body && document.body.classList) {
                    document.body.classList.remove('modal-open');

                }
            } catch (error) {
                console.error('Error removing body class:', error);
            }
        } else {
            console.error('Modal element not found or missing style property');
        }
    }
    
    function populateForm(activity) {

        
        // Safely populate form fields
        const fields = [
            { id: 'dayNumber', value: activity.day_number },
            { id: 'activityCategory', value: activity.category },
            { id: 'activityName', value: activity.activity_name },
            { id: 'activityDescription', value: activity.description },
            { id: 'activityLocation', value: activity.location || '' },
            { id: 'activityDuration', value: activity.duration_hours || '' },
            { id: 'startTime', value: activity.start_time || '' },
            { id: 'endTime', value: activity.end_time || '' },
            { id: 'additionalCost', value: activity.additional_cost || '' }
        ];
        
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.value = field.value;
            } else {
                console.warn(`Form field ${field.id} not found`);
            }
        });
        
        // Handle checkboxes
        const isIncludedCheckbox = document.getElementById('isIncluded');
        const isOptionalCheckbox = document.getElementById('isOptional');
        
        if (isIncludedCheckbox) {
            // Default to true if not specified, only false if explicitly false
            isIncludedCheckbox.checked = activity.is_included !== false;
        }
        
        if (isOptionalCheckbox) {
            // Only check if explicitly true, otherwise false
            isOptionalCheckbox.checked = activity.is_optional === true;
        }
        
        // Show/hide additional cost section
        if (!activity.is_included) {
            const additionalCostSection = document.getElementById('additionalCostSection');
            if (additionalCostSection && additionalCostSection.style) {
                additionalCostSection.style.display = 'block';
            }
        }
    }
    
    function saveActivity() {

        
        // Try to find the form directly if it wasn't found during initialization
        if (!activityForm) {

            activityForm = document.getElementById('activityForm') || 
                          document.querySelector('#activityModal form') || 
                          document.querySelector('form[id="activityForm"]');
            
            console.log('Direct form search result:', {
                form: !!activityForm,
                byId: !!document.getElementById('activityForm'),
                bySelector: !!document.querySelector('#activityModal form'),
                modalExists: !!document.getElementById('activityModal')
            });
        }
        
        if (!activityForm) {
            console.error('Cannot save activity: form still not found after direct search');
            
            // Try to collect form data directly from form elements as last resort

            const dayNumber = document.getElementById('dayNumber');
            const activityName = document.getElementById('activityName');
            const activityDescription = document.getElementById('activityDescription');
            
            console.log('Direct element access:', {
                dayNumber: !!dayNumber,
                activityName: !!activityName,
                activityDescription: !!activityDescription,
                dayValue: dayNumber ? dayNumber.value : 'null',
                nameValue: activityName ? activityName.value : 'null'
            });
            
            if (dayNumber && activityName && activityDescription) {

                collectFormDataDirectly();
                return;
            }
            
            alert('Form not available. Please refresh the page and try again.');
            return;
        }
        
        const formData = new FormData(activityForm);
        const activity = createActivityFromFormData(formData);
        processActivity(activity);
    }
    
    function createActivityFromFormData(formData) {
        return {
            day_number: parseInt(formData.get('day_number')),
            category: formData.get('category'),
            activity_name: formData.get('activity_name'),
            description: formData.get('description'),
            location: formData.get('location'),
            duration_hours: parseFloat(formData.get('duration_hours')) || null,
            start_time: formData.get('start_time'),
            end_time: formData.get('end_time'),
            is_included: formData.get('is_included') === 'on',
            is_optional: formData.get('is_optional') === 'on',
            additional_cost: parseFloat(formData.get('additional_cost')) || 0
        };
    }
    
    function collectFormDataDirectly() {

        
        const activity = {
            day_number: parseInt(document.getElementById('dayNumber').value),
            category: document.getElementById('activityCategory').value,
            activity_name: document.getElementById('activityName').value,
            description: document.getElementById('activityDescription').value,
            location: document.getElementById('activityLocation').value || '',
            duration_hours: parseFloat(document.getElementById('activityDuration').value) || null,
            start_time: document.getElementById('startTime').value || '',
            end_time: document.getElementById('endTime').value || '',
            is_included: document.getElementById('isIncluded').checked,
            is_optional: document.getElementById('isOptional').checked,
            additional_cost: parseFloat(document.getElementById('additionalCost').value) || 0
        };
        

        processActivity(activity);
    }
    
    function processActivity(activity) {
        
        console.log('Activity data being saved:', {
            activity_name: activity.activity_name,
            is_optional: activity.is_optional,
            is_optional_type: typeof activity.is_optional,
            is_included: activity.is_included,
            is_included_type: typeof activity.is_included
        });
        
        // Validate required fields
        if (!activity.activity_name || !activity.description || !activity.category) {
            alert('Please fill in all required fields');
            return;
        }
        
        if (editingIndex >= 0) {
            // Update existing activity
            activities[editingIndex] = activity;

        } else {
            // Add new activity
            activities.push(activity);

        }
        
        renderActivities();
        updateActivityCounter();
        
        // Update hidden form inputs for draft saving
        updateHiddenInputs();
        
        // Store activities in global variables for draft saving
        window.itineraryActivities = activities;
        window.draftActivitiesData = activities;
        window.draftActivitiesForItinerary = activities;
        
        // Mark wizard as dirty to trigger auto-save
        if (window.packageWizard && typeof window.packageWizard.markDirty === 'function') {
            window.packageWizard.markDirty();
        }
        
        // Close modal - try both methods
        if (activityModal) {
            try {
                activityModal.hide();

            } catch (error) {
                console.error('Error hiding modal with Bootstrap:', error);
                closeModalDirectly();
            }
        } else {
            closeModalDirectly();
        }
        
        // Reset form
        if (activityForm) {
            activityForm.reset();
        }

    }
    
    function renderActivities() {
        const activitiesList = document.getElementById('activitiesList');
        const emptyState = document.getElementById('emptyState');
        
        if (activities.length === 0) {
            if (emptyState && emptyState.style) {
                emptyState.style.display = 'block';
            }
            return;
        }
        
        if (emptyState && emptyState.style) {
            emptyState.style.display = 'none';
        }
        
        // Group activities by day
        const activitiesByDay = {};
        activities.forEach((activity, index) => {
            const day = activity.day_number;
            if (!activitiesByDay[day]) {
                activitiesByDay[day] = [];
            }
            activitiesByDay[day].push({ ...activity, index });
        });
        
        let html = '';
        for (let day = 1; day <= durationDays; day++) {
            const dayActivities = activitiesByDay[day] || [];
            html += renderDaySection(day, dayActivities);
        }
        
        activitiesList.innerHTML = html;
        
        // Add event listeners to buttons
        addActivityEventListeners();
        
        // Update hidden form inputs
        updateHiddenInputs();
    }
    
    function renderDaySection(day, dayActivities) {
        let html = `
            <div class="day-section mb-4">
                <div class="day-header d-flex justify-content-between align-items-center p-3 bg-light rounded">
                    <h6 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Day ${day}</h6>
                    <span class="badge bg-primary">${dayActivities.length} Activities</span>
                </div>
        `;
        
        if (dayActivities.length === 0) {
            html += `
                <div class="day-empty text-center py-4 text-muted">
                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                    <p class="mb-0">No activities planned for this day</p>
                </div>
            `;
        } else {
            dayActivities.forEach(activity => {
                html += renderActivityCard(activity);
            });
        }
        
        html += '</div>';
        return html;
    }
    
    function renderActivityCard(activity) {
        const categoryIcons = {
            'religious': '🕌',
            'cultural': '🏛️',
            'educational': '📚',
            'recreational': '🎯',
            'shopping': '🛍️',
            'dining': '🍽️',
            'transport': '🚌',
            'accommodation': '🏨',
            'free_time': '⏰',
            'group': '👥',
            'individual': '👤',
            'optional': '❓'
        };
        
        const timeDisplay = activity.start_time && activity.end_time 
            ? `${activity.start_time} - ${activity.end_time}`
            : activity.start_time 
                ? `From ${activity.start_time}`
                : activity.duration_hours
                    ? `${activity.duration_hours}h duration`
                    : 'Time not specified';
        
        return `
            <div class="activity-card card mb-3 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">${categoryIcons[activity.category] || '📋'}</span>
                                <h6 class="mb-0 fw-bold">${activity.activity_name}</h6>
                                ${activity.is_optional === true ? '<span class="badge bg-warning ms-2">Optional</span>' : ''}
                                ${activity.is_included === false ? '<span class="badge bg-info ms-2">Extra Cost</span>' : ''}
                            </div>
                            <p class="text-muted mb-2 small">${activity.description}</p>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="fas fa-clock me-1"></i> ${timeDisplay}
                                ${activity.location ? `<i class="fas fa-map-marker-alt ms-3 me-1"></i> ${activity.location}` : ''}
                                ${!activity.is_included && activity.additional_cost > 0 ? `<i class="fas fa-dollar-sign ms-3 me-1"></i> +${activity.additional_cost}` : ''}
                            </div>
                        </div>
                        <div class="ms-3">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary edit-activity-btn" data-index="${activity.index}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger delete-activity-btn" data-index="${activity.index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function addActivityEventListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-activity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                openActivityModal(activities[index], index);
            });
        });
        
        // Delete buttons
        document.querySelectorAll('.delete-activity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (confirm('Are you sure you want to delete this activity?')) {
                    activities.splice(index, 1);
                    renderActivities();
                    updateActivityCounter();
                    
                    // Update hidden form inputs and global variables
                    updateHiddenInputs();
                    window.itineraryActivities = activities;
                    window.draftActivitiesData = activities;
                    window.draftActivitiesForItinerary = activities;
                    
                    // Mark wizard as dirty
                    if (window.packageWizard && typeof window.packageWizard.markDirty === 'function') {
                        window.packageWizard.markDirty();
                    }
                }
            });
        });
    }
    
    function updateActivityCounter() {
        document.getElementById('totalActivities').textContent = `${activities.length} Planned`;
        document.getElementById('itineraryStatus').textContent = activities.length > 0 ? 'In Progress' : 'Planning';
    }
    
    function updateHiddenInputs() {
        // Remove existing hidden inputs
        const existingInputs = document.querySelectorAll('input[name^="activities["]');
        existingInputs.forEach(input => input.remove());
        
        // Add hidden inputs for each activity
        const form = document.getElementById('packageWizardForm');
        if (form) {
            activities.forEach((activity, index) => {
                Object.keys(activity).forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `activities[${index}][${key}]`;
                    input.value = activity[key];
                    form.appendChild(input);
                });
            });
        }
    }
    
    // Render activities if we loaded from draft
    if (activities.length > 0) {

        renderActivities();
        updateActivityCounter();
        
        // Update hidden form inputs and global variables for draft persistence
        updateHiddenInputs();
        window.itineraryActivities = activities;
        window.draftActivitiesData = activities;
        window.draftActivitiesForItinerary = activities;
    }
    
    // Mark as initialized to prevent multiple initializations
    if (addActivityBtn) {
        addActivityBtn.setAttribute('data-initialized', 'true');

    }
    
    } // End completeInitialization function
}

// Global debug function to help diagnose form detection issues
function debugFormDetection() {

    console.log('Basic form searches:', {
        byId: !!document.getElementById('activityForm'),
        byQuerySelector: !!document.querySelector('#activityModal form'),
        byIdAttribute: !!document.querySelector('form[id="activityForm"]'),
        modalExists: !!document.getElementById('activityModal')
    });
    
    const modal = document.getElementById('activityModal');
    if (modal) {
        console.log('Modal details:', {
            display: window.getComputedStyle(modal).display,
            visibility: window.getComputedStyle(modal).visibility,
            innerHTML: modal.innerHTML.includes('activityForm'),
            forms: modal.querySelectorAll('form').length
        });
        
        const forms = modal.querySelectorAll('form');
        if (forms.length > 0) {
            console.log('Forms in modal:', Array.from(forms).map(f => ({ id: f.id, tagName: f.tagName })));
        }
    }
    
    console.log('All forms on page:', {
        totalCount: document.querySelectorAll('form').length,
        formIds: Array.from(document.querySelectorAll('form')).map(f => f.id || 'no-id')
    });
    
    // Test direct element access
    const testElements = ['dayNumber', 'activityName', 'activityDescription', 'activityCategory'];
    const elementStatus = {};
    testElements.forEach(id => {
        const element = document.getElementById(id);
        elementStatus[id] = {
            exists: !!element,
            value: element ? element.value : 'N/A',
            type: element ? element.type || element.tagName : 'N/A'
        };
    });
    


}
</script>
