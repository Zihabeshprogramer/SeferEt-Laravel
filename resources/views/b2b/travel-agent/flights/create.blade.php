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
        <div class="col-12 col-xl-10">
            <!-- Business Model Info Card -->
            <div class="card bg-gradient-info text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h5 class="mb-2">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Group Flight Booking System
                            </h5>
                            <p class="mb-0 opacity-90">
                                Create round-trip group bookings for 10+ passengers. Collaborate with other travel agents 
                                to fill seats and earn commissions. Perfect for tour groups, corporate travel, and events.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="feature-badges">
                                <span class="badge badge-light mr-1"><i class="fas fa-users mr-1"></i>Group Discounts</span>
                                <span class="badge badge-light mr-1"><i class="fas fa-handshake mr-1"></i>Agent Collaboration</span>
                                <span class="badge badge-light"><i class="fas fa-exchange-alt mr-1"></i>Round-trip</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plane-departure mr-2"></i>
                        Flight Booking Details
                    </h3>
                </div>
                <form action="{{ route('b2b.travel-agent.flights.store') }}" method="POST" id="groupFlightForm">
                    @csrf
                    <div class="card-body">
                        <!-- Trip Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-cogs mr-1"></i> Trip Configuration
                                </h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Trip Type <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="trip_type" id="round_trip" 
                                           value="round_trip" {{ old('trip_type', 'round_trip') == 'round_trip' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="round_trip">
                                        <strong><i class="fas fa-exchange-alt mr-1"></i>Round Trip</strong>
                                        <small class="d-block text-muted">Recommended for groups</small>
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="trip_type" id="one_way" 
                                           value="one_way" {{ old('trip_type') == 'one_way' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="one_way">
                                        <strong><i class="fas fa-arrow-right mr-1"></i>One Way</strong>
                                        <small class="d-block text-muted">Single direction</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Group Booking</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_group_booking" 
                                           name="is_group_booking" value="1" {{ old('is_group_booking', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_group_booking">
                                        <strong>Enable Group Discounts</strong>
                                        <small class="d-block text-muted">For 10+ passengers</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Agent Collaboration</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allows_agent_collaboration" 
                                           name="allows_agent_collaboration" value="1" {{ old('allows_agent_collaboration', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allows_agent_collaboration">
                                        <strong>Allow Partner Agents</strong>
                                        <small class="d-block text-muted">Share bookings & commissions</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- Flight Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-plane mr-1"></i> Flight Information
                                </h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="airline" class="form-label fw-bold">Airline <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('airline') is-invalid @enderror" 
                                       id="airline" name="airline" value="{{ old('airline') }}" 
                                       placeholder="e.g., Saudi Arabian Airlines, Emirates" required>
                                @error('airline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="flight_number" class="form-label fw-bold">Outbound Flight <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('flight_number') is-invalid @enderror" 
                                       id="flight_number" name="flight_number" value="{{ old('flight_number') }}" 
                                       placeholder="SV123" required pattern="[A-Z]{2}[0-9]{1,4}[A-Z]?">
                                @error('flight_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3 return-flight-group">
                                <label for="return_flight_number" class="form-label fw-bold">Return Flight</label>
                                <input type="text" class="form-control @error('return_flight_number') is-invalid @enderror" 
                                       id="return_flight_number" name="return_flight_number" value="{{ old('return_flight_number') }}" 
                                       placeholder="SV124" pattern="[A-Z]{2}[0-9]{1,4}[A-Z]?">
                                @error('return_flight_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Route Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-route mr-1"></i> Route & Schedule
                                </h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="departure_airport" class="form-label fw-bold">From <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('departure_airport') is-invalid @enderror" 
                                        id="departure_airport" name="departure_airport"
                                        data-placeholder="Select departure airport..." required>
                                    <option value=""></option>
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
                                <label for="arrival_airport" class="form-label fw-bold">To <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('arrival_airport') is-invalid @enderror" 
                                        id="arrival_airport" name="arrival_airport"
                                        data-placeholder="Select destination airport..." required>
                                    <option value=""></option>
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
                            <div class="col-md-6 mb-3">
                                <label for="departure_datetime" class="form-label fw-bold">Departure <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('departure_datetime') is-invalid @enderror" 
                                       id="departure_datetime" name="departure_datetime" value="{{ old('departure_datetime') }}" required>
                                @error('departure_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="arrival_datetime" class="form-label fw-bold">Arrival <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('arrival_datetime') is-invalid @enderror" 
                                       id="arrival_datetime" name="arrival_datetime" value="{{ old('arrival_datetime') }}" required>
                                @error('arrival_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3 return-schedule-group">
                                <label for="return_departure_datetime" class="form-label fw-bold">Return Departure</label>
                                <input type="datetime-local" class="form-control @error('return_departure_datetime') is-invalid @enderror" 
                                       id="return_departure_datetime" name="return_departure_datetime" value="{{ old('return_departure_datetime') }}">
                                @error('return_departure_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3 return-schedule-group">
                                <label for="return_arrival_datetime" class="form-label fw-bold">Return Arrival</label>
                                <input type="datetime-local" class="form-control @error('return_arrival_datetime') is-invalid @enderror" 
                                       id="return_arrival_datetime" name="return_arrival_datetime" value="{{ old('return_arrival_datetime') }}">
                                @error('return_arrival_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Group Settings -->
                        <div class="row mb-4 group-settings">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-users mr-1"></i> Group Booking Settings
                                </h6>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="total_seats" class="form-label fw-bold">Total Seats <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('total_seats') is-invalid @enderror" 
                                       id="total_seats" name="total_seats" value="{{ old('total_seats', 50) }}" 
                                       min="10" max="500" required>
                                @error('total_seats')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="available_seats" class="form-label fw-bold">Available Seats <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('available_seats') is-invalid @enderror" 
                                       id="available_seats" name="available_seats" value="{{ old('available_seats', 50) }}" 
                                       min="0" max="500" required>
                                @error('available_seats')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Seats currently available for booking</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="min_group_size" class="form-label fw-bold">Min Group Size</label>
                                <input type="number" class="form-control @error('min_group_size') is-invalid @enderror" 
                                       id="min_group_size" name="min_group_size" value="{{ old('min_group_size', 10) }}" 
                                       min="5" max="100">
                                @error('min_group_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="max_group_size" class="form-label fw-bold">Max Group Size</label>
                                <input type="number" class="form-control @error('max_group_size') is-invalid @enderror" 
                                       id="max_group_size" name="max_group_size" value="{{ old('max_group_size', 50) }}" 
                                       min="10" max="500">
                                @error('max_group_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="group_discount_percentage" class="form-label fw-bold">Group Discount %</label>
                                <input type="number" step="0.01" class="form-control @error('group_discount_percentage') is-invalid @enderror" 
                                       id="group_discount_percentage" name="group_discount_percentage" value="{{ old('group_discount_percentage', 10) }}" 
                                       min="0" max="50">
                                @error('group_discount_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="booking_deadline" class="form-label fw-bold">Booking Deadline</label>
                                <input type="date" class="form-control @error('booking_deadline') is-invalid @enderror" 
                                       id="booking_deadline" name="booking_deadline" value="{{ old('booking_deadline') }}">
                                @error('booking_deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Last day to accept bookings</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_terms" class="form-label fw-bold">Payment Terms <span class="text-danger">*</span></label>
                                <select class="form-control @error('payment_terms') is-invalid @enderror"
                                        id="payment_terms" name="payment_terms" required>
                                    @foreach($paymentTerms as $term => $label)
                                        <option value="{{ $term }}" {{ old('payment_terms', '50_percent_deposit') == $term ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Pricing -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-dollar-sign mr-1"></i> Pricing
                                </h6>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="currency" class="form-label fw-bold">Currency</label>
                                <select class="form-control @error('currency') is-invalid @enderror"
                                        id="currency" name="currency" required>
                                    @foreach($currencies as $code => $name)
                                        <option value="{{ $code }}" {{ old('currency', 'SAR') == $code ? 'selected' : '' }}>
                                            {{ $code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="economy_price" class="form-label fw-bold">Regular Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('economy_price') is-invalid @enderror" 
                                           id="economy_price" name="economy_price" value="{{ old('economy_price') }}" 
                                           min="0" required>
                                    <span class="input-group-text">per person</span>
                                </div>
                                @error('economy_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-3 group-pricing">
                                <label for="group_economy_price" class="form-label fw-bold">Group Price</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('group_economy_price') is-invalid @enderror" 
                                           id="group_economy_price" name="group_economy_price" value="{{ old('group_economy_price') }}" 
                                           min="0">
                                    <span class="input-group-text">per person</span>
                                </div>
                                @error('group_economy_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Discounted price for group bookings</small>
                            </div>
                        </div>
                        <!-- Agent Collaboration Settings -->
                        <div class="row mb-4 collaboration-settings">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-handshake mr-1"></i> Agent Collaboration
                                </h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="collaboration_commission_percentage" class="form-label fw-bold">Commission Rate %</label>
                                <input type="number" step="0.01" class="form-control @error('collaboration_commission_percentage') is-invalid @enderror" 
                                       id="collaboration_commission_percentage" name="collaboration_commission_percentage" 
                                       value="{{ old('collaboration_commission_percentage', 5.00) }}" min="1" max="20">
                                @error('collaboration_commission_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Commission for partner agents per booking</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="collaboration_terms" class="form-label fw-bold">Collaboration Terms</label>
                                <textarea class="form-control @error('collaboration_terms') is-invalid @enderror" 
                                          id="collaboration_terms" name="collaboration_terms" rows="3" 
                                          placeholder="Special terms, requirements, or conditions for partner agents...">{{ old('collaboration_terms') }}</textarea>
                                @error('collaboration_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-info mr-1"></i> Additional Information
                                </h6>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label fw-bold">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Describe your group flight offering, included services, special features...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    All group bookings are subject to our terms and conditions
                                </small>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('b2b.travel-agent.flights.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times mr-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Create Group Booking
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
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    .form-section {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    .form-check-input:checked {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    .feature-badges .badge {
        font-size: 0.75rem;
    }
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
        padding-right: 0;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem);
        right: 3px;
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection,
    .select2-container--bootstrap4.select2-container--open .select2-selection {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    .text-primary {
        color: #17a2b8 !important;
    }
</style>
@stop
@section('js')
<script>
$(document).ready(function() {
    console.log('Found .select2 elements:', $('.select2').length);
    // Initialize Select2 with proper configuration
    $('.select2').each(function(index) {
        const element = $(this);
        if (!element.hasClass('select2-hidden-accessible')) {
            try {
                element.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: element.data('placeholder') || 'Select an option...',
                    allowClear: true,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
            } catch (error) {
                console.error('✗ Select2 initialization failed on', this.id, ':', error);
            }
        } else {
        }
    });
    // Handle trip type change
    function toggleReturnFlightFields() {
        const tripType = $('input[name="trip_type"]:checked').val();
        const returnFields = $('.return-flight-group, .return-schedule-group');
        if (tripType === 'round_trip') {
            returnFields.show();
            $('#return_flight_number').attr('required', true);
            $('#return_departure_datetime, #return_arrival_datetime').attr('required', true);
        } else {
            returnFields.hide();
            $('#return_flight_number').removeAttr('required').val('');
            $('#return_departure_datetime, #return_arrival_datetime').removeAttr('required').val('');
        }
    }
    // Handle group booking toggle
    function toggleGroupSettings() {
        const isGroupBooking = $('#is_group_booking').is(':checked');
        $('.group-settings, .group-pricing').toggle(isGroupBooking);
        if (isGroupBooking) {
            $('#min_group_size, #max_group_size').attr('required', true);
        } else {
            $('#min_group_size, #max_group_size').removeAttr('required');
        }
    }
    // Handle collaboration toggle
    function toggleCollaborationSettings() {
        const allowsCollaboration = $('#allows_agent_collaboration').is(':checked');
        $('.collaboration-settings').toggle(allowsCollaboration);
        if (allowsCollaboration) {
            $('#collaboration_commission_percentage').attr('required', true);
        } else {
            $('#collaboration_commission_percentage').removeAttr('required');
        }
    }
    // Event handlers
    $('input[name="trip_type"]').on('change', toggleReturnFlightFields);
    $('#is_group_booking').on('change', toggleGroupSettings);
    $('#allows_agent_collaboration').on('change', toggleCollaborationSettings);
    // Set minimum datetime to current time
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDateTime = now.toISOString().slice(0, 16);
    $('#departure_datetime').attr('min', minDateTime);
    // Update dependent datetime fields
    $('#departure_datetime').on('change', function() {
        const departureTime = new Date(this.value);
        departureTime.setHours(departureTime.getHours() + 1); // Min 1 hour flight
        const minArrival = departureTime.toISOString().slice(0, 16);
        $('#arrival_datetime').attr('min', minArrival);
    });
    $('#arrival_datetime').on('change', function() {
        const arrivalTime = new Date(this.value);
        arrivalTime.setHours(arrivalTime.getHours() + 2); // Min 2 hours layover
        const minReturnDeparture = arrivalTime.toISOString().slice(0, 16);
        $('#return_departure_datetime').attr('min', minReturnDeparture);
    });
    $('#return_departure_datetime').on('change', function() {
        if (this.value) {
            const returnDepartureTime = new Date(this.value);
            returnDepartureTime.setHours(returnDepartureTime.getHours() + 1);
            const minReturnArrival = returnDepartureTime.toISOString().slice(0, 16);
            $('#return_arrival_datetime').attr('min', minReturnArrival);
        }
    });
    // Update group size limits and validation
    $('#total_seats').on('change', function() {
        const totalSeats = parseInt(this.value) || 0;
        $('#max_group_size').attr('max', totalSeats);
        $('#available_seats').attr('max', totalSeats);
        // Sync available seats with total seats by default
        if (!$('#available_seats').val() || parseInt($('#available_seats').val()) > totalSeats) {
            $('#available_seats').val(totalSeats);
        }
        if (parseInt($('#max_group_size').val()) > totalSeats) {
            $('#max_group_size').val(totalSeats);
        }
    });
    // Group size validation
    $('#min_group_size').on('input', function() {
        const minSize = parseInt(this.value) || 0;
        const maxSize = parseInt($('#max_group_size').val()) || 0;
        // Update max_group_size minimum
        $('#max_group_size').attr('min', minSize);
        // If max size is less than min size, update it
        if (maxSize > 0 && maxSize < minSize) {
            $('#max_group_size').val(minSize);
        }
    });
    $('#max_group_size').on('input', function() {
        const maxSize = parseInt(this.value) || 0;
        const minSize = parseInt($('#min_group_size').val()) || 0;
        const totalSeats = parseInt($('#total_seats').val()) || 0;
        // Validate against minimum group size
        if (minSize > 0 && maxSize < minSize) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Maximum group size must be greater than or equal to minimum group size.</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
        // Validate against total seats
        if (totalSeats > 0 && maxSize > totalSeats) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Maximum group size cannot exceed total seats.</div>');
            }
        }
    });
    // Auto-calculate group price
    $('#economy_price, #group_discount_percentage').on('input', function() {
        const regularPrice = parseFloat($('#economy_price').val()) || 0;
        const discountPercent = parseFloat($('#group_discount_percentage').val()) || 0;
        if (regularPrice > 0 && discountPercent > 0) {
            const groupPrice = regularPrice * (1 - discountPercent / 100);
            $('#group_economy_price').val(groupPrice.toFixed(2));
        }
    });
    // Prevent same airport selection
    $('#departure_airport, #arrival_airport').on('change', function() {
        const departureAirport = $('#departure_airport').val();
        const arrivalAirport = $('#arrival_airport').val();
        if (departureAirport && arrivalAirport && departureAirport === arrivalAirport) {
            $(this).val('').trigger('change');
            alert('Departure and arrival airports must be different.');
        }
    });
    // Initialize visibility
    toggleReturnFlightFields();
    toggleGroupSettings();
    toggleCollaborationSettings();
    // Set default booking deadline (30 days from departure)
    $('#departure_datetime').on('change', function() {
        if (this.value && !$('#booking_deadline').val()) {
            const departureDate = new Date(this.value);
            departureDate.setDate(departureDate.getDate() - 30);
            const deadlineDate = departureDate.toISOString().split('T')[0];
            $('#booking_deadline').val(deadlineDate);
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
@if($errors->any())
    console.log('Validation errors found:', @json($errors->all()));
    @foreach($errors->all() as $error)
        toastr.error('{{ $error }}');
    @endforeach
@endif
</script>
@stop
