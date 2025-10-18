@extends('layouts.b2b')

@section('title', 'Create Service Offer')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-plus text-success mr-2"></i>
                Create Service Offer
            </h1>
            <p class="text-muted">Create a new service offer for package creators</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.service-offers.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Offers
            </a>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('b2b.service-offers.store') }}" method="POST" id="serviceOfferForm">
        @csrf
        
        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Basic Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_type">Service Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('service_type') is-invalid @enderror" 
                                    id="service_type" name="service_type" required>
                                <option value="">Select Service Type</option>
                                @if(auth()->user()->role === 'hotel_provider')
                                    <option value="hotel" {{ old('service_type') === 'hotel' ? 'selected' : '' }}>Hotel</option>
                                @endif
                                @if(auth()->user()->role === 'transport_provider')
                                    <option value="transport" {{ old('service_type') === 'transport' ? 'selected' : '' }}>Transport</option>
                                @endif
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_id">Select Service <span class="text-danger">*</span></label>
                            <select class="form-control @error('service_id') is-invalid @enderror" 
                                    id="service_id" name="service_id" required>
                                <option value="">Select a service first</option>
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Offer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required
                                   placeholder="e.g., Premium Hotel Package, Airport Transfer Service">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4" required
                              placeholder="Describe your service offer in detail...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Pricing Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    Pricing Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="base_price">Base Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('base_price') is-invalid @enderror" 
                                       id="base_price" name="base_price" value="{{ old('base_price') }}" required>
                                @error('base_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <select class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency">
                                <option value="USD" {{ old('currency', 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="SAR" {{ old('currency') === 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="AED" {{ old('currency') === 'AED' ? 'selected' : '' }}>AED</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="max_capacity">Maximum Capacity</label>
                            <input type="number" min="1" 
                                   class="form-control @error('max_capacity') is-invalid @enderror" 
                                   id="max_capacity" name="max_capacity" value="{{ old('max_capacity') }}"
                                   placeholder="e.g., 4 (leave empty for no limit)">
                            @error('max_capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    Advanced Settings
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: none;">
                <!-- Specifications -->
                <div class="form-group">
                    <label>Service Specifications</label>
                    <div id="specifications-container">
                        <div class="specification-item mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" name="specifications[0][key]" placeholder="e.g., Includes">
                                <input type="text" class="form-control" name="specifications[0][value]" placeholder="e.g., Breakfast, WiFi, Parking">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" onclick="addSpecification()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Terms & Conditions</label>
                            <textarea class="form-control" name="terms_conditions" rows="3" 
                                      placeholder="Enter terms and conditions...">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cancellation Policy</label>
                            <textarea class="form-control" name="cancellation_policy" rows="3"
                                      placeholder="Enter cancellation policy...">{{ old('cancellation_policy') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save mr-2"></i>
                            Create Service Offer
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-lg ml-2" onclick="saveDraft()">
                            <i class="fas fa-edit mr-2"></i>
                            Save as Draft
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('b2b.service-offers.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .specification-item {
            border: 1px solid #e9ecef;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>
@stop

@section('js')
    <script>
        let specificationIndex = 1;
        const services = @json($services);
        
        $(document).ready(function() {
            // Handle service type change
            $('#service_type').on('change', function() {
                const serviceType = $(this).val();
                const serviceSelect = $('#service_id');
                
                serviceSelect.html('<option value="">Loading...</option>');
                
                if (serviceType) {
                    const filteredServices = services.filter(service => service.type === serviceType);
                    let options = '<option value="">Select a service</option>';
                    
                    filteredServices.forEach(function(service) {
                        options += `<option value="${service.id}">${service.name}`;
                        if (service.location) {
                            options += ` (${service.location})`;
                        }
                        if (service.transport_type) {
                            options += ` - ${service.transport_type}`;
                        }
                        options += '</option>';
                    });
                    
                    serviceSelect.html(options);
                } else {
                    serviceSelect.html('<option value="">Select a service first</option>');
                }
            });
            
            // Trigger change if old value exists
            @if(old('service_type'))
                $('#service_type').trigger('change');
                @if(old('service_id'))
                    setTimeout(() => {
                        $('#service_id').val('{{ old('service_id') }}');
                    }, 100);
                @endif
            @endif
        });
        
        function addSpecification() {
            const container = $('#specifications-container');
            const newItem = `
                <div class="specification-item mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="specifications[${specificationIndex}][key]" placeholder="e.g., Features">
                        <input type="text" class="form-control" name="specifications[${specificationIndex}][value]" placeholder="e.g., Air Conditioning, Private Bathroom">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger" onclick="removeSpecification(this)">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.append(newItem);
            specificationIndex++;
        }
        
        function removeSpecification(button) {
            $(button).closest('.specification-item').remove();
        }
        
        function saveDraft() {
            $('#status').val('draft');
            $('#serviceOfferForm').submit();
        }
        
        // Form validation
        $('#serviceOfferForm').on('submit', function(e) {
            let isValid = true;
            const requiredFields = ['service_type', 'service_id', 'name', 'description', 'base_price'];
            
            requiredFields.forEach(function(field) {
                const input = $(`#${field}`);
                if (!input.val() || input.val().trim() === '') {
                    input.addClass('is-invalid');
                    isValid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                toastr.error('Please fill in all required fields.');
            }
        });
        
        // Success messages
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@stop
