@extends('layouts.b2b')

@section('title', 'Package Management')

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1 class="m-0">
                <i class="fas fa-box text-info mr-2"></i>
                Package Management
            </h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('b2b.travel-agent.packages.create') }}" class="btn btn-info">
                <i class="fas fa-plus mr-1"></i>
                Create New Package
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
                    <h3>{{ $stats['total_packages'] ?? 0 }}</h3>
                    <p>Total Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_packages'] ?? 0 }}</h3>
                    <p>Active Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['draft_packages'] ?? 0 }}</h3>
                    <p>Draft Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['featured_packages'] ?? 0 }}</h3>
                    <p>Featured Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Packages Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list mr-2"></i>
                            Your Travel Packages
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group" role="group" aria-label="Package filters">
                                <a href="{{ route('b2b.travel-agent.packages.index') }}" 
                                   class="btn btn-sm {{ request('status') ? 'btn-outline-primary' : 'btn-primary' }}">
                                    <i class="fas fa-list"></i> All
                                </a>
                                <a href="{{ route('b2b.travel-agent.packages.index', ['status' => 'draft']) }}" 
                                   class="btn btn-sm {{ request('status') === 'draft' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    <i class="fas fa-edit"></i> Drafts ({{ $stats['draft_packages'] ?? 0 }})
                                </a>
                                <a href="{{ route('b2b.travel-agent.packages.index', ['status' => 'active']) }}" 
                                   class="btn btn-sm {{ request('status') === 'active' ? 'btn-success' : 'btn-outline-success' }}">
                                    <i class="fas fa-check-circle"></i> Active
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(request('status') === 'draft' && isset($stats['draft_packages']) && $stats['draft_packages'] > 0)
                        <div class="alert alert-info border-0 mb-4">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Draft Packages</h6>
                            <p class="mb-0">You have <strong>{{ $stats['draft_packages'] }}</strong> draft package(s) that need to be completed. Click the <span class="badge badge-success"><i class="fas fa-play-circle"></i></span> button to continue working on a draft.</p>
                        </div>
                    @endif
                    
                    @if(isset($packages) && $packages->count() > 0)
                        <div class="table-responsive">
                            <table id="packagesTable" class="table table-bordered table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">Package Name</th>
                                        <th width="10%">Type</th>
                                        <th width="10%">Duration</th>
                                        <th width="12%">Base Price</th>
                                        <th width="8%">Flights</th>
                                        <th width="10%">Status</th>
                                        <th width="12%">Created</th>
                                        <th width="13%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packages as $index => $package)
                                        <tr id="package-row-{{ $package->id }}">
                                            <td>{{ $packages->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="package-thumbnail mr-3">
                                                        @if($package->hasImages())
                                                            <img src="{{ $package->getMainImageUrl('thumbnail') }}" 
                                                                 alt="{{ $package->name }}" 
                                                                 class="rounded" 
                                                                 style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #dee2e6;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 50px; height: 50px; border: 2px solid #dee2e6;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ $package->name }}</strong>
                                                        <small class="text-muted">ID: #{{ $package->id }}</small>
                                                        @if($package->hasImages())
                                                            <small class="text-success d-block">
                                                                <i class="fas fa-images mr-1"></i>{{ $package->getImageCount() }} image(s)
                                                            </small>
                                                        @endif
                                                        @if($package->is_featured)
                                                            <span class="badge badge-warning badge-pill ml-1">
                                                                <i class="fas fa-star"></i> Featured
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary badge-pill">
                                                    {{ ucfirst($package->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info badge-pill">
                                                    <i class="fas fa-calendar-alt mr-1"></i>{{ $package->duration }} days
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">
                                                    {{ number_format($package->base_price, 2) }} {{ $package->currency }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary badge-pill">
                                                    <i class="fas fa-plane mr-1"></i>{{ $package->flights ? $package->flights->count() : 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($package->status === 'active')
                                                    <span class="badge badge-success badge-pill">
                                                        <i class="fas fa-check-circle mr-1"></i>Active
                                                    </span>
                                                @elseif($package->status === 'draft')
                                                    <span class="badge badge-warning badge-pill">
                                                        <i class="fas fa-edit mr-1"></i>Draft
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary badge-pill">
                                                        <i class="fas fa-pause-circle mr-1"></i>{{ ucfirst($package->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $package->created_at->format('M d, Y') }}<br>
                                                    <span class="text-xs">{{ $package->created_at->diffForHumans() }}</span>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('b2b.travel-agent.packages.show', $package->id) }}" 
                                                       class="btn btn-outline-info"
                                                       title="View Details"
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('b2b.travel-agent.packages.edit', $package->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Package"
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-{{ $package->status === 'active' ? 'secondary' : 'success' }} toggle-status-btn"
                                                            title="{{ $package->status === 'active' ? 'Deactivate' : 'Activate' }} Package"
                                                            data-toggle="tooltip"
                                                            data-package-id="{{ $package->id }}"
                                                            data-package-name="{{ $package->name }}"
                                                            data-current-status="{{ $package->status }}">
                                                        <i class="fas {{ $package->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-package-btn"
                                                            title="Delete Package"
                                                            data-toggle="tooltip"
                                                            data-package-id="{{ $package->id }}"
                                                            data-package-name="{{ $package->name }}">
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
                            {{ $packages->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-suitcase-rolling fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Packages Created Yet</h4>
                            <p class="text-muted">Start by creating your first travel package to offer to customers.</p>
                            <a href="{{ route('b2b.travel-agent.packages.create') }}" class="btn btn-info">
                                <i class="fas fa-plus mr-2"></i>
                                Create Your First Package
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
        
        .package-icon {
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
        
        .text-xs {
            font-size: 0.7rem;
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
        
        @if(session('warning'))
            toastr.warning('{{ session('warning') }}');
        @endif
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Delete Package functionality
        $(document).on('click', '.delete-package-btn', function(e) {
            e.preventDefault();
            
            const packageId = $(this).data('package-id');
            const packageName = $(this).data('package-name');
            const rowElement = $(`#package-row-${packageId}`);
            
            // Show confirmation modal
            Swal.fire({
                title: 'Delete Package?',
                html: `Are you sure you want to delete <strong>${packageName}</strong>?<br><small class="text-danger">This action cannot be undone!</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash mr-2"></i>Yes, Delete It!',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form to submit the delete request
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': `{{ url('b2b/travel-agent/packages') }}/${packageId}`
                    });
                    
                    // Add CSRF token
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': $('meta[name="csrf-token"]').attr('content')
                    }));
                    
                    // Add method override for DELETE
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'DELETE'
                    }));
                    
                    // Append form to body and submit
                    $('body').append(form);
                    form.submit();
                }
            });
        });
        
        // Toggle Package Status functionality
        $(document).on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            
            const packageId = $(this).data('package-id');
            const packageName = $(this).data('package-name');
            const currentStatus = $(this).data('current-status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            
            // Show confirmation
            Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)} Package?`,
                html: `Are you sure you want to <strong>${action}</strong> ${packageName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: newStatus === 'active' ? '#28a745' : '#6c757d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `<i class="fas fa-${newStatus === 'active' ? 'play' : 'pause'} mr-2"></i>Yes, ${action.charAt(0).toUpperCase() + action.slice(1)}!`,
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form to submit the toggle request
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': `{{ url('b2b/travel-agent/packages') }}/${packageId}/toggle-status`
                    });
                    
                    // Add CSRF token
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': $('meta[name="csrf-token"]').attr('content')
                    }));
                    
                    // Add method override for PATCH
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'PATCH'
                    }));
                    
                    // Append form to body and submit
                    $('body').append(form);
                    form.submit();
                }
            });
        });
        
        // Note: Package actions now use reliable form submissions 
        // instead of AJAX to avoid CSRF and routing issues.
        // This ensures compatibility with Laravel's route model binding
        // and proper middleware execution.
        
        // Enhanced DataTable initialization (if needed)
        $(document).ready(function() {
            if ($('#packagesTable').length && typeof $.fn.DataTable !== 'undefined') {
                $('#packagesTable').DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "searching": false,
                    "paging": false,
                    "info": false,
                    "ordering": true,
                    "order": [[ 7, "desc" ]], // Order by created date
                    "columnDefs": [
                        { "orderable": false, "targets": [0, 8] } // Disable ordering for # and Actions columns
                    ],
                    "language": {
                        "emptyTable": "No packages found",
                        "zeroRecords": "No matching packages found"
                    }
                });
            }
        });
    </script>
@stop
