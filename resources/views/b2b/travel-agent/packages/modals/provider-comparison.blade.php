<!-- Provider Comparison Modal -->
<div class="modal fade" id="providerComparisonModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-balance-scale me-2"></i>
                    Compare Providers
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Comparison Header -->
                <div class="bg-light p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold" id="comparisonTitle">Comparing 0 providers</span>
                            <small class="text-muted d-block">Side-by-side comparison of selected providers</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="clearComparison()">
                                <i class="fas fa-times me-1"></i> Clear All
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="selectFromComparison()">
                                <i class="fas fa-check me-1"></i> Select Best
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Comparison Table -->
                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                    <table class="table table-bordered mb-0" id="comparisonTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="min-width: 200px;">Provider Details</th>
                                <!-- Dynamic provider columns will be added here -->
                            </tr>
                        </thead>
                        <tbody id="comparisonTableBody">
                            <!-- Comparison rows will be populated here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div id="comparisonEmptyState" class="text-center py-5">
                    <i class="fas fa-balance-scale fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Providers to Compare</h6>
                    <p class="text-muted">Select multiple providers from the search results to compare them side by side</p>
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i> Back to Search
                    </button>
                </div>
            </div>
            
            <div class="modal-footer bg-light">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="comparisonHelp">Compare features, prices, and ratings to make the best choice</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary" onclick="addSelectedFromComparison()" id="addSelectedBtn" disabled>
                            <i class="fas fa-plus me-1"></i> Add Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Provider Comparison System
 * Handles side-by-side comparison of multiple providers
 */
window.ProviderComparison = {
    compareList: [],
    selectedForAddition: [],
    
    // Add provider to comparison
    addToComparison(provider) {
        if (this.compareList.length >= 4) {
            this.showNotification('Maximum 4 providers can be compared at once', 'warning');
            return false;
        }
        
        if (this.compareList.find(p => p.id === provider.id)) {
            this.showNotification('Provider already in comparison', 'info');
            return false;
        }
        
        this.compareList.push(provider);
        this.updateComparisonBadge();
        return true;
    },
    
    // Remove provider from comparison
    removeFromComparison(providerId) {
        this.compareList = this.compareList.filter(p => p.id !== providerId);
        this.updateComparisonBadge();
        this.renderComparison();
    },
    
    // Clear all comparisons
    clearComparison() {
        this.compareList = [];
        this.selectedForAddition = [];
        this.updateComparisonBadge();
        this.renderComparison();
    },
    
    // Open comparison modal
    openComparison() {
        if (this.compareList.length < 2) {
            this.showNotification('Select at least 2 providers to compare', 'warning');
            return;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('providerComparisonModal'));
        modal.show();
        this.renderComparison();
    },
    
    // Render comparison table
    renderComparison() {
        const table = document.getElementById('comparisonTable');
        const tbody = document.getElementById('comparisonTableBody');
        const emptyState = document.getElementById('comparisonEmptyState');
        const title = document.getElementById('comparisonTitle');
        
        if (this.compareList.length === 0) {
            table.style.display = 'none';
            emptyState.style.display = 'block';
            title.textContent = 'Comparing 0 providers';
            return;
        }
        
        table.style.display = 'table';
        emptyState.style.display = 'none';
        title.textContent = `Comparing ${this.compareList.length} providers`;
        
        // Update table headers
        const thead = table.querySelector('thead tr');
        thead.innerHTML = '<th style="min-width: 200px;">Provider Details</th>';
        
        this.compareList.forEach((provider, index) => {
            const th = document.createElement('th');
            th.style.minWidth = '250px';
            th.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <span>${provider.name}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="ProviderComparison.removeFromComparison(${provider.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            thead.appendChild(th);
        });
        
        // Generate comparison rows
        tbody.innerHTML = this.generateComparisonRows();
    },
    
    // Generate comparison table rows
    generateComparisonRows() {
        const rows = [
            { label: 'Selection', type: 'checkbox' },
            { label: 'Provider Image', type: 'image' },
            { label: 'Rating', type: 'rating' },
            { label: 'Price', type: 'price' },
            { label: 'Location', type: 'location' },
            { label: 'Features', type: 'features' },
            { label: 'Availability', type: 'availability' },
            { label: 'Contact', type: 'contact' },
            { label: 'Reviews', type: 'reviews' }
        ];
        
        return rows.map(row => {
            const cells = this.compareList.map(provider => {
                return this.generateComparisonCell(provider, row.type, row.label);
            }).join('');
            
            return `
                <tr>
                    <td class="fw-bold bg-light">${row.label}</td>
                    ${cells}
                </tr>
            `;
        }).join('');
    },
    
    // Generate individual comparison cell
    generateComparisonCell(provider, type, label) {
        switch (type) {
            case 'checkbox':
                return `
                    <td class="text-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="${provider.id}" 
                                   onchange="ProviderComparison.toggleSelection(${provider.id}, this.checked)">
                            <label class="form-check-label small text-muted">Select</label>
                        </div>
                    </td>
                `;
                
            case 'image':
                return `
                    <td class="text-center">
                        <img src="${provider.image || '/images/placeholder-provider.jpg'}" 
                             alt="${provider.name}" class="provider-image">
                    </td>
                `;
                
            case 'rating':
                const stars = '★'.repeat(Math.floor(provider.rating || 0)) + 
                             '☆'.repeat(5 - Math.floor(provider.rating || 0));
                return `
                    <td>
                        <div class="provider-rating">
                            <span class="text-warning me-1">${stars}</span>
                            <small class="text-muted">(${provider.rating || 'N/A'})</small>
                        </div>
                        <small class="text-muted d-block">${provider.review_count || 0} reviews</small>
                    </td>
                `;
                
            case 'price':
                return `
                    <td>
                        <div class="fw-bold text-primary">${provider.price || 'Quote on request'}</div>
                        <small class="text-muted">${provider.price_unit || ''}</small>
                    </td>
                `;
                
            case 'location':
                return `
                    <td>
                        <div>${provider.location || 'Not specified'}</div>
                        ${provider.distance ? `<small class="text-muted">${provider.distance}</small>` : ''}
                    </td>
                `;
                
            case 'features':
                const features = provider.features || [];
                return `
                    <td>
                        ${features.length > 0 ? 
                            features.slice(0, 3).map(f => `<span class="badge bg-light text-dark me-1 mb-1">${f}</span>`).join('') +
                            (features.length > 3 ? `<div class="small text-muted">+${features.length - 3} more</div>` : '')
                            : '<small class="text-muted">No features listed</small>'
                        }
                    </td>
                `;
                
            case 'availability':
                const status = provider.availability_status || 'unknown';
                const badgeClass = status === 'available' ? 'bg-success' : 
                                 status === 'limited' ? 'bg-warning' : 'bg-secondary';
                return `
                    <td>
                        <span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                        ${provider.availability_note ? `<div class="small text-muted mt-1">${provider.availability_note}</div>` : ''}
                    </td>
                `;
                
            case 'contact':
                return `
                    <td>
                        ${provider.phone ? `<div><i class="fas fa-phone fa-sm me-1"></i> ${provider.phone}</div>` : ''}
                        ${provider.email ? `<div><i class="fas fa-envelope fa-sm me-1"></i> ${provider.email}</div>` : ''}
                        ${!provider.phone && !provider.email ? '<small class="text-muted">Contact via platform</small>' : ''}
                    </td>
                `;
                
            case 'reviews':
                return `
                    <td>
                        ${provider.recent_review ? 
                            `<blockquote class="blockquote-footer small">
                                "${provider.recent_review.text}"
                                <cite title="Source Title">${provider.recent_review.author}</cite>
                            </blockquote>` : 
                            '<small class="text-muted">No recent reviews</small>'
                        }
                    </td>
                `;
                
            default:
                return '<td>-</td>';
        }
    },
    
    // Toggle provider selection for addition
    toggleSelection(providerId, isSelected) {
        if (isSelected) {
            if (!this.selectedForAddition.includes(providerId)) {
                this.selectedForAddition.push(providerId);
            }
        } else {
            this.selectedForAddition = this.selectedForAddition.filter(id => id !== providerId);
        }
        
        document.getElementById('addSelectedBtn').disabled = this.selectedForAddition.length === 0;
    },
    
    // Add selected providers from comparison
    addSelectedFromComparison() {
        const selectedProviders = this.compareList.filter(p => this.selectedForAddition.includes(p.id));
        
        selectedProviders.forEach(provider => {
            window.EnhancedProviderSelector.addProvider(provider);
        });
        
        this.showNotification(`Added ${selectedProviders.length} provider(s) to selection`, 'success');
        
        // Close modal and return to main selection
        bootstrap.Modal.getInstance(document.getElementById('providerComparisonModal')).hide();
    },
    
    // Update comparison badge
    updateComparisonBadge() {
        const badge = document.getElementById('comparisonBadge');
        if (badge) {
            if (this.compareList.length > 0) {
                badge.textContent = this.compareList.length;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    },
    
    // Show notification
    showNotification(message, type = 'info') {
        // Implementation depends on your notification system
        // This is a placeholder - you can integrate with toast notifications
        console.log(`${type.toUpperCase()}: ${message}`);
    }
};

// Global functions
function clearComparison() {
    ProviderComparison.clearComparison();
}

function selectFromComparison() {
    // Auto-select the highest rated or best value provider
    const bestProvider = ProviderComparison.compareList.reduce((best, current) => {
        if (!best) return current;
        return (current.rating || 0) > (best.rating || 0) ? current : best;
    }, null);
    
    if (bestProvider) {
        ProviderComparison.selectedForAddition = [bestProvider.id];
        document.querySelector(`input[value="${bestProvider.id}"]`).checked = true;
        ProviderComparison.toggleSelection(bestProvider.id, true);
    }
}

function addSelectedFromComparison() {
    ProviderComparison.addSelectedFromComparison();
}
</script>
