{{-- Create Pricing Rule Modal --}}
<div class="modal fade" id="createPricingRuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Create Transport Pricing Rule
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createPricingRuleForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Pricing Rules:</strong> Create automated pricing adjustments based on specific conditions like dates, passenger count, or routes.
                    </div>
                    
                    {{-- Basic Information Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create-rule-service">Transport Service <span class="text-danger">*</span></label>
                                        <select class="form-control" id="create-rule-service" name="transport_service_id" required>
                                            <option value="">Select a service</option>
                                            @if(isset($services))
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}" data-base-price="{{ $service->price ?? 100 }}">{{ $service->service_name }} ({{ ucfirst($service->transport_type) }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <small class="form-text text-muted">This rule will apply to the selected transport service</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create-rule-name">Rule Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="create-rule-name" name="rule_name" required placeholder="e.g., Weekend Premium, Holiday Surcharge">
                                        <small class="form-text text-muted">A descriptive name for this pricing rule</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create-rule-description">Description</label>
                                <input type="text" class="form-control" id="create-rule-description" name="description" placeholder="Brief description of when and how this rule applies">
                                <small class="form-text text-muted">Optional: Explain when this rule should be applied</small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Rule Type Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cogs mr-1"></i>Rule Conditions</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="create-rule-type">When should this rule apply? <span class="text-danger">*</span></label>
                                <select class="form-control" id="create-rule-type" name="rule_type" required>
                                    <option value="">Select when this rule should be triggered...</option>
                                    <option value="seasonal">🌱 Seasonal Pricing - Apply during specific date ranges</option>
                                    <option value="day_of_week">📅 Day of Week - Apply on specific days (e.g., weekends)</option>
                                    <option value="passenger_count">👥 Passenger Count - Apply based on number of passengers</option>
                                    <option value="route_specific">🗺️ Route Specific - Apply to specific routes only</option>
                                    <option value="advance_booking">⏰ Advance Booking - Apply based on booking timing</option>
                                    <option value="distance">📏 Distance Based - Apply based on route distance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Pricing Adjustment Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-calculator mr-1"></i>Price Adjustment</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create-adjustment-type">Adjustment Type <span class="text-danger">*</span></label>
                                        <select class="form-control" id="create-adjustment-type" name="adjustment_type" required>
                                            <option value="">Choose how to adjust the price...</option>
                                            <option value="percentage">📊 Percentage - Increase/decrease by a percentage</option>
                                            <option value="fixed">💰 Fixed Amount - Add/subtract a specific amount</option>
                                            <option value="multiplier">✖️ Multiplier - Multiply price by a factor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Adjustment Direction <span class="text-danger">*</span></label>
                                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons" id="adjustment-direction-group">
                                            <label class="btn btn-outline-success flex-fill" id="create-increase-btn">
                                                <input type="radio" name="adjustment_direction" value="increase" autocomplete="off" required>
                                                <i class="fas fa-arrow-up mr-1"></i>Increase/Premium
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill" id="create-decrease-btn">
                                                <input type="radio" name="adjustment_direction" value="decrease" autocomplete="off" required>
                                                <i class="fas fa-arrow-down mr-1"></i>Decrease/Discount
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Will this rule increase or decrease the base price?</small>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Percentage Configuration --}}
                            <div id="percentage-config" class="adjustment-config" style="display: none;">
                                <div class="form-group">
                                    <label>Percentage Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="percentage-symbol">+</span>
                                        </div>
                                        <input type="number" class="form-control" id="percentage-value" step="0.1" placeholder="15.0" min="0" max="100">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Enter percentage (e.g., 25 for 25% increase/decrease)</small>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-calculator mr-2"></i>
                                    <span id="percentage-example">Example: Base price 100 SAR + 15% = 115 SAR</span>
                                </div>
                            </div>
                            
                            {{-- Fixed Amount Configuration --}}
                            <div id="fixed-config" class="adjustment-config" style="display: none;">
                                <div class="form-group">
                                    <label>Fixed Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        </div>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="fixed-symbol">+</span>
                                        </div>
                                        <input type="number" class="form-control" id="fixed-value" step="0.01" placeholder="25.00" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">SAR</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Enter amount in SAR (e.g., 50 for 50 SAR increase/decrease)</small>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-calculator mr-2"></i>
                                    <span id="fixed-example">Example: Base price 100 SAR + 25 SAR = 125 SAR</span>
                                </div>
                            </div>
                            
                            {{-- Multiplier Configuration --}}
                            <div id="multiplier-config" class="adjustment-config" style="display: none;">
                                <div class="form-group">
                                    <label>Multiplier Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">×</span>
                                        </div>
                                        <input type="number" class="form-control" id="multiplier-value" step="0.01" placeholder="1.5" min="0.1" max="10">
                                    </div>
                                    <small class="form-text text-muted">Enter multiplier (e.g., 1.5 for 1.5x price, 0.8 for 20% discount)</small>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-calculator mr-2"></i>
                                    <span id="multiplier-example">Example: Base price 100 SAR × 1.5 = 150 SAR</span>
                                </div>
                            </div>
                            
                            {{-- Hidden input for the final adjustment value --}}
                            <input type="hidden" name="adjustment_value" id="final-adjustment-value">
                        </div>
                    </div>
                    
                    {{-- Conditional Fields Card --}}
                    <div class="card mb-4" id="conditional-fields-card" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-sliders-h mr-1"></i>Rule Conditions</h6>
                        </div>
                        <div class="card-body">
                            {{-- Date Range Fields (for seasonal) --}}
                            <div class="conditional-field" id="date-range-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="create-start-date">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="create-start-date" name="start_date">
                                            <small class="form-text text-muted">When does this seasonal pricing begin?</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="create-end-date">End Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="create-end-date" name="end_date">
                                            <small class="form-text text-muted">When does this seasonal pricing end?</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Days of Week Fields --}}
                            <div class="conditional-field" id="days-of-week-fields" style="display: none;">
                                <div class="form-group">
                                    <label>Select Days of Week <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-outline-primary btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="monday" autocomplete="off"> Monday
                                                </label>
                                                <label class="btn btn-outline-primary btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="tuesday" autocomplete="off"> Tuesday
                                                </label>
                                                <label class="btn btn-outline-primary btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="wednesday" autocomplete="off"> Wednesday
                                                </label>
                                                <label class="btn btn-outline-primary btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="thursday" autocomplete="off"> Thursday
                                                </label>
                                                <label class="btn btn-outline-primary btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="friday" autocomplete="off"> Friday
                                                </label>
                                                <label class="btn btn-outline-warning btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="saturday" autocomplete="off"> Saturday
                                                </label>
                                                <label class="btn btn-outline-warning btn-sm mr-1 mb-1">
                                                    <input type="checkbox" name="days_of_week[]" value="sunday" autocomplete="off"> Sunday
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">Select which days this rule should apply</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Settings Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cog mr-1"></i>Rule Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create-priority">Priority (1-100)</label>
                                        <input type="number" class="form-control" id="create-priority" name="priority" min="1" max="100" value="10">
                                        <small class="form-text text-muted">Higher numbers = higher priority when multiple rules apply</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check mt-4">
                                            {{-- Hidden input to ensure a value is always sent --}}
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox" id="create-is-active" name="is_active" value="1" checked>
                                            <label class="form-check-label" for="create-is-active">
                                                <strong>Activate rule immediately</strong>
                                            </label>
                                            <small class="form-text text-muted">Uncheck to create rule but keep it inactive</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Live Preview Card --}}
                    <div class="card" id="preview-card" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-eye mr-1"></i>Live Preview</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success" id="pricing-preview">
                                <i class="fas fa-calculator mr-2"></i>
                                <strong>Preview:</strong> <span id="rule-preview-text">Configure the rule to see a preview</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Create Pricing Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Pricing Rule Modal --}}
<div class="modal fade" id="editPricingRuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Edit Transport Pricing Rule
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editPricingRuleForm">
                <input type="hidden" id="edit-rule-id" name="rule_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-rule-service">Transport Service <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit-rule-service" name="transport_service_id" required>
                                    @if(isset($services))
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->service_name }} ({{ ucfirst($service->transport_type) }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-rule-name">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-rule-name" name="rule_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-rule-type">Rule Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit-rule-type" name="rule_type" required>
                                    <option value="seasonal">Seasonal Pricing</option>
                                    <option value="day_of_week">Day of Week</option>
                                    <option value="passenger_count">Passenger Count</option>
                                    <option value="route_specific">Route Specific</option>
                                    <option value="advance_booking">Advance Booking</option>
                                    <option value="distance">Distance Based</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-rule-description">Description</label>
                                <input type="text" class="form-control" id="edit-rule-description" name="description">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-adjustment-type">Adjustment Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit-adjustment-type" name="adjustment_type" required>
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount (SAR)</option>
                                    <option value="multiplier">Multiplier (x)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-adjustment-value">Adjustment Value <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit-adjustment-value" name="adjustment_value" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-start-date">Start Date</label>
                                <input type="date" class="form-control" id="edit-start-date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-end-date">End Date</label>
                                <input type="date" class="form-control" id="edit-end-date" name="end_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-priority">Priority (1-100)</label>
                                <input type="number" class="form-control" id="edit-priority" name="priority" min="1" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit-is-active" name="is_active">
                                    <label class="form-check-label" for="edit-is-active">
                                        Rule is active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>Update Pricing Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
