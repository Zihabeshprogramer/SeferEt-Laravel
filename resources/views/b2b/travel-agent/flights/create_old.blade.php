@extends('layouts.b2b')

@section('title', 'Create Group Flight Booking')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-users-cog text-info mr-2"></i>
                Create Group Flight Booking
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Set up round-trip group flights and collaborate with other travel agents
            </p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.travel-agent.flights.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Flights
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        Group Flight Booking Setup
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            <i class="fas fa-info mr-1"></i>
                            Optimized for Group Travel
                        </span>
                    </div>
                </div>
                
                <form action="{{ route('b2b.travel-agent.flights.store') }}" method="POST" id="flightForm">
                    @csrf
                    <div class="card-body">
                        {{-- Trip Type Selection --}}
                        <div class="form-section mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-route mr-1"></i>
                                Trip Configuration
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="trip_type" class="form-label">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        Trip Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('trip_type') is-invalid @enderror" 
                                            id="trip_type" name="trip_type" required>
                                        @foreach($tripTypes as $type => $label)
                                            <option value="{{ $type }}" {{ old('trip_type', 'round_trip') == $type ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('trip_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Round-trip is recommended for group bookings</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_group_booking" 
                                               name="is_group_booking" value="1" {{ old('is_group_booking', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_group_booking">
                                            <i class="fas fa-users mr-1"></i>
                                            <strong>Group Booking</strong>
                                        </label>
                                        <small class="form-text text-muted d-block">Enable group discounts and collaboration</small>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="allows_agent_collaboration" 
                                               name="allows_agent_collaboration" value="1" {{ old('allows_agent_collaboration', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allows_agent_collaboration">
                                            <i class="fas fa-handshake mr-1"></i>
                                            <strong>Agent Collaboration</strong>
                                        </label>
                                        <small class="form-text text-muted d-block">Allow other agents to help with bookings</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Basic Flight Information --}}
                        <div class="form-section mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Flight Details
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="airline" class="form-label">
                                        <i class="fas fa-building mr-1"></i>
                                        Airline <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('airline') is-invalid @enderror" 
                                           id="airline" name="airline" value="{{ old('airline') }}" 
                                           placeholder="e.g., Saudi Arabian Airlines, Emirates, Turkish Airlines" required>
                                    @error('airline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="flight_number" class="form-label">
                                        <i class="fas fa-hashtag mr-1"></i>
                                        Outbound Flight <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('flight_number') is-invalid @enderror" 
                                           id="flight_number" name="flight_number" value="{{ old('flight_number') }}" 
                                           placeholder="e.g., SV123" required pattern="[A-Z]{2}[0-9]{1,4}[A-Z]?">
                                    @error('flight_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3 return-flight-field" style="display: none;">
                                    <label for="return_flight_number" class="form-label">
                                        <i class="fas fa-undo mr-1"></i>
                                        Return Flight
                                    </label>
                                    <input type="text" class="form-control @error('return_flight_number') is-invalid @enderror" 
                                           id="return_flight_number" name="return_flight_number" value="{{ old('return_flight_number') }}" 
                                           placeholder="e.g., SV124" pattern="[A-Z]{2}[0-9]{1,4}[A-Z]?">
                                    @error('return_flight_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="aircraft_type" class="form-label">
                                        <i class="fas fa-plane mr-1"></i>
                                        Aircraft Type
                                    </label>
                                    <input type="text" class="form-control @error('aircraft_type') is-invalid @enderror" 
                                           id="aircraft_type" name="aircraft_type" value="{{ old('aircraft_type') }}" 
                                           placeholder="e.g., Boeing 777-300ER, Airbus A330-300">
                                    @error('aircraft_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-info mr-1"></i>
                                        Flight Status
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status">
                                        @foreach($flightStatuses as $status => $label)
                                            <option value="{{ $status }}" {{ old('status', 'scheduled') == $status ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="is_active" class="form-label">
                                        <i class="fas fa-toggle-on mr-1"></i>
                                        Booking Status
                                    </label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Accept Bookings</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Route Information --}}
                        <div class="form-section mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-route mr-1"></i>
                                Route & Schedule
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="departure_airport" class="form-label">
                                        <i class="fas fa-plane-departure mr-1"></i>
                                        Departure Airport <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2 @error('departure_airport') is-invalid @enderror" 
                                            id="departure_airport" name="departure_airport" required>
                                        <option value="">Select Departure Airport</option>
                                        @foreach($commonAirports as $code => $name)
                                            <option value="{{ $code }}" {{ old('departure_airport') == $code ? 'selected' : '' }}>
                                                {{ $code }} - {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('departure_airport')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="arrival_airport" class="form-label">
                                        <i class="fas fa-plane-arrival mr-1"></i>
                                        Arrival Airport <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2 @error('arrival_airport') is-invalid @enderror" 
                                            id="arrival_airport" name="arrival_airport" required>
                                        <option value="">Select Arrival Airport</option>
                                        @foreach($commonAirports as $code => $name)
                                            <option value="{{ $code }}" {{ old('arrival_airport') == $code ? 'selected' : '' }}>
                                                {{ $code }} - {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('arrival_airport')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="departure_datetime" class="form-label">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Departure Date & Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" class="form-control @error('departure_datetime') is-invalid @enderror" 
                                           id="departure_datetime" name="departure_datetime" 
                                           value="{{ old('departure_datetime') }}" required>
                                    @error('departure_datetime')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="arrival_datetime" class="form-label">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        Arrival Date & Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" class="form-control @error('arrival_datetime') is-invalid @enderror" 
                                           id="arrival_datetime" name="arrival_datetime" 
                                           value="{{ old('arrival_datetime') }}" required>
                                    @error('arrival_datetime')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Seating Information --}}
                        <div class="form-section mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-chair mr-1"></i>
                                Seating Configuration
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_seats" class="form-label">
                                        <i class="fas fa-list-ol mr-1"></i>
                                        Total Seats <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('total_seats') is-invalid @enderror" 
                                           id="total_seats" name="total_seats" value="{{ old('total_seats') }}" 
                                           min="1" max="1000" required>
                                    @error('total_seats')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="available_seats" class="form-label">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Available Seats <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('available_seats') is-invalid @enderror" 
                                           id="available_seats" name="available_seats" value="{{ old('available_seats') }}" 
                                           min="0" max="1000" required>
                                    @error('available_seats')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Pricing Information --}}
                        <div class="form-section mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-dollar-sign mr-1"></i>
                                Pricing Configuration
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="currency" class="form-label">
                                        <i class="fas fa-coins mr-1"></i>
                                        Currency <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('currency') is-invalid @enderror" 
                                            id="currency" name="currency" required>
                                        @foreach($currencies as $code => $name)
                                            <option value="{{ $code }}" {{ old('currency', 'SAR') == $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="economy_price" class="form-label">
                                        <i class="fas fa-money-bill mr-1"></i>
                                        Economy Price <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control @error('economy_price') is-invalid @enderror" 
                                           id="economy_price" name="economy_price" value="{{ old('economy_price') }}" 
                                           min="0" max="999999.99" required>
                                    @error('economy_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="business_price" class="form-label">
                                        <i class="fas fa-money-bill-wave mr-1"></i>
                                        Business Price
                                    </label>
                                    <input type="number" step="0.01" class="form-control @error('business_price') is-invalid @enderror" 
                                           id="business_price" name="business_price" value="{{ old('business_price') }}" 
                                           min="0" max="999999.99">
                                    @error('business_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="first_class_price" class="form-label">
                                        <i class="fas fa-crown mr-1"></i>
                                        First Class Price
                                    </label>
                                    <input type="number" step="0.01" class="form-control @error('first_class_price') is-invalid @enderror" 
                                           id="first_class_price" name="first_class_price" value="{{ old('first_class_price') }}" 
                                           min="0" max="999999.99">
                                    @error('first_class_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Additional Information --}}
                        <div class="form-section mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-cogs mr-1"></i>
                                Additional Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-file-alt mr-1"></i>
                                        Description
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Additional flight information, special notes, etc.">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="amenities" class="form-label">
                                        <i class="fas fa-star mr-1"></i>
                                        Amenities
                                    </label>
                                    <input type="text" class="form-control" id="amenities_input" 
                                           placeholder="Type amenity and press Enter">
                                    <input type="hidden" name="amenities" id="amenities" value="{{ json_encode(old('amenities', [])) }}">
                                    <div id="amenities_tags" class="mt-2"></div>
                                    <small class="form-text text-muted">Add flight amenities (WiFi, Meals, Entertainment, etc.)</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-suitcase mr-1"></i>
                                        Baggage Allowance
                                    </label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="baggage_allowance[carry_on]" 
                                                   value="{{ old('baggage_allowance.carry_on') }}" placeholder="Carry-on">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="baggage_allowance[checked]" 
                                                   value="{{ old('baggage_allowance.checked') }}" placeholder="Checked baggage">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="meal_economy" class="form-label">
                                        <i class="fas fa-utensils mr-1"></i>
                                        Economy Meal Service
                                    </label>
                                    <input type="text" class="form-control" name="meal_service[economy]" 
                                           value="{{ old('meal_service.economy') }}" placeholder="e.g., Light snacks">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="meal_business" class="form-label">
                                        <i class="fas fa-utensils mr-1"></i>
                                        Business Meal Service
                                    </label>
                                    <input type="text" class="form-control" name="meal_service[business]" 
                                           value="{{ old('meal_service.business') }}" placeholder="e.g., Full course meal">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="meal_first" class="form-label">
                                        <i class="fas fa-utensils mr-1"></i>
                                        First Class Meal Service
                                    </label>
                                    <input type="text" class="form-control" name="meal_service[first_class]" 
                                           value="{{ old('meal_service.first_class') }}" placeholder="e.g., Gourmet dining">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Fields marked with <span class="text-danger">*</span> are required
                                </small>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('b2b.travel-agent.flights.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times mr-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-save mr-1"></i>
                                    Create Flight
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .form-section {
        background: #f8f9fa;
        border-left: 4px solid #17a2b8;
        padding: 1.5rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }
    
    .form-section h5 {
        color: #17a2b8;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border: none;
    }
    
    .card-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-bottom: none;
    }
    
    .card-header h3 {
        margin: 0;
        font-weight: 600;
    }
    
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    
    .form-select:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    
    .amenity-tag {
        display: inline-block;
        background: #17a2b8;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        margin: 0.125rem;
        font-size: 0.875rem;
    }
    
    .amenity-tag .remove-tag {
        margin-left: 0.25rem;
        cursor: pointer;
        opacity: 0.7;
    }
    
    .amenity-tag .remove-tag:hover {
        opacity: 1;
    }
    
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select an option',
        allowClear: true
    });
    
    // Set minimum datetime to current time
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDateTime = now.toISOString().slice(0, 16);
    $('#departure_datetime').attr('min', minDateTime);
    
    // Update arrival minimum when departure changes
    $('#departure_datetime').on('change', function() {
        const departureTime = new Date(this.value);
        departureTime.setMinutes(departureTime.getMinutes() + 30); // Minimum 30 min flight
        departureTime.setMinutes(departureTime.getMinutes() - departureTime.getTimezoneOffset());
        const minArrival = departureTime.toISOString().slice(0, 16);
        $('#arrival_datetime').attr('min', minArrival);
    });
    
    // Auto-update available seats when total seats changes
    $('#total_seats').on('change', function() {
        const totalSeats = parseInt(this.value) || 0;
        const availableSeats = parseInt($('#available_seats').val()) || 0;
        $('#available_seats').attr('max', totalSeats);
        if (availableSeats > totalSeats) {
            $('#available_seats').val(totalSeats);
        }
    });
    
    // Validate pricing hierarchy
    $('#business_price, #first_class_price').on('input', function() {
        validatePricing();
    });
    
    $('#economy_price').on('input', function() {
        validatePricing();
    });
    
    function validatePricing() {
        const economyPrice = parseFloat($('#economy_price').val()) || 0;
        const businessPrice = parseFloat($('#business_price').val()) || 0;
        const firstClassPrice = parseFloat($('#first_class_price').val()) || 0;
        
        if (businessPrice > 0 && businessPrice <= economyPrice) {
            $('#business_price').addClass('is-invalid');
            $('#business_price').next('.invalid-feedback').text('Business price must be higher than economy price.');
        } else {
            $('#business_price').removeClass('is-invalid');
        }
        
        if (firstClassPrice > 0 && firstClassPrice <= businessPrice) {
            $('#first_class_price').addClass('is-invalid');
            $('#first_class_price').next('.invalid-feedback').text('First class price must be higher than business price.');
        } else {
            $('#first_class_price').removeClass('is-invalid');
        }
    }
    
    // Amenities management
    let amenities = @json(old('amenities', []));
    
    function updateAmenitiesDisplay() {
        const container = $('#amenities_tags');
        container.empty();
        
        amenities.forEach(function(amenity, index) {
            const tag = $(`
                <span class="amenity-tag">
                    ${amenity}
                    <span class="remove-tag" data-index="${index}">×</span>
                </span>
            `);
            container.append(tag);
        });
        
        $('#amenities').val(JSON.stringify(amenities));
    }
    
    $('#amenities_input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const value = $(this).val().trim();
            if (value && !amenities.includes(value)) {
                amenities.push(value);
                updateAmenitiesDisplay();
                $(this).val('');
            }
        }
    });
    
    $(document).on('click', '.remove-tag', function() {
        const index = $(this).data('index');
        amenities.splice(index, 1);
        updateAmenitiesDisplay();
    });
    
    // Initialize amenities display
    updateAmenitiesDisplay();
    
    // Prevent same airport selection
    $('#departure_airport, #arrival_airport').on('change', function() {
        const departureAirport = $('#departure_airport').val();
        const arrivalAirport = $('#arrival_airport').val();
        
        if (departureAirport && arrivalAirport && departureAirport === arrivalAirport) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Departure and arrival airports must be different.</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
    // Form validation before submit
    $('#flightForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fill in all required fields.');
        }
    });
});

// Success/Error messages
@if(session('success'))
    toastr.success('{{ session('success') }}');
@endif

@if(session('error'))
    toastr.error('{{ session('error') }}');
@endif

// Validation errors
@if($errors->any())
    @foreach($errors->all() as $error)
        toastr.error('{{ $error }}');
    @endforeach
@endif
</script>
@stop
