@extends('layouts.b2b')

@section('title', 'Hotel Provider Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-hotel text-info mr-2"></i>
                Hotel Provider Dashboard
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.hotel-provider.hotels.create') }}" class="btn btn-info">
                <i class="fas fa-plus mr-1"></i>
                Add New Hotel
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
                    <h3>{{ $stats['total_hotels'] ?? 0 }}</h3>
                    <p>Total Hotels</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hotel"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_hotels'] ?? 0 }}</h3>
                    <p>Active Hotels</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_hotels'] ?? 0 }}</h3>
                    <p>Pending Approval</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total_rooms'] ?? 0 }}</h3>
                    <p>Total Rooms</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Hotels Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Your Hotels
                    </h3>
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
                    
                    @if(isset($hotels) && $hotels->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Hotel Name</th>
                                        <th>Location</th>
                                        <th>Star Rating</th>
                                        <th>Admin Approval</th>
                                        <th>Overall Status</th>
                                        <th>Rooms</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hotels as $hotel)
                                        <tr>
                                            <td>
                                                <strong>{{ $hotel->hotel_name ?? $hotel->name }}</strong>
                                            </td>
                                            <td>{{ $hotel->city }}, {{ $hotel->country }}</td>
                                            <td>
                                                @if($hotel->star_rating)
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $hotel->star_rating ? 'text-warning' : 'text-muted' }}"></i>
                                                    @endfor
                                                @else
                                                    <span class="text-muted">Not rated</span>
                                                @endif
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
                                                @else
                                                    <span class="badge badge-secondary">
                                                        {{ ucfirst($hotel->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $hotel->rooms ? $hotel->rooms->count() : 0 }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('b2b.hotel-provider.hotels.show', $hotel->id) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('b2b.hotel-provider.hotels.edit', $hotel->id) }}" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($hotel->status === 'active')
                                                        <form action="{{ route('b2b.hotel-provider.hotels.toggle-status', $hotel->id) }}"
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="btn btn-sm {{ $hotel->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                                    onclick="return confirm('Are you sure you want to {{ $hotel->is_active ? 'deactivate' : 'activate' }} this hotel?')">
                                                                <i class="fas {{ $hotel->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-sm btn-light" disabled
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
                        
                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            {{ $hotels->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-hotel fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Hotels Added Yet</h4>
                            <p class="text-muted">Start by adding your first hotel to offer services to travel package creators.</p>
                            <a href="{{ route('b2b.hotel-provider.hotels.create') }}" class="btn btn-info">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Hotel
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
            white-space: nowrap;
        }
        .btn-group .btn {
            margin-right: 0;
        }
        
        /* Status column styling */
        .table td:nth-child(4),
        .table td:nth-child(5) {
            min-width: 120px;
            text-align: center;
        }
        
        /* Responsive improvements */
        @media (max-width: 1200px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .badge {
                font-size: 0.7em;
            }
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
