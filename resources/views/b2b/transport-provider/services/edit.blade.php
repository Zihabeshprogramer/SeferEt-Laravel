@extends('layouts.b2b')

@section('title', 'Edit Transport Service')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-edit text-info mr-2"></i>
                Edit: {{ $transportService->service_name }}
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.transport-provider.show', $transportService) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Service
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
                
                <form action="{{ route('b2b.transport-provider.update', $transportService) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
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
                                           value="{{ old('service_name', $transportService->service_name) }}" 
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
                                                    {{ old('transport_type', $transportService->transport_type) === $type ? 'selected' : '' }}>
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
                                                    {{ old('route_type', $transportService->route_type) === $type ? 'selected' : '' }}>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_passengers">Maximum Passengers <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('max_passengers') is-invalid @enderror" 
                                           id="max_passengers" 
                                           name="max_passengers" 
                                           value="{{ old('max_passengers', $transportService->max_passengers) }}" 
                                           min="1" 
                                           placeholder="e.g., 50">
                                    @error('max_passengers')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
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
                                               value="{{ old('price', $transportService->price) }}" 
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
                            <label>Routes</label>
                            <div id="routes-container">
                                @if($transportService->routes && count($transportService->routes) > 0)
                                    @foreach($transportService->routes as $index => $route)
                                        <div class="route-item border p-3 mb-2">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="routes[{{ $index }}][from]" 
                                                           value="{{ $route['from'] ?? '' }}"
                                                           placeholder="From (e.g., Jeddah Airport)">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="routes[{{ $index }}][to]" 
                                                           value="{{ $route['to'] ?? '' }}"
                                                           placeholder="To (e.g., Makkah)">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" 
                                                           class="form-control" 
                                                           name="routes[{{ $index }}][duration]" 
                                                           value="{{ $route['duration'] ?? '' }}"
                                                           placeholder="Duration (minutes)" 
                                                           min="1">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger btn-sm remove-route">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="route-item border p-3 mb-2">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="routes[0][from]" 
                                                       placeholder="From (e.g., Jeddah Airport)">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="routes[0][to]" 
                                                       placeholder="To (e.g., Makkah)">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="routes[0][duration]" 
                                                       placeholder="Duration (minutes)" 
                                                       min="1">
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger btn-sm remove-route">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
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
                                              placeholder="Enter pickup locations, one per line">{{ $transportService->pickup_locations ? implode("\n", $transportService->pickup_locations) : '' }}</textarea>
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
                                              placeholder="Enter dropoff locations, one per line">{{ $transportService->dropoff_locations ? implode("\n", $transportService->dropoff_locations) : '' }}</textarea>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="time" 
                                           class="form-control" 
                                           name="operating_hours[start]" 
                                           value="{{ $transportService->operating_hours['start'] ?? '' }}"
                                           placeholder="Start Time">
                                    <small class="form-text text-muted">Start Time</small>
                                </div>
                                <div class="col-md-6">
                                    <input type="time" 
                                           class="form-control" 
                                           name="operating_hours[end]" 
                                           value="{{ $transportService->operating_hours['end'] ?? '' }}"
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
                                      placeholder="Describe vehicle specifications, amenities, etc.">{{ isset($transportService->specifications['description']) ? $transportService->specifications['description'] : '' }}</textarea>
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
                                      placeholder="Describe cancellation policy, luggage policy, etc.">{{ isset($transportService->policies['general']) ? $transportService->policies['general'] : '' }}</textarea>
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
                                           value="{{ $transportService->contact_info['phone'] ?? '' }}"
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
                                           value="{{ $transportService->contact_info['email'] ?? '' }}"
                                           placeholder="Service contact email">
                                </div>
                            </div>
                        </div>
                        
                        {{-- Active Status --}}
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ $transportService->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Service Active
                                </label>
                            </div>
                            <small class="form-text text-muted">Only active services will be available for booking.</small>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Update Transport Service
                        </button>
                        <a href="{{ route('b2b.transport-provider.show', $transportService) }}" class="btn btn-secondary ml-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .route-item {
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .remove-route:hover {
            background-color: #dc3545;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            let routeIndex = {{ $transportService->routes ? count($transportService->routes) : 1 }};
            
            // Add new route
            $('#add-route').click(function() {
                const routeHtml = `
                    <div class="route-item border p-3 mb-2">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" 
                                       class="form-control" 
                                       name="routes[${routeIndex}][from]" 
                                       placeholder="From (e.g., Jeddah Airport)">
                            </div>
                            <div class="col-md-4">
                                <input type="text" 
                                       class="form-control" 
                                       name="routes[${routeIndex}][to]" 
                                       placeholder="To (e.g., Makkah)">
                            </div>
                            <div class="col-md-3">
                                <input type="number" 
                                       class="form-control" 
                                       name="routes[${routeIndex}][duration]" 
                                       placeholder="Duration (minutes)" 
                                       min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-route">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#routes-container').append(routeHtml);
                routeIndex++;
            });
            
            // Remove route
            $(document).on('click', '.remove-route', function() {
                $(this).closest('.route-item').remove();
            });
            
            // Convert text inputs to arrays before form submission
            $('form').submit(function() {
                // Vehicle types
                const vehicleTypes = $('#vehicle_types_input').val().split(',').map(s => s.trim()).filter(s => s);
                $('#vehicle_types').val(JSON.stringify(vehicleTypes));
                
                // Pickup locations
                const pickupLocations = $('#pickup_locations_input').val().split('\n').map(s => s.trim()).filter(s => s);
                $('#pickup_locations').val(JSON.stringify(pickupLocations));
                
                // Dropoff locations
                const dropoffLocations = $('#dropoff_locations_input').val().split('\n').map(s => s.trim()).filter(s => s);
                $('#dropoff_locations').val(JSON.stringify(dropoffLocations));
                
                // Specifications
                const specifications = $('#specifications_input').val();
                if (specifications) {
                    $('#specifications').val(JSON.stringify({description: specifications}));
                }
                
                // Policies
                const policies = $('#policies_input').val();
                if (policies) {
                    $('#policies').val(JSON.stringify({general: policies}));
                }
            });
        });
    </script>
@stop
