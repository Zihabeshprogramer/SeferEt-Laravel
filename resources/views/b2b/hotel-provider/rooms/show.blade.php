@extends('layouts.b2b')

@section('title', 'Room Details - ' . ($room->name ?: $room->room_number))

@section('page-title', 'Room Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('b2b.hotel-provider.rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item active">{{ $room->name ?: $room->room_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Room Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bed mr-2"></i>
                        {{ $room->name ?: 'Room ' . $room->room_number }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>Room Number:</strong></td>
                                        <td>{{ $room->room_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Room Name:</strong></td>
                                        <td>{{ $room->name ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hotel:</strong></td>
                                        <td>
                                            <a href="{{ route('b2b.hotel-provider.hotels.show', $room->hotel) }}" 
                                               class="text-decoration-none">
                                                {{ $room->hotel->name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Category:</strong></td>
                                        <td>
                                            @if($room->category)
                                                <span class="badge badge-info">{{ $room->category_name }}</span>
                                            @else
                                                <span class="badge badge-secondary">No Category Assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base Price:</strong></td>
                                        <td>
                                            <span class="h5 text-success mb-0">
                                                ${{ number_format($room->base_price, 2) }}
                                            </span>
                                            <small class="text-muted">per night</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>Maximum Occupancy:</strong></td>
                                        <td>
                                            <i class="fas fa-users text-muted mr-1"></i>
                                            {{ $room->max_occupancy }} guests
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bed Type:</strong></td>
                                        <td>{{ ucwords(str_replace('_', ' ', $room->bed_type)) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bed Count:</strong></td>
                                        <td>{{ $room->bed_count }} bed(s)</td>
                                    </tr>
                                    @if($room->size_sqm)
                                    <tr>
                                        <td><strong>Room Size:</strong></td>
                                        <td>{{ $room->size_sqm }} m²</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($room->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Availability:</strong></td>
                                        <td>
                                            @if($room->is_available)
                                                <span class="badge badge-success">Available</span>
                                            @else
                                                <span class="badge badge-danger">Occupied</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($room->description)
                        <div class="mt-4">
                            <h6>Description</h6>
                            <p class="text-muted">{{ $room->description }}</p>
                        </div>
                    @endif

                    @if($room->amenities && count($room->amenities) > 0)
                        <div class="mt-4">
                            <h6>Amenities</h6>
                            <div class="row">
                                @foreach($room->amenities as $amenityKey)
                                    <div class="col-md-4 mb-2">
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                        {{ ucwords(str_replace('_', ' ', $amenityKey)) }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($room->images && count($room->images) > 0)
                        <div class="mt-4">
                            <h6>Images</h6>
                            <div class="row">
                                @foreach($room->images as $image)
                                    <div class="col-md-4 mb-3">
                                        <img src="{{ Storage::url($image) }}" 
                                             class="img-fluid rounded" 
                                             alt="Room Image"
                                             style="height: 200px; object-fit: cover; width: 100%;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions & Statistics -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('b2b.hotel-provider.rooms.edit', $room) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-edit mr-2"></i>Edit Room
                        </a>
                        
                        <button class="btn btn-outline-warning toggle-availability-btn" 
                                data-room-id="{{ $room->id }}" 
                                data-current-availability="{{ $room->is_available ? 'true' : 'false' }}">
                            <i class="fas fa-toggle-{{ $room->is_available ? 'on' : 'off' }} mr-2"></i>
                            {{ $room->is_available ? 'Mark as Occupied' : 'Mark as Available' }}
                        </button>
                        
                        <button class="btn btn-outline-secondary toggle-status-btn" 
                                data-room-id="{{ $room->id }}">
                            <i class="fas fa-power-off mr-2"></i>
                            {{ $room->is_active ? 'Deactivate' : 'Activate' }} Room
                        </button>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-info dropdown-toggle" type="button" 
                                    id="moreActionsDropdown" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-h mr-2"></i>More Actions
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('b2b.hotel-provider.hotels.rooms', $room->hotel) }}">
                                    <i class="fas fa-list mr-2"></i>View All Hotel Rooms
                                </a>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteRoomModal">
                                    <i class="fas fa-trash text-danger mr-2"></i>Delete Room
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Room Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary mb-1">{{ $stats['total_bookings'] }}</div>
                            <small class="text-muted">Total Bookings</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success mb-1">${{ number_format($stats['revenue'], 2) }}</div>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                        <div class="col-6 mt-3">
                            <div class="h4 text-info mb-1">{{ $stats['occupancy_rate'] }}%</div>
                            <small class="text-muted">Occupancy Rate</small>
                        </div>
                        <div class="col-6 mt-3">
                            <div class="h4 text-warning mb-1">{{ number_format($stats['average_rating'], 1) }}</div>
                            <small class="text-muted">Avg Rating</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Info</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">Created</small>
                    <div class="mb-2">{{ $room->created_at->format('M d, Y \a\t H:i') }}</div>
                    
                    <small class="text-muted">Last Updated</small>
                    <div>{{ $room->updated_at->format('M d, Y \a\t H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Delete Room
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $room->name ?: $room->room_number }}</strong>?</p>
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        This action cannot be undone. All associated data will be permanently deleted.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('b2b.hotel-provider.rooms.destroy', $room) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Delete Room
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Toggle room availability
            $('.toggle-availability-btn').on('click', function() {
                const roomId = $(this).data('room-id');
                const currentAvailability = $(this).data('current-availability') === 'true';
                
                $.ajax({
                    url: `/b2b/hotel-provider/rooms/${roomId}/toggle-availability`,
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Error toggling availability');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error toggling room availability');
                    }
                });
            });

            // Toggle room status
            $('.toggle-status-btn').on('click', function() {
                const roomId = $(this).data('room-id');
                
                if (confirm('Are you sure you want to toggle this room\'s status?')) {
                    $.ajax({
                        url: `/b2b/hotel-provider/rooms/${roomId}/toggle-status`,
                        method: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                toastr.error(response.message || 'Error toggling status');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Error toggling room status');
                        }
                    });
                }
            });
        });
    </script>
@endsection
