@extends('layouts.b2b')

@section('title', 'Rates & Pricing Management')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-title', 'Rates & Pricing Management')

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
                    <a class="nav-link" id="room-categories-tab" data-toggle="tab" href="#room-categories" role="tab">
                        <i class="fas fa-bed mr-2"></i>Room Categories
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
                    @if($hotels->count() > 0)
                        @foreach($hotels as $hotel)
                            <div class="hotel-section mb-4" id="hotel-section-{{ $hotel->id }}">
                                <div class="hotel-header d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h5 class="mb-1">{{ $hotel->name }}</h5>
                                        <span class="text-muted">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $hotel->city }}, {{ $hotel->country }}
                                        </span>
                                        <span class="badge badge-info ml-2">{{ $hotel->rooms->count() }} rooms</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary expand-hotel-btn" data-hotel-id="{{ $hotel->id }}">
                                            <i class="fas fa-expand-arrows-alt mr-1"></i>Manage Rates
                                        </button>
                                        <button class="btn btn-sm btn-success bulk-pricing-hotel-btn" 
                                                data-toggle="modal" 
                                                data-target="#bulkPricingModal"
                                                data-hotel-id="{{ $hotel->id }}" 
                                                data-hotel-name="{{ $hotel->name }}">
                                            <i class="fas fa-magic mr-1"></i>Bulk Price
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="hotel-rooms mt-3" id="hotel-{{ $hotel->id }}-rooms" style="display: none;">
                                    @if($hotel->rooms->count() > 0)
                                        @php
                                            // Group rooms by type, category, and occupancy
                                            $roomGroups = $hotel->rooms->groupBy(function($room) {
                                                return $room->category . '|' . ($room->max_occupancy ?? 2) . '|' . number_format($room->base_price, 2);
                                            });
                                        @endphp
                                        
                                        <div class="room-groups-container">
                                            @foreach($roomGroups as $groupKey => $roomsInGroup)
                                                @php
                                                    $sampleRoom = $roomsInGroup->first();
                                                    $roomCount = $roomsInGroup->count();
                                                    $activeRooms = $roomsInGroup->where('is_active', true)->count();
                                                    $availableRooms = $roomsInGroup->where('is_available', true)->count();
                                                @endphp
                                                
                                                <div class="room-group-card mb-4" data-group-key="{{ $groupKey }}">
                                                    <div class="card">
                                                        <div class="card-header bg-light">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-8">
                                                                    <h6 class="mb-1 font-weight-bold text-primary">
                                                                        <i class="fas fa-bed mr-2"></i>
                                                                        {{ $sampleRoom->category_name ?? 'Standard Room' }}
                                                                        <span class="badge badge-info ml-2">{{ $roomCount }} rooms</span>
                                                                    </h6>
                                                                    <div class="room-group-details text-muted small">
                                                                        <i class="fas fa-users mr-1"></i> Max {{ $sampleRoom->max_occupancy ?? 2 }} guests
                                                                        <span class="mx-2">•</span>
                                                                        <i class="fas fa-dollar-sign mr-1"></i> Base: ${{ number_format($sampleRoom->base_price, 2) }}
                                                                        <span class="mx-2">•</span>
                                                                        <i class="fas fa-check-circle mr-1"></i> {{ $activeRooms }}/{{ $roomCount }} active
                                                                        <span class="mx-2">•</span>
                                                                        <i class="fas fa-door-open mr-1"></i> {{ $availableRooms }}/{{ $roomCount }} available
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 text-right">
                                                                    <div class="btn-group" role="group">
                                                                        <button class="btn btn-success btn-sm set-group-rate-btn" 
                                                                                data-toggle="modal" 
                                                                                data-target="#setGroupRateModal"
                                                                                data-group-key="{{ $groupKey }}"
                                                                                data-room-type="{{ $sampleRoom->category_name ?? 'Standard Room' }}"
                                                                                data-base-price="{{ $sampleRoom->base_price }}"
                                                                                data-room-count="{{ $roomCount }}"
                                                                                title="Set Rate for All {{ $roomCount }} Rooms">
                                                                            <i class="fas fa-magic mr-1"></i>Set Group Rate
                                                                        </button>
                                                        <button class="btn btn-outline-primary btn-sm toggle-group-details" 
                                                                data-group-key="{{ $groupKey }}"
                                                                data-group-id="group-details-{{ md5($groupKey) }}"
                                                                title="View Individual Rooms">
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
                                                                foreach($roomsInGroup as $room) {
                                                                    $currentRate = $room->getCurrentRate();
                                                                    if($currentRate) {
                                                                        $groupCurrentRates[] = $currentRate;
                                                                    }
                                                                }
                                                                $uniqueRates = collect($groupCurrentRates)->groupBy('price');
                                                            @endphp
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <small class="text-muted font-weight-bold">Current Group Rates:</small>
                                                                    <div class="mt-1">
                                                                        @if($uniqueRates->count() === 1)
                                                                            @php $rate = $groupCurrentRates[0]; @endphp
                                                                            <span class="badge badge-success badge-lg">
                                                                                <i class="fas fa-check-circle mr-1"></i>
                                                                                ${{ number_format($rate->price, 2) }} (All {{ $roomCount }} rooms)
                                                                            </span>
                                                                            @if($rate->notes)
                                                                                <small class="text-info d-block">{{ $rate->notes }}</small>
                                                                            @endif
                                                                        @elseif($uniqueRates->count() > 1)
                                                                            <span class="badge badge-warning badge-lg">
                                                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                                                Mixed Rates ({{ $uniqueRates->count() }} different prices)
                                                                            </span>
                                                                            <div class="mt-1">
                                                                                @foreach($uniqueRates as $price => $rates)
                                                                                    <small class="badge badge-outline-info mr-1">
                                                                                        ${{ number_format($price, 2) }} ({{ $rates->count() }} rooms)
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
                                                                                data-room-type="{{ $sampleRoom->category_name ?? 'Standard Room' }}">
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
                                                        
                                        <!-- Individual Room Details (Hidden by Default) -->
                                        <div class="individual-rooms-details" id="group-details-{{ md5($groupKey) }}" style="display: none;"
                                             data-group-key="{{ $groupKey }}">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-striped mb-0">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th width="25%">Room Number</th>
                                                                            <th width="20%">Current Rate</th>
                                                                            <th width="15%">Status</th>
                                                                            <th width="15%">Availability</th>
                                                                            <th width="25%">Individual Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($roomsInGroup as $room)
                                                                            <tr>
                                                                                <td>
                                                                                    <strong>{{ $room->room_number }}</strong>
                                                                                    @if($room->name && $room->name !== $room->room_number)
                                                                                        <br><small class="text-muted">{{ $room->name }}</small>
                                                                                    @endif
                                                                                </td>
                                                                                <td id="individual-rate-{{ $room->id }}">
                                                                                    @php
                                                                                        $currentRate = $room->getCurrentRate();
                                                                                    @endphp
                                                                                    @if($currentRate)
                                                                                        <span class="badge badge-success">
                                                                                            ${{ number_format($currentRate->price, 2) }}
                                                                                        </span>
                                                                                        @if($currentRate->notes)
                                                                                            <br><small class="text-info">{{ Str::limit($currentRate->notes, 20) }}</small>
                                                                                        @endif
                                                                                    @else
                                                                                        <span class="badge badge-outline-secondary">Base: ${{ number_format($room->base_price, 2) }}</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>
                                                                                    <span class="badge badge-sm {{ $room->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                                                        {{ $room->is_active ? 'Active' : 'Inactive' }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    @php
                                                                                        $isOccupied = $room->bookings()->where('status', 'confirmed')
                                                                                            ->where('check_in_date', '<=', now())
                                                                                            ->where('check_out_date', '>=', now())
                                                                                            ->exists();
                                                                                    @endphp
                                                                                    <span class="badge badge-sm {{ $isOccupied ? 'badge-danger' : 'badge-success' }}">
                                                                                        {{ $isOccupied ? 'Occupied' : 'Available' }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="btn-group" role="group">
                                                                                        <button class="btn btn-xs btn-outline-info set-individual-rate-btn" 
                                                                                                data-toggle="modal" 
                                                                                                data-target="#setRateModal"
                                                                                                data-room-id="{{ $room->id }}"
                                                                                                data-room-name="{{ $room->room_number }}"
                                                                                                data-base-price="{{ $room->base_price }}"
                                                                                                title="Set Individual Rate">
                                                                                            <i class="fas fa-dollar-sign"></i>
                                                                                        </button>
                                                                                        <button class="btn btn-xs btn-outline-warning view-individual-history-btn" 
                                                                                                data-toggle="modal" 
                                                                                                data-target="#rateHistoryModal"
                                                                                                data-room-id="{{ $room->id }}"
                                                                                                data-room-name="{{ $room->room_number }}"
                                                                                                title="Rate History">
                                                                                            <i class="fas fa-history"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            No rooms found for this hotel. 
                                            <a href="{{ route('b2b.hotel-provider.hotels.show', $hotel) }}" class="alert-link">
                                                Add rooms first
                                            </a>.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-hotel fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Hotels Found</h4>
                            <p class="text-muted">Add hotels first to manage their rates and pricing.</p>
                            <a href="{{ route('b2b.hotel-provider.hotels.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus mr-2"></i>Add Your First Hotel
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Room Categories Information Tab -->
                <div class="tab-pane fade" id="room-categories" role="tabpanel">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Room Categories Overview</h5>
                                    <p class="text-muted mb-0">Feature-based room categories help guests find the perfect room for their needs.</p>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                            $categories = \App\Models\Room::getRoomTypeCategories();
                                            $categoryGroups = [
                                                'Views' => ['window_view', 'balcony_view', 'sea_view', 'mountain_view', 'city_view', 'garden_view'],
                                                'Premium Services' => ['vip_access', 'executive_lounge', 'concierge_service', 'room_service_24h'],
                                                'Family & Accessibility' => ['family_suite', 'connecting_rooms', 'accessible', 'pet_friendly'],
                                                'In-Room Features' => ['kitchenette', 'jacuzzi', 'fireplace', 'soundproof'],
                                                'Business & Recreation' => ['business_center', 'conference_ready', 'spa_access', 'pool_access', 'gym_access'],
                                                'Environment' => ['smoking_allowed', 'non_smoking']
                                            ];
                                        @endphp
                                        
                                        @foreach($categoryGroups as $groupName => $groupCategories)
                                            <div class="col-md-6 mb-4">
                                                <h6 class="text-primary font-weight-bold">{{ $groupName }}</h6>
                                                <div class="list-group">
                                                    @foreach($groupCategories as $key)
                                                        @if(isset($categories[$key]))
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $categories[$key] }}</strong>
                                                                    <small class="text-muted d-block">{{ $key }}</small>
                                                                </div>
                                                                @php
                                                                    $roomCount = $hotels->flatMap->rooms->where('category', $key)->count();
                                                                @endphp
                                                                <span class="badge badge-primary">{{ $roomCount }} rooms</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="alert alert-info mt-4">
                                        <h6><i class="fas fa-info-circle mr-2"></i>About Room Categories</h6>
                                        <p class="mb-2">Room categories are assigned when creating or editing rooms. They help guests understand what makes each room special.</p>
                                        <p class="mb-0">
                                            <a href="{{ route('b2b.hotel-provider.rooms.create') }}" class="btn btn-sm btn-primary mr-2">
                                                <i class="fas fa-plus mr-1"></i>Create New Room
                                            </a>
                                            <a href="{{ route('b2b.hotel-provider.rooms.index') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-list mr-1"></i>Manage Existing Rooms
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar View Tab -->
                <div class="tab-pane fade" id="calendar-view" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <select class="form-control" id="calendarHotelSelect">
                                <option value="">Select Hotel</option>
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}">
                                        {{ $hotel->name }} ({{ $hotel->rooms->count() }} rooms)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="calendarRoomSelect">
                                <option value="">Select Room (Choose Hotel First)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info" id="loadCalendarRatesBtn">
                                <i class="fas fa-sync mr-1"></i> Load Rates
                            </button>
                            <button class="btn btn-success" id="quickSetRateBtn">
                                <i class="fas fa-plus mr-1"></i> Quick Rate
                            </button>
                        </div>
                    </div>
                    
                    <div class="calendar-container">
                        <div id="rateCalendar" style="min-height: 600px;"></div>
                    </div>
                </div>

                <!-- Pricing Rules Tab -->
                <div class="tab-pane fade" id="pricing-rules" role="tabpanel">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 statistics-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Rules</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalRulesCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 statistics-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Active Rules</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeRulesCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 statistics-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Seasonal Rules</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="seasonalRulesCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 statistics-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Promotional Rules</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="promotionalRulesCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Bar -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPricingRuleModal">
                                    <i class="fas fa-plus me-1"></i>Create New Rule
                                </button>
                                <button type="button" class="btn btn-success" id="bulkCreateBtn">
                                    <i class="fas fa-layer-group me-1"></i>Bulk Create
                                </button>
                                <button type="button" class="btn btn-info" id="applyPricingRulesBtn">
                                    <i class="fas fa-magic me-1"></i>Apply Rules Now
                                </button>
                                <button type="button" class="btn btn-warning" id="previewRulesBtn">
                                    <i class="fas fa-eye me-1"></i>Preview Impact
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-cogs me-1"></i>More Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <h6 class="dropdown-header">Import/Export</h6>
                                        <a class="dropdown-item" href="#" data-action="import">
                                            <i class="fas fa-upload me-1"></i>Import Rules
                                        </a>
                                        <a class="dropdown-item" href="#" data-action="export">
                                            <i class="fas fa-download me-1"></i>Export Rules
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">Bulk Operations</h6>
                                        <a class="dropdown-item" href="#" data-action="bulk-enable">
                                            <i class="fas fa-play me-1 text-success"></i>Bulk Enable
                                        </a>
                                        <a class="dropdown-item" href="#" data-action="bulk-disable">
                                            <i class="fas fa-pause me-1 text-warning"></i>Bulk Disable
                                        </a>
                                        <a class="dropdown-item" href="#" data-action="update-priority">
                                            <i class="fas fa-sort-amount-up me-1 text-info"></i>Update Priority
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="#" data-action="bulk-delete">
                                            <i class="fas fa-trash me-1"></i>Bulk Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="pricingRulesSearch" placeholder="Search pricing rules...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="searchRulesBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Rules Management -->
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Create/Edit Rule Card -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-plus text-primary mr-2"></i>
                                        Quick Create Rule
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="quickCreateRuleForm">
                                        <div class="form-group">
                                            <label>Rule Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="rule_name" placeholder="e.g., Summer Season" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Rule Type <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm" name="rule_type" id="quickRuleType" required>
                                                <option value="">Select Type</option>
                                                <option value="seasonal">Seasonal Pricing</option>
                                                <option value="advance_booking">Advance Booking</option>
                                                <option value="length_of_stay">Length of Stay</option>
                                                <option value="day_of_week">Day of Week</option>
                                                <option value="occupancy">Occupancy Based</option>
                                                <option value="promotional">Promotional</option>
                                                <option value="blackout">Blackout Dates</option>
                                                <option value="minimum_stay">Minimum Stay</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Apply To</label>
                                            <select class="form-control form-control-sm" name="hotel_id">
                                                <option value="">All My Hotels</option>
                                                @foreach($hotels as $hotel)
                                                    <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Room Category</label>
                                            <select class="form-control form-control-sm" name="room_category">
                                                <option value="">All Categories</option>
                                                <option value="window_view">Window View</option>
                                                <option value="sea_view">Sea View</option>
                                                <option value="balcony">Balcony</option>
                                                <option value="kitchenette">Kitchenette</option>
                                                <option value="family_suite">Family Suite</option>
                                                <option value="executive_lounge">Executive Lounge</option>
                                            </select>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Start Date <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control form-control-sm" name="start_date" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>End Date <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control form-control-sm" name="end_date" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Adjustment Type <span class="text-danger">*</span></label>
                                                    <select class="form-control form-control-sm" name="adjustment_type" id="quickAdjustmentType" required>
                                                        <option value="">Select Type</option>
                                                        <option value="percentage">Percentage</option>
                                                        <option value="fixed">Fixed Amount</option>
                                                        <option value="multiply">Multiplier</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Value <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" class="form-control" name="adjustment_value" id="quickAdjustmentValue" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text" id="adjustmentUnitDisplay">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Priority (1-10)</label>
                                            <input type="number" class="form-control form-control-sm" name="priority" value="5" min="1" max="10">
                                            <small class="form-text text-muted">Higher numbers = higher priority</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="quickRuleActive" name="is_active" checked>
                                                <label class="custom-control-label" for="quickRuleActive">
                                                    Activate Rule Immediately
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                                            <i class="fas fa-plus mr-1"></i> Create Pricing Rule
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Rule Types Info -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle text-info mr-2"></i>
                                        Rule Types Guide
                                    </h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="accordion" id="ruleTypesAccordion">
                                        <div class="card border-0">
                                            <div class="card-header p-2 bg-transparent border-0">
                                                <button class="btn btn-link btn-sm text-left p-0 collapsed" type="button" data-toggle="collapse" data-target="#seasonalInfo">
                                                    <i class="fas fa-calendar-week mr-1 text-warning"></i> Seasonal Pricing
                                                </button>
                                            </div>
                                            <div id="seasonalInfo" class="collapse" data-parent="#ruleTypesAccordion">
                                                <div class="card-body p-2 pt-0">
                                                    <small class="text-muted">Adjust prices for peak seasons, holidays, and special events.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card border-0">
                                            <div class="card-header p-2 bg-transparent border-0">
                                                <button class="btn btn-link btn-sm text-left p-0 collapsed" type="button" data-toggle="collapse" data-target="#advanceInfo">
                                                    <i class="fas fa-clock mr-1 text-success"></i> Advance Booking
                                                </button>
                                            </div>
                                            <div id="advanceInfo" class="collapse" data-parent="#ruleTypesAccordion">
                                                <div class="card-body p-2 pt-0">
                                                    <small class="text-muted">Offer discounts for early bookings made in advance.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card border-0">
                                            <div class="card-header p-2 bg-transparent border-0">
                                                <button class="btn btn-link btn-sm text-left p-0 collapsed" type="button" data-toggle="collapse" data-target="#lengthInfo">
                                                    <i class="fas fa-bed mr-1 text-primary"></i> Length of Stay
                                                </button>
                                            </div>
                                            <div id="lengthInfo" class="collapse" data-parent="#ruleTypesAccordion">
                                                <div class="card-body p-2 pt-0">
                                                    <small class="text-muted">Adjust pricing based on the number of nights stayed.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card border-0">
                                            <div class="card-header p-2 bg-transparent border-0">
                                                <button class="btn btn-link btn-sm text-left p-0 collapsed" type="button" data-toggle="collapse" data-target="#occupancyInfo">
                                                    <i class="fas fa-users mr-1 text-info"></i> Occupancy Based
                                                </button>
                                            </div>
                                            <div id="occupancyInfo" class="collapse" data-parent="#ruleTypesAccordion">
                                                <div class="card-body p-2 pt-0">
                                                    <small class="text-muted">Dynamic pricing based on current hotel occupancy rates.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <!-- Analytics Summary -->
                            <div class="card mb-3">
                                <div class="card-header bg-gradient-info text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-chart-bar mr-2"></i>
                                        Pricing Analytics Summary
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="metric-item">
                                                <div class="h4 text-success mb-1" id="avgPriceIncrease">+0%</div>
                                                <small class="text-muted">Avg Price Impact</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="metric-item">
                                                <div class="h4 text-info mb-1" id="rulesAppliedToday">0</div>
                                                <small class="text-muted">Rules Applied Today</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="metric-item">
                                                <div class="h4 text-warning mb-1" id="roomsAffected">0</div>
                                                <small class="text-muted">Rooms Affected</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="metric-item">
                                                <div class="h4 text-primary mb-1" id="revenueImpact">$0</div>
                                                <small class="text-muted">Est. Revenue Impact</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Active Pricing Rules List -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list text-info mr-2"></i>
                                        Active Pricing Rules
                                    </h5>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-info" id="viewAnalyticsBtn" title="View Detailed Analytics">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" id="refreshRulesBtn">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary" id="viewGridBtn" title="Grid View">
                                                <i class="fas fa-th"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary active" id="viewListBtn" title="List View">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <!-- Filters and Bulk Selection -->
                                    <div class="border-bottom p-3">
                                        <!-- Bulk Selection Row -->
                                        <div class="row align-items-center mb-3">
                                            <div class="col-md-6">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="selectAllRules">
                                                    <label class="custom-control-label" for="selectAllRules">
                                                        <strong>Select All Rules</strong>
                                                        <span class="text-muted">(<span id="selectedRulesCount">0</span> selected)</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <div id="bulkActionButtons" class="d-none">
                                                    <span class="text-muted small mr-2">With selected:</span>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-success" data-action="bulk-enable" title="Enable Selected">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" data-action="bulk-disable" title="Disable Selected">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info" data-action="update-priority" title="Update Priority">
                                                            <i class="fas fa-sort-amount-up"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary" data-action="export" title="Export Selected">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" data-action="bulk-delete" title="Delete Selected">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Filter Row -->
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <select class="form-control form-control-sm" id="filterRuleType">
                                                    <option value="">All Types</option>
                                                    <option value="seasonal">Seasonal</option>
                                                    <option value="advance_booking">Advance Booking</option>
                                                    <option value="length_of_stay">Length of Stay</option>
                                                    <option value="day_of_week">Day of Week</option>
                                                    <option value="occupancy">Occupancy</option>
                                                    <option value="promotional">Promotional</option>
                                                    <option value="blackout">Blackout</option>
                                                    <option value="minimum_stay">Minimum Stay</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control form-control-sm" id="filterRuleStatus">
                                                    <option value="">All Status</option>
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control form-control-sm" id="filterRuleHotel">
                                                    <option value="">All Hotels</option>
                                                    @foreach($hotels as $hotel)
                                                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-sm btn-primary" id="applyFiltersBtn">
                                                    <i class="fas fa-filter me-1"></i>Apply Filters
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary ml-1" id="clearFiltersBtn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rules List -->
                                    <div id="pricing-rules-container" style="max-height: 600px; overflow-y: auto;">
                                        <div id="pricing-rules-list">
                                            <div class="text-muted text-center py-5">
                                                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                                                <div>Loading pricing rules...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- MODALS -->

<!-- Set Group Rate Modal -->
<div class="modal fade" id="setGroupRateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-magic mr-2"></i>
                    Set Group Rate - <span id="groupRoomType"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="setGroupRateForm">
                <div class="modal-body">
                    <input type="hidden" id="groupKey" name="group_key">
                    
                    <!-- Room Group Summary -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle mr-2"></i>Group Details</h6>
                                <p class="mb-1"><strong>Room Type:</strong> <span id="groupModalRoomType"></span></p>
                                <p class="mb-1"><strong>Total Rooms:</strong> <span id="groupModalRoomCount"></span></p>
                                <p class="mb-0"><strong>Base Price:</strong> $<span id="groupModalBasePrice"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-cogs mr-2"></i>Rate Application</h6>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="applyToAllRooms" name="apply_to_all" checked>
                                    <label class="custom-control-label" for="applyToAllRooms">
                                        Apply to all rooms in this group
                                    </label>
                                </div>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="overrideExistingRates" name="override_existing">
                                    <label class="custom-control-label" for="overrideExistingRates">
                                        Override existing rates
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Start Date</label>
                                <input type="date" class="form-control" id="groupStartDate" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">End Date</label>
                                <input type="date" class="form-control" id="groupEndDate" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Rate per Night</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" class="form-control" id="groupRatePrice" name="price" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="useGroupBasePriceBtn">
                                            Use Base Price
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Rate Type</label>
                                <select class="form-control" id="groupRateType" name="rate_type">
                                    <option value="fixed">Fixed Price</option>
                                    <option value="base_plus">Base Price + Amount</option>
                                    <option value="base_percentage">Base Price + Percentage</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Notes <small class="text-muted">(Optional)</small></label>
                        <textarea class="form-control" rows="2" id="groupNotes" name="notes" placeholder="e.g., Holiday pricing, Group discount, Special event..."></textarea>
                    </div>
                    
                    <!-- Room Selection (when not applying to all) -->
                    <div id="roomSelectionArea" style="display: none;">
                        <h6><i class="fas fa-check-square mr-2"></i>Select Rooms</h6>
                        <div class="row" id="roomCheckboxes">
                            <!-- Room checkboxes will be populated here -->
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important:</strong> This rate will be applied to <span id="affectedRoomsCount">all</span> selected rooms for the specified date range. 
                        <span id="overrideWarning" style="display: none;">Existing rates will be overwritten.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-magic mr-1"></i> Apply Group Rate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Set Individual Room Rate Modal -->
<div class="modal fade" id="setRateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    Set Individual Room Rate
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="setRateForm">
                <div class="modal-body">
                    <input type="hidden" id="roomId" name="room_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Room</label>
                                <input type="text" class="form-control" id="roomName" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Base Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="basePrice" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Rate per Night</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="0.01" min="0" class="form-control" id="ratePrice" name="price" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="useBasePriceBtn">
                                    Use Base Price
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Notes <small class="text-muted">(Optional)</small></label>
                        <textarea class="form-control" rows="3" name="notes" placeholder="e.g., Holiday pricing, Special event, Season rate..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        This will set an individual rate for this specific room, overriding any group rates.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Set Individual Rate
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
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-magic mr-2"></i>
                    Bulk Pricing Management
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkPricingForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Hotel</label>
                                <select class="form-control" name="hotel_id" id="bulkHotelSelect" required>
                                    <option value="">Select Hotel</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Apply To</label>
                                <select class="form-control" name="apply_to" id="bulkApplyTo" required>
                                    <option value="all_rooms">All Rooms</option>
                                    <option value="room_category">By Room Category</option>
                                    <option value="specific_rooms">Specific Rooms</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Pricing Method</label>
                                <select class="form-control" name="pricing_method" required>
                                    <option value="">Select Method</option>
                                    <option value="fixed">Set Fixed Price</option>
                                    <option value="percentage">Percentage Adjustment</option>
                                    <option value="amount">Amount Adjustment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Value</label>
                                <input type="number" step="0.01" class="form-control" name="value" required>
                                <small class="form-text text-muted" id="valuePricingHint">
                                    Enter the value based on selected pricing method
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important:</strong> This will update rates for all selected rooms across the specified date range. 
                        Existing rates will be overwritten.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-magic mr-1"></i> Apply Bulk Pricing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rate History Modal -->
<div class="modal fade" id="rateHistoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-history mr-2"></i>
                    Rate History - <span id="historyRoomName"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="rateHistoryContent">
                    <!-- Rate history will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Pricing Rule Modal -->
<div class="modal fade" id="createPricingRuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create New Pricing Rule
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createPricingRuleForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i>Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Rule Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" placeholder="e.g., Summer Peak Season" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Description</label>
                                        <textarea class="form-control" name="description" rows="3" placeholder="Optional description of this pricing rule"></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Rule Type <span class="text-danger">*</span></label>
                                        <select class="form-control" name="rule_type" id="modalRuleType" required>
                                            <option value="">Select Rule Type</option>
                                            <option value="seasonal">Seasonal Pricing</option>
                                            <option value="advance_booking">Advance Booking Discount</option>
                                            <option value="length_of_stay">Length of Stay Discount</option>
                                            <option value="day_of_week">Day of Week Pricing</option>
                                            <option value="occupancy">Occupancy Based Pricing</option>
                                            <option value="promotional">Promotional Pricing</option>
                                            <option value="blackout">Blackout Dates</option>
                                            <option value="minimum_stay">Minimum Stay Requirement</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Priority (1-10) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="priority" value="5" min="1" max="10" required>
                                        <small class="form-text text-muted">Higher numbers = higher priority when multiple rules apply</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-target mr-1"></i>Targeting & Dates</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Apply To Hotel</label>
                                        <select class="form-control" name="hotel_id">
                                            <option value="">All My Hotels</option>
                                            @foreach($hotels as $hotel)
                                                <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Room Category</label>
                                        <select class="form-control" name="room_category">
                                            <option value="">All Room Categories</option>
                                            <option value="window_view">Window View</option>
                                            <option value="sea_view">Sea View</option>
                                            <option value="balcony">Balcony</option>
                                            <option value="kitchenette">Kitchenette</option>
                                            <option value="family_suite">Family Suite</option>
                                            <option value="executive_lounge">Executive Lounge</option>
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="start_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="end_date" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Min Nights</label>
                                                <input type="number" class="form-control" name="min_nights" min="1" placeholder="Optional">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Max Nights</label>
                                                <input type="number" class="form-control" name="max_nights" min="1" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calculator mr-1"></i>Price Adjustment</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Adjustment Type <span class="text-danger">*</span></label>
                                                <select class="form-control" name="adjustment_type" id="modalAdjustmentType" required>
                                                    <option value="">Select Type</option>
                                                    <option value="percentage">Percentage Change</option>
                                                    <option value="fixed">Fixed Amount Change</option>
                                                    <option value="multiply">Multiply by Factor</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Adjustment Value <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" name="adjustment_value" id="modalAdjustmentValue" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text" id="modalAdjustmentUnit">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Adjustment Direction</label>
                                                <div class="mt-2">
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" class="custom-control-input" id="increaseRadio" name="adjustment_direction" value="increase" checked>
                                                        <label class="custom-control-label" for="increaseRadio">
                                                            <i class="fas fa-arrow-up text-success mr-1"></i>Increase
                                                        </label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" class="custom-control-input" id="decreaseRadio" name="adjustment_direction" value="decrease">
                                                        <label class="custom-control-label" for="decreaseRadio">
                                                            <i class="fas fa-arrow-down text-danger mr-1"></i>Decrease
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="adjustmentPreview" class="alert alert-info mt-3" style="display: none;">
                                        <i class="fas fa-calculator mr-2"></i>
                                        <strong>Preview:</strong> <span id="previewText"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cogs mr-1"></i>Advanced Options</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="modalRuleActive" name="is_active" checked>
                                                <label class="custom-control-label" for="modalRuleActive">
                                                    <strong>Activate Rule Immediately</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">Rule will be applied to matching bookings right away</small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="modalApplyExisting" name="apply_to_existing">
                                                <label class="custom-control-label" for="modalApplyExisting">
                                                    <strong>Apply to Existing Rates</strong>
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">Update existing room rates with this rule</small>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Create Pricing Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('css')
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        .hotel-section {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .hotel-section:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .hotel-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }
        
        .calendar-container {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .fc-event {
            cursor: pointer;
        }
        
        .fc-event-title {
            font-weight: bold;
        }
        
        .rate-high { background-color: #dc3545 !important; border-color: #dc3545 !important; }
        .rate-medium { background-color: #ffc107 !important; border-color: #ffc107 !important; }
        .rate-low { background-color: #28a745 !important; border-color: #28a745 !important; }
        
        .nav-tabs .nav-link {
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #007bff;
            background: none;
            font-weight: bold;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        
        .modal-header.bg-info,
        .modal-header.bg-success,
        .modal-header.bg-warning {
            border-bottom: none;
        }
        
        .alert {
            border-radius: 0.375rem;
        }
        
        .custom-control-label::before {
            border-radius: 0.25rem;
        }
        
        /* Pricing Rules Dashboard Styles */
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }
        
        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }
        
        .border-left-info {
            border-left: 4px solid #17a2b8 !important;
        }
        
        .border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }
        
        .rule-item {
            transition: all 0.2s ease;
        }
        
        .rule-item:hover {
            background-color: #f8f9fa;
        }
        
        .badge-outline-info {
            color: #17a2b8;
            border: 1px solid #17a2b8;
            background: transparent;
        }
        
        .pricing-rules-container {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .pricing-rules-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .pricing-rules-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .pricing-rules-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        .pricing-rules-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .dropdown-header {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-group-sm .btn {
            border-radius: 0.2rem;
        }
        
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .custom-control-input:indeterminate ~ .custom-control-label::before {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .statistics-card {
            transition: transform 0.2s ease;
        }
        
        .statistics-card:hover {
            transform: translateY(-2px);
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .shadow {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        }
        
        .rule-form-group {
            border: 1px solid #e3e6f0;
            border-radius: 0.5rem;
        }
        
        .rule-form-group .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #e3e6f0;
        }
        
        #adjustmentPreview {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border: 1px solid #b6d4da;
            border-radius: 0.5rem;
        }
        
        .modal-xl {
            max-width: 1140px;
        }
        
        @media (max-width: 768px) {
            .modal-xl {
                max-width: 95%;
            }
        }
        
        /* Analytics Dashboard Styles */
        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        .metric-item {
            padding: 0.5rem;
            border-right: 1px solid #dee2e6;
        }
        
        .metric-item:last-child {
            border-right: none;
        }
        
        .metric-item .h4 {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .card.bg-primary,
        .card.bg-success,
        .card.bg-warning,
        .card.bg-info {
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
            border: none;
        }
        
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: #6c757d;
        }
        
        /* Room Group Styles */
        .room-group-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .room-group-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .room-group-card .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #dee2e6;
        }
        
        .room-group-details {
            font-size: 0.875rem;
        }
        
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        
        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }
        
        .individual-rooms-details {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .individual-rooms-details .table {
            margin-bottom: 0;
        }
        
        .individual-rooms-details .table td,
        .individual-rooms-details .table th {
            padding: 0.5rem;
            border-color: #dee2e6;
        }
        
        .toggle-group-details {
            transition: all 0.3s ease;
        }
        
        .toggle-group-details.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .badge-outline-info {
            background: transparent;
            border: 1px solid #17a2b8;
            color: #17a2b8;
        }
        
        .badge-outline-secondary {
            background: transparent;
            border: 1px solid #6c757d;
            color: #6c757d;
        }
        
        .room-groups-container .card-body {
            border-top: 1px solid #f0f0f0;
        }
        
        /* Group rate status indicators */
        .rate-status-unified {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        
        .rate-status-mixed {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: #212529;
        }
        
        .rate-status-none {
            background: linear-gradient(45deg, #6c757d, #adb5bd);
            color: white;
        }
        
        /* Modal enhancements */
        .modal-xl .modal-dialog {
            max-width: 90%;
        }
        
        #roomCheckboxes .custom-control {
            margin-bottom: 0.5rem;
        }
        
        .room-checkbox-item {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
        }
        
        .room-checkbox-item:hover {
            background-color: #f8f9fa;
        }
        
        .room-checkbox-item.selected {
            background-color: #e7f3ff;
            border-color: #007bff;
        }
        
        /* Pricing Rules Tab Styles */
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
        
        .pricing-rule-card {
            transition: all 0.3s ease;
        }
        
        .pricing-rule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .rule-type-icon {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1rem;
        }
        
        .adjustment-display {
            font-weight: 600;
            color: #495057;
        }
        
        .pricing-rules-container {
            min-height: 400px;
        }
        
        .no-rules-found {
            color: #6c757d;
            font-style: italic;
        }
        
        .d-flex.gap-2 > * {
            margin-right: 0.5rem;
        }
        
        .d-flex.gap-2 > *:last-child {
            margin-right: 0;
        }
    </style>
@stop

@section('scripts')
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        // Fallback to alternative CDN if FullCalendar doesn't load
        if (typeof FullCalendar === 'undefined') {
            console.warn('Primary FullCalendar CDN failed, loading fallback...');
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"><\/script>');
        }
        
        // ===== PRICING RULES SUPPORT FUNCTIONS =====
        
        function loadPricingRulesData() {
            const container = $('#pricing-rules-list');
            container.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><div>Loading pricing rules...</div></div>');
            
            // Get filter parameters
            const filters = {
                type: $('#filterRuleType').val(),
                status: $('#filterRuleStatus').val(),
                hotel: $('#filterRuleHotel').val(),
                search: $('#pricingRulesSearch').val()
            };
            
            $.ajax({
                url: '{{ route("api.b2b.pricing-rules.ajax") }}',
                method: 'GET',
                data: filters,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success || response.data) {
                        const rules = response.data || response;
                        displayPricingRules(rules);
                        updatePricingRulesStats(rules);
                    } else {
                        container.html('<div class="text-muted text-center py-5"><i class="fas fa-info-circle fa-2x mb-3"></i><div>No pricing rules found.</div></div>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading pricing rules:', xhr);
                    container.html('<div class="text-danger text-center py-5"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><div>Error loading pricing rules.</div></div>');
                }
            });
        }
        
        function displayPricingRules(rules) {
            const container = $('#pricing-rules-list');
            const isGridView = $('#viewGridBtn').hasClass('active');
            
            if (!rules || rules.length === 0) {
                container.html('<div class="text-muted text-center py-5"><i class="fas fa-info-circle fa-2x mb-3"></i><div>No pricing rules found.</div></div>');
                return;
            }
            
            let html = '';
            
            if (isGridView) {
                html = '<div class="row">';
                rules.forEach(function(rule) {
                    html += createPricingRuleCard(rule);
                });
                html += '</div>';
            } else {
                html = '<div class="list-group list-group-flush">';
                rules.forEach(function(rule) {
                    html += createPricingRuleListItem(rule);
                });
                html += '</div>';
            }
            
            container.html(html);
        }
        
        function createPricingRuleCard(rule) {
            const typeConfig = getRuleTypeConfig(rule.rule_type);
            const statusBadge = rule.is_active 
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>';
            
            const adjustmentDisplay = formatAdjustmentDisplay(rule.adjustment_type, rule.adjustment_value);
            
            return `
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">
                                    <i class="fas ${typeConfig.icon} text-${typeConfig.color} mr-1"></i>
                                    ${rule.name}
                                </h6>
                                ${statusBadge}
                            </div>
                            <p class="card-text text-muted small mb-2">
                                <strong>Type:</strong> ${typeConfig.label}<br>
                                <strong>Adjustment:</strong> ${adjustmentDisplay}<br>
                                <strong>Dates:</strong> ${rule.start_date} to ${rule.end_date}<br>
                                <strong>Priority:</strong> ${rule.priority}/10
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    ${rule.hotel_name ? rule.hotel_name : 'All Hotels'}
                                </small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-info" onclick="viewRuleDetails(${rule.id})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleRuleStatus(${rule.id})" title="Toggle Status">
                                        <i class="fas fa-${rule.is_active ? 'pause' : 'play'}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteRule(${rule.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function createPricingRuleListItem(rule) {
            const typeConfig = getRuleTypeConfig(rule.rule_type);
            const statusBadge = rule.is_active 
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>';
            
            const adjustmentDisplay = formatAdjustmentDisplay(rule.adjustment_type, rule.adjustment_value);
            
            return `
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h6 class="mb-1">
                                <i class="fas ${typeConfig.icon} text-${typeConfig.color} mr-1"></i>
                                ${rule.name}
                            </h6>
                            <p class="mb-1 text-muted small">${typeConfig.label}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">
                                <strong>Adjustment:</strong><br>
                                ${adjustmentDisplay}<br>
                                <strong>Priority:</strong> ${rule.priority}/10
                            </small>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">
                                <strong>Active Period:</strong><br>
                                ${rule.start_date} to ${rule.end_date}<br>
                                <strong>Scope:</strong> ${rule.hotel_name ? rule.hotel_name : 'All Hotels'}
                            </small>
                        </div>
                        <div class="col-md-2 text-right">
                            ${statusBadge}
                            <div class="btn-group btn-group-sm mt-2" role="group">
                                <button type="button" class="btn btn-outline-info" onclick="viewRuleDetails(${rule.id})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleRuleStatus(${rule.id})" title="Toggle Status">
                                    <i class="fas fa-${rule.is_active ? 'pause' : 'play'}"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteRule(${rule.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function updatePricingRulesStats(rules) {
            const total = rules.length;
            const active = rules.filter(rule => rule.is_active).length;
            const seasonal = rules.filter(rule => rule.rule_type === 'seasonal').length;
            const promotional = rules.filter(rule => rule.rule_type === 'promotional').length;
            
            $('#totalRulesCount').text(total);
            $('#activeRulesCount').text(active);
            $('#seasonalRulesCount').text(seasonal);
            $('#promotionalRulesCount').text(promotional);
        }
        
        function getRuleTypeConfig(type) {
            const configs = {
                seasonal: { label: 'Seasonal Pricing', icon: 'fa-calendar-week', color: 'warning' },
                advance_booking: { label: 'Advance Booking', icon: 'fa-clock', color: 'success' },
                length_of_stay: { label: 'Length of Stay', icon: 'fa-bed', color: 'primary' },
                day_of_week: { label: 'Day of Week', icon: 'fa-calendar-day', color: 'secondary' },
                occupancy: { label: 'Occupancy Based', icon: 'fa-users', color: 'info' },
                promotional: { label: 'Promotional', icon: 'fa-tags', color: 'danger' },
                blackout: { label: 'Blackout Dates', icon: 'fa-ban', color: 'dark' },
                minimum_stay: { label: 'Minimum Stay', icon: 'fa-hourglass-half', color: 'indigo' }
            };
            
            return configs[type] || { label: type, icon: 'fa-cog', color: 'secondary' };
        }
        
        function formatAdjustmentDisplay(type, value) {
            switch (type) {
                case 'percentage':
                    return (value > 0 ? '+' : '') + value + '%';
                case 'fixed':
                    return '$' + parseFloat(value).toFixed(2);
                case 'multiply':
                    return 'x' + value;
                default:
                    return value;
            }
        }
        
        function validateQuickRuleForm() {
            const form = $('#quickCreateRuleForm');
            const ruleName = form.find('input[name="rule_name"]').val();
            const ruleType = form.find('select[name="rule_type"]').val();
            const startDate = form.find('input[name="start_date"]').val();
            const endDate = form.find('input[name="end_date"]').val();
            const adjustmentType = form.find('select[name="adjustment_type"]').val();
            const adjustmentValue = form.find('input[name="adjustment_value"]').val();
            
            if (!ruleName.trim()) {
                toastr.error('Please enter a rule name');
                return false;
            }
            
            if (!ruleType) {
                toastr.error('Please select a rule type');
                return false;
            }
            
            if (!startDate) {
                toastr.error('Please select a start date');
                return false;
            }
            
            if (!endDate) {
                toastr.error('Please select an end date');
                return false;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                toastr.error('End date must be after start date');
                return false;
            }
            
            if (!adjustmentType) {
                toastr.error('Please select an adjustment type');
                return false;
            }
            
            if (!adjustmentValue || isNaN(adjustmentValue)) {
                toastr.error('Please enter a valid adjustment value');
                return false;
            }
            
            return true;
        }
        
        function viewRuleDetails(ruleId) {
            toastr.info('Rule details view - Feature coming soon!');
            // TODO: Implement detailed rule view modal
        }
        
        function toggleRuleStatus(ruleId) {
            $.ajax({
                url: `{{ route('b2b.hotel-provider.pricing-rules.toggle', '') }}/${ruleId}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success || response.message) {
                        toastr.success(response.message || 'Rule status updated successfully');
                        loadPricingRulesData();
                    } else {
                        toastr.error(response.message || 'Error updating rule status');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error updating rule status');
                }
            });
        }
        
        function deleteRule(ruleId) {
            if (confirm('Are you sure you want to delete this pricing rule? This action cannot be undone.')) {
                $.ajax({
                    url: `{{ route('api.b2b.pricing-rules.ajax.destroy', '') }}/${ruleId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success || response.message) {
                            toastr.success(response.message || 'Pricing rule deleted successfully');
                            loadPricingRulesData();
                        } else {
                            toastr.error(response.message || 'Error deleting pricing rule');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error deleting pricing rule');
                    }
                });
            }
        }
        
        function applyPricingRulesNow() {
            const btn = $('#applyPricingRulesBtn');
            const originalText = btn.html();
            
            // Show confirmation modal first
            if (confirm('This will apply all active pricing rules to room rates. Continue?')) {
                btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Applying...').prop('disabled', true);
                
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.room-rates.apply-pricing-rules") }}',
                    method: 'POST',
                    data: {
                        start_date: new Date().toISOString().split('T')[0],
                        end_date: new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().split('T')[0]
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Pricing rules applied successfully');
                            showPricingRulesResults(response.data);
                            
                            // Refresh the current rates view after a short delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            toastr.error(response.message || 'Error applying pricing rules');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error applying pricing rules');
                    },
                    complete: function() {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            }
        }
        
        function showPricingRulesResults(data) {
            const summary = data.summary;
            const message = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle mr-2"></i>Pricing Rules Applied Successfully</h6>
                    <p><strong>Rooms affected:</strong> ${summary.rooms_affected}</p>
                    <p><strong>Rates created:</strong> ${summary.rates_created}</p>
                    <p><strong>Rates updated:</strong> ${summary.rates_updated}</p>
                    <p><strong>Date range:</strong> ${summary.date_range.start} to ${summary.date_range.end}</p>
                </div>
            `;
            
            toastr.success(message, 'Success', {
                timeOut: 10000,
                extendedTimeOut: 5000,
                allowHtml: true
            });
        }
        
        function previewRulesImpact() {
            const btn = $('#previewRulesBtn');
            const originalText = btn.html();
            
            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Previewing...').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("b2b.hotel-provider.room-rates.apply-pricing-rules") }}',
                method: 'POST',
                data: {
                    start_date: new Date().toISOString().split('T')[0],
                    end_date: new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().split('T')[0],
                    dry_run: true
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showPricingRulesPreview(response.data);
                    } else {
                        toastr.error(response.message || 'Error previewing pricing rules');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error previewing pricing rules');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }
        
        function showPricingRulesPreview(data) {
            const affectedRooms = data.affected_rooms;
            const summary = data.summary;
            
            let previewHtml = `
                <div class="modal fade" id="pricingRulesPreviewModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">
                                    <i class="fas fa-eye mr-2"></i>Pricing Rules Impact Preview
                                </h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle mr-2"></i>Preview Summary</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Rooms Affected:</strong><br>
                                            <span class="badge badge-primary badge-lg">${summary.rooms_affected}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>New Rates:</strong><br>
                                            <span class="badge badge-success badge-lg">${summary.rates_created}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Updated Rates:</strong><br>
                                            <span class="badge badge-warning badge-lg">${summary.rates_updated}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Date Range:</strong><br>
                                            <small>${summary.date_range.start} to ${summary.date_range.end}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-striped">
                                        <thead class="thead-light sticky-top">
                                            <tr>
                                                <th>Room</th>
                                                <th>Hotel</th>
                                                <th>Base Price</th>
                                                <th>Adjustments</th>
                                                <th>Impact</th>
                                            </tr>
                                        </thead>
                                        <tbody>
            `;
            
            affectedRooms.forEach(function(room) {
                const adjustmentCount = room.rates_applied.length;
                const totalAdjustment = room.rates_applied.reduce((sum, rate) => sum + rate.adjustment, 0);
                const avgAdjustment = adjustmentCount > 0 ? (totalAdjustment / adjustmentCount).toFixed(2) : 0;
                
                previewHtml += `
                    <tr>
                        <td>
                            <strong>${room.room_number}</strong>
                        </td>
                        <td>${room.hotel_name}</td>
                        <td>$${room.base_price}</td>
                        <td>
                            <span class="badge badge-info">${adjustmentCount} dates</span>
                        </td>
                        <td>
                            <span class="badge ${avgAdjustment > 0 ? 'badge-success' : avgAdjustment < 0 ? 'badge-danger' : 'badge-secondary'}">
                                ${avgAdjustment > 0 ? '+' : ''}$${avgAdjustment} avg
                            </span>
                        </td>
                    </tr>
                `;
            });
            
            previewHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-success" onclick="confirmApplyPricingRules()">
                                    <i class="fas fa-check mr-1"></i>Apply These Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            $('#pricingRulesPreviewModal').remove();
            
            // Add and show new modal
            $('body').append(previewHtml);
            $('#pricingRulesPreviewModal').modal('show');
        }
        
        function confirmApplyPricingRules() {
            $('#pricingRulesPreviewModal').modal('hide');
            
            // Wait for modal to hide, then apply rules
            setTimeout(function() {
                applyPricingRulesNow();
            }, 500);
        }
        
        // ===== ENHANCED BULK OPERATIONS =====
        
        function showBulkCreateModal() {
            const modalHtml = `
                <div class="modal fade" id="bulkCreateRulesModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-plus-circle mr-2"></i>Bulk Create Pricing Rules
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Create multiple pricing rules at once. Add as many rules as needed below.
                                </div>
                                
                                <form id="bulkCreateForm">
                                    <div id="rulesContainer">
                                        <div class="rule-form-group card mb-3" data-index="0">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Rule #1</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-rule-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                ${generateRuleFormFields(0)}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mb-3">
                                        <button type="button" class="btn btn-outline-primary" id="addRuleBtn">
                                            <i class="fas fa-plus mr-1"></i>Add Another Rule
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-primary" id="executeBulkCreate">
                                    <i class="fas fa-magic mr-1"></i>Create All Rules
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#bulkCreateRulesModal').remove();
            $('body').append(modalHtml);
            $('#bulkCreateRulesModal').modal('show');
            
            // Bind events
            bindBulkCreateEvents();
        }
        
        function generateRuleFormFields(index) {
            return `
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rule Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="rules[${index}][name]" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rule Type <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" name="rules[${index}][rule_type]" required>
                                <option value="">Select Type</option>
                                <option value="seasonal">Seasonal Pricing</option>
                                <option value="advance_booking">Advance Booking</option>
                                <option value="length_of_stay">Length of Stay</option>
                                <option value="day_of_week">Day of Week</option>
                                <option value="occupancy">Occupancy Based</option>
                                <option value="promotional">Promotional</option>
                                <option value="blackout">Blackout Dates</option>
                                <option value="minimum_stay">Minimum Stay</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hotel</label>
                            <select class="form-control form-control-sm" name="rules[${index}][hotel_id]">
                                <option value="">All Hotels</option>
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Room Category</label>
                            <select class="form-control form-control-sm" name="rules[${index}][room_category]">
                                <option value="">All Categories</option>
                                <option value="window_view">Window View</option>
                                <option value="sea_view">Sea View</option>
                                <option value="balcony">Balcony</option>
                                <option value="kitchenette">Kitchenette</option>
                                <option value="family_suite">Family Suite</option>
                                <option value="executive_lounge">Executive Lounge</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="rules[${index}][start_date]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="rules[${index}][end_date]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Adjustment Type <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" name="rules[${index}][adjustment_type]" required>
                                <option value="">Select Type</option>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                                <option value="multiply">Multiplier</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="rules[${index}][adjustment_value]" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Priority (1-10)</label>
                            <input type="number" class="form-control form-control-sm" name="rules[${index}][priority]" value="5" min="1" max="10">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Min Nights</label>
                            <input type="number" class="form-control form-control-sm" name="rules[${index}][min_nights]" min="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Max Nights</label>
                            <input type="number" class="form-control form-control-sm" name="rules[${index}][max_nights]" min="1">
                        </div>
                    </div>
                </div>
            `;
        }
        
        function bindBulkCreateEvents() {
            let ruleIndex = 0;
            
            // Add new rule
            $('#addRuleBtn').on('click', function() {
                ruleIndex++;
                const newRuleHtml = `
                    <div class="rule-form-group card mb-3" data-index="${ruleIndex}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Rule #${ruleIndex + 1}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-rule-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            ${generateRuleFormFields(ruleIndex)}
                        </div>
                    </div>
                `;
                $('#rulesContainer').append(newRuleHtml);
            });
            
            // Remove rule
            $(document).on('click', '.remove-rule-btn', function() {
                if ($('.rule-form-group').length > 1) {
                    $(this).closest('.rule-form-group').remove();
                    updateRuleNumbers();
                } else {
                    toastr.warning('At least one rule is required');
                }
            });
            
            // Execute bulk create
            $('#executeBulkCreate').on('click', function() {
                const formData = $('#bulkCreateForm').serializeArray();
                const rules = [];
                
                // Group form data by rule index
                $('.rule-form-group').each(function() {
                    const index = $(this).data('index');
                    const rule = {};
                    
                    $(this).find('input, select').each(function() {
                        const name = $(this).attr('name');
                        const value = $(this).val();
                        if (name && value) {
                            const fieldName = name.match(/\[([^\]]+)\]$/)[1];
                            rule[fieldName] = value;
                        }
                    });
                    
                    if (rule.name && rule.rule_type) {
                        // Set default is_active for bulk created rules
                        rule.is_active = 1;
                        rules.push(rule);
                    }
                });
                
                if (rules.length === 0) {
                    toastr.error('Please fill in at least one complete rule');
                    return;
                }
                
                executeBulkCreate(rules);
            });
        }
        
        function updateRuleNumbers() {
            $('.rule-form-group').each(function(index) {
                $(this).find('.card-header h6').text(`Rule #${index + 1}`);
            });
        }
        
        function executeBulkCreate(rules) {
            const btn = $('#executeBulkCreate');
            const originalText = btn.html();
            
            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Creating...').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.bulk-create") }}',
                method: 'POST',
                data: {
                    rules: rules,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#bulkCreateRulesModal').modal('hide');
                        loadPricingRulesData();
                    } else {
                        toastr.error(response.message || 'Error creating rules');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error creating bulk rules');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }
        
        function showImportModal() {
            const modalHtml = `
                <div class="modal fade" id="importRulesModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-upload mr-2"></i>Import Pricing Rules
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Import pricing rules from a JSON file exported from this system.
                                </div>
                                
                                <form id="importForm">
                                    <div class="form-group">
                                        <label>Import Mode</label>
                                        <select class="form-control" name="import_mode" required>
                                            <option value="create_new">Create New Rules Only</option>
                                            <option value="replace_existing">Replace Existing Rules (by ID)</option>
                                            <option value="merge">Merge (update existing, create new)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Name Prefix (Optional)</label>
                                        <input type="text" class="form-control" name="name_prefix" placeholder="e.g., 'Imported'">
                                        <small class="form-text text-muted">Add a prefix to imported rule names to avoid conflicts</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Import Data</label>
                                        <textarea class="form-control" name="import_data" rows="10" placeholder="Paste JSON data here..." required></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-info" id="executeImport">
                                    <i class="fas fa-upload mr-1"></i>Import Rules
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#importRulesModal').remove();
            $('body').append(modalHtml);
            $('#importRulesModal').modal('show');
            
            $('#executeImport').on('click', function() {
                const formData = $('#importForm').serializeArray();
                const importData = formData.find(item => item.name === 'import_data').value;
                
                try {
                    const jsonData = JSON.parse(importData);
                    executeImport({
                        import_data: jsonData,
                        import_mode: formData.find(item => item.name === 'import_mode').value,
                        name_prefix: formData.find(item => item.name === 'name_prefix')?.value || ''
                    });
                } catch (e) {
                    toastr.error('Invalid JSON data. Please check the format.');
                }
            });
        }
        
        function executeImport(data) {
            const btn = $('#executeImport');
            const originalText = btn.html();
            
            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Importing...').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.import") }}',
                method: 'POST',
                data: Object.assign(data, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#importRulesModal').modal('hide');
                        loadPricingRulesData();
                    } else {
                        toastr.error(response.message || 'Error importing rules');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error importing rules');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }
        
        function exportPricingRules(selectedIds) {
            if (!selectedIds || selectedIds.length === 0) {
                toastr.error('Please select rules to export');
                return;
            }
            
            const url = '{{ route("b2b.hotel-provider.pricing-rules.export", "RULE_IDS") }}'.replace('RULE_IDS', selectedIds.join(','));
            
            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Create and download the export file
                        const dataStr = JSON.stringify(response.data.export_data, null, 2);
                        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                        
                        const exportFileDefaultName = response.data.filename;
                        const linkElement = document.createElement('a');
                        linkElement.setAttribute('href', dataUri);
                        linkElement.setAttribute('download', exportFileDefaultName);
                        linkElement.click();
                        
                        toastr.success(`Exported ${response.data.count} pricing rules successfully`);
                    } else {
                        toastr.error(response.message || 'Error exporting rules');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error exporting rules');
                }
            });
        }
        
        // Enhanced bulk action buttons event handlers
        
        // Bulk Create button handler
        $(document).on('click', '#bulkCreateBtn', function(e) {
            e.preventDefault();
            showBulkCreateModal();
        });
        
        // More Actions dropdown handlers
        $(document).on('click', '[data-action="import"]', function(e) {
            e.preventDefault();
            showImportModal();
        });
        
        $(document).on('click', '[data-action="export"]', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to export first');
                return;
            }
            
            exportPricingRules(selectedRules);
        });
        
        $(document).on('click', '[data-action="bulk-disable"]', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to disable first');
                return;
            }
            
            if (confirm(`Are you sure you want to disable ${selectedRules.length} pricing rule(s)?`)) {
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.pricing-rules.bulk-action") }}',
                    method: 'POST',
                    data: {
                        action: 'deactivate',
                        pricing_rule_ids: selectedRules,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadPricingRulesData();
                        } else {
                            toastr.error(response.message || 'Error disabling rules');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error disabling rules');
                    }
                });
            }
        });
        
        // Additional bulk actions for priority update and date range updates
        $(document).on('click', '[data-action="update-priority"]', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to update priority first');
                return;
            }
            
            const newPriority = prompt('Enter new priority (1-10):', '5');
            if (newPriority && newPriority >= 1 && newPriority <= 10) {
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.pricing-rules.bulk-action") }}',
                    method: 'POST',
                    data: {
                        action: 'update_priority',
                        pricing_rule_ids: selectedRules,
                        priority: newPriority,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadPricingRulesData();
                        } else {
                            toastr.error(response.message || 'Error updating priority');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error updating priority');
                    }
                });
            }
        });
        
        $(document).on('click', '[data-action="bulk-enable"]', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to enable first');
                return;
            }
            
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.bulk-action") }}',
                method: 'POST',
                data: {
                    action: 'activate',
                    pricing_rule_ids: selectedRules,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadPricingRulesData();
                    } else {
                        toastr.error(response.message || 'Error enabling rules');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error enabling rules');
                }
            });
        });
        
        $(document).on('click', '[data-action="bulk-delete"]', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to delete first');
                return;
            }
            
            if (confirm(`Are you sure you want to permanently delete ${selectedRules.length} pricing rule(s)? This action cannot be undone.`)) {
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.pricing-rules.bulk-action") }}',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        pricing_rule_ids: selectedRules,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadPricingRulesData();
                        } else {
                            toastr.error(response.message || 'Error deleting rules');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error deleting rules');
                    }
                });
            }
        });
        
        // Legacy event handlers for backward compatibility
        $(document).on('click', '#importRulesBtn', function(e) {
            e.preventDefault();
            showImportModal();
        });
        
        // ===== BULK SELECTION AND UI MANAGEMENT =====
        
        // Handle select all functionality
        $(document).on('change', '#selectAllRules', function() {
            const isChecked = $(this).is(':checked');
            $('.rule-checkbox').prop('checked', isChecked);
            updateBulkSelectionUI();
        });
        
        // Handle individual rule selection
        $(document).on('change', '.rule-checkbox', function() {
            updateBulkSelectionUI();
            
            // Update select all checkbox state
            const totalCheckboxes = $('.rule-checkbox').length;
            const checkedCheckboxes = $('.rule-checkbox:checked').length;
            
            if (checkedCheckboxes === 0) {
                $('#selectAllRules').prop('indeterminate', false).prop('checked', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#selectAllRules').prop('indeterminate', false).prop('checked', true);
            } else {
                $('#selectAllRules').prop('indeterminate', true).prop('checked', false);
            }
        });
        
        // Update bulk selection UI elements
        function updateBulkSelectionUI() {
            const selectedCount = $('.rule-checkbox:checked').length;
            $('#selectedRulesCount').text(selectedCount);
            
            if (selectedCount > 0) {
                $('#bulkActionButtons').removeClass('d-none');
            } else {
                $('#bulkActionButtons').addClass('d-none');
            }
        }
        
        // Clear filters functionality
        $(document).on('click', '#clearFiltersBtn', function() {
            $('#filterRuleType').val('');
            $('#filterRuleStatus').val('');
            $('#filterRuleHotel').val('');
            $('#pricingRulesSearch').val('');
            loadPricingRulesData();
        });
        
        // ===== PRICING RULES CORE FUNCTIONALITY =====
        
        // Load pricing rules data
        function loadPricingRulesData() {
            const filters = {
                type: $('#filterRuleType').val(),
                status: $('#filterRuleStatus').val(),
                hotel_id: $('#filterRuleHotel').val(),
                search: $('#pricingRulesSearch').val()
            };
            
            $('#pricing-rules-list').html(`
                <div class="text-muted text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <div>Loading pricing rules...</div>
                </div>
            `);
            
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.index") }}',
                method: 'GET',
                data: Object.assign(filters, { ajax: 1 }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Pricing rules response:', response); // Debug log
                    
                    if (response && response.success && response.data) {
                        // Controller returns response.data as the array of rules
                        displayPricingRules(response.data);
                        updatePricingRulesStats({
                            total: response.data.length,
                            active: response.data.filter(r => r.is_active).length,
                            seasonal: response.data.filter(r => r.rule_type === 'seasonal').length,
                            promotional: response.data.filter(r => r.rule_type === 'promotional').length
                        });
                    } else if (response && Array.isArray(response)) {
                        // Handle direct array response
                        displayPricingRules(response);
                        updatePricingRulesStats({
                            total: response.length,
                            active: response.filter(r => r.is_active).length,
                            seasonal: response.filter(r => r.rule_type === 'seasonal').length,
                            promotional: response.filter(r => r.rule_type === 'promotional').length
                        });
                    } else {
                        console.warn('Unexpected response format:', response);
                        displayPlaceholderPricingRules();
                    }
                },
                error: function(xhr) {
                    console.warn('Pricing rules endpoint not fully implemented, showing placeholder');
                    displayPlaceholderPricingRules();
                }
            });
        }
        
        // Show placeholder when backend is not ready
        function displayPlaceholderPricingRules() {
            $('#pricing-rules-list').html(`
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                    <h6 class="text-muted">Pricing Rules Dashboard Ready</h6>
                    <p class="text-muted mb-3">This is your pricing rules management interface. Create your first rule to get started.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPricingRuleModal">
                        <i class="fas fa-plus mr-1"></i>Create Your First Rule
                    </button>
                </div>
            `);
            
            // Set placeholder stats
            updatePricingRulesStats({
                total: 0,
                active: 0,
                seasonal: 0,
                promotional: 0
            });
        }
        
        // Display pricing rules in the list
        function displayPricingRules(rules) {
            console.log('Displaying pricing rules:', rules); // Debug log
            
            if (!rules || rules.length === 0) {
                $('#pricing-rules-list').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No pricing rules found</h6>
                        <p class="text-muted mb-3">Create your first pricing rule to get started</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPricingRuleModal">
                            <i class="fas fa-plus mr-1"></i>Create Pricing Rule
                        </button>
                    </div>
                `);
                return;
            }
            
            let rulesHtml = '';
            rules.forEach((rule, index) => {
                try {
                    const statusBadge = rule.is_active 
                        ? '<span class="badge badge-success">Active</span>' 
                        : '<span class="badge badge-secondary">Inactive</span>';
                    
                    const ruleTypeIcon = getRuleTypeIcon(rule.rule_type || 'default');
                    const adjustmentDisplay = getAdjustmentDisplay(rule.adjustment_type || 'percentage', rule.adjustment_value || 0);
                    
                    // Safely handle rule data with defaults
                    const ruleName = rule.name || 'Unnamed Rule';
                    const ruleType = (rule.rule_type || 'unknown').replace('_', ' ').toUpperCase();
                    const ruleId = rule.id || index;
                    const priority = rule.priority || 5;
                    const startDate = formatDate(rule.start_date || null);
                    const endDate = formatDate(rule.end_date || null);
                    const isActive = rule.is_active ? 'active' : 'inactive';
                    const toggleTitle = rule.is_active ? 'Disable' : 'Enable';
                    const toggleIcon = rule.is_active ? 'fa-pause' : 'fa-play';
                    
                    rulesHtml += `
                        <div class="border-bottom p-3 rule-item" data-rule-id="${ruleId}">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input rule-checkbox" id="rule-${ruleId}" value="${ruleId}">
                                        <label class="custom-control-label" for="rule-${ruleId}"></label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="${ruleTypeIcon} fa-lg mr-3 text-primary"></i>
                                        <div>
                                            <h6 class="mb-1">${ruleName}</h6>
                                            <small class="text-muted">${ruleType}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <strong>${adjustmentDisplay}</strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted d-block">${startDate}</small>
                                    <small class="text-muted">to ${endDate}</small>
                                </div>
                                <div class="col-md-1 text-center">
                                    <span class="badge badge-outline-info">P${priority}</span>
                                </div>
                                <div class="col-md-1">
                                    ${statusBadge}
                                </div>
                                <div class="col-md-1 text-right">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-info btn-sm edit-rule-btn" data-rule-id="${ruleId}" title="Edit Rule">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm toggle-rule-btn" 
                                                data-rule-id="${ruleId}" 
                                                data-status="${isActive}"
                                                title="${toggleTitle} Rule">
                                            <i class="fas ${toggleIcon}"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm delete-rule-btn" data-rule-id="${ruleId}" title="Delete Rule">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } catch (error) {
                    console.error('Error rendering rule:', rule, error);
                    rulesHtml += `
                        <div class="border-bottom p-3 rule-item alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error rendering rule #${index + 1}: ${error.message}
                        </div>
                    `;
                }
            });
            
            $('#pricing-rules-list').html(rulesHtml);
        }
        
        // Update pricing rules statistics
        function updatePricingRulesStats(stats) {
            if (stats) {
                $('#totalRulesCount').text(stats.total || 0);
                $('#activeRulesCount').text(stats.active || 0);
                $('#seasonalRulesCount').text(stats.seasonal || 0);
                $('#promotionalRulesCount').text(stats.promotional || 0);
                
                // Update analytics metrics
                updateAnalyticsMetrics(stats.analytics || {});
            }
        }
        
        // Update analytics metrics
        function updateAnalyticsMetrics(analytics) {
            const avgIncrease = analytics.avg_price_impact || 0;
            const rulesApplied = analytics.rules_applied_today || 0;
            const roomsAffected = analytics.rooms_affected || 0;
            const revenueImpact = analytics.estimated_revenue_impact || 0;
            
            // Format average price increase
            if (avgIncrease > 0) {
                $('#avgPriceIncrease').text(`+${avgIncrease.toFixed(1)}%`).removeClass('text-danger').addClass('text-success');
            } else if (avgIncrease < 0) {
                $('#avgPriceIncrease').text(`${avgIncrease.toFixed(1)}%`).removeClass('text-success').addClass('text-danger');
            } else {
                $('#avgPriceIncrease').text('0%').removeClass('text-success text-danger').addClass('text-muted');
            }
            
            $('#rulesAppliedToday').text(rulesApplied);
            $('#roomsAffected').text(roomsAffected.toLocaleString());
            $('#revenueImpact').text(`$${revenueImpact.toLocaleString()}`);
        }
        
        // Helper functions
        function getRuleTypeIcon(ruleType) {
            const icons = {
                'seasonal': 'fas fa-calendar-week',
                'advance_booking': 'fas fa-clock',
                'length_of_stay': 'fas fa-bed',
                'day_of_week': 'fas fa-calendar-day',
                'occupancy': 'fas fa-users',
                'promotional': 'fas fa-tags',
                'blackout': 'fas fa-ban',
                'minimum_stay': 'fas fa-hourglass'
            };
            return icons[ruleType] || 'fas fa-cog';
        }
        
        function getAdjustmentDisplay(type, value) {
            switch(type) {
                case 'percentage':
                    return value > 0 ? `+${value}%` : `${value}%`;
                case 'fixed':
                    return value > 0 ? `+$${value}` : `-$${Math.abs(value)}`;
                case 'multiply':
                    return `×${value}`;
                default:
                    return value;
            }
        }
        
        function formatDate(dateString) {
            if (!dateString) return '-';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return dateString; // Return original string if invalid date
                }
                return date.toLocaleDateString();
            } catch (error) {
                console.warn('Error formatting date:', dateString, error);
                return dateString || '-';
            }
        }
        
        // Event handlers
        $(document).on('click', '#applyFiltersBtn', function() {
            loadPricingRulesData();
        });
        
        $(document).on('click', '#refreshRulesBtn', function() {
            loadPricingRulesData();
        });
        
        // Search functionality with debounce
        const debouncedSearch = debounce(function() {
            loadPricingRulesData();
        }, 500);
        
        $(document).on('input', '#pricingRulesSearch', debouncedSearch);
        
        // Toggle rule status
        $(document).on('click', '.toggle-rule-btn', function() {
            const ruleId = $(this).data('rule-id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.toggle", ":id") }}'.replace(':id', ruleId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadPricingRulesData();
                    } else {
                        toastr.error(response.message || 'Error toggling rule status');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error toggling rule status');
                }
            });
        });
        
        // Delete rule
        $(document).on('click', '.delete-rule-btn', function() {
            const ruleId = $(this).data('rule-id');
            
            if (confirm('Are you sure you want to delete this pricing rule? This action cannot be undone.')) {
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.pricing-rules.destroy", ":id") }}'.replace(':id', ruleId),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadPricingRulesData();
                        } else {
                            toastr.error(response.message || 'Error deleting rule');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error deleting rule');
                    }
                });
            }
        });
        
        // Initialize pricing rules when tab is shown
        $(document).on('shown.bs.tab', '#pricing-rules-tab', function() {

            loadPricingRulesData();
        });
        
        // Also load when tab is clicked (backup)
        $(document).on('click', '#pricing-rules-tab', function() {
            setTimeout(() => {
                if ($(this).hasClass('active') && $('#pricing-rules-list .text-center').length > 0) {

                    loadPricingRulesData();
                }
            }, 100);
        });
        
        // Manual test function - can be called from console
        window.testPricingRulesLoad = function() {

            loadPricingRulesData();
        };
        
        // Test with demo data
        window.testWithDemoData = function() {

            const demoRules = [
                {
                    id: 1,
                    name: 'Summer Peak Season',
                    rule_type: 'seasonal',
                    hotel_id: 1,
                    hotel_name: 'Demo Hotel',
                    room_category: 'sea_view',
                    room_category_display: 'Sea View',
                    start_date: '2024-06-01',
                    end_date: '2024-08-31',
                    adjustment_type: 'percentage',
                    adjustment_value: 25,
                    min_nights: 2,
                    max_nights: null,
                    priority: 8,
                    is_active: true,
                    created_at: '2024-01-01 12:00:00',
                    updated_at: '2024-01-01 12:00:00'
                },
                {
                    id: 2,
                    name: 'Early Booking Discount',
                    rule_type: 'advance_booking',
                    hotel_id: null,
                    hotel_name: 'All Hotels',
                    room_category: null,
                    room_category_display: 'All Categories',
                    start_date: '2024-01-01',
                    end_date: '2024-12-31',
                    adjustment_type: 'percentage',
                    adjustment_value: -15,
                    min_nights: 3,
                    max_nights: null,
                    priority: 5,
                    is_active: true,
                    created_at: '2024-01-01 12:00:00',
                    updated_at: '2024-01-01 12:00:00'
                }
            ];
            
            displayPricingRules(demoRules);
            updatePricingRulesStats({
                total: 2,
                active: 2,
                seasonal: 1,
                promotional: 0
            });
        };
        
        // View detailed analytics
        $(document).on('click', '#viewAnalyticsBtn', function() {
            showDetailedAnalyticsModal();
        });
        
        // Show detailed analytics modal
        function showDetailedAnalyticsModal() {
            const modalHtml = `
                <div class="modal fade" id="detailedAnalyticsModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-chart-line mr-2"></i>Pricing Rules Analytics Dashboard
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-1" id="detailTotalRules">0</h3>
                                                <p class="mb-0">Total Rules Created</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-1" id="detailActiveRules">0</h3>
                                                <p class="mb-0">Currently Active</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-1" id="detailRoomsImpacted">0</h3>
                                                <p class="mb-0">Rooms Impacted</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-1" id="detailAvgImpact">0%</h3>
                                                <p class="mb-0">Avg Price Impact</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Rule Types Distribution</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="ruleTypesChart" class="text-center py-4">
                                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Rule types breakdown would appear here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Recent Performance</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="performanceChart" class="text-center py-4">
                                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Performance trends would appear here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Top Performing Rules</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Rule Name</th>
                                                        <th>Type</th>
                                                        <th>Rooms Affected</th>
                                                        <th>Avg Impact</th>
                                                        <th>Est. Revenue</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="topRulesTable">
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-3">
                                                            <i class="fas fa-spinner fa-spin mr-2"></i>Loading analytics data...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Close
                                </button>
                                <button type="button" class="btn btn-info" onclick="exportAnalytics()">
                                    <i class="fas fa-download mr-1"></i>Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#detailedAnalyticsModal').remove();
            $('body').append(modalHtml);
            $('#detailedAnalyticsModal').modal('show');
            
            // Load detailed analytics data
            loadDetailedAnalytics();
        }
        
        // Load detailed analytics data
        function loadDetailedAnalytics() {
            // Check if route exists, if not show placeholder data
            const analyticsUrl = '{{ route("b2b.hotel-provider.pricing-rules.analytics") }}';
            
            $.ajax({
                url: analyticsUrl,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response && response.success) {
                        populateDetailedAnalytics(response.data);
                    } else {
                        loadPlaceholderAnalytics();
                    }
                },
                error: function(xhr) {
                    console.warn('Analytics endpoint not available, showing placeholder data');
                    loadPlaceholderAnalytics();
                }
            });
        }
        
        // Load placeholder analytics when backend is not ready
        function loadPlaceholderAnalytics() {
            const placeholderData = {
                summary: {
                    total_rules: parseInt($('#totalRulesCount').text()) || 0,
                    active_rules: parseInt($('#activeRulesCount').text()) || 0,
                    rooms_impacted: 0,
                    avg_impact: 0
                },
                top_rules: []
            };
            
            populateDetailedAnalytics(placeholderData);
            
            // Show placeholder message
            $('#topRulesTable').html(`
                <tr>
                    <td colspan="6" class="text-center text-info py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p class="mb-0">Analytics data will be available once you start creating and applying pricing rules.</p>
                        <small class="text-muted">This is a preview of the analytics dashboard.</small>
                    </td>
                </tr>
            `);
        }
        
        // Populate detailed analytics
        function populateDetailedAnalytics(data) {
            // Update summary cards
            $('#detailTotalRules').text(data.summary?.total_rules || 0);
            $('#detailActiveRules').text(data.summary?.active_rules || 0);
            $('#detailRoomsImpacted').text((data.summary?.rooms_impacted || 0).toLocaleString());
            $('#detailAvgImpact').text(`${(data.summary?.avg_impact || 0).toFixed(1)}%`);
            
            // Update top rules table
            if (data.top_rules && data.top_rules.length > 0) {
                let tableHtml = '';
                data.top_rules.forEach(rule => {
                    const statusBadge = rule.is_active 
                        ? '<span class="badge badge-success">Active</span>' 
                        : '<span class="badge badge-secondary">Inactive</span>';
                    
                    tableHtml += `
                        <tr>
                            <td><strong>${rule.name}</strong></td>
                            <td><span class="badge badge-info">${rule.rule_type.replace('_', ' ')}</span></td>
                            <td>${(rule.rooms_affected || 0).toLocaleString()}</td>
                            <td>${rule.avg_impact >= 0 ? '+' : ''}${(rule.avg_impact || 0).toFixed(1)}%</td>
                            <td>$${(rule.estimated_revenue || 0).toLocaleString()}</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                });
                $('#topRulesTable').html(tableHtml);
            } else {
                $('#topRulesTable').html('<tr><td colspan="6" class="text-center text-muted">No analytics data available yet</td></tr>');
            }
        }
        
        // Export analytics function
        function exportAnalytics() {
            toastr.info('Analytics export functionality would be implemented here');
        }
        
        // ===== QUICK CREATE RULE FUNCTIONALITY =====
        
        // Handle adjustment type change for unit display
        $(document).on('change', '#quickAdjustmentType', function() {
            const adjustmentType = $(this).val();
            let unitText = '';
            let placeholder = '';
            
            switch(adjustmentType) {
                case 'percentage':
                    unitText = '%';
                    placeholder = 'e.g., 20 for +20%';
                    break;
                case 'fixed':
                    unitText = '$';
                    placeholder = 'e.g., 50 for +$50';
                    break;
                case 'multiply':
                    unitText = '×';
                    placeholder = 'e.g., 1.5 for ×1.5';
                    break;
                default:
                    unitText = '';
                    placeholder = '';
            }
            
            $('#adjustmentUnitDisplay').text(unitText);
            $('#quickAdjustmentValue').attr('placeholder', placeholder);
        });
        
        // Handle quick create rule form submission
        $(document).on('submit', '#quickCreateRuleForm', function(e) {
            e.preventDefault();
            
            // Validate form first
            if (!validateQuickRuleForm()) {
                return false;
            }
            
            const formData = $(this).serializeArray();
            const ruleData = {};
            formData.forEach(item => {
                ruleData[item.name] = item.value;
            });
            
            // Map rule_name to name for backend compatibility
            if (ruleData.rule_name) {
                ruleData.name = ruleData.rule_name;
                delete ruleData.rule_name;
            }
            
            // Handle checkbox - convert to integer (0 or 1) instead of boolean
            ruleData.is_active = $('#quickRuleActive').is(':checked') ? 1 : 0;
            
            createPricingRule(ruleData);
        });
        
        // Create pricing rule via AJAX
        function createPricingRule(ruleData) {
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.store") }}',
                method: 'POST',
                data: Object.assign(ruleData, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#quickCreateRuleForm button[type="submit"]').prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#quickCreateRuleForm')[0].reset();
                        $('#adjustmentUnitDisplay').text('%');
                        loadPricingRulesData();
                        
                        // Rules are now automatically applied by the backend
                        if (ruleData.is_active === 1) {
                            toastr.info('Pricing rule is being applied to room rates automatically...');
                        }
                    } else {
                        toastr.error(response.message || 'Error creating pricing rule');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error creating pricing rule');
                },
                complete: function() {
                    $('#quickCreateRuleForm button[type="submit"]').prop('disabled', false)
                        .html('<i class="fas fa-plus mr-1"></i> Create Pricing Rule');
                }
            });
        }
        
        // ===== APPLY PRICING RULES FUNCTIONALITY =====
        
        // Apply pricing rules to room rates
        $(document).on('click', '#applyPricingRulesBtn', function() {
            if (confirm('Apply all active pricing rules to current room rates? This will recalculate rates based on your rules.')) {
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.room-rates.apply-pricing-rules") }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Applying...');
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            // Refresh the rates in other tabs if needed
                        } else {
                            toastr.error(response.message || 'Error applying pricing rules');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error applying pricing rules');
                    },
                    complete: function() {
                        $('#applyPricingRulesBtn').prop('disabled', false)
                            .html('<i class="fas fa-magic me-1"></i>Apply Rules Now');
                    }
                });
            }
        });
        
        // Preview pricing rules impact
        $(document).on('click', '#previewRulesBtn', function() {
            $.ajax({
                url: '{{ route("b2b.hotel-provider.room-rates.preview-pricing-rules") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Loading...');
                },
                success: function(response) {
                    if (response.success) {
                        showPreviewModal(response.data);
                    } else {
                        toastr.error(response.message || 'Error previewing pricing rules');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error previewing pricing rules');
                },
                complete: function() {
                    $('#previewRulesBtn').prop('disabled', false)
                        .html('<i class="fas fa-eye me-1"></i>Preview Impact');
                }
            });
        });
        
        // Show preview modal
        function showPreviewModal(previewData) {
            // Create and show a modal with preview data
            // This can be implemented based on the preview data structure
            toastr.info('Preview functionality coming soon!');
        }
        
        // Apply pricing rules automatically without confirmation
        function applyPricingRulesAutomatically(ruleName) {
            $.ajax({
                url: '{{ route("b2b.hotel-provider.room-rates.apply-pricing-rules") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(`✅ Pricing rule "${ruleName}" applied successfully! Calendar rates updated.`);
                        
                        // Refresh calendar if it's currently active
                        if ($('#calendar-tab').hasClass('active')) {
                            const roomId = $('#calendarRoomSelect').val();
                            if (roomId) {
                                loadCalendarRates(roomId);
                            }
                        }
                        
                        // Also refresh current rates tab by reloading the page section
                        // This ensures all rate displays are updated
                        setTimeout(() => {
                            $('.statistics-card').each(function() {
                                $(this).addClass('border-success').delay(2000).queue(function() {
                                    $(this).removeClass('border-success').dequeue();
                                });
                            });
                        }, 100);
                    } else {
                        toastr.warning(`Rule "${ruleName}" created but couldn't apply automatically: ${response.message || 'Unknown error'}`);
                    }
                },
                error: function(xhr) {
                    console.warn('Auto-apply pricing rules failed:', xhr);
                    toastr.warning(`Rule "${ruleName}" created but couldn't apply automatically. Use "Apply Rules Now" button to apply manually.`);
                }
            });
        }
        
        // ===== CREATE PRICING RULE MODAL FUNCTIONALITY =====
        
        // Handle adjustment type change in create modal
        $(document).on('change', '#modalAdjustmentType', function() {
            const adjustmentType = $(this).val();
            let unitText = '';
            let placeholder = '';
            
            switch(adjustmentType) {
                case 'percentage':
                    unitText = '%';
                    placeholder = 'e.g., 20 for 20%';
                    break;
                case 'fixed':
                    unitText = '$';
                    placeholder = 'e.g., 50 for $50';
                    break;
                case 'multiply':
                    unitText = '×';
                    placeholder = 'e.g., 1.5 for ×1.5';
                    break;
                default:
                    unitText = '';
                    placeholder = '';
            }
            
            $('#modalAdjustmentUnit').text(unitText);
            $('#modalAdjustmentValue').attr('placeholder', placeholder);
            updateAdjustmentPreview();
        });
        
        // Handle adjustment value and direction changes for preview
        $(document).on('input change', '#modalAdjustmentValue, input[name="adjustment_direction"]', function() {
            updateAdjustmentPreview();
        });
        
        // Update adjustment preview
        function updateAdjustmentPreview() {
            const adjustmentType = $('#modalAdjustmentType').val();
            const adjustmentValue = parseFloat($('#modalAdjustmentValue').val());
            const adjustmentDirection = $('input[name="adjustment_direction"]:checked').val();
            
            if (adjustmentType && !isNaN(adjustmentValue) && adjustmentDirection) {
                let previewText = '';
                let exampleBasePrice = 100;
                let newPrice = exampleBasePrice;
                
                switch(adjustmentType) {
                    case 'percentage':
                        if (adjustmentDirection === 'increase') {
                            newPrice = exampleBasePrice * (1 + adjustmentValue / 100);
                            previewText = `Base price $${exampleBasePrice} → $${newPrice.toFixed(2)} (+${adjustmentValue}%)`;
                        } else {
                            newPrice = exampleBasePrice * (1 - adjustmentValue / 100);
                            previewText = `Base price $${exampleBasePrice} → $${newPrice.toFixed(2)} (-${adjustmentValue}%)`;
                        }
                        break;
                    case 'fixed':
                        if (adjustmentDirection === 'increase') {
                            newPrice = exampleBasePrice + adjustmentValue;
                            previewText = `Base price $${exampleBasePrice} → $${newPrice.toFixed(2)} (+$${adjustmentValue})`;
                        } else {
                            newPrice = exampleBasePrice - adjustmentValue;
                            previewText = `Base price $${exampleBasePrice} → $${newPrice.toFixed(2)} (-$${adjustmentValue})`;
                        }
                        break;
                    case 'multiply':
                        newPrice = exampleBasePrice * adjustmentValue;
                        previewText = `Base price $${exampleBasePrice} → $${newPrice.toFixed(2)} (×${adjustmentValue})`;
                        break;
                }
                
                $('#previewText').text(previewText);
                $('#adjustmentPreview').show();
            } else {
                $('#adjustmentPreview').hide();
            }
        }
        
        // Handle create pricing rule form submission
        $(document).on('submit', '#createPricingRuleForm', function(e) {
            e.preventDefault();
            
            const formData = $(this).serializeArray();
            const ruleData = {};
            formData.forEach(item => {
                ruleData[item.name] = item.value;
            });
            
            // Handle checkboxes - convert to integers for Laravel
            ruleData.is_active = $('#modalRuleActive').is(':checked') ? 1 : 0;
            ruleData.apply_to_existing = $('#modalApplyExisting').is(':checked') ? 1 : 0;
            
            // Process adjustment based on direction
            const adjustmentDirection = $('input[name="adjustment_direction"]:checked').val();
            if (adjustmentDirection === 'decrease' && ruleData.adjustment_type === 'percentage') {
                ruleData.adjustment_value = -Math.abs(parseFloat(ruleData.adjustment_value));
            } else if (adjustmentDirection === 'decrease' && ruleData.adjustment_type === 'fixed') {
                ruleData.adjustment_value = -Math.abs(parseFloat(ruleData.adjustment_value));
            }
            
            createFullPricingRule(ruleData);
        });
        
        // Create full pricing rule via AJAX
        function createFullPricingRule(ruleData) {
            $.ajax({
                url: '{{ route("b2b.hotel-provider.pricing-rules.store") }}',
                method: 'POST',
                data: Object.assign(ruleData, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#createPricingRuleForm button[type="submit"]').prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating Rule...');
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#createPricingRuleModal').modal('hide');
                        $('#createPricingRuleForm')[0].reset();
                        $('#modalAdjustmentUnit').text('%');
                        $('#adjustmentPreview').hide();
                        loadPricingRulesData();
                        
                        // Rules are now automatically applied by the backend
                        if (ruleData.is_active === 1) {
                            toastr.info('Pricing rule is being applied to room rates automatically...');
                        }
                    } else {
                        toastr.error(response.message || 'Error creating pricing rule');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error creating pricing rule');
                },
                complete: function() {
                    $('#createPricingRuleForm button[type="submit"]').prop('disabled', false)
                        .html('<i class="fas fa-plus mr-1"></i>Create Pricing Rule');
                }
            });
        }
        
        // Reset form when modal is closed
        $(document).on('hidden.bs.modal', '#createPricingRuleModal', function() {
            $('#createPricingRuleForm')[0].reset();
            $('#modalAdjustmentUnit').text('%');
            $('#adjustmentPreview').hide();
            $('input[name="adjustment_direction"][value="increase"]').prop('checked', true);
        });
        
        $(document).on('click', '#exportRulesBtn', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to export first');
                return;
            }
            
            exportPricingRules(selectedRules);
        });
        
        $(document).on('click', '#bulkDisableBtn', function(e) {
            e.preventDefault();
            const selectedRules = $('.rule-checkbox:checked').map(function() {
                return this.value;
            }).get();
            
            if (selectedRules.length === 0) {
                toastr.warning('Please select pricing rules to disable first');
                return;
            }
            
            if (confirm(`Are you sure you want to disable ${selectedRules.length} pricing rule(s)?`)) {
                $.ajax({
                    url: '{{ route("b2b.hotel-provider.pricing-rules.bulk-action") }}',
                    method: 'POST',
                    data: {
                        action: 'deactivate',
                        pricing_rule_ids: selectedRules,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadPricingRulesData();
                        } else {
                            toastr.error(response.message || 'Error disabling rules');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error disabling rules');
                    }
                });
            }
        });
        
        // Debounce function for search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
    </script>
    <script>
        // Global variables
        let calendar;
        
        // Toastr configuration
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };
        
        $(document).ready(function() {



            console.log('Modals found:', $('.modal').length);
            
            // Check if modals exist
            $('#setRateModal, #bulkPricingModal, #rateHistoryModal').each(function() {
                console.log('Modal found:', this.id, $(this).length);
            });
            
            // Initialize components
            initializeEventHandlers();
            
            // Initialize calendar with proper timing checks
            initializeCalendarWhenReady();
            
            // Set minimum dates to today
            $('input[type="date"]').attr('min', new Date().toISOString().split('T')[0]);
            

        });
        
        // Initialize all event handlers using jQuery event delegation
        function initializeEventHandlers() {
            // Hotel expansion toggle
            $(document).on('click', '.expand-hotel-btn', function() {
                const hotelId = $(this).data('hotel-id');
                const roomsDiv = $('#hotel-' + hotelId + '-rooms');
                const icon = $(this).find('i');
                
                roomsDiv.slideToggle(300, function() {
                    if (roomsDiv.is(':visible')) {
                        icon.removeClass('fa-expand-arrows-alt').addClass('fa-compress-arrows-alt');
                    } else {
                        icon.removeClass('fa-compress-arrows-alt').addClass('fa-expand-arrows-alt');
                    }
                });
            });
            
            // Toggle group details
            $(document).on('click', '.toggle-group-details', function() {
                const groupId = $(this).data('group-id'); // Use the clean MD5 hash ID
                const detailsDiv = $('#' + groupId);
                const button = $(this);
                const icon = button.find('i');
                
                detailsDiv.slideToggle(300, function() {
                    if (detailsDiv.is(':visible')) {
                        button.addClass('active');
                        icon.removeClass('fa-eye').addClass('fa-eye-slash');
                        button.attr('title', 'Hide Individual Rooms');
                    } else {
                        button.removeClass('active');
                        icon.removeClass('fa-eye-slash').addClass('fa-eye');
                        button.attr('title', 'View Individual Rooms');
                    }
                });
            });
            
            // Set group rate modal - use Bootstrap's show.bs.modal event
            $('#setGroupRateModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const groupKey = button.data('group-key');
                const roomType = button.data('room-type');
                const basePrice = button.data('base-price');
                const roomCount = button.data('room-count');
                
                $('#groupKey').val(groupKey);
                $('#groupRoomType').text(roomType);
                $('#groupModalRoomType').text(roomType);
                $('#groupModalRoomCount').text(roomCount);
                $('#groupModalBasePrice').text(basePrice);
                $('#groupRatePrice').val(basePrice);
                $('#affectedRoomsCount').text('all ' + roomCount);
                
                // Populate room checkboxes for selective application
                populateRoomCheckboxes(groupKey);
            }).on('shown.bs.modal', function() {
                // Focus the first input for better accessibility
                $('#groupRatePrice').focus();
            }).on('hidden.bs.modal', function() {
                // Clear any lingering focus issues
                $(document.activeElement).blur();
            });
            
            // Set individual room rate - use Bootstrap's show.bs.modal event
            $('#setRateModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const roomId = button.data('room-id');
                const roomName = button.data('room-name');
                const basePrice = button.data('base-price');
                
                $('#roomId').val(roomId);
                $('#roomName').val(roomName);
                $('#basePrice').val(basePrice);
                $('#ratePrice').val(basePrice);
            }).on('shown.bs.modal', function() {
                // Focus the rate price input for better accessibility
                $('#ratePrice').focus();
            }).on('hidden.bs.modal', function() {
                // Clear any lingering focus issues
                $(document.activeElement).blur();
            });
            
            // View rate history - use Bootstrap's show.bs.modal event
            $('#rateHistoryModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const roomId = button.data('room-id');
                const roomName = button.data('room-name');
                
                $('#historyRoomName').text(roomName);
                loadRateHistory(roomId);
            }).on('hidden.bs.modal', function() {
                // Clear any lingering focus issues
                $(document.activeElement).blur();
            });
            
            // Use base price buttons
            $(document).on('click', '#useBasePriceBtn', function() {
                const basePrice = $('#basePrice').val();
                $('#ratePrice').val(basePrice);
            });
            
            $(document).on('click', '#useGroupBasePriceBtn', function() {
                const basePrice = $('#groupModalBasePrice').text();
                $('#groupRatePrice').val(basePrice);
            });
            
            // Group rate application toggle
            $(document).on('change', '#applyToAllRooms', function() {
                if ($(this).is(':checked')) {
                    $('#roomSelectionArea').hide();
                    const roomCount = $('#groupModalRoomCount').text();
                    $('#affectedRoomsCount').text('all ' + roomCount);
                } else {
                    $('#roomSelectionArea').show();
                    updateAffectedRoomsCount();
                }
            });
            
            // Override existing rates toggle
            $(document).on('change', '#overrideExistingRates', function() {
                if ($(this).is(':checked')) {
                    $('#overrideWarning').show();
                } else {
                    $('#overrideWarning').hide();
                }
            });
            
            // Room checkbox selection
            $(document).on('change', '.room-checkbox', function() {
                updateAffectedRoomsCount();
                
                const checkboxItem = $(this).closest('.room-checkbox-item');
                if ($(this).is(':checked')) {
                    checkboxItem.addClass('selected');
                } else {
                    checkboxItem.removeClass('selected');
                }
            });
            
            // Quick action buttons
            $(document).on('click', '.group-history-btn', function() {
                const groupKey = $(this).data('group-key');
                const roomType = $(this).data('room-type');
                
                // For now, show a placeholder modal - can be enhanced later
                toastr.info(`Group rate history for ${roomType} - Feature coming soon!`);
                
                // TODO: Implement group rate history modal
                // showGroupRateHistoryModal(groupKey, roomType);
            });
            
            $(document).on('click', '.copy-rates-btn', function() {
                const groupKey = $(this).data('group-key');
                
                // For now, show a placeholder - can be enhanced later
                toastr.info('Copy rates functionality - Feature coming soon!');
                
                // TODO: Implement copy rates functionality
                // showCopyRatesModal(groupKey);
            });
            
            $(document).on('click', '.clear-rates-btn', function() {
                const groupKey = $(this).data('group-key');
                
                if (confirm('Are you sure you want to clear all rates for this room group? This action cannot be undone.')) {
                    $.ajax({
                        url: '/b2b/hotel-provider/rates/group-clear',
                        method: 'DELETE',
                        data: {
                            group_key: groupKey,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                toastr.error(response.message || 'Error clearing rates');
                            }
                        },
                        error: function(xhr) {
                            handleAjaxError(xhr, 'Error clearing group rates');
                        }
                    });
                }
            });
            
            // Bulk pricing modal - use Bootstrap's show.bs.modal event
            $('#bulkPricingModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const hotelId = button.data('hotel-id');
                if (hotelId) {
                    $('#bulkHotelSelect').val(hotelId);
                }
            }).on('shown.bs.modal', function() {
                // Focus the first available input for better accessibility
                $('#bulkHotelSelect').focus();
            }).on('hidden.bs.modal', function() {
                // Clear any lingering focus issues
                $(document.activeElement).blur();
            });
            
            
            // Test button - test modal functionality
            $(document).on('click', '#testAjaxBtn', function() {



                
                // Test opening modal directly
                $('#setRateModal').modal('show');
                
                toastr.info('Modal test - check console for details');
            });
            
            // Hotel selection for calendar
            $(document).on('change', '#calendarHotelSelect', function() {
                const hotelId = $(this).val();



                loadHotelRooms(hotelId);
            });
            
            // Load calendar rates
            $(document).on('click', '#loadCalendarRatesBtn', function() {
                const roomId = $('#calendarRoomSelect').val();
                if (roomId) {
                    loadCalendarRates(roomId);
                } else {
                    toastr.warning('Please select a hotel and room first');
                }
            });
            
            // Pricing method hints
            $(document).on('change', 'select[name="pricing_method"]', function() {
                const method = $(this).val();
                let hint = 'Enter the value based on selected pricing method';
                
                switch(method) {
                    case 'fixed':
                        hint = 'Enter the fixed price amount (e.g., 150.00)';
                        break;
                    case 'percentage':
                        hint = 'Enter percentage increase/decrease (e.g., 25 for 25% increase, -10 for 10% decrease)';
                        break;
                    case 'amount':
                        hint = 'Enter amount to add/subtract (e.g., 50 to add $50, -20 to subtract $20)';
                        break;
                }
                
                $('#valuePricingHint').text(hint);
            });
            
            // Dynamic pricing toggle (disabled for now)
            $(document).on('change', '#enableDynamicPricing', function() {
                if ($(this).is(':checked')) {
                    $('.dynamic-pricing-settings').slideDown();
                } else {
                    $('.dynamic-pricing-settings').slideUp();
                }
            });
            
            // ===== PRICING RULES TAB FUNCTIONALITY =====
            
            // Initialize pricing rules when tab is activated
            $(document).on('shown.bs.tab', 'a[href="#pricing-rules"]', function() {
                loadPricingRulesData();
            });
            
            // Quick create rule form adjustment type change
            $(document).on('change', '#quickAdjustmentType', function() {
                const type = $(this).val();
                const unitDisplay = $('#adjustmentUnitDisplay');
                
                switch(type) {
                    case 'percentage':
                        unitDisplay.text('%');
                        break;
                    case 'fixed':
                        unitDisplay.text('$');
                        break;
                    case 'multiply':
                        unitDisplay.text('x');
                        break;
                    default:
                        unitDisplay.text('');
                }
            });
            
            // Note: Quick create rule form submission is handled earlier in the file (lines 3703-3716)
            
            // Pricing rules management buttons
            $(document).on('click', '#refreshRulesBtn', function() {
                loadPricingRulesData();
            });
            
            $(document).on('click', '#applyPricingRulesBtn', function() {
                applyPricingRulesNow();
            });
            
            $(document).on('click', '#previewRulesBtn', function() {
                previewRulesImpact();
            });
            
            $(document).on('click', '#applyFiltersBtn', function() {
                loadPricingRulesData();
            });
            
            // Search functionality
            $(document).on('keyup', '#pricingRulesSearch', debounce(function() {
                loadPricingRulesData();
            }, 500));
            
            $(document).on('click', '#searchRulesBtn', function() {
                loadPricingRulesData();
            });
            
            // View toggle buttons
            $(document).on('click', '#viewListBtn, #viewGridBtn', function() {
                $('#viewListBtn, #viewGridBtn').removeClass('active');
                $(this).addClass('active');
                loadPricingRulesData();
            });
            
        }
        
        // Form submission handlers
        $(document).on('submit', '#setGroupRateForm', function(e) {
            e.preventDefault();
            
            const applyToAll = $('#applyToAllRooms').is(':checked');
            const selectedRooms = applyToAll ? [] : getSelectedRooms();
            
            const formData = {
                group_key: $('#groupKey').val(),
                start_date: $('#groupStartDate').val(),
                end_date: $('#groupEndDate').val(),
                price: $('#groupRatePrice').val(),
                rate_type: $('#groupRateType').val(),
                notes: $('#groupNotes').val(),
                apply_to_all: applyToAll ? 1 : 0,
                override_existing: $('#overrideExistingRates').is(':checked') ? 1 : 0,
                selected_rooms: selectedRooms,
                _token: '{{ csrf_token() }}'
            };
            
            // Validation
            if (!formData.group_key || !formData.start_date || !formData.end_date || !formData.price) {
                toastr.error('Please fill in all required fields');
                return false;
            }
            
            if (parseFloat(formData.price) < 0) {
                toastr.error('Price must be a positive number');
                return false;
            }
            
            if (new Date(formData.start_date) > new Date(formData.end_date)) {
                toastr.error('End date must be after start date');
                return false;
            }
            
            if (!applyToAll && selectedRooms.length === 0) {
                toastr.error('Please select at least one room or choose "Apply to all rooms"');
                return false;
            }
            
            submitGroupRateForm(formData);
        });
        
        $(document).on('submit', '#setRateForm', function(e) {
            e.preventDefault();
            
            const formData = {
                room_id: $('#roomId').val(),
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                price: $('#ratePrice').val(),
                notes: $('textarea[name="notes"]').val(),
                _token: '{{ csrf_token() }}'
            };
            
            // Validation
            if (!formData.room_id || !formData.start_date || !formData.end_date || !formData.price) {
                toastr.error('Please fill in all required fields');
                return false;
            }
            
            if (parseFloat(formData.price) < 0) {
                toastr.error('Price must be a positive number');
                return false;
            }
            
            if (new Date(formData.start_date) > new Date(formData.end_date)) {
                toastr.error('End date must be after start date');
                return false;
            }
            
            submitRateForm(formData);
        });
        
        $(document).on('submit', '#bulkPricingForm', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize() + '&_token={{ csrf_token() }}';
            submitBulkPricing(formData);
        });
        
        
        // Removed old seasonal rule form - now replaced with comprehensive pricing rules system
        
        // Utility Functions for Group Management
        function createSafeId(groupKey) {
            // Create a safe ID from group key by removing special characters
            return groupKey.replace(/[|.]/g, '_').replace(/[^a-zA-Z0-9_-]/g, '');
        }
        function populateRoomCheckboxes(groupKey) {
            const roomCheckboxes = $('#roomCheckboxes');
            roomCheckboxes.html('<div class="col-12"><p class="text-info">Loading room selection...</p></div>');
            
            $.ajax({
                url: '/b2b/hotel-provider/rates/group-rooms',
                method: 'GET',
                data: { group_key: groupKey },
                success: function(response) {
                    if (response.success && response.data) {
                        let checkboxHtml = '';
                        response.data.forEach(function(room) {
                            checkboxHtml += `
                                <div class="col-md-6">
                                    <div class="room-checkbox-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input room-checkbox" 
                                                   id="room_${room.id}" value="${room.id}" checked>
                                            <label class="custom-control-label" for="room_${room.id}">
                                                <strong>${room.room_number}</strong>
                                                ${room.name ? '<br><small class="text-muted">' + room.name + '</small>' : ''}
                                                <br><small class="text-info">${room.hotel_name}</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        roomCheckboxes.html(checkboxHtml);
                        updateAffectedRoomsCount();
                    } else {
                        roomCheckboxes.html('<div class="col-12"><p class="text-warning">No rooms found for this group.</p></div>');
                    }
                },
                error: function(xhr) {
                    roomCheckboxes.html('<div class="col-12"><p class="text-danger">Error loading rooms. Please try again.</p></div>');
                    handleAjaxError(xhr, 'Error loading rooms for selection');
                }
            });
        }
        
        function getSelectedRooms() {
            const selected = [];
            $('.room-checkbox:checked').each(function() {
                selected.push($(this).val());
            });
            return selected;
        }
        
        function updateAffectedRoomsCount() {
            const selectedCount = $('.room-checkbox:checked').length;
            $('#affectedRoomsCount').text(selectedCount + ' room' + (selectedCount !== 1 ? 's' : ''));
        }
        
        function submitGroupRateForm(formData) {
            const submitBtn = $('#setGroupRateForm button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Applying Group Rate...').prop('disabled', true);
            
            $.ajax({
                url: '/b2b/hotel-provider/rates/group-store',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Handle focus properly before hiding modal
                        const modal = $('#setGroupRateModal');
                        modal.one('hidden.bs.modal', function() {
                            // Focus management after modal is fully closed
                            $(document.activeElement).blur();
                            
                            // Update UI to reflect new rates
                            updateGroupRateDisplay(formData.group_key, response.data.affected_rooms);
                        });
                        
                        // Reset form before hiding
                        $('#setGroupRateForm')[0].reset();
                        $('#applyToAllRooms').prop('checked', true);
                        $('#overrideExistingRates').prop('checked', false);
                        $('#roomSelectionArea').hide();
                        
                        toastr.success(response.message);
                        modal.modal('hide');
                    } else {
                        toastr.error(response.message || 'Error applying group rate');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error applying group rate');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        }
        
        function updateGroupRateDisplay(groupKey, updatedRooms) {
            // Update the UI to reflect the new group rates
            // Use attribute selector which is safer with special characters
            const groupCard = $(`.room-group-card`).filter(function() {
                return $(this).data('group-key') === groupKey;
            });
            
            if (groupCard.length > 0 && updatedRooms && updatedRooms.length > 0) {
                // Update the group rate status badge
                const firstRoom = updatedRooms[0];
                const allSamePrice = updatedRooms.every(room => parseFloat(room.final_price) === parseFloat(firstRoom.final_price));
                
                const rateStatusHtml = allSamePrice ? 
                    `<span class="badge badge-success badge-lg">
                        <i class="fas fa-check-circle mr-1"></i>
                        $${parseFloat(firstRoom.final_price).toFixed(2)} (All ${updatedRooms.length} rooms)
                    </span>` :
                    `<span class="badge badge-warning badge-lg">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Mixed Rates (${updatedRooms.length} rooms updated)
                    </span>`;
                
                groupCard.find('.badge-lg').first().replaceWith(rateStatusHtml);
                
                // Update individual room rates in the expanded view if visible
                const expandedView = groupCard.find('.individual-rooms-details');
                if (expandedView.is(':visible')) {
                    updatedRooms.forEach(function(room) {
                        const roomRateCell = $(`#individual-rate-${room.id}`);
                        if (roomRateCell.length > 0) {
                            roomRateCell.html(`
                                <span class="badge badge-success">
                                    $${parseFloat(room.final_price).toFixed(2)}
                                </span>
                            `);
                        }
                    });
                }
                
                // Add a subtle highlight effect
                groupCard.addClass('border-success');
                setTimeout(function() {
                    groupCard.removeClass('border-success');
                }, 3000);
            }
        }
        
        // AJAX Functions
        function submitRateForm(formData) {
            const submitBtn = $('#setRateForm button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Setting Rate...').prop('disabled', true);
            
            $.ajax({
                url: '/b2b/hotel-provider/rates',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Handle focus properly before hiding modal
                        const modal = $('#setRateModal');
                        modal.one('hidden.bs.modal', function() {
                            // Focus management after modal is fully closed
                            $(document.activeElement).blur();
                            
                            // Update the current rate display
                            updateCurrentRateDisplay(formData.room_id, formData.price, formData.start_date, formData.notes);
                        });
                        
                        // Reset form before hiding
                        $('#setRateForm')[0].reset();
                        
                        toastr.success(response.message);
                        modal.modal('hide');
                    } else {
                        toastr.error(response.message || 'Error setting rate');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error setting rate');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        }
        
        function submitBulkPricing(formData) {
            const submitBtn = $('#bulkPricingForm button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Applying...').prop('disabled', true);
            
            $.ajax({
                url: '/b2b/hotel-provider/rates/group-store',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Handle focus properly before hiding modal
                        const modal = $('#bulkPricingModal');
                        modal.one('hidden.bs.modal', function() {
                            // Focus management after modal is fully closed
                            $(document.activeElement).blur();
                        });
                        
                        toastr.success(response.message);
                        modal.modal('hide');
                        
                        // Reload the page to show updated rates
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message || 'Error applying bulk pricing');
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr, 'Error applying bulk pricing');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        }
        
        
        function loadHotelRooms(hotelId) {
            if (!hotelId) {
                $('#calendarRoomSelect').html('<option value="">Select Room (Choose Hotel First)</option>');
                return;
            }
            



            
            $('#calendarRoomSelect').html('<option value="">Loading rooms...</option>');
            
            // Use the correct route that expects hotel ID in URL path
            $.ajax({
                url: `/b2b/hotel-provider/hotels/${hotelId}/rooms`,
                method: 'GET',
                cache: false, // Prevent caching
                data: {
                    '_t': Date.now() // Cache busting parameter
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {


                    console.log('Response data length:', (response.data || response).length);
                    
                    let options = '<option value="">Select Room</option>';
                    
                    // Check if response has data property (for API responses) or is direct array
                    const rooms = response.data || response;

                    
                    if (Array.isArray(rooms) && rooms.length > 0) {
                        // Group rooms by category/type
                        const groupedRooms = {};
                        
                        rooms.forEach(function(room) {
                            const category = room.category_name || room.category || 'Other';
                            if (!groupedRooms[category]) {
                                groupedRooms[category] = [];
                            }
                            groupedRooms[category].push(room);
                        });
                        
                        // Create grouped options with enhanced organization
                        const categoryKeys = Object.keys(groupedRooms).sort();
                        
                        categoryKeys.forEach(function(category) {
                            const categoryRooms = groupedRooms[category];
                            
                            // If there are many categories, group by bed type as well
                            if (categoryKeys.length > 6 && categoryRooms.length > 8) {
                                // Sub-group by bed type within category
                                const bedTypeGroups = {};
                                categoryRooms.forEach(function(room) {
                                    const bedType = room.bed_type || 'standard';
                                    if (!bedTypeGroups[bedType]) {
                                        bedTypeGroups[bedType] = [];
                                    }
                                    bedTypeGroups[bedType].push(room);
                                });
                                
                                const totalCategoryRooms = categoryRooms.length;
                                options += `<optgroup label="${category} (${totalCategoryRooms} rooms)">`;
                                Object.keys(bedTypeGroups).sort().forEach(function(bedType) {
                                    const bedLabel = getBedTypeLabel(bedType);
                                    const bedCount = bedTypeGroups[bedType].length;
                                    options += `<option disabled style="font-style: italic; color: #666;">── ${bedLabel} (${bedCount}) ──</option>`;
                                    
                                    bedTypeGroups[bedType].sort(function(a, b) {
                                        return a.room_number.localeCompare(b.room_number, undefined, {numeric: true});
                                    }).forEach(function(room) {
                                        const roomLabel = room.name ? 
                                            `Room ${room.room_number} - ${room.name} ($${room.base_price})` : 
                                            `Room ${room.room_number} ($${room.base_price})`;
                                        options += `<option value="${room.id}">&nbsp;&nbsp;${roomLabel}</option>`;
                                    });
                                });
                                options += `</optgroup>`;
                            } else {
                                // Simple category grouping
                                const totalCategoryRooms = categoryRooms.length;
                                options += `<optgroup label="${category} (${totalCategoryRooms} rooms)">`;
                                
                                categoryRooms.sort(function(a, b) {
                                    return a.room_number.localeCompare(b.room_number, undefined, {numeric: true});
                                }).forEach(function(room) {
                                    const roomLabel = room.name ? 
                                        `Room ${room.room_number} - ${room.name} ($${room.base_price})` : 
                                        `Room ${room.room_number} ($${room.base_price})`;
                                    options += `<option value="${room.id}">${roomLabel}</option>`;
                                });
                                
                                options += `</optgroup>`;
                            }
                        });
                    } else {
                        options = '<option value="">No rooms available</option>';
                    }
                    
                    $('#calendarRoomSelect').html(options);
                },
                error: function(xhr) {

                    console.error('Error loading hotel rooms for hotel', hotelId, ':', xhr);



                    $('#calendarRoomSelect').html('<option value="">Error loading rooms</option>');
                    handleAjaxError(xhr, 'Error loading rooms');
                }
            });
        }
        
        function loadRateHistory(roomId) {
            $('#rateHistoryContent').html(`
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading rate history...</p>
                </div>
            `);
            
            $.ajax({
                url: '/b2b/hotel-provider/rates/history',
                method: 'GET',
                data: { room_id: roomId, limit: 50 },
                success: function(response) {
                    if (response.success && response.data) {
                        const room = response.data.room;
                        const history = response.data.history;
                        
                        let tableHtml = `
                            <div class="mb-3">
                                <h6>Room: ${room.room_number}</h6>
                                <p class="text-muted">Base Price: $${parseFloat(room.base_price).toFixed(2)}</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Price</th>
                                            <th>Notes</th>
                                            <th>Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        if (history.length > 0) {
                            history.forEach(function(rate) {
                                const priceClass = parseFloat(rate.price) > parseFloat(room.base_price) ? 'badge-success' : 'badge-info';
                                tableHtml += `
                                    <tr>
                                        <td>${rate.date}</td>
                                        <td><span class="badge ${priceClass}">$${parseFloat(rate.price).toFixed(2)}</span></td>
                                        <td>${rate.notes || '-'}</td>
                                        <td><small>${rate.updated_at}</small></td>
                                    </tr>
                                `;
                            });
                        } else {
                            tableHtml += `
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No rate history found for this room
                                    </td>
                                </tr>
                            `;
                        }
                        
                        tableHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                        
                        $('#rateHistoryContent').html(tableHtml);
                    } else {
                        $('#rateHistoryContent').html('<p class="text-danger">Error loading rate history</p>');
                    }
                },
                error: function(xhr) {
                    $('#rateHistoryContent').html('<p class="text-danger">Error loading rate history</p>');
                    handleAjaxError(xhr, 'Error loading rate history');
                }
            });
        }
        
        
        function loadCalendarRates(roomId) {
            if (calendar) {
                calendar.removeAllEventSources();
                
                $.ajax({
                    url: '/b2b/hotel-provider/rates/calendar',
                    method: 'GET',
                    data: { room_id: roomId },
                    success: function(response) {
                        if (response.success && response.data && Array.isArray(response.data)) {
                            const rates = response.data;
                            if (rates.length > 0) {
                                const events = rates.map(function(rate) {
                                    return {
                                        title: '$' + parseFloat(rate.price).toFixed(2),
                                        start: rate.date,
                                        backgroundColor: getRateColor(rate.price),
                                        borderColor: getRateColor(rate.price),
                                        extendedProps: {
                                            notes: rate.notes,
                                            price: rate.price
                                        }
                                    };
                                });
                                
                                calendar.addEventSource(events);
                                toastr.success(`Loaded ${rates.length} rate entries`);
                            } else {
                                toastr.info('No rates found for this room');
                            }
                        } else {
                            toastr.info('No rates found for this room');
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Error loading calendar rates');
                    }
                });
            }
        }
        
        function initializeCalendarWhenReady() {
            let attempts = 0;
            const maxAttempts = 10;
            
            function tryInitialize() {
                attempts++;

                
                if (typeof FullCalendar !== 'undefined') {

                    initializeCalendar();
                } else if (attempts < maxAttempts) {

                    setTimeout(tryInitialize, 200);
                } else {
                    console.error('FullCalendar failed to load after all attempts');
                    toastr.error('Calendar failed to load. Please refresh the page to try again.');
                    // Hide the calendar tab if it failed to load
                    $('#calendar-tab').addClass('disabled').off('click');
                }
            }
            
            tryInitialize();
        }
        
        function initializeCalendar() {


            
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar is not loaded!');
                toastr.error('Calendar library failed to load. Please refresh the page.');
                return;
            }
            
            const calendarEl = document.getElementById('rateCalendar');
            
            if (calendarEl) {
                try {
                    calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 600,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listMonth'
                    },
                    events: [],
                    eventClick: function(info) {
                        const event = info.event;
                        const price = event.extendedProps.price;
                        const notes = event.extendedProps.notes || 'No notes';
                        const date = event.start.toLocaleDateString();
                        
                        toastr.info(`
                            <strong>Date:</strong> ${date}<br>
                            <strong>Price:</strong> $${price}<br>
                            <strong>Notes:</strong> ${notes}
                        `, 'Rate Details', {timeOut: 0, closeButton: true});
                    },
                    dateClick: function(info) {
                        const roomId = $('#calendarRoomSelect').val();
                        if (roomId) {
                            $('#startDate').val(info.dateStr);
                            $('#endDate').val(info.dateStr);
                            
                            // Get room details for the modal
                            const roomOption = $('#calendarRoomSelect option:selected');
                            const roomText = roomOption.text();
                            const roomName = roomText.split(' (')[0];
                            const basePrice = roomText.match(/\\$([0-9.]+)/);
                            
                            $('#roomId').val(roomId);
                            $('#roomName').val(roomName);
                            $('#basePrice').val(basePrice ? basePrice[1] : '0');
                            $('#ratePrice').val(basePrice ? basePrice[1] : '0');
                            
                            $('#setRateModal').modal('show');
                        } else {
                            toastr.warning('Please select a room first');
                        }
                    }
                });
                
                calendar.render();

                } catch (error) {
                    console.error('Error initializing calendar:', error);
                    toastr.error('Failed to initialize calendar: ' + error.message);
                }
            } else {
                console.error('Calendar element not found');
            }
        }
        
        // Utility functions
        function getBedTypeLabel(bedType) {
            const bedTypes = {
                'single': 'Single Bed',
                'twin': 'Twin Beds', 
                'double': 'Double Bed',
                'queen': 'Queen Bed',
                'king': 'King Bed',
                'sofa_bed': 'Sofa Bed',
                'bunk_bed': 'Bunk Bed'
            };
            return bedTypes[bedType] || bedType.replace('_', ' ').toUpperCase();
        }
        
        function getRateColor(price) {
            const numPrice = parseFloat(price);
            if (numPrice >= 200) return '#dc3545'; // High rate - red
            if (numPrice >= 100) return '#ffc107'; // Medium rate - yellow  
            return '#28a745'; // Low rate - green
        }
        
        function updateCurrentRateDisplay(roomId, price, date, notes) {
            const rateCell = $('#current-rate-' + roomId);
            const notesText = notes ? `<br><small class="text-info">${notes}</small>` : '';
            
            rateCell.html(`
                <span class="badge badge-success">$${parseFloat(price).toFixed(2)}</span>
                <br><small class="text-muted">${new Date(date).toLocaleDateString()}</small>
                ${notesText}
            `);
        }
        
        function testAjaxConnection() {
            toastr.info('Testing AJAX connection...');
            
            $.ajax({
                url: '/b2b/hotel-provider/rates',
                method: 'GET',
                success: function(response) {
                    toastr.success('AJAX connection successful!');
                },
                error: function(xhr) {
                    toastr.error('AJAX connection failed: ' + xhr.status);
                }
            });
        }
        
        function handleAjaxError(xhr, defaultMessage) {
            let errorMessage = defaultMessage || 'An error occurred';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
            }
            
            toastr.error(errorMessage);
        }
        
        // Session messages
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
        
        @if(session('info'))
            toastr.info('{{ session('info') }}');
        @endif
        
        @if(session('warning'))
            toastr.warning('{{ session('warning') }}');
        @endif
    </script>
@stop
