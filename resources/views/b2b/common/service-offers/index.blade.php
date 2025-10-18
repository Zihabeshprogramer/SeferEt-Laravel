@extends('layouts.b2b')

@section('title', 'Service Offers')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-tags text-primary mr-2"></i>
                Service Offers
            </h1>
            <p class="text-muted">Manage your service offers and pricing</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.service-offers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Create Service Offer
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_offers'] ?? 0 }}</h3>
                    <p>Total Offers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_offers'] ?? 0 }}</h3>
                    <p>Active Offers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['draft_offers'] ?? 0 }}</h3>
                    <p>Draft Offers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_bookings'] ?? 0 }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Offers Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Your Service Offers
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($offers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Service Offer</th>
                                <th>Service Type</th>
                                <th>Base Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                                <tr>
                                    <td>
                                        <strong>{{ $offer->name }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($offer->description, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($offer->service_type) }}
                                        </span>
                                        @if($offer->service)
                                            <br><small class="text-muted">
                                                {{ $offer->service->name ?? $offer->service->service_name ?? 'N/A' }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>${{ number_format($offer->base_price, 2) }}</strong>
                                        <small class="text-muted d-block">{{ $offer->currency }}</small>
                                        @if($offer->max_capacity)
                                            <small class="text-info">Max: {{ $offer->max_capacity }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $offer->status === 'active' ? 'success' : ($offer->status === 'draft' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($offer->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $offer->created_at->format('M d, Y') }}<br>
                                        <small class="text-muted">{{ $offer->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('b2b.service-offers.show', $offer) }}" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('b2b.service-offers.edit', $offer) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('b2b.service-offers.toggle-status', $offer) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $offer->status === 'active' ? 'btn-secondary' : 'btn-success' }}"
                                                        title="{{ $offer->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas {{ $offer->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete('{{ $offer->id }}', '{{ $offer->name }}')"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $offers->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Service Offers Yet</h4>
                    <p class="text-muted">Create your first service offer to start selling to package creators.</p>
                    <a href="{{ route('b2b.service-offers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Create First Service Offer
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Service Offer</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the service offer "<strong id="deleteOfferName"></strong>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box {
            border-radius: 0.5rem;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
    </style>
@stop

@section('js')
    <script>
        function confirmDelete(offerId, offerName) {
            $('#deleteOfferName').text(offerName);
            $('#deleteForm').attr('action', '/b2b/service-offers/' + offerId);
            $('#deleteModal').modal('show');
        }
        
        // Success messages
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@stop
