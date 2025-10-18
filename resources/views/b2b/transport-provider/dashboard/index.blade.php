@extends('layouts.b2b')

@section('title', 'Transport Provider Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-bus text-info mr-2"></i>
                Transport Provider Dashboard
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.transport-provider.rates') }}" class="btn btn-success mr-2">
                <i class="fas fa-dollar-sign mr-1"></i>
                Manage Rates
            </a>
            <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-info">
                <i class="fas fa-plus mr-1"></i>
                Add New Service
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
                    <h3>{{ $stats['total_services'] ?? 0 }}</h3>
                    <p>Total Services</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bus"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_services'] ?? 0 }}</h3>
                    <p>Active Services</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_offers'] ?? 0 }}</h3>
                    <p>Total Offers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tag"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['active_offers'] ?? 0 }}</h3>
                    <p>Active Offers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Services Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Your Transport Services
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($services) && $services->count() > 0)
                        <div class="table-responsive">
                            <table id="servicesTable" class="table table-bordered table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">Service Name</th>
                                        <th width="12%">Transport Type</th>
                                        <th width="10%">Price (SAR)</th>
                                        <th width="10%">Capacity</th>
                                        <th width="8%">Routes</th>
                                        <th width="10%">Status</th>
                                        <th width="12%">Created</th>
                                        <th width="13%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($services as $index => $service)
                                        <tr id="service-row-{{ $service->id }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="service-icon mr-2">
                                                        <i class="fas fa-{{ $service->transport_type === 'bus' ? 'bus' : ($service->transport_type === 'taxi' ? 'taxi' : 'car') }} text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ $service->service_name }}</strong>
                                                        <small class="text-muted">ID: #{{ $service->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary badge-pill">
                                                    {{ ucfirst(str_replace('_', ' ', $service->transport_type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">
                                                    {{ number_format($service->price, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info badge-pill">
                                                    <i class="fas fa-users mr-1"></i>{{ $service->max_passengers }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary badge-pill">
                                                    <i class="fas fa-route mr-1"></i>{{ $service->routes ? count($service->routes) : 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($service->is_active)
                                                    <span class="badge badge-success badge-pill">
                                                        <i class="fas fa-check-circle mr-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary badge-pill">
                                                        <i class="fas fa-pause-circle mr-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $service->created_at->format('M d, Y') }}<br>
                                                    <span class="text-xs">{{ $service->created_at->diffForHumans() }}</span>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('b2b.transport-provider.show', $service->id) }}" 
                                                       class="btn btn-outline-info"
                                                       title="View Details"
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('b2b.transport-provider.edit', $service->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Service"
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-{{ $service->is_active ? 'secondary' : 'success' }} toggle-status-btn"
                                                            title="{{ $service->is_active ? 'Deactivate' : 'Activate' }} Service"
                                                            data-toggle="tooltip"
                                                            data-service-id="{{ $service->id }}"
                                                            data-service-name="{{ $service->service_name }}"
                                                            data-current-status="{{ $service->is_active ? 'active' : 'inactive' }}">
                                                        <i class="fas {{ $service->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-service-btn"
                                                            title="Delete Service"
                                                            data-toggle="tooltip"
                                                            data-service-id="{{ $service->id }}"
                                                            data-service-name="{{ $service->service_name }}">
                                                        <i class="fas fa-trash"></i>
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
                            <h4 class="text-muted">No Transport Services Added Yet</h4>
                            <p class="text-muted">Start by adding your first transport service to offer to travel package creators.</p>
                            <a href="{{ route('b2b.transport-provider.create') }}" class="btn btn-info">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Service
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteServiceModal" tabindex="-1" role="dialog" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteServiceModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5>Are you sure you want to delete this service?</h5>
                        <p class="text-muted">
                            You are about to delete "<strong id="service-name-to-delete"></strong>". 
                            This action cannot be undone and will remove all associated data.
                        </p>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Warning:</strong> This will also delete:
                        <ul class="mb-0 mt-2">
                            <li>All pricing rules for this service</li>
                            <li>All rate configurations</li>
                            <li>Service booking history</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <form id="deleteServiceForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Delete Service
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Confirmation Modal -->
    <div class="modal fade" id="toggleStatusModal" tabindex="-1" role="dialog" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="toggleStatusModalLabel">
                        <i class="fas fa-question-circle mr-2"></i>Confirm Status Change
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i id="status-icon" class="fas fa-3x mb-3"></i>
                        <h5 id="status-title"></h5>
                        <p class="text-muted" id="status-message"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <form id="toggleStatusForm" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" id="confirmStatusBtn" class="btn">
                            <i class="fas mr-1"></i><span id="confirm-text"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    
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
        
        .service-icon {
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
        
        .modal-header.bg-danger,
        .modal-header.bg-warning {
            border-bottom: none;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
        
        /* DataTables customization */
        .dataTables_wrapper .dataTables_length select {
            padding: 4px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 2px;
        }
        
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        
        .btn-outline-info:hover,
        .btn-outline-warning:hover,
        .btn-outline-success:hover,
        .btn-outline-secondary:hover,
        .btn-outline-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Loading animation */
        .table-loading {
            position: relative;
        }
        
        .table-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

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

@section('scripts')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var servicesTable = $('#servicesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                language: {
                    search: "Search services:",
                    lengthMenu: "Show _MENU_ services per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ services",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No services found",
                    zeroRecords: "No matching services found"
                },
                order: [[1, 'asc']], // Sort by service name by default
                columnDefs: [
                    {
                        targets: [0], // Index column
                        orderable: false,
                        searchable: false,
                        width: "5%"
                    },
                    {
                        targets: [8], // Actions column
                        orderable: false,
                        searchable: false,
                        width: "13%"
                    },
                    {
                        targets: [6], // Status column
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'type') {
                                return data; // Return the HTML badge
                            }
                            return data.replace(/<[^>]*>/g, ''); // Strip HTML for sorting/filtering
                        }
                    },
                    {
                        targets: [2, 4, 5], // Transport Type, Capacity, Routes columns
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'type') {
                                return data; // Return the HTML badge
                            }
                            return data.replace(/<[^>]*>/g, ''); // Strip HTML for sorting/filtering
                        }
                    }
                ],
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-5"i><"col-sm-7"p>>',
                drawCallback: function() {
                    // Re-initialize tooltips after table redraw
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            // Handle delete button click
            $(document).on('click', '.delete-service-btn', function(e) {
                e.preventDefault();
                
                var serviceId = $(this).data('service-id');
                var serviceName = $(this).data('service-name');
                
                // Update modal content with service name
                $('#service-name-to-delete').text(serviceName);
                
                // Update form action
                var formAction = '{{ route("b2b.transport-provider.destroy", "") }}/' + serviceId;
                $('#deleteServiceForm').attr('action', formAction);
                
                // Show modal
                $('#deleteServiceModal').modal('show');
            });

            // Handle status toggle button click
            $(document).on('click', '.toggle-status-btn', function(e) {
                e.preventDefault();
                
                var serviceId = $(this).data('service-id');
                var serviceName = $(this).data('service-name');
                var currentStatus = $(this).data('current-status');
                var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                var newStatusDisplay = newStatus === 'active' ? 'Active' : 'Inactive';
                
                // Update modal content based on action
                if (newStatus === 'active') {
                    $('#status-icon').removeClass().addClass('fas fa-play-circle fa-3x text-success mb-3');
                    $('#status-title').text('Activate Service');
                    $('#status-message').html('Are you sure you want to <strong>activate</strong> "' + serviceName + '"? This will make the service available for bookings.');
                    $('#confirmStatusBtn').removeClass().addClass('btn btn-success');
                    $('#confirmStatusBtn i').removeClass().addClass('fas fa-check mr-1');
                    $('#confirm-text').text('Activate Service');
                } else {
                    $('#status-icon').removeClass().addClass('fas fa-pause-circle fa-3x text-warning mb-3');
                    $('#status-title').text('Deactivate Service');
                    $('#status-message').html('Are you sure you want to <strong>deactivate</strong> "' + serviceName + '"? This will prevent new bookings for this service.');
                    $('#confirmStatusBtn').removeClass().addClass('btn btn-warning');
                    $('#confirmStatusBtn i').removeClass().addClass('fas fa-pause mr-1');
                    $('#confirm-text').text('Deactivate Service');
                }
                
                // Update form action (you'll need to create this route)
                var formAction = '{{ route("b2b.transport-provider.toggle-status", "") }}/' + serviceId;
                $('#toggleStatusForm').attr('action', formAction);
                
                // Show modal
                $('#toggleStatusModal').modal('show');
            });

            // Handle form submissions with loading states
            $('#deleteServiceForm').on('submit', function() {
                var submitBtn = $(this).find('button[type="submit"]');
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...')
                         .prop('disabled', true);
            });

            $('#toggleStatusForm').on('submit', function() {
                var submitBtn = $(this).find('button[type="submit"]');
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...')
                         .prop('disabled', true);
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Add hover effects for service rows
            $('#servicesTable tbody').on('mouseenter', 'tr', function() {
                $(this).find('.btn-group').addClass('show-actions');
            }).on('mouseleave', 'tr', function() {
                $(this).find('.btn-group').removeClass('show-actions');
            });
        });
        
        // Add some smooth animations
        $('.small-box').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
    </script>
@endsection
