@extends('layouts.b2b')

@section('title', 'My Hotels')
@section('page-title', 'My Hotels')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Hotels</li>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_hotels'] }}</h3>
                    <p>Total Hotels</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_hotels'] }}</h3>
                    <p>Active Hotels</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_hotels'] }}</h3>
                    <p>Pending Approval</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Awaiting Admin Review <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total_rooms'] }}</h3>
                    <p>Total Rooms</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bed"></i>
                </div>
                <a href="{{ route('b2b.hotel-provider.rooms.index') }}" class="small-box-footer">
                    Manage Rooms <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Hotels Management -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-hotel mr-2"></i>
                        Hotels Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('b2b.hotel-provider.hotels.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>
                            Add New Hotel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Information Panel -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle mr-2"></i>Admin Approval Status</h6>
                                <ul class="mb-0 small">
                                    <li><span class="badge badge-warning">Pending</span> - Awaiting admin review</li>
                                    <li><span class="badge badge-success">Approved</span> - Admin has approved your hotel</li>
                                    <li><span class="badge badge-danger">Suspended/Rejected</span> - Admin action required</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-cog mr-2"></i>Overall Status</h6>
                                <ul class="mb-0 small">
                                    <li><span class="badge badge-success">Active</span> - Approved by admin and active</li>
                                    <li><span class="badge badge-warning">Awaiting Approval</span> - Pending admin review</li>
                                    <li><span class="badge badge-secondary">Inactive (Self)</span> - You deactivated the hotel</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    @if($hotels->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Hotel</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Star Rating</th>
                                        <th>Rooms</th>
                                        <th data-toggle="tooltip" title="Status set by admin after reviewing your hotel">
                                            Admin Approval
                                            <i class="fas fa-info-circle text-muted ml-1"></i>
                                        </th>
                                        <th data-toggle="tooltip" title="Combined status including admin approval and your activation">
                                            Overall Status
                                            <i class="fas fa-info-circle text-muted ml-1"></i>
                                        </th>
                                        <th>Distance to Haram</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hotels as $hotel)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($hotel->images && count($hotel->images) > 0)
                                                        <img src="{{ asset('storage/' . $hotel->images[0]) }}" 
                                                             alt="{{ $hotel->name }}" 
                                                             class="img-thumbnail me-3" 
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 60px; height: 60px;">
                                                            <i class="fas fa-hotel text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $hotel->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            Created {{ $hotel->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    {{ ucwords(str_replace('_', ' ', $hotel->type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                                {{ $hotel->city }}, {{ $hotel->country }}
                                                <br>
                                                <small class="text-muted">{{ $hotel->address }}</small>
                                            </td>
                                            <td>
                                                <div class="text-warning">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $hotel->star_rating ? '' : 'text-muted' }}"></i>
                                                    @endfor
                                                </div>
                                                <small class="text-muted">{{ $hotel->star_rating }}-Star</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $hotel->rooms->count() }} Rooms
                                                </span>
                                                <br>
                                                <small class="text-success">
                                                    {{ $hotel->rooms->where('is_available', true)->count() }} Available
                                                </small>
                                            </td>
                                            <td>
                                                <!-- Admin Approval Status -->
                                                @switch($hotel->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>Pending
                                                        </span>
                                                        @break
                                                    @case('active')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i>Approved
                                                        </span>
                                                        @break
                                                    @case('suspended')
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-ban mr-1"></i>Suspended
                                                        </span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times mr-1"></i>Rejected
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">
                                                            {{ ucfirst($hotel->status) }}
                                                        </span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <!-- Overall Status (Approval + Provider Control) -->
                                                @if($hotel->isAvailable())
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i>Active
                                                    </span>
                                                @elseif($hotel->status === 'pending')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock mr-1"></i>Awaiting Approval
                                                    </span>
                                                @elseif($hotel->status === 'suspended')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-ban mr-1"></i>Suspended by Admin
                                                    </span>
                                                @elseif($hotel->status === 'rejected')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times mr-1"></i>Rejected by Admin
                                                    </span>
                                                @elseif($hotel->status === 'active' && !$hotel->is_active)
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-pause mr-1"></i>Inactive (Self)
                                                    </span>
                                                    <br><small class="text-muted">You deactivated this hotel</small>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        {{ ucfirst($hotel->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($hotel->distance_to_haram)
                                                    <span class="text-primary">
                                                        <i class="fas fa-kaaba mr-1"></i>
                                                        {{ $hotel->distance_to_haram }} km
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('b2b.hotel-provider.hotels.show', $hotel) }}" 
                                                       class="btn btn-info btn-sm"
                                                       data-toggle="tooltip" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('b2b.hotel-provider.hotels.edit', $hotel) }}" 
                                                       class="btn btn-warning btn-sm"
                                                       data-toggle="tooltip" title="Edit Hotel">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($hotel->status === 'active')
                                                        <button type="button" 
                                                                class="btn btn-{{ $hotel->is_active ? 'secondary' : 'success' }} btn-sm"
                                                                data-toggle="tooltip" 
                                                                title="{{ $hotel->is_active ? 'Deactivate' : 'Activate' }} Hotel"
                                                                onclick="toggleHotelStatus({{ $hotel->id }}, {{ $hotel->is_active ? 'false' : 'true' }})">
                                                            <i class="fas fa-{{ $hotel->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-light btn-sm" disabled
                                                                data-toggle="tooltip" 
                                                                title="Hotel must be approved by admin before you can activate/deactivate it">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $hotels->firstItem() }} to {{ $hotels->lastItem() }} 
                                    of {{ $hotels->total() }} results
                                </small>
                            </div>
                            <div>
                                {{ $hotels->links() }}
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="fas fa-hotel text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No Hotels Yet</h4>
                            <p class="text-muted">You haven't added any hotels to your portfolio yet.</p>
                            <a href="{{ route('b2b.hotel-provider.hotels.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Hotel
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .small-box {
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
        }
        .badge {
            font-size: 0.75em;
        }
        .btn-group .btn {
            margin-right: 0;
        }
        .img-thumbnail {
            border-radius: 8px;
        }
        
        /* Status column styling */
        .table th[data-toggle="tooltip"] {
            cursor: help;
        }
        
        .badge {
            white-space: nowrap;
        }
        
        /* Make status columns more compact */
        .table td:nth-child(6),
        .table td:nth-child(7) {
            min-width: 120px;
            text-align: center;
        }
        
        /* Responsive table improvements */
        @media (max-width: 1200px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .badge {
                font-size: 0.7em;
            }
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });

        function toggleHotelStatus(hotelId, newStatus) {
            const action = newStatus === 'true' ? 'activate' : 'deactivate';
            const actionText = newStatus === 'true' ? 'activated' : 'deactivated';
            
            if (confirm(`Are you sure you want to ${action} this hotel?`)) {
                $.post(`{{ route('b2b.hotel-provider.hotels.index') }}/${hotelId}/toggle-status`, {
                    _token: '{{ csrf_token() }}',
                    _method: 'PATCH'
                })
                .done(function(response) {
                    if (response.success) {
                        // Show success message
                        toastr.success(`Hotel has been ${actionText} successfully.`);
                        
                        // Reload the page to reflect changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'An error occurred.');
                    }
                })
                .fail(function(xhr) {
                    toastr.error('An error occurred while updating hotel status.');
                });
            }
        }

        // Show success message if available
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        // Show error message if available
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@endsection
