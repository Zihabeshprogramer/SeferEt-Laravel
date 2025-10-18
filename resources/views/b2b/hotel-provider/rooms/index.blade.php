@extends('layouts.b2b')

@section('title', 'Room Management')

@section('page-title', 'Room Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rooms</li>
@endsection

@section('content')
    <!-- Page Header with Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="mb-2">
                <i class="fas fa-bed text-primary mr-2"></i>
                Room Management
            </h2>
            <p class="text-muted">Manage all rooms across your hotels efficiently</p>
        </div>
        <div class="col-lg-4 text-right">
            <div class="btn-group" role="group">
                <a href="{{ route('b2b.hotel-provider.rooms.create') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i>
                    Add New Room
                </a>
                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" id="bulk-actions-btn">
                        <i class="fas fa-tasks mr-2"></i>Bulk Actions
                    </a>
                    <a class="dropdown-item" href="#" id="export-csv-btn">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('b2b.hotel-provider.hotels.index') }}">
                        <i class="fas fa-hotel mr-2"></i>Manage Hotels
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $rooms->total() }}</h4>
                            <p class="mb-0">Total Rooms</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-bed fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @php $activeRooms = $rooms->where('is_active', true)->count(); @endphp
                            <h4 class="mb-0">{{ $activeRooms }}</h4>
                            <p class="mb-0">Active Rooms</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @php $availableRooms = $rooms->where('is_available', true)->count(); @endphp
                            <h4 class="mb-0">{{ $availableRooms }}</h4>
                            <p class="mb-0">Available</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-door-open fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @php $avgPrice = $rooms->avg('base_price') ?? 0; @endphp
                            <h4 class="mb-0">${{ number_format($avgPrice, 0) }}</h4>
                            <p class="mb-0">Avg. Price</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter mr-2"></i>Filters & Search
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                Use the filters below and the search functionality in the table to find specific rooms quickly.
            </p>
            
            <!-- Quick Filters for DataTables -->
            <div class="mt-3 pt-3 border-top">
                <div class="row">
                    <div class="col-md-3">
                        <label for="hotel-filter" class="form-label text-muted">Filter by Hotel:</label>
                        <select id="hotel-filter" class="form-control form-control-sm">
                            <option value="">All Hotels</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->name }}">{{ $hotel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category-filter" class="form-label text-muted">Filter by Category:</label>
                        <select id="category-filter" class="form-control form-control-sm">
                            <option value="">All Categories</option>
                            @foreach($roomTypeCategories as $key => $label)
                                <option value="{{ $label }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status-filter" class="form-label text-muted">Filter by Status:</label>
                        <select id="status-filter" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="availability-filter" class="form-label text-muted">Filter by Availability:</label>
                        <select id="availability-filter" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Available">Available</option>
                            <option value="Occupied">Occupied</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted">&nbsp;</label>
                        <div>
                            <button type="button" id="clear-filters" class="btn btn-outline-secondary btn-sm btn-block">
                                <i class="fas fa-times mr-1"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bed mr-2"></i>All Rooms
                    <span class="badge badge-primary ml-2">{{ $rooms->count() }}</span>
                </h5>
                <div class="card-tools">
                    <a href="{{ route('b2b.hotel-provider.rooms.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add New Room
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($rooms->count() > 0)
                <div class="table-responsive">
                    <table id="roomsTable" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Room Details</th>
                                <th>Hotel</th>
                                <th>Category</th>
                                <th>Price</th>
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
                                        <div class="room-details">
                                            <div class="room-number">{{ $room->room_number }}</div>
                                            @if($room->name)
                                                <div class="room-name">{{ $room->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hotel-details">
                                            <a href="{{ route('b2b.hotel-provider.hotels.show', $room->hotel) }}" 
                                               class="hotel-name text-decoration-none">
                                                {{ $room->hotel->name }}
                                            </a>
                                            <div class="hotel-city">{{ $room->hotel->city }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($room->category)
                                            <span class="badge badge-info">{{ $room->category_name }}</span>
                                        @else
                                            <span class="badge badge-secondary">No Category</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="price-display">
                                            ${{ number_format($room->base_price, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="occupancy-display">
                                            <i class="fas fa-users"></i>
                                            <span>{{ $room->max_occupancy }}</span>
                                        </div>
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
                                               class="btn btn-sm btn-outline-info" title="View Room">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('b2b.hotel-provider.rooms.edit', $room) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit Room">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning toggle-status-btn" 
                                                    data-room-id="{{ $room->id }}" 
                                                    title="Toggle Room Status">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bed fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Rooms Found</h4>
                    <p class="text-muted">
                        @if(request()->hasAny(['hotel_id', 'category', 'status']))
                            No rooms match your current filters.
                        @else
                            You haven't created any rooms yet.
                        @endif
                    </p>
                    <a href="{{ route('b2b.hotel-provider.rooms.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Create Your First Room
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <style>
        .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .badge {
            font-size: 0.75rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 0.5rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.25rem;
        }
        .dt-buttons {
            margin-bottom: 0.5rem;
        }
        .room-details {
            font-size: 0.875rem;
        }
        .room-details .room-number {
            font-weight: bold;
            color: #2E8B57;
        }
        .room-details .room-name {
            color: #6c757d;
            font-size: 0.8rem;
        }
        .hotel-details .hotel-name {
            font-weight: 500;
        }
        .hotel-details .hotel-city {
            color: #6c757d;
            font-size: 0.8rem;
        }
        .price-display {
            font-weight: bold;
            color: #28a745;
            font-size: 1rem;
        }
        .occupancy-display {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        .occupancy-display i {
            margin-right: 0.25rem;
            color: #6c757d;
        }
    </style>
@endpush

@push('scripts')
    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
@endpush

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const roomsTable = $('#roomsTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[0, 'asc']], // Default sort by room details
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6">>t' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fas fa-copy"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        title: 'Rooms_Export_' + new Date().toISOString().slice(0, 10)
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        title: 'Rooms_Export_' + new Date().toISOString().slice(0, 10)
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        title: 'Rooms Report',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-info btn-sm',
                        text: '<i class="fas fa-print"></i> Print'
                    }
                ],
                language: {
                    lengthMenu: "Show _MENU_ rooms per page",
                    zeroRecords: "No rooms found matching your criteria",
                    info: "Showing _START_ to _END_ of _TOTAL_ rooms",
                    infoEmpty: "No rooms available",
                    infoFiltered: "(filtered from _MAX_ total rooms)",
                    search: "Search rooms:",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    {
                        targets: [3], // Price column
                        type: 'num-fmt',
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'type') {
                                return data;
                            }
                            // For sorting, extract numeric value
                            return parseFloat(data.replace(/[^0-9.-]+/g, "")) || 0;
                        }
                    },
                    {
                        targets: [4], // Occupancy column
                        type: 'num',
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'type') {
                                return data;
                            }
                            // For sorting, extract numeric value
                            const match = data.match(/>(\d+)</)
                            return match ? parseInt(match[1]) : 0;
                        }
                    },
                    {
                        targets: [7], // Actions column
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Custom filter functionality
            $('#hotel-filter').on('change', function() {
                const selectedHotel = $(this).val();
                roomsTable.column(1).search(selectedHotel).draw();
            });

            $('#category-filter').on('change', function() {
                const selectedCategory = $(this).val();
                roomsTable.column(2).search(selectedCategory).draw();
            });

            $('#status-filter').on('change', function() {
                const selectedStatus = $(this).val();
                roomsTable.column(5).search(selectedStatus).draw();
            });

            $('#availability-filter').on('change', function() {
                const selectedAvailability = $(this).val();
                roomsTable.column(6).search(selectedAvailability).draw();
            });

            // Clear all filters
            $('#clear-filters').on('click', function() {
                $('#hotel-filter, #category-filter, #status-filter, #availability-filter').val('');
                roomsTable.columns().search('').draw();
            });

            // Toggle room status
            $('#roomsTable').on('click', '.toggle-status-btn', function() {
                const roomId = $(this).data('room-id');
                const button = $(this);
                
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
