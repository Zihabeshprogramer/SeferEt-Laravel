/**
 * Enhanced Package Creation Page Script
 * 
 * This script ensures proper draft saving and loading for all form data
 * including pricing information and optional extras.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the enhanced package wizard
    if (typeof PackageWizard !== 'undefined' && document.getElementById('packageWizardForm')) {
        // Configure the package wizard with proper routes
        const wizardOptions = {
            formId: 'packageWizardForm',
            stepsCount: 5,
            autoSave: true,
            autoSaveInterval: 30000, // Save draft every 30 seconds
            apiRoutes: {
                validateStep: window.packageRoutes?.validateStep || '/b2b/travel-agent/packages/validate-step',
                saveDraft: window.packageRoutes?.saveDraft || '/b2b/travel-agent/packages/save-draft'
            }
        };
        // Initialize the wizard
        window.packageWizard = new PackageWizard(wizardOptions);
        // Load draft data if available
        if (window.draftData && typeof window.draftData === 'object') {
            setTimeout(() => {
                window.packageWizard.loadDraftData(window.draftData);
            }, 500); // Small delay to ensure DOM is ready
        }
        // Enhanced event listeners for better draft persistence
        setupEnhancedDraftListeners();
    }
});
function setupEnhancedDraftListeners() {
    // Listen for step changes to save current progress
    if (window.packageWizard) {
        window.packageWizard.on('stepChanged', function(stepNumber) {
            window.packageWizard.saveDraft();
        });
        // Listen for successful draft saves
        window.packageWizard.on('draftSaved', function(result) {
            // Update any UI indicators if needed
            showDraftSavedIndicator();
        });
        // Listen for draft load completion
        window.packageWizard.on('draftLoaded', function(draftData) {
            // Trigger any necessary UI updates after draft load
            setTimeout(() => {
                // Trigger pricing preview update if on step 4
                if (typeof updatePricingPreview === 'function') {
                    updatePricingPreview();
                }
                // Update any other step-specific UI elements
                updateStepSpecificUI();
            }, 200);
        });
    }
    // Enhanced auto-save for critical user actions
    setupCriticalActionListeners();
}
function setupCriticalActionListeners() {
    // Save draft when user adds or removes optional extras
    const addExtraBtn = document.getElementById('addExtraBtn');
    if (addExtraBtn) {
        addExtraBtn.addEventListener('click', function() {
            setTimeout(() => {
                if (window.packageWizard) {
                    window.packageWizard.markDirty();
                }
            }, 100);
        });
    }
    // Listen for optional extra removals
    document.addEventListener('click', function(e) {
        if (e.target.closest('button[onclick*="removeExtra"]')) {
            setTimeout(() => {
                if (window.packageWizard) {
                    window.packageWizard.markDirty();
                }
            }, 100);
        }
    });
    // Save draft when provider selections change
    document.addEventListener('providerSelected', function() {
        if (window.packageWizard) {
            window.packageWizard.markDirty();
            setTimeout(() => {
                window.packageWizard.saveDraft();
            }, 1000);
        }
    });
    document.addEventListener('providerRemoved', function() {
        if (window.packageWizard) {
            window.packageWizard.markDirty();
            setTimeout(() => {
                window.packageWizard.saveDraft();
            }, 1000);
        }
    });
}
function showDraftSavedIndicator() {
    // Show a subtle indicator that draft was saved
    const indicator = document.getElementById('draftSavedIndicator');
    if (indicator) {
        indicator.style.display = 'inline-block';
        indicator.style.opacity = '1';
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 300);
        }, 2000);
    }
}
function updateStepSpecificUI() {
    const currentStep = window.packageWizard?.currentStep || 1;
    switch (currentStep) {
        case 2:
            // Update itinerary UI if needed
            if (typeof refreshItineraryDisplay === 'function') {
                refreshItineraryDisplay();
            }
            break;
        case 3:
            // Update provider selection UI
            if (typeof refreshProviderSelections === 'function') {
                refreshProviderSelections();
            }
            break;
        case 4:
            // Update pricing calculations
            if (typeof updatePricingPreview === 'function') {
                updatePricingPreview();
            }
            // Show/hide deposit field based on checkbox state
            const requiresDepositCheckbox = document.getElementById('requires_deposit');
            const depositField = document.getElementById('depositAmountField');
            if (requiresDepositCheckbox && depositField) {
                depositField.style.display = requiresDepositCheckbox.checked ? 'block' : 'none';
            }
            break;
    }
}
// Global utility functions for manual draft operations
window.saveDraftManually = function() {
    if (window.packageWizard) {
        window.packageWizard.saveDraft();
    } else {
        console.warn('⚠️ Package wizard not initialized');
    }
};
window.loadDraftManually = function(draftData) {
    if (window.packageWizard && draftData) {
        window.packageWizard.loadDraftData(draftData);
    } else {
        console.warn('⚠️ Package wizard not initialized or no draft data provided');
    }
};
// Auto-save before page unload
window.addEventListener('beforeunload', function() {
    if (window.packageWizard && window.packageWizard.isDirty) {
        // Attempt a synchronous save (might not complete, but try anyway)
        navigator.sendBeacon(
            window.packageWizard.options.apiRoutes.saveDraft,
            window.packageWizard.gatherAllFormData()
        );
    }
});
