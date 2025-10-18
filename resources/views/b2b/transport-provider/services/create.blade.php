@extends('layouts.b2b')

@section('title', 'Add New Transport Service')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-plus text-info mr-2"></i>
                Add New Transport Service
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bus mr-2"></i>
                        Service Details
                    </h3>
                </div>
                
                <form action="{{ route('b2b.transport-provider.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="card-body">
                        <div class="row">
                            {{-- Basic Information --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_name">Service Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('service_name') is-invalid @enderror" 
                                           id="service_name" 
                                           name="service_name" 
                                           value="{{ old('service_name') }}" 
                                           placeholder="e.g., Makkah Express Bus Service">
                                    @error('service_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="transport_type">Transport Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('transport_type') is-invalid @enderror" 
                                            id="transport_type" 
                                            name="transport_type">
                                        <option value="">Select Type</option>
                                        @foreach($transportTypes as $type)
                                            <option value="{{ $type }}" 
                                                    {{ old('transport_type') === $type ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('transport_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="route_type">Route Type <small class="text-muted">(Optional)</small></label>
                                    <select class="form-control @error('route_type') is-invalid @enderror" 
                                            id="route_type" 
                                            name="route_type">
                                        <option value="">Select Route Type (Optional)</option>
                                        @foreach($routeTypes as $type)
                                            <option value="{{ $type }}" 
                                                    {{ old('route_type') === $type ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">General route category (since specific routes are defined below)</small>
                                    @error('route_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_passengers">Maximum Passengers <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('max_passengers') is-invalid @enderror" 
                                           id="max_passengers" 
                                           name="max_passengers" 
                                           value="{{ old('max_passengers') }}" 
                                           min="1" 
                                           placeholder="e.g., 50">
                                    @error('max_passengers')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price">
                                        <i class="fas fa-dollar-sign mr-1 text-success"></i>
                                        Base Price (SAR) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-coins"></i> SAR</span>
                                        </div>
                                        <input type="number" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price') }}" 
                                               min="0" 
                                               step="0.01" 
                                               placeholder="100.00"
                                               required>
                                    </div>
                                    <small class="form-text text-success">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Base price</strong> per passenger - used as reference for rate calculations and pricing rules
                                    </small>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                        </div>
                        
                        {{-- Routes Section --}}
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-route mr-2 text-info"></i>
                                Service Routes
                            </label>
                            <div id="routes-container">
                                <div class="route-item border p-3 mb-3" style="background-color: #f8f9fa; border-radius: 8px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                <label class="text-sm font-weight-bold text-muted">From Location</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="routes[0][from]" 
                                                       placeholder="e.g., Jeddah Airport"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                <label class="text-sm font-weight-bold text-muted">To Location</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="routes[0][to]" 
                                                       placeholder="e.g., Makkah"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-0">
                                                <label class="text-sm font-weight-bold text-muted">Duration (mins)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="routes[0][duration]" 
                                                       placeholder="120" 
                                                       min="1"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <label class="text-sm font-weight-bold text-muted d-block">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm remove-route" title="Remove Route">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" id="add-route">
                                <i class="fas fa-plus mr-1"></i>
                                Add Route
                            </button>
                        </div>
                        
                        {{-- Pickup/Dropoff Locations --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_locations">Pickup Locations</label>
                                    <textarea class="form-control @error('pickup_locations') is-invalid @enderror" 
                                              id="pickup_locations_input" 
                                              rows="3" 
                                              placeholder="Enter pickup locations, one per line"></textarea>
                                    <input type="hidden" name="pickup_locations" id="pickup_locations">
                                    @error('pickup_locations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dropoff_locations">Dropoff Locations</label>
                                    <textarea class="form-control @error('dropoff_locations') is-invalid @enderror" 
                                              id="dropoff_locations_input" 
                                              rows="3" 
                                              placeholder="Enter dropoff locations, one per line"></textarea>
                                    <input type="hidden" name="dropoff_locations" id="dropoff_locations">
                                    @error('dropoff_locations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        {{-- Operating Hours --}}
                        <div class="form-group">
                            <label>Operating Hours</label>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="is_24_7" name="is_24_7" value="1">
                                <label class="custom-control-label" for="is_24_7">
                                    24/7 Service (Always Available)
                                </label>
                            </div>
                            <div class="row" id="time-inputs">
                                <div class="col-md-6">
                                    <input type="time" 
                                           class="form-control" 
                                           id="start_time"
                                           name="operating_hours[start]" 
                                           placeholder="Start Time">
                                    <small class="form-text text-muted">Start Time</small>
                                </div>
                                <div class="col-md-6">
                                    <input type="time" 
                                           class="form-control" 
                                           id="end_time"
                                           name="operating_hours[end]" 
                                           placeholder="End Time">
                                    <small class="form-text text-muted">End Time</small>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Specifications --}}
                        <div class="form-group">
                            <label for="specifications">Vehicle Specifications</label>
                            <textarea class="form-control @error('specifications') is-invalid @enderror" 
                                      id="specifications_input" 
                                      rows="4" 
                                      placeholder="Describe vehicle specifications, amenities, etc."></textarea>
                            <input type="hidden" name="specifications" id="specifications">
                            @error('specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Policies --}}
                        <div class="form-group">
                            <label for="policies">Service Policies</label>
                            <textarea class="form-control @error('policies') is-invalid @enderror" 
                                      id="policies_input" 
                                      rows="4" 
                                      placeholder="Describe cancellation policy, luggage policy, etc."></textarea>
                            <input type="hidden" name="policies" id="policies">
                            @error('policies')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Contact Information --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_phone">Contact Phone</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="contact_phone" 
                                           name="contact_info[phone]" 
                                           placeholder="Service contact phone">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_email">Contact Email</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="contact_email" 
                                           name="contact_info[email]" 
                                           placeholder="Service contact email">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Create Transport Service
                        </button>
                        <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary ml-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop


@push('styles')
<!-- Additional Select2 Bootstrap 4 theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
    .route-item {
        background-color: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .route-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .remove-route:hover {
        background-color: #dc3545 !important;
        transform: scale(1.05);
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    'use strict';
    


    
    // Initialize Select2 for existing selects using global function
    if (typeof window.initializeSelect2 === 'function') {
        window.initializeSelect2('#transport_type, #route_type');

    } else {
        console.error('Global Select2 initialization function not available');
    }
    
    let routeIndex = 1;
    
    // Debug: Check if elements exist
    console.log('Add route button found:', $('#add-route').length);
    console.log('Routes container found:', $('#routes-container').length);
    
    // Add new route functionality
    $(document).on('click', '#add-route', function(e) {
        e.preventDefault();
        e.stopPropagation();
        

        
        const routeHtml = `
            <div class="route-item border p-3 mb-3" style="background-color: #f8f9fa; border-radius: 8px; animation: fadeInUp 0.3s ease;">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="text-sm font-weight-bold text-muted">From Location</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="routes[${routeIndex}][from]" 
                                   placeholder="e.g., Jeddah Airport"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="text-sm font-weight-bold text-muted">To Location</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="routes[${routeIndex}][to]" 
                                   placeholder="e.g., Makkah"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="text-sm font-weight-bold text-muted">Duration (mins)</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="routes[${routeIndex}][duration]" 
                                   placeholder="120" 
                                   min="1"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-1 text-center">
                        <label class="text-sm font-weight-bold text-muted d-block">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm remove-route" title="Remove Route">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add the new route with animation
        const $newRoute = $(routeHtml).hide();
        $('#routes-container').append($newRoute);
        $newRoute.slideDown(300);
        routeIndex++;
        
        console.log('New route added, total routes:', $('#routes-container .route-item').length);
        
        // Focus on the first input of the new route
        $newRoute.find('input[type="text"]').first().focus();
    });
    
    // Remove route with event delegation
    $(document).on('click', '.remove-route', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const totalRoutes = $('#routes-container .route-item').length;

        
        if (totalRoutes > 1) {
            const $routeItem = $(this).closest('.route-item');
            $routeItem.slideUp(300, function() {
                $(this).remove();
                console.log('Route removed, remaining routes:', $('#routes-container .route-item').length);
            });
        } else {
            // Show a nicer alert using SweetAlert if available, or a custom modal
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Cannot Remove',
                    text: 'At least one route is required for the service.',
                    icon: 'warning',
                    confirmButtonColor: '#007bff'
                });
            } else {
                alert('At least one route is required for the service.');
            }
        }
    });
    
    // Handle 24/7 checkbox with smooth animations
    $('#is_24_7').on('change', function() {
        console.log('24/7 checkbox changed:', $(this).is(':checked'));
        
        if ($(this).is(':checked')) {
            $('#time-inputs').slideUp(300);
            $('#start_time, #end_time').val('').prop('required', false);
        } else {
            $('#time-inputs').slideDown(300);
            $('#start_time, #end_time').prop('required', true);
        }
    });
    
    // Enhanced form validation and submission
    $('form').on('submit', function(e) {

        
        try {
            // Show loading state
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating Service...');
            
            
            // Process pickup locations
            const pickupLocationsInput = $('#pickup_locations_input').val();
            if (pickupLocationsInput) {
                const pickupLocations = pickupLocationsInput.split('\n')
                    .map(s => s.trim())
                    .filter(s => s);
                $('#pickup_locations').val(JSON.stringify(pickupLocations));

            }
            
            // Process dropoff locations
            const dropoffLocationsInput = $('#dropoff_locations_input').val();
            if (dropoffLocationsInput) {
                const dropoffLocations = dropoffLocationsInput.split('\n')
                    .map(s => s.trim())
                    .filter(s => s);
                $('#dropoff_locations').val(JSON.stringify(dropoffLocations));

            }
            
            // Process specifications
            const specificationsInput = $('#specifications_input').val();
            if (specificationsInput) {
                $('#specifications').val(JSON.stringify({
                    description: specificationsInput.trim()
                }));
            }
            
            // Process policies
            const policiesInput = $('#policies_input').val();
            if (policiesInput) {
                $('#policies').val(JSON.stringify({
                    general: policiesInput.trim()
                }));
            }
            
            // Handle 24/7 operating hours
            if ($('#is_24_7').is(':checked')) {
                // Remove existing 24/7 inputs
                $('input[name="operating_hours[is_24_7]"]').remove();
                // Create hidden input for 24/7
                $('<input>').attr({
                    type: 'hidden',
                    name: 'operating_hours[is_24_7]',
                    value: 'true'
                }).appendTo(this);
            }
            
            // Validate routes
            let routesValid = true;
            $('#routes-container .route-item').each(function(index) {
                const from = $(this).find('input[name$="[from]"]').val().trim();
                const to = $(this).find('input[name$="[to]"]').val().trim();
                const duration = $(this).find('input[name$="[duration]"]').val();
                
                if (!from || !to || !duration || duration <= 0) {
                    routesValid = false;
                    $(this).addClass('border-danger');
                } else {
                    $(this).removeClass('border-danger');
                }
            });
            
            if (!routesValid) {
                e.preventDefault();
                $submitBtn.prop('disabled', false).html(originalText);
                alert('Please fill in all route information correctly.');
                return false;
            }
            

            
        } catch (error) {
            console.error('Error processing form data:', error);
            e.preventDefault();
            $(this).find('button[type="submit"]').prop('disabled', false)
                  .html('<i class="fas fa-save mr-2"></i>Create Transport Service');
        }
    });
    
    // Real-time validation feedback
    $(document).on('input', '.route-item input[required]', function() {
        const $routeItem = $(this).closest('.route-item');
        const allFilled = $routeItem.find('input[required]').toArray().every(input => $(input).val().trim());
        
        if (allFilled) {
            $routeItem.removeClass('border-danger').addClass('border-success');
        } else {
            $routeItem.removeClass('border-success');
        }
    });
    
    // Add some nice animations with CSS
    $('<style>').text(`
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .border-success {
            border-color: #28a745 !important;
        }
        .border-danger {
            border-color: #dc3545 !important;
        }
    `).appendTo('head');
    

});
</script>
@endpush
