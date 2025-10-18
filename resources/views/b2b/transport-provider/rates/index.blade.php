@extends('layouts.b2b')

@section('title', 'Transport Rates & Pricing Management')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-title', 'Transport Rates & Pricing Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rates & Pricing</li>
@endsection

@section('content')
    <!-- Rate Management Tabs -->
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="rateTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="current-rates-tab" data-toggle="tab" href="#current-rates" role="tab">
                        <i class="fas fa-list mr-2"></i>Current Rates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="service-overview-tab" data-toggle="tab" href="#service-overview" role="tab">
                        <i class="fas fa-bus mr-2"></i>Service Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar-view" role="tab">
                        <i class="fas fa-calendar-alt mr-2"></i>Calendar View
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pricing-rules-tab" data-toggle="tab" href="#pricing-rules" role="tab">
                        <i class="fas fa-cogs mr-2"></i>Pricing Rules
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="rateTabContent">
                <!-- Current Rates Tab -->
                <div class="tab-pane fade show active" id="current-rates" role="tabpanel">
                    @if($services->count() > 0)
                        @foreach($services as $service)
                            <div class="service-section mb-4" id="service-section-{{ $service->id }}">
                                <div class="service-header d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h5 class="mb-1">{{ $service->service_name }}</h5>
                                        <span class="text-muted">
                                            <i class="fas fa-bus mr-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $service->transport_type)) }}
                                        </span>
                                        <span class="badge badge-info ml-2">{{ count($service->routes ?? []) }} routes</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary expand-service-btn" data-service-id="{{ $service->id }}">
                                            <i class="fas fa-expand-arrows-alt mr-1"></i>Manage Rates
                                        </button>
                                        <button class="btn btn-sm btn-success bulk-pricing-service-btn" 
                                                data-toggle="modal" 
                                                data-target="#bulkPricingModal"
                                                data-service-id="{{ $service->id }}" 
                                                data-service-name="{{ $service->service_name }}">
                                            <i class="fas fa-magic mr-1"></i>Bulk Price
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="service-routes mt-3" id="service-{{ $service->id }}-routes" style="display: none;">
                                    @if($service->routes && count($service->routes) > 0)
                                        @php
                                            // Group routes by route combinations with transport type and base price
                                            $routeGroups = collect($service->routes)->groupBy(function($route) use ($service) {
                                                return $service->id . '|' . $service->transport_type . '|' . number_format($service->price ?? 0, 2);
                                            });
                                        @endphp
                                        
                                        <div class="route-groups-container">
                                            @foreach($routeGroups as $groupKey => $routesInGroup)
                                                @php
                                                    $routeCount = $routesInGroup->count();
                                                @endphp
                                                
                                                <div class="route-group-card mb-4" data-group-key="{{ $groupKey }}">
                                                    <div class="card">
                                                        <div class="card-header bg-light">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-8">
                                                                    <h6 class="mb-1 font-weight-bold text-primary">
                                                                        <i class="fas fa-route mr-2"></i>
                                                                        {{ $service->service_name }} Routes
                                                                        <span class="badge badge-info ml-2">{{ $routeCount }} routes</span>
                                                                    </h6>
                                                                    <div class="route-group-details text-muted small">
                                                                        <i class="fas fa-users mr-1"></i> Max {{ $service->max_passengers ?? 4 }} passengers
                                                                        <span class="mx-2">•</span>
                                                                        <i class="fas fa-dollar-sign mr-1"></i> Base: ${{ number_format($service->price ?? 0, 2) }}
                                                                        <span class="mx-2">•</span>
                                                                        <i class="fas fa-check-circle mr-1"></i> {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 text-right">
                                                                    <div class="btn-group" role="group">
                                                                        <button class="btn btn-success btn-sm set-group-rate-btn" 
                                                                                data-toggle="modal" 
                                                                                data-target="#setGroupRateModal"
                                                                                data-group-key="{{ $groupKey }}"
                                                                                data-service-type="{{ $service->transport_type }}"
                                                                                data-base-price="{{ $service->price ?? 0 }}"
                                                                                data-route-count="{{ $routeCount }}"
                                                                                title="Set Rate for All {{ $routeCount }} Routes">
                                                                            <i class="fas fa-magic mr-1"></i>Set Group Rate
                                                                        </button>
                                                                        <button class="btn btn-outline-primary btn-sm toggle-group-details" 
                                                                                data-group-key="{{ $groupKey }}"
                                                                                data-group-id="group-details-{{ md5($groupKey) }}"
                                                                                title="View Individual Routes">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Group Rate Display -->
                                                        <div class="card-body py-2 bg-white">
                                                            @php
                                                                $groupCurrentRates = [];
                                                                foreach($routesInGroup as $route) {
                                                                    // Get current rates for each route
                                                                    $currentRates = \App\Models\TransportRate::where('transport_service_id', $service->id)
                                                                        ->where('route_from', $route['from'])
                                                                        ->where('route_to', $route['to'])
                                                                        ->where('date', '>=', now()->format('Y-m-d'))
                                                                        ->where('is_available', true)
                                                                        ->orderBy('date')
                                                                        ->take(3)
                                                                        ->get();
                                                                    
                                                                    if($currentRates->count() > 0) {
                                                                        $groupCurrentRates = array_merge($groupCurrentRates, $currentRates->toArray());
                                                                    }
                                                                }
                                                                $uniqueRates = collect($groupCurrentRates)->groupBy('base_rate');
                                                            @endphp
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <small class="text-muted font-weight-bold">Current Group Rates:</small>
                                                                    <div class="mt-1">
                                                                        @if($uniqueRates->count() === 1)
                                                                            @php $rate = collect($groupCurrentRates)->first(); @endphp
                                                                            <span class="badge badge-success badge-lg">
                                                                                <i class="fas fa-check-circle mr-1"></i>
                                                                                ${{ number_format($rate['base_rate'], 2) }} (Consistent)
                                                                            </span>
                                                                            @if($rate['notes'])
                                                                                <small class="text-info d-block">{{ $rate['notes'] }}</small>
                                                                            @endif
                                                                        @elseif($uniqueRates->count() > 1)
                                                                            <span class="badge badge-warning badge-lg">
                                                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                                                Mixed Rates ({{ $uniqueRates->count() }} different prices)
                                                                            </span>
                                                                            <div class="mt-1">
                                                                                @foreach($uniqueRates as $price => $rates)
                                                                                    <small class="badge badge-outline-info mr-1">
                                                                                        ${{ number_format($price, 2) }} ({{ count($rates) }} entries)
                                                                                    </small>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <span class="badge badge-secondary">
                                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                                Using Base Prices
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <small class="text-muted font-weight-bold">Quick Actions:</small>
                                                                    <div class="mt-1">
                                                                        <button class="btn btn-outline-info btn-xs mr-1 group-history-btn" 
                                                                                data-group-key="{{ $groupKey }}"
                                                                                data-service-type="{{ $service->transport_type }}">
                                                                            <i class="fas fa-history mr-1"></i>History
                                                                        </button>
                                                                        <button class="btn btn-outline-warning btn-xs mr-1 copy-rates-btn" 
                                                                                data-group-key="{{ $groupKey }}">
                                                                            <i class="fas fa-copy mr-1"></i>Copy Rates
                                                                        </button>
                                                                        <button class="btn btn-outline-danger btn-xs clear-rates-btn" 
                                                                                data-group-key="{{ $groupKey }}">
                                                                            <i class="fas fa-trash mr-1"></i>Clear
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Individual Route Details (Hidden by Default) -->
                                                        <div class="group-route-details" id="group-details-{{ md5($groupKey) }}" style="display: none;">
                                                            <div class="p-3">
                                                                <h6 class="font-weight-bold mb-3">Individual Routes in this Group</h6>
                                                                <div class="row">
                                                                    @foreach($routesInGroup as $route)
                                                                        <div class="col-md-6 mb-3">
                                                                            <div class="card bg-light">
                                                                                <div class="card-body p-3">
                                                                                    <h6 class="card-title mb-2">
                                                                                        {{ $route['from'] }} <i class="fas fa-arrow-right mx-1"></i> {{ $route['to'] }}
                                                                                    </h6>
                                                                                    <div class="small text-muted mb-2">
                                                                                        @if(isset($route['duration']))
                                                                                            <i class="fas fa-clock mr-1"></i> {{ $route['duration'] }}
                                                                                        @endif
                                                                                        @if(isset($route['distance']))
                                                                                            <span class="mx-2">•</span>
                                                                                            <i class="fas fa-ruler mr-1"></i> {{ $route['distance'] }}
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                                        <button class="btn btn-outline-primary set-individual-rate-btn" 
                                                                                                data-toggle="modal"
                                                                                                data-target="#setIndividualRateModal"
                                                                                                data-service-id="{{ $service->id }}"
                                                                                                data-route-from="{{ $route['from'] }}"
                                                                                                data-route-to="{{ $route['to'] }}"
                                                                                                data-service-name="{{ $service->service_name }}">
                                                                                            <i class="fas fa-edit mr-1"></i>Set Rate
                                                                                        </button>
                                                                                        <button class="btn btn-outline-info route-history-btn"
                                                                                                data-service-id="{{ $service->id }}"
                                                                                                data-route-from="{{ $route['from'] }}"
                                                                                                data-route-to="{{ $route['to'] }}">
                                                                                            <i class="fas fa-history"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            No routes defined for this service. 
                                            <a href="{{ route('b2b.transport-provider.edit', $service) }}" class="alert-link">
                                                Add routes first
                                            </a>.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bus fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Transport Services Found</h4>
                            <p class="text-muted">Add transport services first to manage their rates and pricing.</p>
                            <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus mr-2"></i>Add Your First Transport Service
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Service Overview Tab -->
                <div class="tab-pane fade" id="service-overview" role="tabpanel">
                    <div class="row">
                        @if($services->count() > 0)
                            @foreach($services as $service)
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">{{ $service->service_name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Transport Type:</strong><br>
                                                    <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $service->transport_type)) }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Max Passengers:</strong><br>
                                                    {{ $service->max_passengers ?? 'N/A' }}
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Base Price:</strong><br>
                                                    ${{ number_format($service->price ?? 0, 2) }}
                                                </div>
                                                <div class="col-6">
                                                    <strong>Routes:</strong><br>
                                                    {{ count($service->routes ?? []) }} routes
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#serviceDetailsModal-{{ $service->id }}">
                                                <i class="fas fa-info-circle mr-1"></i>View Details
                                            </button>
                                            <a href="{{ route('b2b.transport-provider.edit', $service) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit mr-1"></i>Edit Service
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-bus fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Services Available</h4>
                                <p class="text-muted">Create your first transport service to get started.</p>
                                <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-2"></i>Add Transport Service
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Calendar View Tab -->
                <div class="tab-pane fade" id="calendar-view" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-control" id="calendar-service-filter">
                                <option value="">All Services</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->service_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="calendar-route-filter">
                                <option value="">All Routes</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="calendar-passenger-filter">
                                <option value="">All Passenger Types</option>
                                <option value="adult">Adult</option>
                                <option value="child">Child</option>
                                <option value="infant">Infant</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Transport Rate Calendar</h5>
                                <div class="calendar-legend">
                                    <small class="text-muted mr-3">
                                        <i class="fas fa-database text-primary mr-1"></i>Fixed Rate
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-magic text-warning mr-1"></i>🏷️ Pricing Rule
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="transport-rate-calendar">
                                <!-- Calendar will be loaded here via JavaScript -->
                                <div class="text-center py-5" id="calendar-loading-state">
                                    <i class="fas fa-info-circle fa-2x text-primary mb-3"></i>
                                    <h5 class="text-muted">Select a Service to View Calendar</h5>
                                    <p class="text-muted">Choose a transport service from the dropdown above to view its rates calendar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Rules Tab -->
                <div class="tab-pane fade" id="pricing-rules" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h5>Transport Pricing Rules</h5>
                            <p class="text-muted">Manage automatic pricing adjustments based on various criteria.</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-success" data-toggle="modal" data-target="#createPricingRuleModal">
                                <i class="fas fa-plus mr-1"></i>Create Pricing Rule
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
                </div>
            </div>
        </div>
    </div>

    <!-- Service Details Modals -->
    @foreach($services as $service)
    <div class="modal fade" id="serviceDetailsModal-{{ $service->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-bus mr-2"></i>{{ $service->service_name }} - Service Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="35%">Service Name:</th>
                                            <td>{{ $service->service_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Transport Type:</th>
                                            <td><span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $service->transport_type)) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Route Type:</th>
                                            <td><span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $service->route_type)) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Max Passengers:</th>
                                            <td><span class="badge badge-success"><i class="fas fa-users mr-1"></i>{{ $service->max_passengers ?? 'N/A' }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Base Price:</th>
                                            <td><span class="badge badge-warning">SAR {{ number_format($service->price ?? 0, 2) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($service->is_active)
                                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Active</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-pause-circle mr-1"></i>Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created:</th>
                                            <td>{{ $service->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Routes ({{ count($service->routes ?? []) }})</h6>
                                </div>
                                <div class="card-body">
                                    @if($service->routes && count($service->routes) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Duration</th>
                                                        <th>Distance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($service->routes as $route)
                                                        <tr>
                                                            <td><strong>{{ $route['from'] ?? 'N/A' }}</strong></td>
                                                            <td><strong>{{ $route['to'] ?? 'N/A' }}</strong></td>
                                                            <td>{{ $route['duration'] ?? 'N/A' }}</td>
                                                            <td>{{ $route['distance'] ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-route fa-2x mb-2"></i>
                                            <p>No routes defined yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Rate Statistics</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $rateStats = \App\Models\TransportRate::where('transport_service_id', $service->id)
                                                                                  ->selectRaw('COUNT(*) as total_rates, MIN(base_rate) as min_rate, MAX(base_rate) as max_rate, AVG(base_rate) as avg_rate')
                                                                                  ->first();
                                        $upcomingRates = \App\Models\TransportRate::where('transport_service_id', $service->id)
                                                                                     ->where('date', '>=', now())
                                                                                     ->where('is_available', true)
                                                                                     ->count();
                                    @endphp
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-primary">{{ $rateStats->total_rates ?? 0 }}</h4>
                                            <small class="text-muted">Total Rates</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $upcomingRates }}</h4>
                                            <small class="text-muted">Upcoming Rates</small>
                                        </div>
                                    </div>
                                    @if($rateStats && $rateStats->total_rates > 0)
                                        <hr>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <strong>SAR {{ number_format($rateStats->min_rate, 2) }}</strong>
                                                <br><small class="text-muted">Min Rate</small>
                                            </div>
                                            <div class="col-4">
                                                <strong>SAR {{ number_format($rateStats->avg_rate, 2) }}</strong>
                                                <br><small class="text-muted">Avg Rate</small>
                                            </div>
                                            <div class="col-4">
                                                <strong>SAR {{ number_format($rateStats->max_rate, 2) }}</strong>
                                                <br><small class="text-muted">Max Rate</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Pricing Rules</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $servicePricingRules = \App\Models\TransportPricingRule::where('transport_service_id', $service->id)
                                                                                                     ->where('is_active', true)
                                                                                                     ->orderBy('priority', 'desc')
                                                                                                     ->get();
                                    @endphp
                                    @if($servicePricingRules->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($servicePricingRules->take(3) as $rule)
                                                <div class="list-group-item px-0 py-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong class="text-primary">{{ $rule->rule_name }}</strong>
                                                            <br><small class="text-muted">{{ $rule->description }}</small>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="badge badge-info">{{ $rule->formatted_adjustment }}</span>
                                                            <br><small class="text-muted">Priority: {{ $rule->priority }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($servicePricingRules->count() > 3)
                                                <div class="list-group-item px-0 py-2 text-center">
                                                    <small class="text-muted">... and {{ $servicePricingRules->count() - 3 }} more rules</small>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-cogs fa-2x mb-2"></i>
                                            <p>No pricing rules configured</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('b2b.transport-provider.edit', $service) }}" class="btn btn-primary">
                        <i class="fas fa-edit mr-1"></i>Edit Service
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Modals -->
    <!-- Set Individual Rate Modal -->
    <div class="modal fade" id="setIndividualRateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>Set Individual Rate
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="individualRateForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Individual Rate Setting:</strong> Set specific rates for a single route and date range. This will apply to all days in the selected period.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-bus mr-1 text-primary"></i>Transport Service <span class="text-danger">*</span></label>
                                    <select class="form-control" id="individual-rate-service-id" name="service_id" required>
                                        <option value="">Choose a transport service...</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->service_name }} ({{ ucfirst($service->transport_type) }})</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select which transport service this rate applies to</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-users mr-1 text-primary"></i>Passenger Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="passenger_type" required>
                                        <option value="">Select passenger type...</option>
                                        <option value="adult">Adult (12+ years)</option>
                                        <option value="child">Child (2-11 years)</option>
                                        <option value="infant">Infant (0-2 years)</option>
                                    </select>
                                    <small class="form-text text-muted">Different rates can apply to different age groups</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-route mr-1"></i>Route Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Departure Location <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="individual-rate-route-from" name="route_from" required placeholder="e.g., Jeddah Airport">
                                            <small class="form-text text-muted">Starting point of the journey</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Arrival Location <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="individual-rate-route-to" name="route_to" required placeholder="e.g., Makkah Hotel District">
                                            <small class="form-text text-muted">Destination point of the journey</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-calendar mr-1"></i>Date Range</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="individual-rate-start-date" name="start_date" required min="{{ date('Y-m-d') }}">
                                            <small class="form-text text-muted">First day this rate applies</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="individual-rate-end-date" name="end_date" required min="{{ date('Y-m-d') }}">
                                            <small class="form-text text-muted">Last day this rate applies</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-1"></i>Pricing</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Rate Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="base_rate" step="0.01" required placeholder="0.00" min="0">
                                            </div>
                                            <small class="form-text text-muted">Enter the rate per passenger for this route</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Currency <span class="text-danger">*</span></label>
                                            <select class="form-control" name="currency" required>
                                                <option value="SAR" selected>SAR (Saudi Riyal)</option>
                                                <option value="USD">USD (US Dollar)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-sticky-note mr-1"></i>Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional: Add any special conditions, discounts, or information about this rate..."></textarea>
                            <small class="form-text text-muted">Any additional information about this rate (optional)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Save Individual Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Set Group Rate Modal -->
    <div class="modal fade" id="setGroupRateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-layer-group mr-2"></i>Set Group Rate
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="groupRateForm">
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Group Rate Setting:</strong> <span id="group-rate-info">Apply the same rate to multiple routes at once</span>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-calendar mr-1"></i>Date Range</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="start_date" required min="{{ date('Y-m-d') }}">
                                            <small class="form-text text-muted">First day the group rate applies</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="end_date" required min="{{ date('Y-m-d') }}">
                                            <small class="form-text text-muted">Last day the group rate applies</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-1"></i>Rate Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rate Type <span class="text-danger">*</span></label>
                                            <select class="form-control" id="group-rate-type" name="rate_type" required>
                                                <option value="">Choose rate type...</option>
                                                <option value="fixed" selected>Fixed Price - Set exact amount</option>
                                                <option value="base_plus">Base + Amount - Add/subtract to base price</option>
                                                <option value="base_percentage">Base + Percentage - Percentage increase/discount</option>
                                            </select>
                                            <small class="form-text text-muted">How to calculate the final rate</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Currency <span class="text-danger">*</span></label>
                                            <select class="form-control" name="currency" required>
                                                <option value="SAR" selected>SAR (Saudi Riyal)</option>
                                                <option value="USD">USD (US Dollar)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Fixed Price Configuration -->
                                <div id="fixed-price-config" class="rate-config" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Fixed Rate Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control" id="fixed-rate-value" step="0.01" placeholder="100.00" min="0">
                                                </div>
                                                <small class="form-text text-muted">The exact price per passenger for all routes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Base Plus Configuration -->
                                <div id="base-plus-config" class="rate-config" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Adjustment Type <span class="text-danger">*</span></label>
                                                <select class="form-control" id="base-plus-type">
                                                    <option value="increase">Increase (Add to base price)</option>
                                                    <option value="decrease">Discount (Subtract from base price)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="base-plus-symbol">+</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="base-plus-value" step="0.01" placeholder="25.00" min="0">
                                                </div>
                                                <small class="form-text text-muted">Amount to add or subtract from base price</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Example:</strong> If base price is 100 SAR and you set +25 SAR, final price = 125 SAR
                                    </div>
                                </div>
                                
                                <!-- Base Percentage Configuration -->
                                <div id="base-percentage-config" class="rate-config" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Adjustment Type <span class="text-danger">*</span></label>
                                                <select class="form-control" id="base-percentage-type">
                                                    <option value="increase">Increase (Percentage markup)</option>
                                                    <option value="decrease">Discount (Percentage discount)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Percentage <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="base-percentage-symbol">+</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="base-percentage-value" step="0.1" placeholder="15.0" min="0" max="100">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Percentage to increase or decrease base price</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Example:</strong> If base price is 100 SAR and you set +15%, final price = 115 SAR
                                    </div>
                                </div>
                                
                                <!-- Hidden input for actual rate value -->
                                <input type="hidden" name="base_rate" id="final-base-rate">
                                <input type="hidden" name="adjustment_direction" id="adjustment-direction" value="increase">
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-cogs mr-1"></i>Application Options</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="apply-to-all" name="apply_to_all" value="1" checked>
                                            <label class="custom-control-label" for="apply-to-all">
                                                <strong>Apply to all routes</strong>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Apply this rate to all routes in the selected service group</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="override-existing" name="override_existing" value="1">
                                            <label class="custom-control-label" for="override-existing">
                                                <strong>Override existing rates</strong>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Replace any existing rates for the same dates and routes</small>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning mt-3" id="override-warning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Warning:</strong> This will replace existing rates. Make sure this is what you want to do.
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-sticky-note mr-1"></i>Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional: Add any special conditions or information about this group rate..."></textarea>
                            <small class="form-text text-muted">Any additional information about this group rate (optional)</small>
                        </div>
                        
                        <input type="hidden" id="group-rate-group-key" name="group_key">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-layer-group mr-1"></i>Apply Group Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Pricing Modal -->
    <div class="modal fade" id="bulkPricingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Pricing</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-wrench mr-2"></i>
                        <strong>Coming Soon!</strong> Bulk pricing feature is under development.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Pricing Rule Modal -->
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
                        
                        <!-- Basic Information Card -->
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
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}" data-base-price="{{ $service->price ?? 100 }}">{{ $service->service_name }} ({{ ucfirst($service->transport_type) }})</option>
                                                @endforeach
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
                        
                        <!-- Rule Type Card -->
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
                        
                        <!-- Pricing Adjustment Card -->
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
                                
                                <!-- Percentage Configuration -->
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
                                
                                <!-- Fixed Amount Configuration -->
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
                                
                                <!-- Multiplier Configuration -->
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
                                
                                <!-- Hidden input for the final adjustment value -->
                                <input type="hidden" name="adjustment_value" id="final-adjustment-value">
                            </div>
                        </div>
                        
                        <!-- Conditional Fields Card -->
                        <div class="card mb-4" id="conditional-fields-card" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-sliders-h mr-1"></i>Rule Conditions</h6>
                            </div>
                            <div class="card-body">
                                <!-- Date Range Fields (for seasonal) -->
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
                                
                                <!-- Days of Week Fields -->
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
                                
                                <!-- Passenger Count Fields -->
                                <div class="conditional-field" id="passenger-count-fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="create-min-passengers">Minimum Passengers</label>
                                                <input type="number" class="form-control" id="create-min-passengers" name="min_passengers" min="1" placeholder="1">
                                                <small class="form-text text-muted">Minimum number of passengers for this rule</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="create-max-passengers">Maximum Passengers</label>
                                                <input type="number" class="form-control" id="create-max-passengers" name="max_passengers" min="1" placeholder="10">
                                                <small class="form-text text-muted">Maximum number of passengers for this rule</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Distance Fields -->
                                <div class="conditional-field" id="distance-fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="create-min-distance">Minimum Distance (km)</label>
                                                <input type="number" class="form-control" id="create-min-distance" name="min_distance" step="0.1" min="0" placeholder="0">
                                                <small class="form-text text-muted">Minimum route distance for this rule</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="create-max-distance">Maximum Distance (km)</label>
                                                <input type="number" class="form-control" id="create-max-distance" name="max_distance" step="0.1" min="0" placeholder="100">
                                                <small class="form-text text-muted">Maximum route distance for this rule</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Advance Booking Fields -->
                                <div class="conditional-field" id="advance-booking-fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="create-min-advance-hours">Minimum Advance Hours</label>
                                                <input type="number" class="form-control" id="create-min-advance-hours" name="min_advance_hours" min="0" placeholder="24">
                                                <small class="form-text text-muted">Minimum hours before trip for this rule</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="create-max-advance-hours">Maximum Advance Hours</label>
                                                <input type="number" class="form-control" id="create-max-advance-hours" name="max_advance_hours" min="0" placeholder="720">
                                                <small class="form-text text-muted">Maximum hours before trip for this rule</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Settings Card -->
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
                                                <!-- Hidden input to ensure a value is always sent -->
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
                        
                        <!-- Live Preview Card -->
                        <div class="card" id="preview-card" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-eye mr-1"></i>Live Preview</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success" id="pricing-preview">
                                    <i class="fas fa-calculator mr-2"></i>
                                    <strong>Preview:</strong> <span id="rule-preview-text">Configure the rule to see a preview</span>
                                </div>
                                <div class="row text-center" id="preview-examples" style="display: none;">
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <small class="text-muted">Base Price</small>
                                                <h5 id="preview-base-price" class="mb-0">100 SAR</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <small class="text-muted">Adjustment</small>
                                                <h5 id="preview-adjustment" class="mb-0">+15%</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body py-2">
                                                <small>Final Price</small>
                                                <h5 id="preview-final-price" class="mb-0">115 SAR</h5>
                                            </div>
                                        </div>
                                    </div>
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

    <!-- Edit Pricing Rule Modal -->
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
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->service_name }} ({{ ucfirst($service->transport_type) }})</option>
                                        @endforeach
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

@endsection

@section('css')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <style>
        /* General Layout Improvements */
        .small-box {
            border-radius: 0.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .small-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Card Enhancements */
        .card {
            border: none;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            transition: box-shadow 0.15s ease-in-out;
        }
        
        .card:hover {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 2px 6px rgba(0,0,0,.3);
        }
        
        .route-group-card {
            transition: all 0.3s ease;
        }
        
        .route-group-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .group-route-details {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        /* Badge and Button Styling */
        .badge-lg {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        
        .btn-group-sm > .btn {
            border-radius: 0.25rem;
        }
        
        .btn {
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        /* Calendar Styling */
        #transport-rate-calendar {
            min-height: 500px;
        }
        
        .fc-event {
            border: none !important;
            border-radius: 4px !important;
            padding: 2px 4px !important;
            font-size: 0.75rem !important;
        }
        
        .fc-daygrid-event {
            margin: 1px !important;
        }
        
        /* Tab Styling */
        .nav-tabs .nav-link {
            border-radius: 0.5rem 0.5rem 0 0;
            border: 1px solid transparent;
            color: #495057;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6;
            background-color: #f8f9fa;
        }
        
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            color: #495057;
        }
        
        /* Modal Enhancements */
        .modal-content {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            border-radius: 0.5rem 0.5rem 0 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .modal-footer {
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        
        /* Form Styling */
        .form-control {
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Pricing Rules Cards */
        .pricing-rule-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .pricing-rule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Status Badges */
        .badge {
            font-weight: 500;
            letter-spacing: 0.025em;
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-info {
            background-color: #17a2b8;
        }
        
        /* Loading States */
        .loading-overlay {
            position: relative;
        }
        
        .loading-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            z-index: 1000;
        }
        
        /* Enhanced Pricing Rules Modal Styling */
        .adjustment-config {
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 0.5rem;
            background-color: #f8f9fa;
        }
        
        .conditional-field {
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }
        
        .btn-group-toggle .btn {
            border-radius: 0.25rem !important;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .btn-group-toggle .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-group-toggle .btn.active {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        #adjustment-direction-group .btn {
            font-weight: 600;
            min-height: 44px;
        }
        
        #adjustment-direction-group .btn-outline-success.active {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        #adjustment-direction-group .btn-outline-danger.active {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        #preview-card .card-header {
            background-color: #e9ecef;
        }
        
        #preview-examples .card {
            transition: transform 0.2s ease;
        }
        
        #preview-examples .card:hover {
            transform: scale(1.05);
        }
        
        /* Emojis in select options */
        select option {
            padding: 0.375rem 0.75rem;
        }
        
        /* Animation for conditional fields */
        .conditional-field, .adjustment-config {
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Days of week styling */
        .btn-group-toggle .btn-outline-warning.active {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        /* Responsive Improvements */
        @media (max-width: 768px) {
            .btn-group-sm > .btn {
                padding: 0.25rem 0.375rem;
                font-size: 0.7rem;
            }
            
            .modal-dialog {
                margin: 1rem;
            }
            
            .card-header {
                padding: 0.75rem;
            }
        }
        
        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-up {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Service Overview Cards */
        .service-overview-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .service-overview-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }
        
        /* Notification Styling */
        .alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
    </style>
@endsection

@section('scripts')
<!-- Include FullCalendar with fallback -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js" onload="console.log('FullCalendar loaded successfully')" onerror="console.error('Failed to load FullCalendar from primary CDN')"></script>
<script>
// Fallback FullCalendar CDN if primary fails
if (typeof FullCalendar === 'undefined') {
    document.write('<script src="https://unpkg.com/fullcalendar@6.1.8/index.global.min.js"><\/script>');
}
</script>
<script>
let transportCalendar = null;
let currentPricingRules = [];

$(document).ready(function() {

    
    // Initialize CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Expand/Collapse service sections
    $('.expand-service-btn').click(function() {
        var serviceId = $(this).data('service-id');
        var routesContainer = $('#service-' + serviceId + '-routes');
        
        routesContainer.slideToggle();
        
        var icon = $(this).find('i');
        icon.toggleClass('fa-expand-arrows-alt fa-compress-arrows-alt');
    });

    // Toggle group details
    $('.toggle-group-details').click(function() {
        var groupId = $(this).data('group-id');
        $('#' + groupId).slideToggle();
        
        // Update icon
        var icon = $(this).find('i');
        icon.toggleClass('fa-eye fa-eye-slash');
    });
    
    // QUICK ACTION HANDLERS
    
    // Group History Button
    $(document).on('click', '.group-history-btn', function() {
        var groupKey = $(this).data('group-key');
        var serviceType = $(this).data('service-type');
        
        showNotification('info', 'Loading rate history for ' + serviceType + ' group...');
        
        // Parse group key to get service details
        var keyParts = groupKey.split('|');
        var serviceId = keyParts[0];
        
        // Make AJAX call to get group history
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-rates.group-history") }}',
            method: 'GET',
            data: {
                service_id: serviceId,
                group_key: groupKey,
                limit: 30
            },
            success: function(response) {
                if (response.success) {
                    showRateHistoryModal(response.data, 'Group Rate History - ' + serviceType);
                } else {
                    showNotification('warning', 'No rate history found for this group.');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to load rate history.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('error', errorMessage);
            }
        });
    });
    
    // Copy Rates Button
    $(document).on('click', '.copy-rates-btn', function() {
        var groupKey = $(this).data('group-key');
        
        // Show copy rates modal/dialog
        showCopyRatesDialog(groupKey);
    });
    
    // Clear Rates Button
    $(document).on('click', '.clear-rates-btn', function() {
        var groupKey = $(this).data('group-key');
        var btn = $(this);
        
        // Show confirmation dialog
        if (confirm('Are you sure you want to clear all rates for this group? This action cannot be undone.')) {
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Clearing...');
            
            $.ajax({
                url: '{{ route("b2b.transport-provider.transport-rates.group-clear") }}',
                method: 'DELETE',
                data: {
                    group_key: groupKey
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        // Refresh the rates display
                        location.reload();
                    } else {
                        showNotification('error', response.message || 'Failed to clear rates.');
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Failed to clear rates.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', errorMessage);
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-trash mr-1"></i>Clear');
                }
            });
        }
    });
    
    // Route History Button (Individual Routes)
    $(document).on('click', '.route-history-btn', function() {
        var serviceId = $(this).data('service-id');
        var routeFrom = $(this).data('route-from');
        var routeTo = $(this).data('route-to');
        
        showNotification('info', 'Loading route history for ' + routeFrom + ' → ' + routeTo + '...');
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-rates.history") }}',
            method: 'GET',
            data: {
                service_id: serviceId,
                route_from: routeFrom,
                route_to: routeTo,
                passenger_type: 'adult', // Default to adult
                limit: 20
            },
            success: function(response) {
                if (response.success) {
                    showRateHistoryModal(response.data, 'Route History - ' + routeFrom + ' → ' + routeTo);
                } else {
                    showNotification('warning', 'No rate history found for this route.');
                }
            },
            error: function() {
                showNotification('error', 'Failed to load route history.');
            }
        });
    });

    // Load pricing rules when tab is clicked
    $('#pricing-rules-tab').on('shown.bs.tab', function() {
        loadPricingRules();
    });

    // Load calendar when tab is clicked with delay to ensure FullCalendar is loaded
    $('#calendar-tab').on('shown.bs.tab', function() {
        // Try to initialize calendar with retry mechanism
        attemptCalendarInitialization(0);
    });
    
    function attemptCalendarInitialization(attempt) {
        if (attempt > 3) {
            // After 3 attempts, show fallback
            console.warn('FullCalendar failed to load after multiple attempts');
            initializeFallbackCalendar();
            return;
        }
        
        if (typeof FullCalendar !== 'undefined') {
            initializeCalendar();
        } else {
            console.log('FullCalendar not ready, attempt ' + (attempt + 1));
            setTimeout(function() {
                attemptCalendarInitialization(attempt + 1);
            }, 500);
        }
    }

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
        $('#passenger-count-fields, #distance-fields, #days-of-week-fields, #advance-booking-fields').hide();
        
        // Show relevant fields based on rule type
        if (ruleType === 'passenger_count') {
            $('#passenger-count-fields').show();
        } else if (ruleType === 'distance') {
            $('#distance-fields').show();
        } else if (ruleType === 'day_of_week') {
            $('#days-of-week-fields').show();
        } else if (ruleType === 'advance_booking') {
            $('#advance-booking-fields').show();
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
            $('#rule-preview').text('adjust pricing');
            return;
        }
        
        var action = parseFloat(adjustmentValue) > 0 ? 'increase' : 'decrease';
        var amount = Math.abs(parseFloat(adjustmentValue));
        var unit = adjustmentType === 'percentage' ? '%' : (adjustmentType === 'fixed' ? ' SAR' : 'x');
        
        $('#rule-preview').text(action + ' rates by ' + amount + unit + ' for ' + ruleType.replace('_', ' ') + ' conditions');
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
                        '<div class="card h-100">' +
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
    
    // CALENDAR FUNCTIONALITY
    
    function initializeCalendar() {
        // Check if FullCalendar is loaded
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar is not loaded');
            // Provide a fallback simple calendar
            initializeFallbackCalendar();
            return;
        }
        
        if (transportCalendar) {
            transportCalendar.destroy();
        }
        
        var calendarEl = document.getElementById('transport-rate-calendar');
        if (!calendarEl) {
            console.error('Calendar element not found');
            return;
        }
        
        try {
            transportCalendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            events: function(info, successCallback, failureCallback) {
                loadCalendarRates(info.startStr, info.endStr, successCallback, failureCallback);
            },
            eventClick: function(info) {
                showRateDetails(info.event);
            },
            dateClick: function(info) {
                openRateModal(info.dateStr);
            },
            eventDidMount: function(info) {
                $(info.el).tooltip({
                    title: info.event.extendedProps.tooltip,
                    placement: 'top',
                    container: 'body'
                });
            }
        });
        
            transportCalendar.render();
            
            // Check initial state - if no service selected, show message
            var initialServiceId = $('#calendar-service-filter').val();
            if (!initialServiceId) {
                $('#calendar-loading-state').show();
            } else {
                $('#calendar-loading-state').hide();
            }
            
            // Update calendar when filters change
            $('#calendar-service-filter, #calendar-route-filter, #calendar-passenger-filter').on('change', function() {
                var serviceId = $('#calendar-service-filter').val();
                
                if (serviceId) {
                    // Show loading and refetch events
                    $('#calendar-loading-state').hide();
                    if (transportCalendar) {
                        transportCalendar.refetchEvents();
                    }
                } else {
                    // Show service selection message
                    $('#calendar-loading-state').show();
                    if (transportCalendar) {
                        transportCalendar.removeAllEvents();
                    }
                }
                
                updateRouteFilter();
            });
            
        } catch (error) {
            console.error('Error initializing calendar:', error);
            $('#transport-rate-calendar').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Calendar Error:</strong> ' + error.message + '</div>');
        }
    }
    
    function loadCalendarRates(startDate, endDate, successCallback, failureCallback) {
        var serviceId = $('#calendar-service-filter').val();
        var routeFrom = $('#calendar-route-filter').val() ? $('#calendar-route-filter').val().split('|')[0] : '';
        var routeTo = $('#calendar-route-filter').val() ? $('#calendar-route-filter').val().split('|')[1] : '';
        var passengerType = $('#calendar-passenger-filter').val() || '';
        
        // Require service selection
        if (!serviceId) {
            failureCallback('Please select a service to view rates');
            return;
        }
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-rates.calendar") }}',
            method: 'GET',
            data: {
                service_id: serviceId,
                route_from: routeFrom,
                route_to: routeTo,
                passenger_type: passengerType,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    var events = response.data.map(function(rate) {
                        // Simple, clean title showing just the rate
                        var title = rate.currency + ' ' + parseFloat(rate.base_rate).toFixed(2);
                        
                        // When showing all routes, add a short route indicator
                        var showRoute = !routeFrom && !routeTo;
                        if (showRoute && rate.route_display) {
                            // Show just the first letters of from/to for compact display
                            var routeParts = rate.route_display.split(' → ');
                            if (routeParts.length === 2) {
                                var shortRoute = routeParts[0].substring(0, 3) + '-' + routeParts[1].substring(0, 3);
                                title = shortRoute + ': ' + title;
                            }
                        }
                        
                        // Create detailed tooltip and determine rate source styling
                        var tooltip = 'Rate: ' + rate.currency + ' ' + parseFloat(rate.base_rate).toFixed(2);
                        var rateSource = rate.rate_source || 'database';
                        var backgroundColor = getServiceColor(serviceId);
                        var borderColor = backgroundColor;
                        var textColor = '#ffffff';
                        
                        // Style based on rate source
                        if (rateSource === 'pricing_rules') {
                            // Pricing rule rates get a gradient or special indicator
                            backgroundColor = backgroundColor + '99'; // Add transparency
                            borderColor = '#ffc107'; // Golden border for pricing rules
                            title = '🏷️ ' + title; // Add pricing rule icon
                            tooltip = '⚡ Pricing Rule Rate\n' + tooltip;
                        } else {
                            tooltip = '📊 Fixed Rate\n' + tooltip;
                        }
                        
                        if (rate.route_display) {
                            tooltip += '\nRoute: ' + rate.route_display;
                        }
                        if (rate.passenger_type) {
                            tooltip += '\nPassenger: ' + rate.passenger_type.charAt(0).toUpperCase() + rate.passenger_type.slice(1);
                        }
                        if (rate.notes) {
                            tooltip += '\nNotes: ' + rate.notes;
                        }
                        
                        return {
                            id: rate.date + '_' + rate.service_id + '_' + (rate.route_from || 'all') + '_' + (rate.passenger_type || 'all'),
                            title: title,
                            start: rate.date,
                            backgroundColor: backgroundColor,
                            borderColor: borderColor,
                            textColor: textColor,
                            extendedProps: {
                                rate: rate.base_rate,
                                currency: rate.currency,
                                notes: rate.notes,
                                service_id: rate.service_id,
                                route_from: rate.route_from,
                                route_to: rate.route_to,
                                route_display: rate.route_display,
                                passenger_type: rate.passenger_type,
                                rate_source: rateSource,
                                tooltip: tooltip
                            }
                        };
                    });
                    successCallback(events);
                } else {
                    failureCallback('Failed to load calendar rates');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Failed to load calendar rates';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                failureCallback(errorMessage);
            }
        });
    }
    
    function initializeFallbackCalendar() {
        var currentDate = new Date();
        var currentMonth = currentDate.getMonth();
        var currentYear = currentDate.getFullYear();
        
        var calendarHtml = '<div class="alert alert-info mb-3">' +
                          '<i class="fas fa-info-circle mr-2"></i>' +
                          '<strong>Calendar View:</strong> FullCalendar library is loading. Using simplified view.' +
                          '</div>' +
                          '<div class="card">' +
                          '<div class="card-header">' +
                          '<h5 class="card-title mb-0">' +
                          '<i class="fas fa-calendar mr-2"></i>' +
                          getMonthName(currentMonth) + ' ' + currentYear +
                          '</h5>' +
                          '</div>' +
                          '<div class="card-body">' +
                          '<div class="row text-center">' +
                          '<div class="col-12">' +
                          '<p class="text-muted">Calendar rates will be displayed here once the full calendar loads.</p>' +
                          '<button class="btn btn-primary btn-sm" onclick="location.reload()">' +
                          '<i class="fas fa-refresh mr-1"></i>Reload Page' +
                          '</button>' +
                          '</div>' +
                          '</div>' +
                          '</div>' +
                          '</div>';
        
        $('#transport-rate-calendar').html(calendarHtml);
    }
    
    function getMonthName(monthIndex) {
        var months = ['January', 'February', 'March', 'April', 'May', 'June',
                     'July', 'August', 'September', 'October', 'November', 'December'];
        return months[monthIndex];
    }
    
    function updateRouteFilter() {
        var serviceId = $('#calendar-service-filter').val();
        var routeFilter = $('#calendar-route-filter');
        
        routeFilter.html('<option value="">All Routes</option>');
        
        if (serviceId) {
            // Get routes for selected service
            @foreach($services as $service)
                if ('{{ $service->id }}' == serviceId) {
                    @if($service->routes)
                        @foreach($service->routes as $route)
                            routeFilter.append('<option value="{{ $route['from'] ?? '' }}|{{ $route['to'] ?? '' }}">{{ $route['from'] ?? '' }} → {{ $route['to'] ?? '' }}</option>');
                        @endforeach
                    @endif
                }
            @endforeach
        }
    }
    
    function getServiceColor(serviceId) {
        var colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14'];
        return colors[serviceId % colors.length] || '#007bff';
    }
    
    function showRateDetails(event) {
        var props = event.extendedProps;
        var rateSourceInfo = '';
        var sourceIcon = '';
        var sourceBadge = '';
        
        if (props.rate_source === 'pricing_rules') {
            sourceIcon = '<i class="fas fa-magic text-warning mr-1"></i>';
            sourceBadge = '<span class="badge badge-warning"><i class="fas fa-magic mr-1"></i>Pricing Rule</span>';
            rateSourceInfo = '<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>This rate was calculated automatically using pricing rules.</div>';
        } else {
            sourceIcon = '<i class="fas fa-database text-primary mr-1"></i>';
            sourceBadge = '<span class="badge badge-primary"><i class="fas fa-database mr-1"></i>Fixed Rate</span>';
            rateSourceInfo = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>This is a manually set fixed rate.</div>';
        }
        
        var content = '<div class="rate-details">' +
                     '<div class="d-flex justify-content-between align-items-center mb-3">' +
                     '<h5 class="mb-0">' + sourceIcon + props.currency + ' ' + parseFloat(props.rate).toFixed(2) + '</h5>' +
                     sourceBadge +
                     '</div>' +
                     rateSourceInfo +
                     '<div class="row mb-3">' +
                     '<div class="col-md-6">' +
                     '<p class="mb-1"><strong><i class="fas fa-calendar mr-1"></i>Date:</strong></p>' +
                     '<p class="text-muted">' + event.start.toDateString() + '</p>' +
                     '</div>' +
                     '<div class="col-md-6">' +
                     '<p class="mb-1"><strong><i class="fas fa-money-bill mr-1"></i>Currency:</strong></p>' +
                     '<p class="text-muted">' + props.currency + '</p>' +
                     '</div>' +
                     '</div>';
        
        if (props.route_display) {
            content += '<div class="mb-3">' +
                      '<p class="mb-1"><strong><i class="fas fa-route mr-1"></i>Route:</strong></p>' +
                      '<p class="text-muted">' + props.route_display + '</p>' +
                      '</div>';
        }
        
        if (props.passenger_type) {
            content += '<div class="mb-3">' +
                      '<p class="mb-1"><strong><i class="fas fa-user mr-1"></i>Passenger Type:</strong></p>' +
                      '<span class="badge badge-info">' + props.passenger_type.charAt(0).toUpperCase() + props.passenger_type.slice(1) + '</span>' +
                      '</div>';
        }
                     
        if (props.notes) {
            content += '<div class="mb-3">' +
                      '<p class="mb-1"><strong><i class="fas fa-sticky-note mr-1"></i>Notes:</strong></p>' +
                      '<div class="alert alert-info">' + props.notes + '</div>' +
                      '</div>';
        }
        
        content += '</div>';
        
        showModal('Rate Details', content);
    }
    
    function openRateModal(date) {
        // Open individual rate setting modal with pre-filled date
        var startDate = new Date(date);
        $('#individual-rate-start-date').val(date);
        $('#individual-rate-end-date').val(date);
        $('#setIndividualRateModal').modal('show');
    }
    
    // FORM SUBMISSIONS FOR RATES
    
    // Individual rate form submission
    $('#individualRateForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-rates.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#setIndividualRateModal').modal('hide');
                    $('#individualRateForm')[0].reset();
                    if (transportCalendar) {
                        transportCalendar.refetchEvents();
                    }
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to save rate';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Rate');
            }
        });
    });
    
    // Group rate form submission
    $('#groupRateForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-rates.group-store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#setGroupRateModal').modal('hide');
                    $('#groupRateForm')[0].reset();
                    if (transportCalendar) {
                        transportCalendar.refetchEvents();
                    }
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to save group rates';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Group Rates');
            }
        });
    });
    
    // UTILITY FUNCTIONS
    
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
    
    function showModal(title, content) {
        var modal = $('<div class="modal fade" tabindex="-1">' +
                     '<div class="modal-dialog">' +
                     '<div class="modal-content">' +
                     '<div class="modal-header">' +
                     '<h5 class="modal-title">' + title + '</h5>' +
                     '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>' +
                     '</div>' +
                     '<div class="modal-body">' + content + '</div>' +
                     '<div class="modal-footer">' +
                     '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
                     '</div>' +
                     '</div>' +
                     '</div>' +
                     '</div>');
        
        $('body').append(modal);
        modal.modal('show');
        
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }
    
    // Show Rate History Modal
    function showRateHistoryModal(data, title) {
        var content = '<div class="rate-history-content">';
        
        if (data.service) {
            content += '<div class="mb-3">' +
                      '<h6><i class="fas fa-bus mr-2"></i>' + data.service.service_name + '</h6>' +
                      '<small class="text-muted">' + data.service.transport_type + '</small>';
            if (data.service.route) {
                content += '<br><small class="text-info">' + data.service.route + ' (' + data.service.passenger_type + ')</small>';
            }
            content += '</div>';
        }
        
        if (data.history && data.history.length > 0) {
            // Determine if this is group history or individual route history
            var isGroupHistory = data.service.route === 'All Routes';
            var routeHeaderHtml = isGroupHistory ? '<th>Route</th>' : '';
            
            content += '<div class="table-responsive">' +
                      '<table class="table table-sm table-hover">' +
                      '<thead class="table-light">' +
                      '<tr>' +
                      '<th>Date</th>' +
                      routeHeaderHtml +
                      '<th>Rate</th>' +
                      '<th>Available</th>' +
                      '<th>Notes</th>' +
                      '<th>Updated</th>' +
                      '</tr>' +
                      '</thead>' +
                      '<tbody>';
            
            data.history.forEach(function(rate) {
                var availableIcon = rate.is_available ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
                var updatedDate = new Date(rate.updated_at).toLocaleDateString();
                
                // Check if this is group history (has route field) or individual route history
                var routeDisplay = '';
                if (isGroupHistory && rate.route) {
                    // Group history - show the route info
                    routeDisplay = '<td><small class="text-muted">' + rate.route + '</small></td>';
                }
                
                content += '<tr>' +
                          '<td>' + rate.date + '</td>' +
                          routeDisplay +
                          '<td><strong>' + rate.currency + ' ' + parseFloat(rate.base_rate).toFixed(2) + '</strong></td>' +
                          '<td class="text-center">' + availableIcon + '</td>' +
                          '<td>' + (rate.notes || '-') + '</td>' +
                          '<td><small class="text-muted">' + updatedDate + '</small></td>' +
                          '</tr>';
            });
            
            content += '</tbody></table></div>';
        } else {
            content += '<div class="text-center py-4">' +
                      '<i class="fas fa-history fa-3x text-muted mb-3"></i>' +
                      '<p class="text-muted">No rate history found</p>' +
                      '</div>';
        }
        
        content += '</div>';
        
        var modal = $('<div class="modal fade" tabindex="-1">' +
                     '<div class="modal-dialog modal-lg">' +
                     '<div class="modal-content">' +
                     '<div class="modal-header bg-info text-white">' +
                     '<h5 class="modal-title"><i class="fas fa-history mr-2"></i>' + title + '</h5>' +
                     '<button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>' +
                     '</div>' +
                     '<div class="modal-body">' + content + '</div>' +
                     '<div class="modal-footer">' +
                     '<button type="button" class="btn btn-secondary" data-dismiss="modal">' +
                     '<i class="fas fa-times mr-1"></i>Close</button>' +
                     '</div>' +
                     '</div>' +
                     '</div>' +
                     '</div>');
        
        $('body').append(modal);
        modal.modal('show');
        
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }
    
    // Show Copy Rates Dialog
    function showCopyRatesDialog(sourceGroupKey) {
        var content = '<div class="copy-rates-content">' +
                     '<div class="alert alert-info">' +
                     '<i class="fas fa-info-circle mr-2"></i>' +
                     '<strong>Copy Rates:</strong> This will copy rates from the current group to another date range or service.' +
                     '</div>' +
                     '<form id="copyRatesForm">' +
                     '<div class="form-group">' +
                     '<label>Copy To Date Range:</label>' +
                     '<div class="row">' +
                     '<div class="col-md-6">' +
                     '<input type="date" class="form-control" name="copy_start_date" required>' +
                     '<small class="form-text text-muted">Start date</small>' +
                     '</div>' +
                     '<div class="col-md-6">' +
                     '<input type="date" class="form-control" name="copy_end_date" required>' +
                     '<small class="form-text text-muted">End date</small>' +
                     '</div>' +
                     '</div>' +
                     '</div>' +
                     '<div class="form-group">' +
                     '<div class="custom-control custom-checkbox">' +
                     '<input type="checkbox" class="custom-control-input" id="overwrite-existing" name="overwrite_existing">' +
                     '<label class="custom-control-label" for="overwrite-existing">' +
                     'Overwrite existing rates in target date range' +
                     '</label>' +
                     '</div>' +
                     '</div>' +
                     '<input type="hidden" name="source_group_key" value="' + sourceGroupKey + '">' +
                     '</form>' +
                     '</div>';
        
        var modal = $('<div class="modal fade" tabindex="-1">' +
                     '<div class="modal-dialog">' +
                     '<div class="modal-content">' +
                     '<div class="modal-header bg-warning text-white">' +
                     '<h5 class="modal-title"><i class="fas fa-copy mr-2"></i>Copy Rates</h5>' +
                     '<button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>' +
                     '</div>' +
                     '<div class="modal-body">' + content + '</div>' +
                     '<div class="modal-footer">' +
                     '<button type="button" class="btn btn-secondary" data-dismiss="modal">' +
                     '<i class="fas fa-times mr-1"></i>Cancel</button>' +
                     '<button type="button" class="btn btn-warning" id="executeCopyRates">' +
                     '<i class="fas fa-copy mr-1"></i>Copy Rates</button>' +
                     '</div>' +
                     '</div>' +
                     '</div>' +
                     '</div>');
        
        $('body').append(modal);
        modal.modal('show');
        
        // Handle copy execution
        modal.find('#executeCopyRates').on('click', function() {
            var formData = modal.find('#copyRatesForm').serialize();
            var btn = $(this);
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Copying...');
            
            $.ajax({
                url: '/b2b/transport-provider/transport-rates/copy-rates', // TODO: Add this route
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        modal.modal('hide');
                        // Optionally reload the rates display
                        location.reload();
                    } else {
                        showNotification('error', response.message || 'Failed to copy rates.');
                    }
                },
                error: function() {
                    showNotification('error', 'Failed to copy rates.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-copy mr-1"></i>Copy Rates');
                }
            });
        });
        
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }
    
    // Set individual rate button click handler
    $(document).on('click', '.set-individual-rate-btn', function() {
        var serviceId = $(this).data('service-id');
        var routeFrom = $(this).data('route-from');
        var routeTo = $(this).data('route-to');
        
        $('#individual-rate-service-id').val(serviceId);
        $('#individual-rate-route-from').val(routeFrom);
        $('#individual-rate-route-to').val(routeTo);
    });

    // Set group rate button click handler
    $(document).on('click', '.set-group-rate-btn', function() {
        var groupKey = $(this).data('group-key');
        var serviceType = $(this).data('service-type');
        var basePrice = $(this).data('base-price');
        var routeCount = $(this).data('route-count');
        
        $('#group-rate-group-key').val(groupKey);
        $('#group-rate-info').text('Setting rates for ' + routeCount + ' routes (' + serviceType + ') with base price: SAR ' + basePrice);
        
        // Initialize form - show fixed price config by default
        $('.rate-config').hide();
        $('#fixed-price-config').show();
        $('#group-rate-type').val('fixed');
        updateFinalRateValue();
    });
    
    // Group Rate Form - Dynamic Rate Type Handling
    $(document).on('change', '#group-rate-type', function() {
        var rateType = $(this).val();
        
        // Hide all rate config sections
        $('.rate-config').hide();
        
        // Show relevant config section
        if (rateType === 'fixed') {
            $('#fixed-price-config').show();
        } else if (rateType === 'base_plus') {
            $('#base-plus-config').show();
        } else if (rateType === 'base_percentage') {
            $('#base-percentage-config').show();
        }
        
        updateFinalRateValue();
    });
    
    // Handle adjustment type changes (increase/decrease)
    $(document).on('change', '#base-plus-type', function() {
        var type = $(this).val();
        var symbol = type === 'increase' ? '+' : '-';
        $('#base-plus-symbol').text(symbol);
        $('#adjustment-direction').val(type);
        updateFinalRateValue();
    });
    
    $(document).on('change', '#base-percentage-type', function() {
        var type = $(this).val();
        var symbol = type === 'increase' ? '+' : '-';
        $('#base-percentage-symbol').text(symbol);
        $('#adjustment-direction').val(type);
        updateFinalRateValue();
    });
    
    // PRICING RULES MODAL FUNCTIONALITY
    
    // Handle rule type selection to show/hide conditional fields
    $(document).on('change', '#create-rule-type', function() {
        var ruleType = $(this).val();
        
        // Hide all conditional fields first
        $('.conditional-field').fadeOut(200);
        $('#conditional-fields-card').hide();
        
        // Show relevant conditional fields based on rule type
        if (ruleType) {
            setTimeout(function() {
                $('#conditional-fields-card').fadeIn(300);
                
                switch(ruleType) {
                    case 'seasonal':
                        $('#date-range-fields').fadeIn(300);
                        break;
                    case 'day_of_week':
                        $('#days-of-week-fields').fadeIn(300);
                        break;
                    case 'passenger_count':
                        $('#passenger-count-fields').fadeIn(300);
                        break;
                    case 'distance':
                        $('#distance-fields').fadeIn(300);
                        break;
                    case 'advance_booking':
                        $('#advance-booking-fields').fadeIn(300);
                        break;
                }
            }, 200);
        }
        
        updateRulePreview();
    });
    
    // Handle adjustment type selection to show/hide adjustment config
    $(document).on('change', '#create-adjustment-type', function() {
        var adjustmentType = $(this).val();
        
        // Hide all adjustment configs first
        $('.adjustment-config').fadeOut(200);
        
        // Show relevant adjustment config
        if (adjustmentType) {
            setTimeout(function() {
                $('#' + adjustmentType + '-config').fadeIn(300);
                $('#preview-card').fadeIn(300);
            }, 200);
        } else {
            $('#preview-card').fadeOut(200);
        }
        
        updateRulePreview();
    });
    
    // Handle adjustment direction change (increase/decrease)
    $(document).on('change', 'input[name="adjustment_direction"]', function() {
        var direction = $(this).val();
        var isIncrease = direction === 'increase';
        
        // Update symbols in the UI
        $('#percentage-symbol').text(isIncrease ? '+' : '-');
        $('#fixed-symbol').text(isIncrease ? '+' : '-');
        
        // Update button states
        if (isIncrease) {
            $('#create-increase-btn').addClass('active');
            $('#create-decrease-btn').removeClass('active');
        } else {
            $('#create-decrease-btn').addClass('active');
            $('#create-increase-btn').removeClass('active');
        }
        
        updateRulePreview();
    });
    
    // Update final adjustment value and preview when inputs change
    $(document).on('input', '#percentage-value, #fixed-value, #multiplier-value', function() {
        updatePricingRuleFinalValue();
        updateRulePreview();
    });
    
    // Update service selection to get base price for preview
    $(document).on('change', '#create-rule-service', function() {
        updateRulePreview();
    });
    
    function updatePricingRuleFinalValue() {
        var adjustmentType = $('#create-adjustment-type').val();
        var direction = $('input[name="adjustment_direction"]:checked').val();
        var finalValue = 0;
        
        switch(adjustmentType) {
            case 'percentage':
                var percentValue = parseFloat($('#percentage-value').val()) || 0;
                finalValue = direction === 'decrease' ? -percentValue : percentValue;
                break;
            case 'fixed':
                var fixedValue = parseFloat($('#fixed-value').val()) || 0;
                finalValue = direction === 'decrease' ? -fixedValue : fixedValue;
                break;
            case 'multiplier':
                finalValue = parseFloat($('#multiplier-value').val()) || 0;
                break;
        }
        
        $('#final-adjustment-value').val(finalValue);
    }
    
    function updateRulePreview() {
        var service = $('#create-rule-service option:selected');
        var ruleType = $('#create-rule-type').val();
        var adjustmentType = $('#create-adjustment-type').val();
        var direction = $('input[name="adjustment_direction"]:checked').val();
        var basePrice = parseFloat(service.attr('data-base-price')) || 100;
        
        if (!ruleType || !adjustmentType || !direction) {
            $('#rule-preview-text').text('Configure the rule to see a preview');
            $('#preview-examples').hide();
            return;
        }
        
        var adjustmentValue = 0;
        var adjustmentDisplay = '';
        var finalPrice = basePrice;
        
        switch(adjustmentType) {
            case 'percentage':
                adjustmentValue = parseFloat($('#percentage-value').val()) || 0;
                if (direction === 'decrease') {
                    adjustmentDisplay = '-' + adjustmentValue + '%';
                    finalPrice = basePrice * (1 - adjustmentValue / 100);
                } else {
                    adjustmentDisplay = '+' + adjustmentValue + '%';
                    finalPrice = basePrice * (1 + adjustmentValue / 100);
                }
                break;
            case 'fixed':
                adjustmentValue = parseFloat($('#fixed-value').val()) || 0;
                if (direction === 'decrease') {
                    adjustmentDisplay = '-' + adjustmentValue + ' SAR';
                    finalPrice = basePrice - adjustmentValue;
                } else {
                    adjustmentDisplay = '+' + adjustmentValue + ' SAR';
                    finalPrice = basePrice + adjustmentValue;
                }
                break;
            case 'multiplier':
                adjustmentValue = parseFloat($('#multiplier-value').val()) || 1;
                adjustmentDisplay = '×' + adjustmentValue;
                finalPrice = basePrice * adjustmentValue;
                break;
        }
        
        // Update preview text
        var ruleTypeText = $('#create-rule-type option:selected').text().replace(/^[\u{1F300}-\u{1F6FF}]\s*/u, '');
        $('#rule-preview-text').html('This rule will apply <strong>' + adjustmentDisplay + '</strong> to prices for <strong>' + ruleTypeText.toLowerCase() + '</strong>');
        
        // Update preview examples
        $('#preview-base-price').text(basePrice.toFixed(0) + ' SAR');
        $('#preview-adjustment').text(adjustmentDisplay);
        $('#preview-final-price').text(Math.max(0, finalPrice).toFixed(0) + ' SAR');
        $('#preview-examples').show();
        
        // Update example texts in config sections
        switch(adjustmentType) {
            case 'percentage':
                var exampleText = 'Example: Base price ' + basePrice + ' SAR ' + adjustmentDisplay + ' = ' + Math.max(0, finalPrice).toFixed(0) + ' SAR';
                $('#percentage-example').text(exampleText);
                break;
            case 'fixed':
                var exampleText = 'Example: Base price ' + basePrice + ' SAR ' + adjustmentDisplay + ' = ' + Math.max(0, finalPrice).toFixed(0) + ' SAR';
                $('#fixed-example').text(exampleText);
                break;
            case 'multiplier':
                var exampleText = 'Example: Base price ' + basePrice + ' SAR × ' + adjustmentValue + ' = ' + Math.max(0, finalPrice).toFixed(0) + ' SAR';
                $('#multiplier-example').text(exampleText);
                break;
        }
        
        // Update final value
        updatePricingRuleFinalValue();
    }
    
    // Update final rate value when inputs change
    $(document).on('input', '#fixed-rate-value, #base-plus-value, #base-percentage-value', function() {
        updateFinalRateValue();
    });
    
    function updateFinalRateValue() {
        var rateType = $('#group-rate-type').val();
        var finalRate = 0;
        
        switch(rateType) {
            case 'fixed':
                finalRate = parseFloat($('#fixed-rate-value').val()) || 0;
                break;
            case 'base_plus':
                var value = parseFloat($('#base-plus-value').val()) || 0;
                var direction = $('#base-plus-type').val();
                finalRate = direction === 'decrease' ? -value : value;
                break;
            case 'base_percentage':
                var value = parseFloat($('#base-percentage-value').val()) || 0;
                var direction = $('#base-percentage-type').val();
                finalRate = direction === 'decrease' ? -value : value;
                break;
        }
        
        $('#final-base-rate').val(finalRate);
    }
    
    // Form interaction improvements
    $(document).on('change', '#override-existing', function() {
        if ($(this).is(':checked')) {
            $('#override-warning').slideDown();
        } else {
            $('#override-warning').slideUp();
        }
    });
    
    // Auto-set end date when start date is selected (same day by default)
    $(document).on('change', '#individual-rate-start-date', function() {
        var startDate = $(this).val();
        if (startDate && !$('#individual-rate-end-date').val()) {
            $('#individual-rate-end-date').val(startDate);
        }
        $('#individual-rate-end-date').attr('min', startDate);
    });
    
    // Form validation improvements
    $(document).on('submit', '#individualRateForm, #groupRateForm', function(e) {
        var form = $(this);
        
        // Common validations
        var startDate = form.find('input[name="start_date"]').val();
        var endDate = form.find('input[name="end_date"]').val();
        
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            e.preventDefault();
            showNotification('error', 'End date must be after or equal to start date.');
            return false;
        }
        
        // Rate validation for rate forms
        var rate = form.find('input[name="base_rate"]').val();
        var rateType = form.find('input[name="rate_type"], select[name="rate_type"]').val();
        
        // Only validate positive rates for fixed type or individual rates (no rate_type)
        if (rate && parseFloat(rate) <= 0 && (!rateType || rateType === 'fixed')) {
            e.preventDefault();
            showNotification('error', 'Rate amount must be greater than 0.');
            return false;
        }
    });
    
    // Handle pricing rule form submission
    $(document).on('submit', '#createPricingRuleForm', function(e) {
        e.preventDefault();
        
        var form = $(this);
        
        // Common validations
        var startDate = form.find('input[name="start_date"]').val();
        var endDate = form.find('input[name="end_date"]').val();
        
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            showNotification('error', 'End date must be after or equal to start date.');
            return false;
        }
        
        // Pricing rule specific validations
        var ruleType = form.find('#create-rule-type').val();
        var adjustmentType = form.find('#create-adjustment-type').val();
        var direction = form.find('input[name="adjustment_direction"]:checked').val();
        
        // Validate rule type selection
        if (!ruleType) {
            showNotification('error', 'Please select when this rule should apply.');
            return false;
        }
        
        // Validate adjustment configuration
        if (!adjustmentType || !direction) {
            showNotification('error', 'Please configure the price adjustment settings.');
            return false;
        }
        
        // Update and validate final adjustment value
        updatePricingRuleFinalValue();
        var adjustmentValue = parseFloat(form.find('#final-adjustment-value').val()) || 0;
        
        if (adjustmentValue === 0) {
            showNotification('error', 'Please enter an adjustment value greater than 0.');
            return false;
        }
        
        // Validate conditional fields based on rule type
        switch(ruleType) {
            case 'seasonal':
                if (!startDate || !endDate) {
                    showNotification('error', 'Please select both start and end dates for seasonal pricing.');
                    return false;
                }
                break;
            case 'day_of_week':
                var daysSelected = form.find('input[name="days_of_week[]"]:checked').length;
                if (daysSelected === 0) {
                    showNotification('error', 'Please select at least one day of the week.');
                    return false;
                }
                break;
        }
        
        // Prevent multiple submissions
        if (form.data('submitting') === true) {
            return false;
        }
        
        // Mark form as submitting
        form.data('submitting', true);
        
        var formData = $(this).serialize();

        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Creating...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("b2b.transport-provider.transport-pricing-rules.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createPricingRuleModal').modal('hide');
                    showNotification('success', response.message || 'Pricing rule created successfully!');
                    
                    // Refresh pricing rules list
                    if (typeof loadPricingRules === 'function') {
                        loadPricingRules();
                    }
                } else {
                    showNotification('error', response.message || 'Failed to create pricing rule.');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to create pricing rule.';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorList = [];
                    for (var field in errors) {
                        errorList = errorList.concat(errors[field]);
                    }
                    errorMessage = errorList.join(' ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showNotification('error', errorMessage);
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                
                // Reset submission flag
                form.data('submitting', false);
            }
        });
    });
    
    // Clear form on modal hide
    $('.modal').on('hidden.bs.modal', function() {
        var form = $(this).find('form');
        if (form.length > 0) {
            form[0].reset();
            form.data('submitting', false); // Reset submission flag
        }
        $('#override-warning').hide();
        
        // Reset pricing rule specific elements
        if ($(this).attr('id') === 'createPricingRuleModal') {
            $('.adjustment-config').hide();
            $('.conditional-field').hide();
            $('#conditional-fields-card').hide();
            $('#preview-card').hide();
            $('#adjustment-direction-group .btn').removeClass('active');
        }
    });
    
    @if(session('success'))
        showNotification('success', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showNotification('error', '{{ session('error') }}');
    @endif
});
</script>
@stop
