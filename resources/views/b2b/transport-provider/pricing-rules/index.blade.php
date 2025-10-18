@extends('layouts.b2b')

@section('title', 'Transport Pricing Rules Management')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-title', 'Transport Pricing Rules Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.transport-provider.rates') }}">Rates</a></li>
    <li class="breadcrumb-item active">Pricing Rules</li>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-2">Transport Pricing Rules</h4>
            <p class="text-muted">Manage automatic pricing adjustments based on various criteria such as dates, passenger count, routes, and more.</p>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-success" data-toggle="modal" data-target="#createPricingRuleModal">
                <i class="fas fa-plus mr-1"></i>Create New Rule
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Active Pricing Rules</h6>
        </div>
        <div class="card-body">
            <div id="pricing-rules-list">
                <!-- Pricing rules will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <i class="fas fa-cogs fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Loading pricing rules...</p>
                </div>
            </div>
        </div>
    </div>

    @include('b2b.transport-provider.pricing-rules.partials.rule-modals')
@endsection

@section('css')
<style>
    .rule-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .rule-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endsection

@section('scripts')
<script>
let currentPricingRules = [];

$(document).ready(function() {
    // Initialize CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load pricing rules on page load
    loadPricingRules();

    // PRICING RULES FUNCTIONALITY
    
    // Create pricing rule form submission
    $('#createPricingRuleForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Creating...');
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-pricing-rules.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#createPricingRuleModal').modal('hide');
                    $('#createPricingRuleForm')[0].reset();
                    loadPricingRules(); // Reload the rules list
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to create pricing rule';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Create Pricing Rule');
            }
        });
    });
    
    // Edit pricing rule form submission
    $('#editPricingRuleForm').on('submit', function(e) {
        e.preventDefault();
        
        var ruleId = $('#edit-rule-id').val();
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...');
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-pricing-rules.update", "") }}/' + ruleId,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#editPricingRuleModal').modal('hide');
                    loadPricingRules();
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to update pricing rule';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Update Pricing Rule');
            }
        });
    });
    
    // Handle pricing rule type change to show/hide relevant fields
    $(document).on('change', '#create-rule-type, #edit-rule-type', function() {
        var ruleType = $(this).val();
        var isCreate = $(this).attr('id').includes('create');
        var prefix = isCreate ? 'create' : 'edit';
        
        // Hide all conditional fields
        $('#passenger-count-fields, #distance-fields, #days-of-week-fields, #advance-booking-fields, #date-range-fields').hide();
        
        // Show relevant fields based on rule type
        if (ruleType === 'passenger_count') {
            $('#passenger-count-fields').show();
        } else if (ruleType === 'distance') {
            $('#distance-fields').show();
        } else if (ruleType === 'day_of_week') {
            $('#days-of-week-fields').show();
        } else if (ruleType === 'advance_booking') {
            $('#advance-booking-fields').show();
        } else if (ruleType === 'seasonal') {
            $('#date-range-fields').show();
        }
        
        updateRulePreview(prefix);
    });
    
    // Handle adjustment type/value change for preview
    $(document).on('change input', '#create-adjustment-type, #create-adjustment-value', function() {
        updateRulePreview('create');
    });
    
    function updateRulePreview(prefix) {
        var ruleType = $('#' + prefix + '-rule-type').val();
        var adjustmentType = $('#' + prefix + '-adjustment-type').val();
        var adjustmentValue = $('#' + prefix + '-adjustment-value').val();
        
        if (!ruleType || !adjustmentType || !adjustmentValue) {
            $('#rule-preview-text').text('Configure the rule to see a preview');
            return;
        }
        
        var action = parseFloat(adjustmentValue) > 0 ? 'increase' : 'decrease';
        var amount = Math.abs(parseFloat(adjustmentValue));
        var unit = adjustmentType === 'percentage' ? '%' : (adjustmentType === 'fixed' ? ' SAR' : 'x');
        
        $('#rule-preview-text').text(action + ' rates by ' + amount + unit + ' for ' + ruleType.replace('_', ' ') + ' conditions');
    }
    
    // Handle edit rule button click
    $(document).on('click', '.edit-rule-btn', function() {
        var ruleId = $(this).data('rule-id');
        
        // Find the rule in currentPricingRules
        var rule = currentPricingRules.find(r => r.id == ruleId);
        if (!rule) {
            showNotification('error', 'Rule not found');
            return;
        }
        
        // Populate the edit form
        $('#edit-rule-id').val(rule.id);
        $('#edit-rule-service').val(rule.transport_service_id);
        $('#edit-rule-name').val(rule.rule_name);
        $('#edit-rule-type').val(rule.rule_type);
        $('#edit-rule-description').val(rule.description || '');
        $('#edit-adjustment-type').val(rule.adjustment_type);
        $('#edit-adjustment-value').val(rule.adjustment_value);
        $('#edit-start-date').val(rule.start_date || '');
        $('#edit-end-date').val(rule.end_date || '');
        $('#edit-priority').val(rule.priority || 10);
        $('#edit-is-active').prop('checked', rule.is_active);
        
        // Show the modal
        $('#editPricingRuleModal').modal('show');
    });
    
    // Handle toggle rule status
    $(document).on('click', '.toggle-rule-btn', function() {
        var ruleId = $(this).data('rule-id');
        var btn = $(this);
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-pricing-rules.toggle-status", "") }}/' + ruleId,
            method: 'PATCH',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    loadPricingRules();
                }
            },
            error: function(xhr) {
                showNotification('error', 'Failed to toggle rule status');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
    
    // Handle delete rule
    $(document).on('click', '.delete-rule-btn', function() {
        var ruleId = $(this).data('rule-id');
        
        if (!confirm('Are you sure you want to delete this pricing rule? This action cannot be undone.')) {
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-pricing-rules.destroy", "") }}/' + ruleId,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    loadPricingRules();
                }
            },
            error: function(xhr) {
                showNotification('error', 'Failed to delete pricing rule');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
});

function loadPricingRules() {
    $('#pricing-rules-list').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i><p class="text-muted">Loading pricing rules...</p></div>');
    
    $.ajax({
        url: '{{ route("b2b.transport-provider.transport-pricing-rules.index") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                currentPricingRules = response.rules;
                displayPricingRules(response.rules);
            }
        },
        error: function() {
            $('#pricing-rules-list').html('<div class="text-center py-4"><div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Failed to load pricing rules</div></div>');
        }
    });
}

function displayPricingRules(rules) {
    var html = '';
    if (rules.length === 0) {
        html = '<div class="text-center py-5">' +
               '<i class="fas fa-cogs fa-4x text-muted mb-3"></i>' +
               '<h4 class="text-muted">No Pricing Rules Yet</h4>' +
               '<p class="text-muted">Create pricing rules to automatically adjust your transport rates based on various criteria.</p>' +
               '<button class="btn btn-success btn-lg" data-toggle="modal" data-target="#createPricingRuleModal">' +
               '<i class="fas fa-plus mr-2"></i>Create Your First Pricing Rule' +
               '</button>' +
               '</div>';
    } else {
        html = '<div class="row">';
        for (var i = 0; i < rules.length; i++) {
            var rule = rules[i];
            var activeClass = rule.is_active ? 'success' : 'secondary';
            var activeText = rule.is_active ? 'Active' : 'Inactive';
            var buttonClass = rule.is_active ? 'warning' : 'success';
            var buttonIcon = rule.is_active ? 'pause' : 'play';
            var buttonText = rule.is_active ? 'Deactivate' : 'Activate';
            
            var serviceName = rule.transport_service ? rule.transport_service.service_name : 'Unknown Service';
            
            html += '<div class="col-md-6 mb-4">' +
                    '<div class="card h-100 rule-card">' +
                    '<div class="card-header d-flex justify-content-between align-items-center">' +
                    '<h6 class="mb-0">' + rule.rule_name + '</h6>' +
                    '<span class="badge badge-' + activeClass + '">' + activeText + '</span>' +
                    '</div>' +
                    '<div class="card-body">' +
                    '<p class="text-muted small mb-2">' + (rule.description || 'No description provided') + '</p>' +
                    '<div class="mb-2">' +
                    '<small class="text-muted"><strong>Service:</strong> ' + serviceName + '</small><br>' +
                    '<small class="text-muted"><strong>Type:</strong> ' + rule.rule_type.replace('_', ' ').toUpperCase() + '</small><br>' +
                    '<small class="text-muted"><strong>Adjustment:</strong> ' + rule.adjustment_type + ' ' + rule.adjustment_value + '</small><br>' +
                    '<small class="text-muted"><strong>Priority:</strong> ' + (rule.priority || 10) + '</small>' +
                    '</div>';
                    
            if (rule.start_date && rule.end_date) {
                html += '<div class="mb-2">' +
                        '<small class="text-info"><i class="fas fa-calendar mr-1"></i>' + rule.start_date + ' to ' + rule.end_date + '</small>' +
                        '</div>';
            }
            
            html += '</div>' +
                    '<div class="card-footer">' +
                    '<div class="btn-group btn-group-sm" role="group">' +
                    '<button class="btn btn-outline-primary edit-rule-btn" data-rule-id="' + rule.id + '" title="Edit Rule">' +
                    '<i class="fas fa-edit"></i>' +
                    '</button>' +
                    '<button class="btn btn-outline-' + buttonClass + ' toggle-rule-btn" data-rule-id="' + rule.id + '" title="' + buttonText + '">' +
                    '<i class="fas fa-' + buttonIcon + '"></i>' +
                    '</button>' +
                    '<button class="btn btn-outline-danger delete-rule-btn" data-rule-id="' + rule.id + '" title="Delete Rule">' +
                    '<i class="fas fa-trash"></i>' +
                    '</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
        }
        html += '</div>';
    }
    $('#pricing-rules-list').html(html);
}

function showNotification(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                       '<i class="fas ' + icon + ' mr-2"></i>' + message +
                       '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
                       '</div>');
    
    $('.content').prepend(notification);
    
    setTimeout(function() {
        notification.fadeOut();
    }, 5000);
}
</script>
@endsection
