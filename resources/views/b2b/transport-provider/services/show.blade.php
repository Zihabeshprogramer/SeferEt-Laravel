@extends('layouts.b2b')

@section('title', 'Transport Service Details')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-bus text-info mr-2"></i>
                {{ $transportService->service_name }}
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Dashboard
            </a>
            <a href="{{ route('b2b.transport-provider.edit', $transportService) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i>
                Edit
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{-- Service Details --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Service Information
                    </h3>
                    <div class="card-tools">
                        @if($transportService->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Service Name:</strong>
                            <p>{{ $transportService->service_name }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Transport Type:</strong>
                            <p>
                                <span class="badge badge-primary">
                                    {{ ucfirst(str_replace('_', ' ', $transportService->transport_type)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <strong>Route Type:</strong>
                            <p>
                                <span class="badge badge-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $transportService->route_type)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Maximum Passengers:</strong>
                            <p>
                                <span class="badge badge-info">{{ $transportService->max_passengers }} passengers</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Price (SAR):</strong>
                            <p>
                                <span class="badge badge-success">SAR {{ number_format($transportService->price, 2) }}</span>
                            </p>
                        </div>
                    </div>
                    
                    @if($transportService->specifications && isset($transportService->specifications['description']))
                        <div class="row">
                            <div class="col-12">
                                <strong>Vehicle Specifications:</strong>
                                <p>{{ $transportService->specifications['description'] }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($transportService->policies && isset($transportService->policies['general']))
                        <div class="row">
                            <div class="col-12">
                                <strong>Service Policies:</strong>
                                <p>{{ $transportService->policies['general'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Routes --}}
            @if($transportService->routes && count($transportService->routes) > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-route mr-2"></i>
                            Available Routes
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transportService->routes as $route)
                                        <tr>
                                            <td>{{ $route['from'] ?? 'N/A' }}</td>
                                            <td>{{ $route['to'] ?? 'N/A' }}</td>
                                            <td>
                                                @if(isset($route['duration']))
                                                    {{ $route['duration'] }} minutes
                                                @else
                                                    <em class="text-muted">Not specified</em>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Locations --}}
            @if(($transportService->pickup_locations && count($transportService->pickup_locations) > 0) || 
                ($transportService->dropoff_locations && count($transportService->dropoff_locations) > 0))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Pickup & Dropoff Locations
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Pickup Locations:</strong>
                                @if($transportService->pickup_locations && count($transportService->pickup_locations) > 0)
                                    <ul class="list-unstyled">
                                        @foreach($transportService->pickup_locations as $location)
                                            <li><i class="fas fa-map-pin text-success mr-1"></i>{{ $location }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p><em class="text-muted">Not specified</em></p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Dropoff Locations:</strong>
                                @if($transportService->dropoff_locations && count($transportService->dropoff_locations) > 0)
                                    <ul class="list-unstyled">
                                        @foreach($transportService->dropoff_locations as $location)
                                            <li><i class="fas fa-map-pin text-danger mr-1"></i>{{ $location }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p><em class="text-muted">Not specified</em></p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Status Actions --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i>
                        Actions
                    </h3>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('b2b.transport-provider.toggle-status', $transportService) }}" 
                          method="POST" 
                          class="mb-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="btn {{ $transportService->is_active ? 'btn-warning' : 'btn-success' }} btn-block"
                                onclick="return confirm('Are you sure you want to {{ $transportService->is_active ? 'deactivate' : 'activate' }} this service?')">
                            <i class="fas {{ $transportService->is_active ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                            {{ $transportService->is_active ? 'Deactivate' : 'Activate' }} Service
                        </button>
                    </form>
                    
                    <a href="{{ route('b2b.transport-provider.edit', $transportService) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit mr-1"></i>
                        Edit Service
                    </a>
                    
                    <form action="{{ route('b2b.transport-provider.destroy', $transportService) }}" 
                          method="POST" 
                          class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger btn-block"
                                onclick="return confirm('Are you sure you want to delete this service? This action cannot be undone.')">
                            <i class="fas fa-trash mr-1"></i>
                            Delete Service
                        </button>
                    </form>
                </div>
            </div>
            
            {{-- Operating Hours --}}
            @if($transportService->operating_hours)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>
                            Operating Hours
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        @if(isset($transportService->operating_hours['is_24_7']) && $transportService->operating_hours['is_24_7'])
                            <p>
                                <span class="badge badge-success">
                                    <i class="fas fa-clock mr-1"></i>
                                    24/7 Service Available
                                </span>
                            </p>
                        @elseif(isset($transportService->operating_hours['start']) && isset($transportService->operating_hours['end']))
                            <p>
                                <strong>From:</strong> {{ $transportService->operating_hours['start'] }}<br>
                                <strong>To:</strong> {{ $transportService->operating_hours['end'] }}
                            </p>
                        @else
                            <p><em class="text-muted">Operating hours not specified</em></p>
                        @endif
                    </div>
                </div>
            @endif
            
            {{-- Contact Information --}}
            @if($transportService->contact_info)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-phone mr-2"></i>
                            Contact Information
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        @if(isset($transportService->contact_info['phone']))
                            <p>
                                <strong>Phone:</strong><br>
                                <a href="tel:{{ $transportService->contact_info['phone'] }}">
                                    {{ $transportService->contact_info['phone'] }}
                                </a>
                            </p>
                        @endif
                        
                        @if(isset($transportService->contact_info['email']))
                            <p>
                                <strong>Email:</strong><br>
                                <a href="mailto:{{ $transportService->contact_info['email'] }}">
                                    {{ $transportService->contact_info['email'] }}
                                </a>
                            </p>
                        @endif
                        
                        @if(!isset($transportService->contact_info['phone']) && !isset($transportService->contact_info['email']))
                            <p><em class="text-muted">Contact information not provided</em></p>
                        @endif
                    </div>
                </div>
            @endif
            
            {{-- Service Offers --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags mr-2"></i>
                        Service Offers
                    </h3>
                </div>
                
                <div class="card-body">
                    @if($transportService->offers && $transportService->offers->count() > 0)
                        <p><strong>{{ $transportService->offers->count() }}</strong> active offers</p>
                        <a href="#" class="btn btn-info btn-sm">Manage Offers</a>
                    @else
                        <p><em class="text-muted">No service offers created yet</em></p>
                        <a href="#" class="btn btn-primary btn-sm">Create Offer</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-tools .badge {
            font-size: 0.875rem;
        }
        .list-unstyled li {
            padding: 2px 0;
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
    </script>
@stop
