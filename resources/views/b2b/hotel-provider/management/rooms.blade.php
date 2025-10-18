@extends('layouts.b2b')

@section('title', 'Hotel Rooms Management')

@section('page-title', 'Hotel Rooms - ' . $hotel->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.hotels.index') }}">Hotels</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.hotels.show', $hotel) }}">{{ $hotel->name }}</a></li>
    <li class="breadcrumb-item active">Rooms</li>
@endsection

@section('content')
    <!-- Hotel Information Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-hotel text-primary mr-2"></i>
                        {{ $hotel->name }}
                    </h5>
                    <p class="card-text">
                        <i class="fas fa-map-marker-alt text-muted mr-1"></i>
                        {{ $hotel->address }}, {{ $hotel->city }}, {{ $hotel->country }}
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Total Rooms</small>
                            <div class="h5 text-primary">{{ $hotel->rooms->count() }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Available</small>
                            <div class="h5 text-success">{{ $hotel->rooms->where('is_available', true)->count() }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Occupied</small>
                            <div class="h5 text-warning">{{ $hotel->rooms->where('is_available', false)->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <a href="{{ route('b2b.hotel-provider.rooms.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Room
                    </a>
                    <div class="mt-3">
                        <a href="{{ route('b2b.hotel-provider.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Back to Hotel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-bed mr-2"></i>
                Hotel Rooms
            </h5>
        </div>
        <div class="card-body">
            @if($rooms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Room Number</th>
                                <th>Room Name</th>
                                <th>Type</th>
                                <th>Base Price</th>
                                <th>Occupancy</th>
                                <th>Status</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                                <tr>
                                    <td>
                                        <strong>{{ $room->room_number }}</strong>
                                    </td>
                                    <td>{{ $room->name }}</td>
                                    <td>
                                        @if($room->roomType)
                                            <span class="badge badge-info">{{ $room->roomType->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">No Type</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-success">
                                            ${{ number_format($room->base_price, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-users text-muted mr-1"></i>
                                        {{ $room->max_occupancy }}
                                    </td>
                                    <td>
                                        @if($room->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($room->is_available)
                                            <span class="badge badge-success">Available</span>
                                        @else
                                            <span class="badge badge-danger">Occupied</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('b2b.hotel-provider.rooms.show', $room) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('b2b.hotel-provider.rooms.edit', $room) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit Room">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning toggle-availability-btn" 
                                                    data-room-id="{{ $room->id }}" 
                                                    data-current-availability="{{ $room->is_available ? 'true' : 'false' }}" 
                                                    title="Toggle Availability">
                                                <i class="fas fa-toggle-{{ $room->is_available ? 'on' : 'off' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($rooms->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $rooms->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bed fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Rooms Found</h4>
                    <p class="text-muted">This hotel doesn't have any rooms yet.</p>
                    <a href="{{ route('b2b.hotel-provider.rooms.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Room
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('css')
    <style>
        .card-title {
            color: #2E8B57;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Toggle room availability
            $('.toggle-availability-btn').on('click', function() {
                const roomId = $(this).data('room-id');
                const currentAvailability = $(this).data('current-availability') === 'true';
                const newAvailability = !currentAvailability;
                
                $.ajax({
                    url: `/b2b/hotel-provider/rooms/${roomId}/toggle-availability`,
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            // Reload page to update display
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Error toggling availability');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error toggling room availability');
                        console.error('AJAX Error:', xhr);
                    }
                });
            });
        });
    </script>
@endsection
