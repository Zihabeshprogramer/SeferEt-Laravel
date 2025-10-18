/**
 * Pricing Calculator - JavaScript
 * Handles dynamic pricing calculations, markup management, and cost breakdowns
 */
class PricingCalculator {
    constructor(options = {}) {
        this.options = {
            apiRoutes: {
                calculatePricing: '/api/b2b/travel-agent/providers/calculate-pricing'
            },
            currency: 'USD',
            defaultMarkup: {
                hotels: 15,
                flights: 10,
                transport: 20,
                activities: 25
            },
            ...options
        };
        this.pricingData = {
            basePrice: 0,
            hotels: [],
            flights: [],
            transport: [],
            activities: [],
            addons: [],
            taxes: 0,
            markup: 0,
            commission: 0,
            totalCost: 0,
            sellingPrice: 0,
            profit: 0
        };
        this.isCalculating = false;
    }
    initialize() {
        this.setupPricingForm();
        this.setupMarkupControls();
        this.setupPricingBreakdown();
        this.setupDynamicCalculation();
        this.bindEvents();
    }
    setupPricingForm() {
        const form = document.getElementById('pricingForm');
        if (!form) return;
        // Initialize form fields with default values
        this.loadDefaultMarkups();
    }
    setupMarkupControls() {
        const markupInputs = document.querySelectorAll('.markup-input');
        markupInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.handleMarkupChange(e.target);
            });
        });
        // Setup markup type toggles (fixed amount vs percentage)
        const markupTypes = document.querySelectorAll('.markup-type');
        markupTypes.forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                this.handleMarkupTypeChange(e.target);
            });
        });
    }
    setupPricingBreakdown() {
        this.updatePricingBreakdown();
    }
    setupDynamicCalculation() {
        // Watch for changes in selected providers
        document.addEventListener('providerSelected', (e) => {
            this.handleProviderSelection(e.detail);
        });
        document.addEventListener('providerRemoved', (e) => {
            this.handleProviderRemoval(e.detail);
        });
        document.addEventListener('activityChanged', (e) => {
            this.handleActivityChange(e.detail);
        });
    }
    bindEvents() {
        // Base price input
        const basePriceInput = document.getElementById('basePrice');
        if (basePriceInput) {
            basePriceInput.addEventListener('input', (e) => {
                this.pricingData.basePrice = parseFloat(e.target.value) || 0;
                this.calculateTotals();
            });
        }
        // Addon management
        const addAddonBtn = document.getElementById('addAddonBtn');
        if (addAddonBtn) {
            addAddonBtn.addEventListener('click', () => this.showAddonModal());
        }
        // Recalculate button
        const recalculateBtn = document.getElementById('recalculateBtn');
        if (recalculateBtn) {
            recalculateBtn.addEventListener('click', () => this.recalculateAll());
        }
    }
    loadDefaultMarkups() {
        Object.keys(this.options.defaultMarkup).forEach(type => {
            const input = document.getElementById(`${type}Markup`);
            if (input && !input.value) {
                input.value = this.options.defaultMarkup[type];
            }
        });
    }
    handleMarkupChange(input) {
        const type = input.dataset.type;
        const value = parseFloat(input.value) || 0;
        if (type && this.pricingData[type]) {
            this.pricingData[type].forEach(item => {
                item.markupValue = value;
                this.calculateItemPrice(item, type);
            });
        }
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    handleMarkupTypeChange(toggle) {
        const type = toggle.dataset.type;
        const isPercentage = toggle.value === 'percentage';
        // Update UI to show percentage or fixed amount
        const markupInput = document.getElementById(`${type}Markup`);
        const markupLabel = document.querySelector(`[for="${type}Markup"]`);
        if (markupInput && markupLabel) {
            if (isPercentage) {
                markupLabel.textContent = `${type} Markup (%)`;
                markupInput.placeholder = 'Enter percentage';
            } else {
                markupLabel.textContent = `${type} Markup (${this.options.currency})`;
                markupInput.placeholder = 'Enter fixed amount';
            }
        }
        // Recalculate with new markup type
        this.handleMarkupChange(markupInput);
    }
    handleProviderSelection(data) {
        const { type, provider } = data;
        if (!this.pricingData[type]) {
            this.pricingData[type] = [];
        }
        // Add provider to pricing data
        const pricingItem = {
            id: provider.id,
            name: provider.name,
            basePrice: provider.price || 0,
            markupType: 'percentage',
            markupValue: this.options.defaultMarkup[type] || 0,
            finalPrice: 0,
            provider: provider
        };
        this.calculateItemPrice(pricingItem, type);
        this.pricingData[type].push(pricingItem);
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    handleProviderRemoval(data) {
        const { type, providerId } = data;
        if (this.pricingData[type]) {
            this.pricingData[type] = this.pricingData[type].filter(
                item => item.id !== providerId
            );
        }
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    handleActivityChange(data) {
        // Update activities pricing when itinerary changes
        if (window.itineraryBuilder) {
            const activities = window.itineraryBuilder.getActivitiesData();
            this.pricingData.activities = activities
                .filter(activity => !activity.is_included && activity.additional_cost > 0)
                .map(activity => ({
                    id: activity.id,
                    name: activity.title,
                    basePrice: parseFloat(activity.additional_cost) || 0,
                    markupType: 'percentage',
                    markupValue: this.options.defaultMarkup.activities || 0,
                    finalPrice: 0,
                    activity: activity
                }));
            this.pricingData.activities.forEach(item => {
                this.calculateItemPrice(item, 'activities');
            });
        }
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    calculateItemPrice(item, type) {
        if (item.markupType === 'percentage') {
            const markupAmount = item.basePrice * (item.markupValue / 100);
            item.finalPrice = item.basePrice + markupAmount;
        } else {
            item.finalPrice = item.basePrice + item.markupValue;
        }
        return item.finalPrice;
    }
    calculateTotals() {
        let totalCost = this.pricingData.basePrice;
        let totalMarkup = 0;
        // Calculate costs for each category
        ['hotels', 'flights', 'transport', 'activities', 'addons'].forEach(category => {
            if (this.pricingData[category]) {
                this.pricingData[category].forEach(item => {
                    totalCost += item.basePrice;
                    totalMarkup += (item.finalPrice - item.basePrice);
                });
            }
        });
        this.pricingData.totalCost = totalCost;
        this.pricingData.markup = totalMarkup;
        this.pricingData.sellingPrice = totalCost + totalMarkup + this.pricingData.taxes;
        this.pricingData.profit = totalMarkup;
        return this.pricingData;
    }
    updateTotals() {
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    updatePricingDisplay() {
        // Update pricing breakdown display
        this.updatePricingBreakdown();
        this.updateTotalSummary();
        this.updateProfitAnalysis();
    }
    updatePricingBreakdown() {
        const container = document.getElementById('pricingBreakdown');
        if (!container) return;
        let html = `
            <div class="pricing-section">
                <h6>Base Package Cost</h6>
                <div class="pricing-item">
                    <span>Base Price</span>
                    <span>${this.formatCurrency(this.pricingData.basePrice)}</span>
                </div>
            </div>
        `;
        // Hotels breakdown
        if (this.pricingData.hotels.length > 0) {
            html += `<div class="pricing-section">
                <h6>Accommodation</h6>`;
            this.pricingData.hotels.forEach(hotel => {
                html += `
                    <div class="pricing-item">
                        <span>${hotel.name}</span>
                        <span>${this.formatCurrency(hotel.finalPrice)}</span>
                    </div>
                `;
            });
            html += `</div>`;
        }
        // Flights breakdown
        if (this.pricingData.flights.length > 0) {
            html += `<div class="pricing-section">
                <h6>Flights</h6>`;
            this.pricingData.flights.forEach(flight => {
                html += `
                    <div class="pricing-item">
                        <span>${flight.name}</span>
                        <span>${this.formatCurrency(flight.finalPrice)}</span>
                    </div>
                `;
            });
            html += `</div>`;
        }
        // Transport breakdown
        if (this.pricingData.transport.length > 0) {
            html += `<div class="pricing-section">
                <h6>Transportation</h6>`;
            this.pricingData.transport.forEach(transport => {
                html += `
                    <div class="pricing-item">
                        <span>${transport.name}</span>
                        <span>${this.formatCurrency(transport.finalPrice)}</span>
                    </div>
                `;
            });
            html += `</div>`;
        }
        // Activities breakdown
        if (this.pricingData.activities.length > 0) {
            html += `<div class="pricing-section">
                <h6>Optional Activities</h6>`;
            this.pricingData.activities.forEach(activity => {
                html += `
                    <div class="pricing-item">
                        <span>${activity.name}</span>
                        <span>${this.formatCurrency(activity.finalPrice)}</span>
                    </div>
                `;
            });
            html += `</div>`;
        }
        // Addons breakdown
        if (this.pricingData.addons.length > 0) {
            html += `<div class="pricing-section">
                <h6>Additional Services</h6>`;
            this.pricingData.addons.forEach(addon => {
                html += `
                    <div class="pricing-item">
                        <span>${addon.name}</span>
                        <span>${this.formatCurrency(addon.finalPrice)}</span>
                    </div>
                `;
            });
            html += `</div>`;
        }
        container.innerHTML = html;
    }
    updateTotalSummary() {
        const elements = {
            totalCost: document.getElementById('totalCostDisplay'),
            totalMarkup: document.getElementById('totalMarkupDisplay'),
            totalTaxes: document.getElementById('totalTaxesDisplay'),
            sellingPrice: document.getElementById('sellingPriceDisplay')
        };
        if (elements.totalCost) {
            elements.totalCost.textContent = this.formatCurrency(this.pricingData.totalCost);
        }
        if (elements.totalMarkup) {
            elements.totalMarkup.textContent = this.formatCurrency(this.pricingData.markup);
        }
        if (elements.totalTaxes) {
            elements.totalTaxes.textContent = this.formatCurrency(this.pricingData.taxes);
        }
        if (elements.sellingPrice) {
            elements.sellingPrice.textContent = this.formatCurrency(this.pricingData.sellingPrice);
        }
    }
    updateProfitAnalysis() {
        const profitElement = document.getElementById('profitAnalysis');
        if (!profitElement) return;
        const profitMargin = this.pricingData.sellingPrice > 0 
            ? (this.pricingData.profit / this.pricingData.sellingPrice) * 100 
            : 0;
        profitElement.innerHTML = `
            <div class="profit-item">
                <span>Total Profit</span>
                <span class="text-success">${this.formatCurrency(this.pricingData.profit)}</span>
            </div>
            <div class="profit-item">
                <span>Profit Margin</span>
                <span class="text-success">${profitMargin.toFixed(1)}%</span>
            </div>
        `;
    }
    showAddonModal() {
        const modal = document.getElementById('addonModal');
        if (modal) {
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        }
    }
    addAddon(addonData) {
        const addon = {
            id: Date.now(),
            name: addonData.name,
            basePrice: parseFloat(addonData.price) || 0,
            markupType: 'percentage',
            markupValue: parseFloat(addonData.markup) || 0,
            finalPrice: 0,
            description: addonData.description
        };
        this.calculateItemPrice(addon, 'addons');
        this.pricingData.addons.push(addon);
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    removeAddon(addonId) {
        this.pricingData.addons = this.pricingData.addons.filter(
            addon => addon.id !== addonId
        );
        this.calculateTotals();
        this.updatePricingDisplay();
    }
    async recalculateAll() {
        if (this.isCalculating) return;
        try {
            this.isCalculating = true;
            this.showCalculating();
            // Gather all selected providers and activities
            const calculationData = {
                basePrice: this.pricingData.basePrice,
                hotels: this.pricingData.hotels.map(h => h.provider),
                flights: this.pricingData.flights.map(f => f.provider),
                transport: this.pricingData.transport.map(t => t.provider),
                activities: this.pricingData.activities.map(a => a.activity),
                markup: this.getCurrentMarkupSettings()
            };
            const response = await fetch(this.options.apiRoutes.calculatePricing, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(calculationData)
            });
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.updatePricingFromAPI(result.data);
                }
            }
        } catch (error) {
            console.error('Pricing calculation error:', error);
            this.showError('Failed to recalculate pricing');
        } finally {
            this.isCalculating = false;
            this.hideCalculating();
        }
    }
    getCurrentMarkupSettings() {
        return {
            hotels: {
                type: document.querySelector('input[name="hotelsMarkupType"]:checked')?.value || 'percentage',
                value: parseFloat(document.getElementById('hotelsMarkup')?.value) || 0
            },
            flights: {
                type: document.querySelector('input[name="flightsMarkupType"]:checked')?.value || 'percentage',
                value: parseFloat(document.getElementById('flightsMarkup')?.value) || 0
            },
            transport: {
                type: document.querySelector('input[name="transportMarkupType"]:checked')?.value || 'percentage',
                value: parseFloat(document.getElementById('transportMarkup')?.value) || 0
            },
            activities: {
                type: document.querySelector('input[name="activitiesMarkupType"]:checked')?.value || 'percentage',
                value: parseFloat(document.getElementById('activitiesMarkup')?.value) || 0
            }
        };
    }
    updatePricingFromAPI(apiData) {
        // Update pricing data from API response
        if (apiData.breakdown) {
            Object.assign(this.pricingData, apiData.breakdown);
        }
        this.updatePricingDisplay();
    }
    getPricingData() {
        return this.pricingData;
    }
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: this.options.currency
        }).format(amount);
    }
    showCalculating() {
        const btn = document.getElementById('recalculateBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Calculating...';
        }
    }
    hideCalculating() {
        const btn = document.getElementById('recalculateBtn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calculator me-1"></i>Recalculate';
        }
    }
    showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            console.error('Error:', message);
        }
    }
}
// Auto-initialize if pricing container exists
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('pricingContainer')) {
        window.pricingCalculator = new PricingCalculator();
    }
});
