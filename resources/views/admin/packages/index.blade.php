@extends('layouts.admin')

@section('title', 'Packages Management')

@section('page-title', 'Packages Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Packages</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
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
                    <h3>{{ $stats['active'] }}</h3>
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
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending Review</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['rejected'] }}</h3>
                    <p>Rejected</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-box mr-2"></i>
                All Packages
            </h3>
            <div class="card-tools">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter mr-1"></i>All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-check mr-1"></i>Active
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-clock mr-1"></i>Pending
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times mr-1"></i>Rejected
                    </button>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-wrench"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" role="menu">
                        <a href="#" class="dropdown-item">Export to PDF</a>
                        <a href="#" class="dropdown-item">Export to Excel</a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">Bulk Actions</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(count($packages) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Package Name</th>
                                <th>Partner</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $package)
                                <tr>
                                    <td>{{ $package->id ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $package->name ?? 'Sample Package' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $package->type ?? 'Umrah Package' }}</small>
                                    </td>
                                    <td>
                                        {{ $package->partner->name ?? 'Sample Partner' }}
                                        <br>
                                        <small class="text-muted">{{ $package->partner->company_name ?? 'Sample Company' }}</small>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($package->price ?? 2500, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">per person</small>
                                    </td>
                                    <td>{{ $package->duration ?? '14' }} days</td>
                                    <td>
                                        @php
                                            $status = $package->status ?? 'active';
                                        @endphp
                                        @if($status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($status === 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @elseif($status === 'draft')
                                            <span class="badge badge-secondary">Draft</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ isset($package->created_at) ? $package->created_at->format('M d, Y') : 'Jan 15, 2024' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if(($package->status ?? 'pending') === 'pending')
                                                <button type="button" class="btn btn-success" title="Approve Package">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Reject Package">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            
                                            <button type="button" class="btn btn-warning" title="Edit Package">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Packages Found</h5>
                                        <p class="text-muted">No packages have been submitted yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Packages Found</h5>
                    <p class="text-muted">There are no packages in the system yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Package management functionality can be added here

});
</script>
@endsection
