<!-- Step 4: Pricing Configuration -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-dollar-sign text-primary me-2"></i>
            Pricing Configuration
        </h5>
        <p class="text-muted mb-0 small">Set pricing, commissions, and payment terms</p>
    </div>
    
    <div class="card-body p-4">
        <div class="row">
            <!-- Base Price -->
            <div class="col-md-6 mb-4">
                <div class="pricing-section">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-tag text-primary me-2"></i>
                        Base Pricing
                    </h6>
                    
                    <div class="mb-3">
                        <label for="base_price" class="form-label fw-bold">
                            Base Price per Person <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ old('currency', $draft->currency ?? '₺') }}</span>
                            <input type="number" class="form-control" id="base_price" name="base_price" 
                                   step="0.01" min="0" placeholder="0.00" 
                                   value="{{ old('base_price', $draft->base_price ?? '') }}" required>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="child_price" class="form-label fw-bold">
                            Child Price (Ages 2-12)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ old('currency', $draft->currency ?? '₺') }}</span>
                            <input type="number" class="form-control" id="child_price" name="child_price" 
                                   step="0.01" min="0" placeholder="0.00" 
                                   value="{{ old('child_price', $draft->child_price ?? '') }}">
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            Enter a specific child price OR use percentage discount below
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="child_discount_percent" class="form-label fw-bold">
                            Child Discount Percentage
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="child_discount_percent" 
                                   name="child_discount_percent" min="0" max="100" placeholder="0" 
                                   value="{{ old('child_discount_percent', $draft->child_discount_percent ?? '') }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-calculator text-info me-1"></i>
                            Auto-calculated when child price is entered, or enter manually
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="infant_price" class="form-label fw-bold">
                            Infant Price (Under 2)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ old('currency', $draft->currency ?? '₺') }}</span>
                            <input type="number" class="form-control" id="infant_price" name="infant_price" 
                                   step="0.01" min="0" placeholder="0.00" 
                                   value="{{ old('infant_price', $draft->infant_price ?? 0) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="single_supplement" class="form-label fw-bold">
                            Single Room Supplement
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ old('currency', $draft->currency ?? '₺') }}</span>
                            <input type="number" class="form-control" id="single_supplement" 
                                   name="single_supplement" step="0.01" min="0" placeholder="0.00" 
                                   value="{{ old('single_supplement', $draft->single_supplement ?? '') }}">
                        </div>
                        <small class="form-text text-muted">Extra charge for single occupancy</small>
                    </div>
                </div>
            </div>

            <!-- Commission & Terms -->
            <div class="col-md-6 mb-4">
                <div class="pricing-section">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-handshake text-success me-2"></i>
                        Commission & Terms
                    </h6>

                    <div class="mb-3">
                        <label for="commission_rate" class="form-label fw-bold">
                            Commission Rate <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="commission_rate" 
                                   name="commission_rate" min="0" max="50" step="0.1" placeholder="10" 
                                   value="{{ old('commission_rate', $draft->commission_rate ?? 15) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Your commission from B2B partners</small>
                    </div>

                    <div class="mb-3">
                        <label for="payment_terms" class="form-label fw-bold">
                            Payment Terms
                        </label>
                        <select class="form-select" id="payment_terms" name="payment_terms">
                            <option value="full_advance" {{ old('payment_terms', $draft->payment_terms ?? '') == 'full_advance' ? 'selected' : '' }}>Full Payment in Advance</option>
                            <option value="50_advance" {{ old('payment_terms', $draft->payment_terms ?? '') == '50_advance' ? 'selected' : '' }}>50% Advance, 50% Before Travel</option>
                            <option value="30_advance" {{ old('payment_terms', $draft->payment_terms ?? '') == '30_advance' ? 'selected' : '' }}>30% Advance, 70% Before Travel</option>
                            <option value="on_arrival" {{ old('payment_terms', $draft->payment_terms ?? '') == 'on_arrival' ? 'selected' : '' }}>Payment on Arrival</option>
                            <option value="custom" {{ old('payment_terms', $draft->payment_terms ?? '') == 'custom' ? 'selected' : '' }}>Custom Terms</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="cancellation_policy" class="form-label fw-bold">
                            Cancellation Policy
                        </label>
                        <select class="form-select" id="cancellation_policy" name="cancellation_policy">
                            <option value="flexible" {{ old('cancellation_policy', $draft->cancellation_policy ?? '') == 'flexible' ? 'selected' : '' }}>Flexible (Free cancellation up to 24 hours)</option>
                            <option value="moderate" {{ old('cancellation_policy', $draft->cancellation_policy ?? '') == 'moderate' ? 'selected' : '' }}>Moderate (Free cancellation up to 7 days)</option>
                            <option value="strict" {{ old('cancellation_policy', $draft->cancellation_policy ?? '') == 'strict' ? 'selected' : '' }}>Strict (Free cancellation up to 14 days)</option>
                            <option value="super_strict" {{ old('cancellation_policy', $draft->cancellation_policy ?? '') == 'super_strict' ? 'selected' : '' }}>Super Strict (Non-refundable)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="min_booking_days" class="form-label fw-bold">
                            Minimum Booking Notice (Days)
                        </label>
                        <input type="number" class="form-control" id="min_booking_days" 
                               name="min_booking_days" min="0" max="365" placeholder="3" 
                               value="{{ old('min_booking_days', $draft->min_booking_days ?? 3) }}">
                        <small class="form-text text-muted">Minimum days required before travel date</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requires_deposit" 
                                   name="requires_deposit" value="1" 
                                   {{ old('requires_deposit', $draft->requires_deposit ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_deposit">
                                <i class="fas fa-credit-card text-primary me-1"></i>
                                Requires Deposit
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="depositAmountField" style="{{ old('requires_deposit', $draft->requires_deposit ?? false) ? '' : 'display: none;' }}">
                        <label for="deposit_amount" class="form-label fw-bold">
                            Deposit Amount
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ old('currency', $draft->currency ?? '₺') }}</span>
                            <input type="number" class="form-control" id="deposit_amount" 
                                   name="deposit_amount" step="0.01" min="0" placeholder="0.00" 
                                   value="{{ old('deposit_amount', $draft->deposit_amount ?? '') }}">
                        </div>
                        <small class="form-text text-muted">Fixed deposit amount per person</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Calculator Preview -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calculator text-info me-2"></i>
                            Pricing Calculator Preview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="pricing-preview" id="pricingPreview">
                                    <!-- Pricing breakdown will be populated by JavaScript -->
                                    <div class="pricing-breakdown">
                                        <h6>Sample Pricing (2 Adults, 1 Child):</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <ul class="list-unstyled">
                                                    <li class="d-flex justify-content-between">
                                                        <span>Adult Price (<span id="adultCount">x2</span>):</span>
                                                        <span id="adultTotal">₺0.00</span>
                                                    </li>
                                                    <li class="d-flex justify-content-between">
                                                        <span>Child Price (<span id="childCount">x1</span>):</span>
                                                        <span id="childTotal">₺0.00</span>
                                                    </li>
                                                    <li class="d-flex justify-content-between" id="infantRow" style="display: none;">
                                                        <span>Infant Price (<span id="infantCount">x0</span>):</span>
                                                        <span id="infantTotal">₺0.00</span>
                                                    </li>
                                                    <li class="d-flex justify-content-between border-top pt-2 fw-bold">
                                                        <span>Subtotal:</span>
                                                        <span id="subtotal">₺0.00</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <ul class="list-unstyled">
                                                    <li class="d-flex justify-content-between text-success">
                                                        <span>Your Commission:</span>
                                                        <span id="commissionAmount">₺0.00</span>
                                                    </li>
                                                    <li class="d-flex justify-content-between">
                                                        <span>B2B Partner Price:</span>
                                                        <span id="partnerPrice">₺0.00</span>
                                                    </li>
                                                    <li class="d-flex justify-content-between border-top pt-2 fw-bold text-primary">
                                                        <span>Final Total:</span>
                                                        <span id="finalTotal">₺0.00</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="pricing-controls">
                                    <h6>Quick Test:</h6>
                                    <div class="mb-2">
                                        <label class="form-label">Adults:</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               id="testAdults" value="2" min="1" max="10">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Children:</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               id="testChildren" value="1" min="0" max="10">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Infants:</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               id="testInfants" value="0" min="0" max="10">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="updatePricingPreview()">
                                        <i class="fas fa-refresh me-1"></i> Recalculate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optional Extras -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-plus-circle text-warning me-2"></i>
                            Optional Extras & Add-ons
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="optionalExtras">
                            @if(old('optional_extras', $draft->optional_extras ?? []))
                                @foreach(old('optional_extras', $draft->optional_extras ?? []) as $index => $extra)
                                    <div class="optional-extra-item row mb-3" data-index="{{ $index }}">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" 
                                                   name="optional_extras[{{ $index }}][name]" 
                                                   placeholder="Extra name" 
                                                   value="{{ $extra['name'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-text">{{ old('currency', $draft->currency ?? '₺') }}</span>
                                                <input type="number" class="form-control" 
                                                       name="optional_extras[{{ $index }}][price]" 
                                                       placeholder="0.00" step="0.01" min="0" 
                                                       value="{{ $extra['price'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="optional_extras[{{ $index }}][type]">
                                                <option value="per_person" {{ ($extra['type'] ?? '') == 'per_person' ? 'selected' : '' }}>Per Person</option>
                                                <option value="per_group" {{ ($extra['type'] ?? '') == 'per_group' ? 'selected' : '' }}>Per Group</option>
                                                <option value="per_day" {{ ($extra['type'] ?? '') == 'per_day' ? 'selected' : '' }}>Per Day</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                                                    onclick="removeExtra(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addExtra()">
                            <i class="fas fa-plus me-1"></i> Add Optional Extra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden field for total_price validation -->
    <input type="hidden" id="total_price" name="total_price" value="{{ old('total_price', $draft->total_price ?? 0) }}">
</div>

<script>

// Initialize extraIndex globally
window.extraIndex = {{ count(old('optional_extras', $draft->optional_extras ?? [])) }};
let extraIndex = window.extraIndex; // Keep local reference for backward compatibility

document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check what Blade template actually rendered
    const container = document.getElementById('optionalExtras');
    const renderedExtras = container ? container.querySelectorAll('.optional-extra-item') : [];

    
    // Set up a mutation observer to track changes to the container
    if (container && renderedExtras.length > 0) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    const currentExtras = container.querySelectorAll('.optional-extra-item');
                    console.log('🚨 Optional extras container changed!', {
                        type: mutation.type,
                        addedNodes: mutation.addedNodes.length,
                        removedNodes: mutation.removedNodes.length,
                        currentExtras: currentExtras.length,
                        stackTrace: new Error().stack
                    });
                    
                    if (currentExtras.length === 0 && mutation.removedNodes.length > 0) {
                        console.log('⚠️ Optional extras were CLEARED! Stack trace:', new Error().stack);
                    }
                }
            });
        });
        
        observer.observe(container, { childList: true, subtree: true });

        
        // Also check visibility status periodically
        setInterval(() => {
            const currentExtras = container.querySelectorAll('.optional-extra-item');
            if (currentExtras.length > 0) {
                const firstExtra = currentExtras[0];
                const containerStyles = getComputedStyle(container);
                const extraStyles = getComputedStyle(firstExtra);
                const cardBody = container.closest('.card-body');
                const card = container.closest('.card');
                const step4 = document.getElementById('step4');
                
                console.log('🔍 Visibility check:', {
                    extrasCount: currentExtras.length,
                    containerDisplay: containerStyles.display,
                    containerVisibility: containerStyles.visibility,
                    containerOpacity: containerStyles.opacity,
                    extraDisplay: extraStyles.display,
                    extraVisibility: extraStyles.visibility,
                    extraOpacity: extraStyles.opacity,
                    cardBodyDisplay: cardBody ? getComputedStyle(cardBody).display : 'N/A',
                    cardDisplay: card ? getComputedStyle(card).display : 'N/A',
                    step4Display: step4 ? getComputedStyle(step4).display : 'N/A',
                    containerRect: container.getBoundingClientRect(),
                    extraRect: firstExtra.getBoundingClientRect()
                });
            }
        }, 5000); // Check every 5 seconds
    }
    
    // Immediate check of current state
    setTimeout(() => {
        const currentExtras = container ? container.querySelectorAll('.optional-extra-item') : [];
        console.log('🕰️ Immediate visibility check after DOM ready:', {
            containerExists: !!container,
            extrasFound: currentExtras.length,
            containerVisible: container ? (container.offsetWidth > 0 && container.offsetHeight > 0) : false,
            containerDisplay: container ? getComputedStyle(container).display : 'N/A',
            step4Visible: document.getElementById('step4') ? getComputedStyle(document.getElementById('step4')).display : 'N/A'
        });
        
        if (currentExtras.length > 0) {
            const firstExtra = currentExtras[0];
            console.log('🕰️ First extra visibility:', {
                visible: firstExtra.offsetWidth > 0 && firstExtra.offsetHeight > 0,
                display: getComputedStyle(firstExtra).display,
                rect: firstExtra.getBoundingClientRect()
            });
        }
    }, 1000); // Check after 1 second
    
    if (renderedExtras.length > 0) {

        renderedExtras.forEach((extra, index) => {
            const nameInput = extra.querySelector('input[name*="[name]"]');
            const priceInput = extra.querySelector('input[name*="[price]"]');
            const typeSelect = extra.querySelector('select[name*="[type]"]');
            console.log(`🎯 Rendered extra ${index}:`, {
                name: nameInput ? nameInput.value : 'N/A',
                price: priceInput ? priceInput.value : 'N/A', 
                type: typeSelect ? typeSelect.value : 'N/A'
            });
        });
    }
    
    // Handle deposit checkbox
    document.getElementById('requires_deposit').addEventListener('change', function() {
        const depositField = document.getElementById('depositAmountField');
        depositField.style.display = this.checked ? 'block' : 'none';
    });

    // Update pricing preview when any pricing field changes
    const pricingFields = ['base_price', 'child_price', 'child_discount_percent', 'infant_price', 'commission_rate'];
    pricingFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', handlePricingFieldChange);
            field.addEventListener('change', handlePricingFieldChange); // Also on change event
        }
    });
    
    // Set up smart child pricing logic
    setupSmartChildPricing();

    // Initial calculation
    setTimeout(() => {
        updateChildPricingLogic();
        updatePricingPreview();
    }, 100); // Small delay to ensure all fields are loaded
});

function addExtra() {
    const container = document.getElementById('optionalExtras');
    const currency = document.querySelector('input[name="currency"]')?.value || '₺';
    
    // Get the current extraIndex from global scope
    let currentExtraIndex = 0;
    if (typeof window.extraIndex !== 'undefined') {
        currentExtraIndex = window.extraIndex;
    } else if (typeof extraIndex !== 'undefined') {
        currentExtraIndex = extraIndex;
    } else {
        // Count existing extras to determine index
        currentExtraIndex = container.querySelectorAll('.optional-extra-item').length;
    }
    

    
    const extraHTML = `
        <div class="optional-extra-item row mb-3" data-index="${currentExtraIndex}">
            <div class="col-md-4">
                <input type="text" class="form-control" 
                       name="optional_extras[${currentExtraIndex}][name]" 
                       placeholder="Extra name">
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">${currency}</span>
                    <input type="number" class="form-control" 
                           name="optional_extras[${currentExtraIndex}][price]" 
                           placeholder="0.00" step="0.01" min="0">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="optional_extras[${currentExtraIndex}][type]">
                    <option value="per_person">Per Person</option>
                    <option value="per_group">Per Group</option>
                    <option value="per_day">Per Day</option>
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
    
    // Update the global extraIndex
    const newIndex = currentExtraIndex + 1;
    if (typeof window.extraIndex !== 'undefined') {
        window.extraIndex = newIndex;
    } else if (typeof extraIndex !== 'undefined') {
        extraIndex = newIndex;
    } else {
        window.extraIndex = newIndex;
    }
    

}

function removeExtra(button) {
    button.closest('.optional-extra-item').remove();
}

function handlePricingFieldChange(event) {
    const fieldId = event.target.id;
    
    // Handle child pricing logic when base price, child price, or discount changes
    if (['base_price', 'child_price', 'child_discount_percent'].includes(fieldId)) {
        updateChildPricingLogic(fieldId);
    }
    
    // Update the pricing preview
    updatePricingPreview();
}

function setupSmartChildPricing() {
    const basePriceField = document.getElementById('base_price');
    const childPriceField = document.getElementById('child_price');
    const childDiscountField = document.getElementById('child_discount_percent');
    
    // When child price is entered, calculate and update discount percentage
    if (childPriceField) {
        childPriceField.addEventListener('input', function() {
            if (this.value && parseFloat(this.value) > 0) {
                // Child price entered - calculate discount percentage
                const basePrice = parseFloat(basePriceField?.value) || 0;
                if (basePrice > 0) {
                    const childPrice = parseFloat(this.value);
                    const discountPercent = Math.max(0, Math.round(((basePrice - childPrice) / basePrice) * 100 * 100) / 100);
                    
                    if (childDiscountField) {
                        childDiscountField.value = discountPercent;
                        childDiscountField.disabled = true;
                        childDiscountField.style.backgroundColor = '#f8f9fa';
                        childDiscountField.style.cursor = 'not-allowed';
                        
                        // Add calculated indicator
                        addCalculatedIndicator(childDiscountField, 'Auto-calculated from child price');
                    }
                }
            } else {
                // Child price cleared - enable discount percentage
                if (childDiscountField) {
                    childDiscountField.disabled = false;
                    childDiscountField.style.backgroundColor = '';
                    childDiscountField.style.cursor = '';
                    removeCalculatedIndicator(childDiscountField);
                }
            }
        });
    }
    
    // When discount percentage is entered, calculate child price
    if (childDiscountField) {
        childDiscountField.addEventListener('input', function() {
            if (!this.disabled && this.value && parseFloat(this.value) >= 0) {
                const basePrice = parseFloat(basePriceField?.value) || 0;
                if (basePrice > 0) {
                    const discountPercent = parseFloat(this.value);
                    const childPrice = basePrice * (1 - discountPercent / 100);
                    
                    if (childPriceField) {
                        childPriceField.value = Math.max(0, childPrice).toFixed(2);
                        childPriceField.disabled = true;
                        childPriceField.style.backgroundColor = '#f8f9fa';
                        childPriceField.style.cursor = 'not-allowed';
                        
                        // Add calculated indicator
                        addCalculatedIndicator(childPriceField, 'Auto-calculated from discount percentage');
                    }
                }
            } else if (this.value === '' || parseFloat(this.value) === 0) {
                // Discount cleared - enable child price
                if (childPriceField) {
                    childPriceField.disabled = false;
                    childPriceField.style.backgroundColor = '';
                    childPriceField.style.cursor = '';
                    removeCalculatedIndicator(childPriceField);
                }
            }
        });
    }
    
    // When base price changes, recalculate dependent fields
    if (basePriceField) {
        basePriceField.addEventListener('input', function() {
            updateChildPricingLogic('base_price');
        });
    }
}

function updateChildPricingLogic(changedField = '') {
    const basePriceField = document.getElementById('base_price');
    const childPriceField = document.getElementById('child_price');
    const childDiscountField = document.getElementById('child_discount_percent');
    
    const basePrice = parseFloat(basePriceField?.value) || 0;
    const childPrice = parseFloat(childPriceField?.value) || 0;
    const discountPercent = parseFloat(childDiscountField?.value) || 0;
    
    if (basePrice <= 0) {
        // No base price - reset everything
        if (childPriceField) {
            childPriceField.disabled = false;
            childPriceField.style.backgroundColor = '';
            childPriceField.style.cursor = '';
            removeCalculatedIndicator(childPriceField);
        }
        if (childDiscountField) {
            childDiscountField.disabled = false;
            childDiscountField.style.backgroundColor = '';
            childDiscountField.style.cursor = '';
            removeCalculatedIndicator(childDiscountField);
        }
        return;
    }
    
    // Determine which field should be the source of truth
    const hasChildPrice = childPrice > 0;
    const hasDiscountPercent = discountPercent > 0;
    
    if (changedField === 'child_price' || (hasChildPrice && !hasDiscountPercent)) {
        // Child price is the source - calculate discount
        const calculatedDiscount = Math.max(0, Math.round(((basePrice - childPrice) / basePrice) * 100 * 100) / 100);
        
        if (childDiscountField) {
            childDiscountField.value = calculatedDiscount;
            childDiscountField.disabled = true;
            childDiscountField.style.backgroundColor = '#f8f9fa';
            childDiscountField.style.cursor = 'not-allowed';
            addCalculatedIndicator(childDiscountField, 'Auto-calculated from child price');
        }
        
        if (childPriceField) {
            childPriceField.disabled = false;
            childPriceField.style.backgroundColor = '';
            childPriceField.style.cursor = '';
            removeCalculatedIndicator(childPriceField);
        }
        
    } else if (changedField === 'child_discount_percent' || (hasDiscountPercent && !hasChildPrice)) {
        // Discount percentage is the source - calculate child price
        const calculatedChildPrice = basePrice * (1 - discountPercent / 100);
        
        if (childPriceField) {
            childPriceField.value = Math.max(0, calculatedChildPrice).toFixed(2);
            childPriceField.disabled = true;
            childPriceField.style.backgroundColor = '#f8f9fa';
            childPriceField.style.cursor = 'not-allowed';
            addCalculatedIndicator(childPriceField, 'Auto-calculated from discount percentage');
        }
        
        if (childDiscountField) {
            childDiscountField.disabled = false;
            childDiscountField.style.backgroundColor = '';
            childDiscountField.style.cursor = '';
            removeCalculatedIndicator(childDiscountField);
        }
        
    } else if (changedField === 'base_price') {
        // Base price changed - update whichever field is calculated
        if (childDiscountField && childDiscountField.disabled) {
            // Discount is calculated - recalculate it
            const calculatedDiscount = Math.max(0, Math.round(((basePrice - childPrice) / basePrice) * 100 * 100) / 100);
            childDiscountField.value = calculatedDiscount;
        } else if (childPriceField && childPriceField.disabled) {
            // Child price is calculated - recalculate it
            const calculatedChildPrice = basePrice * (1 - discountPercent / 100);
            childPriceField.value = Math.max(0, calculatedChildPrice).toFixed(2);
        }
    }
}

function addCalculatedIndicator(field, tooltip) {
    // Remove existing indicator
    removeCalculatedIndicator(field);
    
    const indicator = document.createElement('span');
    indicator.className = 'calculated-indicator';
    indicator.innerHTML = '<i class="fas fa-calculator text-info ms-2"></i>';
    indicator.title = tooltip;
    indicator.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10;';
    
    // Make parent relative if not already
    const parent = field.parentElement;
    if (parent && getComputedStyle(parent).position === 'static') {
        parent.style.position = 'relative';
    }
    
    parent.appendChild(indicator);
}

function removeCalculatedIndicator(field) {
    const parent = field.parentElement;
    const existingIndicator = parent?.querySelector('.calculated-indicator');
    if (existingIndicator) {
        existingIndicator.remove();
    }
}

function updatePricingPreview() {
    const basePrice = parseFloat(document.getElementById('base_price').value) || 0;
    const childPrice = parseFloat(document.getElementById('child_price').value) || 0;
    const childDiscountPercent = parseFloat(document.getElementById('child_discount_percent').value) || 0;
    const infantPrice = parseFloat(document.getElementById('infant_price').value) || 0;
    const commissionRate = parseFloat(document.getElementById('commission_rate').value) || 15;
    
    const adults = parseInt(document.getElementById('testAdults').value) || 2;
    const children = parseInt(document.getElementById('testChildren').value) || 1;
    const infants = parseInt(document.getElementById('testInfants').value) || 0;
    
    // Calculate effective child price using smart logic
    let effectiveChildPrice = 0;
    if (childPrice > 0) {
        // Use explicit child price
        effectiveChildPrice = childPrice;
    } else if (childDiscountPercent > 0) {
        // Use discount percentage to calculate child price
        effectiveChildPrice = basePrice * (1 - childDiscountPercent / 100);
    } else {
        // Default fallback - 50% discount
        effectiveChildPrice = basePrice * 0.5;
    }
    
    // Calculate totals
    const adultTotal = basePrice * adults;
    const childTotal = Math.max(0, effectiveChildPrice) * children;
    const infantTotal = infantPrice * infants;
    const subtotal = adultTotal + childTotal + infantTotal;
    
    // Calculate commission
    const commissionAmount = subtotal * (commissionRate / 100);
    const partnerPrice = subtotal - commissionAmount;
    
    // Update display
    document.getElementById('adultCount').textContent = `x${adults}`;
    document.getElementById('adultTotal').textContent = `₺${adultTotal.toFixed(2)}`;
    document.getElementById('childCount').textContent = `x${children}`;
    document.getElementById('childTotal').textContent = `₺${childTotal.toFixed(2)}`;
    
    // Handle infants display
    const infantRow = document.getElementById('infantRow');
    if (infants > 0) {
        infantRow.style.display = 'flex';
        document.getElementById('infantCount').textContent = `x${infants}`;
        document.getElementById('infantTotal').textContent = `₺${infantTotal.toFixed(2)}`;
    } else {
        infantRow.style.display = 'none';
    }
    
    document.getElementById('subtotal').textContent = `₺${subtotal.toFixed(2)}`;
    document.getElementById('commissionAmount').textContent = `₺${commissionAmount.toFixed(2)}`;
    document.getElementById('partnerPrice').textContent = `₺${partnerPrice.toFixed(2)}`;
    document.getElementById('finalTotal').textContent = `₺${subtotal.toFixed(2)}`;
    
    // Update hidden total_price field for form validation
    const totalPriceField = document.getElementById('total_price');
    if (totalPriceField) {
        totalPriceField.value = subtotal.toFixed(2);
    }
}
</script>
