<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>
                    Bulk Import Providers
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Import Method Selection -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Import Method</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="import-method-card" onclick="BulkImport.selectMethod('csv')">
                                <input type="radio" name="import_method" value="csv" id="csv-method" class="d-none">
                                <label for="csv-method" class="card h-100 cursor-pointer">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-csv fa-3x text-success mb-3"></i>
                                        <h6 class="card-title">CSV Upload</h6>
                                        <p class="card-text small text-muted">Upload a CSV file with provider details</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="import-method-card" onclick="BulkImport.selectMethod('manual')">
                                <input type="radio" name="import_method" value="manual" id="manual-method" class="d-none">
                                <label for="manual-method" class="card h-100 cursor-pointer">
                                    <div class="card-body text-center">
                                        <i class="fas fa-edit fa-3x text-primary mb-3"></i>
                                        <h6 class="card-title">Manual Entry</h6>
                                        <p class="card-text small text-muted">Add multiple providers using forms</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CSV Upload Section -->
                <div id="csvUploadSection" class="import-section" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Upload CSV File</h6>
                            <div class="upload-area border rounded p-4 text-center" id="csvUploadArea">
                                <input type="file" id="csvFileInput" class="d-none" accept=".csv" onchange="BulkImport.handleFileSelect(this)">
                                <div id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Drop your CSV file here or <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('csvFileInput').click()">Browse</button></p>
                                    <small class="text-muted">Supported format: .csv (max 5MB)</small>
                                </div>
                                <div id="fileSelected" style="display: none;">
                                    <i class="fas fa-file-csv fa-2x text-success mb-2"></i>
                                    <p class="mb-1 fw-bold" id="fileName"></p>
                                    <small class="text-muted" id="fileSize"></small>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="BulkImport.clearFile()">
                                            <i class="fas fa-times me-1"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">CSV Format Requirements</h6>
                            <div class="format-guide">
                                <p class="small mb-2"><strong>Required columns:</strong></p>
                                <ul class="small mb-3">
                                    <li>name (Provider Name)</li>
                                    <li>type (hotel/flight/transport)</li>
                                    <li>location (Location/Address)</li>
                                    <li>contact_email (Contact Email)</li>
                                </ul>
                                <p class="small mb-2"><strong>Optional columns:</strong></p>
                                <ul class="small mb-3">
                                    <li>phone (Phone Number)</li>
                                    <li>rating (Rating 1-5)</li>
                                    <li>price (Price per unit)</li>
                                    <li>description (Description)</li>
                                    <li>features (Comma-separated)</li>
                                </ul>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="BulkImport.downloadTemplate()">
                                    <i class="fas fa-download me-1"></i> Download Template
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Import Progress -->
                    <div id="csvImportProgress" class="mt-4" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">Processing CSV...</span>
                            <span id="progressPercentage">0%</span>
                        </div>
                        <div class="progress mb-3">
                            <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="importResults"></div>
                    </div>
                </div>
                
                <!-- Manual Entry Section -->
                <div id="manualEntrySection" class="import-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Provider Entries</h6>
                        <button type="button" class="btn btn-success btn-sm" onclick="BulkImport.addProviderForm()">
                            <i class="fas fa-plus me-1"></i> Add Provider
                        </button>
                    </div>
                    
                    <div id="providerFormsContainer">
                        <!-- Provider forms will be added here dynamically -->
                    </div>
                    
                    <div id="noProviderForms" class="text-center py-4 text-muted">
                        <i class="fas fa-plus-circle fa-2x mb-2"></i>
                        <p>No providers added yet. Click "Add Provider" to start.</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-muted small">
                        <span id="importStatus">Select an import method to continue</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-success" id="startImportBtn" onclick="BulkImport.startImport()" disabled>
                            <i class="fas fa-upload me-1"></i> Import Providers
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.import-method-card .card {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
    cursor: pointer;
}

.import-method-card .card:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.import-method-card input:checked + label.card {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

.upload-area {
    border-style: dashed !important;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.upload-area.dragover {
    border-color: #0d6efd !important;
    background-color: #e7f3ff;
}

.provider-form-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.provider-form-card .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

.cursor-pointer {
    cursor: pointer;
}

.format-guide {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
}
</style>

<script>
/**
 * Bulk Import System
 * Handles CSV upload and manual entry for multiple providers
 */
window.BulkImport = {
    currentMethod: null,
    selectedFile: null,
    providerForms: [],
    serviceType: null,
    
    // Initialize bulk import for specific service type
    init(serviceType) {
        this.serviceType = serviceType;
        this.reset();
        
        const modal = new bootstrap.Modal(document.getElementById('bulkImportModal'));
        modal.show();
    },
    
    // Reset import state
    reset() {
        this.currentMethod = null;
        this.selectedFile = null;
        this.providerForms = [];
        
        // Reset UI
        document.querySelectorAll('input[name="import_method"]').forEach(input => {
            input.checked = false;
        });
        
        document.querySelectorAll('.import-method-card .card').forEach(card => {
            card.classList.remove('selected');
        });
        
        document.querySelectorAll('.import-section').forEach(section => {
            section.style.display = 'none';
        });
        
        document.getElementById('startImportBtn').disabled = true;
        document.getElementById('importStatus').textContent = 'Select an import method to continue';
    },
    
    // Select import method
    selectMethod(method) {
        this.currentMethod = method;
        
        // Update UI
        document.querySelectorAll('input[name="import_method"]').forEach(input => {
            input.checked = input.value === method;
        });
        
        document.querySelectorAll('.import-method-card .card').forEach(card => {
            card.classList.remove('selected');
        });
        
        document.querySelector(`#${method}-method`).closest('.import-method-card').querySelector('.card').classList.add('selected');
        
        // Show appropriate section
        document.querySelectorAll('.import-section').forEach(section => {
            section.style.display = 'none';
        });
        
        if (method === 'csv') {
            document.getElementById('csvUploadSection').style.display = 'block';
            document.getElementById('importStatus').textContent = 'Upload a CSV file to continue';
        } else if (method === 'manual') {
            document.getElementById('manualEntrySection').style.display = 'block';
            document.getElementById('importStatus').textContent = 'Add provider forms to continue';
            this.addProviderForm(); // Add first form by default
        }
        
        this.updateImportButton();
    },
    
    // Handle file selection
    handleFileSelect(input) {
        const file = input.files[0];
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) { // 5MB limit
            alert('File size must be less than 5MB');
            return;
        }
        
        if (!file.name.toLowerCase().endsWith('.csv')) {
            alert('Please select a CSV file');
            return;
        }
        
        this.selectedFile = file;
        this.showFileSelected(file);
        this.updateImportButton();
    },
    
    // Show selected file
    showFileSelected(file) {
        document.getElementById('uploadPlaceholder').style.display = 'none';
        document.getElementById('fileSelected').style.display = 'block';
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = this.formatFileSize(file.size);
        document.getElementById('importStatus').textContent = 'CSV file ready for import';
    },
    
    // Clear selected file
    clearFile() {
        this.selectedFile = null;
        document.getElementById('csvFileInput').value = '';
        document.getElementById('uploadPlaceholder').style.display = 'block';
        document.getElementById('fileSelected').style.display = 'none';
        document.getElementById('importStatus').textContent = 'Upload a CSV file to continue';
        this.updateImportButton();
    },
    
    // Format file size
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    // Download CSV template
    downloadTemplate() {
        const headers = ['name', 'type', 'location', 'contact_email', 'phone', 'rating', 'price', 'description', 'features'];
        const sampleData = [
            'Example Hotel', 'hotel', '123 Main St, City', 'contact@hotel.com', '+1234567890', '4.5', '$100', 'Luxury hotel with amenities', 'WiFi,Pool,Spa'
        ];
        
        const csv = headers.join(',') + '\n' + sampleData.join(',');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `provider_template_${this.serviceType || 'all'}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    },
    
    // Add provider form
    addProviderForm() {
        const formId = Date.now();
        const formHtml = this.generateProviderForm(formId);
        
        document.getElementById('noProviderForms').style.display = 'none';
        document.getElementById('providerFormsContainer').insertAdjacentHTML('beforeend', formHtml);
        
        this.providerForms.push(formId);
        this.updateImportButton();
        document.getElementById('importStatus').textContent = `${this.providerForms.length} provider form(s) ready`;
    },
    
    // Generate provider form HTML
    generateProviderForm(formId) {
        return `
            <div class="provider-form-card" id="providerForm${formId}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Provider #${this.providerForms.length + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="BulkImport.removeProviderForm(${formId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name_${formId}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_${formId}" required>
                                <option value="">Select Type</option>
                                <option value="hotel" ${this.serviceType === 'hotels' ? 'selected' : ''}>Hotel</option>
                                <option value="flight" ${this.serviceType === 'flights' ? 'selected' : ''}>Flight</option>
                                <option value="transport" ${this.serviceType === 'transport' ? 'selected' : ''}>Transport</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location/Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="location_${formId}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="contact_email_${formId}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone_${formId}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating (1-5)</label>
                            <select class="form-select" name="rating_${formId}">
                                <option value="">No Rating</option>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <input type="text" class="form-control" name="price_${formId}" placeholder="e.g., $100/night">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Features</label>
                            <input type="text" class="form-control" name="features_${formId}" placeholder="WiFi, Pool, Parking (comma-separated)">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description_${formId}" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },
    
    // Remove provider form
    removeProviderForm(formId) {
        document.getElementById(`providerForm${formId}`).remove();
        this.providerForms = this.providerForms.filter(id => id !== formId);
        
        if (this.providerForms.length === 0) {
            document.getElementById('noProviderForms').style.display = 'block';
            document.getElementById('importStatus').textContent = 'Add provider forms to continue';
        } else {
            document.getElementById('importStatus').textContent = `${this.providerForms.length} provider form(s) ready`;
        }
        
        this.updateImportButton();
        this.renumberForms();
    },
    
    // Renumber forms after deletion
    renumberForms() {
        document.querySelectorAll('.provider-form-card .card-header span').forEach((span, index) => {
            span.textContent = `Provider #${index + 1}`;
        });
    },
    
    // Update import button state
    updateImportButton() {
        const btn = document.getElementById('startImportBtn');
        let canImport = false;
        
        if (this.currentMethod === 'csv' && this.selectedFile) {
            canImport = true;
        } else if (this.currentMethod === 'manual' && this.providerForms.length > 0) {
            canImport = true;
        }
        
        btn.disabled = !canImport;
    },
    
    // Start import process
    async startImport() {
        if (this.currentMethod === 'csv') {
            await this.processCsvImport();
        } else if (this.currentMethod === 'manual') {
            await this.processManualImport();
        }
    },
    
    // Process CSV import
    async processCsvImport() {
        const progressSection = document.getElementById('csvImportProgress');
        const progressBar = document.getElementById('progressBar');
        const progressPercentage = document.getElementById('progressPercentage');
        const resultsDiv = document.getElementById('importResults');
        
        progressSection.style.display = 'block';
        document.getElementById('startImportBtn').disabled = true;
        
        try {
            // Read CSV file
            const csvText = await this.readFileAsText(this.selectedFile);
            const providers = this.parseCsvData(csvText);
            
            let imported = 0;
            let errors = [];
            
            for (let i = 0; i < providers.length; i++) {
                const provider = providers[i];
                const progress = ((i + 1) / providers.length) * 100;
                
                progressBar.style.width = `${progress}%`;
                progressPercentage.textContent = `${Math.round(progress)}%`;
                
                try {
                    await this.importProvider(provider);
                    imported++;
                } catch (error) {
                    errors.push(`Row ${i + 2}: ${error.message}`);
                }
                
                // Small delay to show progress
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            
            // Show results
            resultsDiv.innerHTML = `
                <div class="alert ${errors.length > 0 ? 'alert-warning' : 'alert-success'}">
                    <h6><i class="fas fa-check-circle me-1"></i> Import Complete</h6>
                    <p class="mb-1"><strong>${imported}</strong> providers imported successfully</p>
                    ${errors.length > 0 ? `<p class="mb-0"><strong>${errors.length}</strong> errors occurred</p>` : ''}
                </div>
                ${errors.length > 0 ? `
                    <div class="mt-2">
                        <h6>Import Errors:</h6>
                        <ul class="small text-danger">
                            ${errors.map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            `;
            
            if (imported > 0) {
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('bulkImportModal')).hide();
                    window.EnhancedProviderSelector.refreshProviders();
                }, 2000);
            }
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-1"></i> Import Failed</h6>
                    <p class="mb-0">${error.message}</p>
                </div>
            `;
        } finally {
            document.getElementById('startImportBtn').disabled = false;
        }
    },
    
    // Process manual import
    async processManualImport() {
        const providers = this.collectManualProviders();
        let imported = 0;
        let errors = [];
        
        document.getElementById('startImportBtn').disabled = true;
        document.getElementById('importStatus').textContent = 'Importing providers...';
        
        try {
            for (const provider of providers) {
                try {
                    await this.importProvider(provider);
                    imported++;
                } catch (error) {
                    errors.push(`${provider.name}: ${error.message}`);
                }
            }
            
            if (imported > 0) {
                document.getElementById('importStatus').textContent = `Successfully imported ${imported} provider(s)`;
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('bulkImportModal')).hide();
                    window.EnhancedProviderSelector.refreshProviders();
                }, 1500);
            } else {
                document.getElementById('importStatus').textContent = 'Import failed - please check your entries';
            }
            
        } catch (error) {
            document.getElementById('importStatus').textContent = 'Import failed: ' + error.message;
        } finally {
            document.getElementById('startImportBtn').disabled = false;
        }
    },
    
    // Read file as text
    readFileAsText(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = e => reject(new Error('Failed to read file'));
            reader.readAsText(file);
        });
    },
    
    // Parse CSV data
    parseCsvData(csvText) {
        const lines = csvText.trim().split('\n');
        if (lines.length < 2) throw new Error('CSV file must contain at least a header and one data row');
        
        const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
        const providers = [];
        
        for (let i = 1; i < lines.length; i++) {
            const values = lines[i].split(',').map(v => v.trim());
            const provider = {};
            
            headers.forEach((header, index) => {
                provider[header] = values[index] || '';
            });
            
            if (provider.name && provider.type && provider.location && provider.contact_email) {
                providers.push(provider);
            }
        }
        
        return providers;
    },
    
    // Collect manual provider data
    collectManualProviders() {
        const providers = [];
        
        this.providerForms.forEach(formId => {
            const form = document.getElementById(`providerForm${formId}`);
            const provider = {};
            
            form.querySelectorAll('input, select, textarea').forEach(field => {
                if (field.name) {
                    const fieldName = field.name.replace(`_${formId}`, '');
                    provider[fieldName] = field.value.trim();
                }
            });
            
            if (provider.name && provider.type && provider.location && provider.contact_email) {
                providers.push(provider);
            }
        });
        
        return providers;
    },
    
    // Import single provider
    async importProvider(providerData) {
        // This would make an API call to save the provider
        // For now, we'll simulate the API call
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (Math.random() > 0.1) { // 90% success rate for demo
                    resolve(providerData);
                } else {
                    reject(new Error('Provider validation failed'));
                }
            }, Math.random() * 200 + 100);
        });
    }
};

// Global functions
function showBulkImport(serviceType) {
    window.BulkImport.init(serviceType);
}

// Setup drag and drop for CSV upload
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('csvUploadArea');
    if (!uploadArea) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        uploadArea.classList.add('dragover');
    }
    
    function unhighlight(e) {
        uploadArea.classList.remove('dragover');
    }
    
    uploadArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            document.getElementById('csvFileInput').files = files;
            BulkImport.handleFileSelect(document.getElementById('csvFileInput'));
        }
    }
});
</script>
