@extends('layouts.b2b')

@section('title', 'Flight Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-plane text-info mr-2"></i>
                Flight Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.travel-agent.flights.create') }}" class="btn btn-info">
                <i class="fas fa-plus mr-1"></i>
                Add New Flight
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_flights'] ?? 0 }}</h3>
                    <p>Total Flights</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plane"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_flights'] ?? 0 }}</h3>
                    <p>Active Flights</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['scheduled_flights'] ?? 0 }}</h3>
                    <p>Scheduled Flights</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_seats'] ?? 0 }}</h3>
                    <p>Total Seats</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chair"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Flights Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Your Flights
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($flights) && $flights->count() > 0)
                        <div class="table-responsive">
                            <table id="flightsTable" class="table table-bordered table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Flight Number</th>
                                        <th width="12%">Airline</th>
                                        <th width="15%">Route</th>
                                        <th width="15%">Departure</th>
                                        <th width="10%">Economy Price</th>
                                        <th width="8%">Seats</th>
                                        <th width="10%">Status</th>
                                        <th width="13%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($flights as $index => $flight)
                                        <tr id="flight-row-{{ $flight->id }}">
                                            <td>{{ $flights->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flight-icon mr-2">
                                                        <i class="fas fa-plane text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ $flight->flight_number }}</strong>
                                                        <small class="text-muted">ID: #{{ $flight->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">{{ $flight->airline }}</span>
                                                @if($flight->aircraft_type)
                                                    <br><small class="text-muted">{{ $flight->aircraft_type }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-primary badge-pill">
                                                    {{ $flight->departure_airport }} → {{ $flight->arrival_airport }}
                                                </span>
                                                <br><small class="text-muted">{{ $flight->formatted_duration ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $flight->departure_datetime->format('M d, Y') }}</strong>
                                                <br><small class="text-muted">{{ $flight->departure_datetime->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">
                                                    {{ number_format($flight->economy_price, 2) }} {{ $flight->currency }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info badge-pill">
                                                    {{ $flight->available_seats }}/{{ $flight->total_seats }}
                                                </span>
                                                <br><small class="text-muted">{{ number_format($flight->occupancy_rate, 1) }}% full</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'scheduled' => 'success',
                                                        'boarding' => 'warning',
                                                        'departed' => 'info',
                                                        'arrived' => 'secondary',
                                                        'cancelled' => 'danger',
                                                        'delayed' => 'warning'
                                                    ];
                                                    $statusColor = $statusColors[$flight->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $statusColor }} badge-pill">
                                                    {{ ucfirst($flight->status) }}
                                                </span>
                                                @if(!$flight->is_active)
                                                    <br><span class="badge badge-secondary badge-pill mt-1">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('b2b.travel-agent.flights.show', $flight->id) }}" 
                                                       class="btn btn-outline-info"
                                                       title="View Details"
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('b2b.travel-agent.flights.edit', $flight->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Flight"
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-{{ $flight->is_active ? 'secondary' : 'success' }} toggle-status-btn"
                                                            title="{{ $flight->is_active ? 'Deactivate' : 'Activate' }} Flight"
                                                            data-toggle="tooltip"
                                                            data-flight-id="{{ $flight->id }}"
                                                            data-flight-number="{{ $flight->flight_number }}">
                                                        <i class="fas {{ $flight->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-flight-btn"
                                                            title="Delete Flight"
                                                            data-toggle="tooltip"
                                                            data-flight-id="{{ $flight->id }}"
                                                            data-flight-number="{{ $flight->flight_number }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            {{ $flights->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Flights Added Yet</h4>
                            <p class="text-muted">Start by adding your first flight to include in travel packages.</p>
                            <a href="{{ route('b2b.travel-agent.flights.create') }}" class="btn btn-info">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Flight
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box {
            border-radius: 0.5rem;
            transition: transform 0.2s ease;
        }
        .small-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .flight-icon {
            font-size: 1.2em;
        }
        
        .badge-pill {
            font-size: 0.75rem;
        }
        
        .btn-group-sm > .btn {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
            border-radius: 0.2rem;
        }
        
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
    </style>
@stop

@section('js')
    <script>
        // Success messages
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@stop
