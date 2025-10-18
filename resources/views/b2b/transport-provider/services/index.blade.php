@extends('layouts.b2b')

@section('title', 'Transport Services Management')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-title', 'Transport Services Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Services</li>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-2">Your Transport Services</h4>
            <p class="text-muted">Manage your transport services, routes, and pricing configurations.</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-2"></i>Add New Service
            </a>
        </div>
    </div>

    @if($services->count() > 0)
        <div class="row">
            @foreach($services as $service)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 service-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $service->service_name }}</h6>
                            @if($service->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="service-info">
                                <p class="mb-2">
                                    <i class="fas fa-bus text-primary mr-2"></i>
                                    <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $service->transport_type)) }}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-users text-info mr-2"></i>
                                    <strong>Max Passengers:</strong> {{ $service->max_passengers ?? 'N/A' }}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-dollar-sign text-success mr-2"></i>
                                    <strong>Base Price:</strong> SAR {{ number_format($service->price ?? 0, 2) }}
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-route text-warning mr-2"></i>
                                    <strong>Routes:</strong> {{ count($service->routes ?? []) }} routes
                                </p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('b2b.transport-provider.show', $service) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('b2b.transport-provider.edit', $service) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-outline-{{ $service->is_active ? 'warning' : 'success' }} btn-sm toggle-status-btn" 
                                        data-service-id="{{ $service->id }}">
                                    <i class="fas fa-{{ $service->is_active ? 'pause' : 'play' }}"></i>
                                    {{ $service->is_active ? 'Pause' : 'Activate' }}
                                </button>
                            </div>
                            <div class="btn-group w-100 mt-2" role="group">
                                <a href="{{ route('b2b.transport-provider.rates') }}?service={{ $service->id }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-dollar-sign mr-1"></i>Manage Rates
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $services->count() }}</h3>
                        <p class="mb-0">Total Services</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $services->where('is_active', true)->count() }}</h3>
                        <p class="mb-0">Active Services</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>{{ $services->sum(function($s) { return count($s->routes ?? []); }) }}</h3>
                        <p class="mb-0">Total Routes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>{{ $services->where('is_active', false)->count() }}</h3>
                        <p class="mb-0">Inactive Services</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-bus fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Transport Services Yet</h4>
            <p class="text-muted mb-4">Get started by creating your first transport service to manage routes and pricing.</p>
            <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-success btn-lg">
                <i class="fas fa-plus mr-2"></i>Create Your First Service
            </a>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .service-card {
            transition: transform 0.2s;
        }
        .service-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .service-info p {
            font-size: 0.9rem;
        }
    </style>
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Toggle service status
    $('.toggle-status-btn').on('click', function() {
        var btn = $(this);
        var serviceId = btn.data('service-id');
        var isActive = btn.hasClass('btn-outline-warning');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: '/b2b/transport-provider/services/' + serviceId + '/toggle-status',
            method: 'PATCH',
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to show updated status
                } else {
                    alert('Failed to toggle service status');
                }
            },
            error: function() {
                alert('An error occurred while updating service status');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
