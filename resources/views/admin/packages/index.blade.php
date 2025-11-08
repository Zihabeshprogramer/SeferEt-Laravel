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

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

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
                                <th>Status & Approval</th>
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
                                        {{ $package->creator->name ?? 'Unknown Partner' }}
                                        <br>
                                        <small class="text-muted">{{ $package->creator->email ?? 'No email' }}</small>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($package->base_price ?? 0, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">per person</small>
                                    </td>
                                    <td>{{ $package->duration ?? '14' }} days</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <!-- Package Status -->
                                            @php
                                                $status = $package->status ?? 'draft';
                                            @endphp
                                            @if($status === 'active')
                                                <span class="badge badge-success mb-1">Active</span>
                                            @elseif($status === 'draft')
                                                <span class="badge badge-secondary mb-1">Draft</span>
                                            @elseif($status === 'inactive')
                                                <span class="badge badge-warning mb-1">Inactive</span>
                                            @else
                                                <span class="badge badge-secondary mb-1">{{ ucfirst($status) }}</span>
                                            @endif
                                            
                                            <!-- Approval Status -->
                                            @php
                                                $approvalStatus = $package->approval_status ?? 'pending';
                                            @endphp
                                            @if($approvalStatus === 'approved')
                                                <span class="badge badge-success">✓ Approved</span>
                                            @elseif($approvalStatus === 'pending')
                                                <span class="badge badge-warning">⏳ Pending Review</span>
                                            @elseif($approvalStatus === 'rejected')
                                                <span class="badge badge-danger">✗ Rejected</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($approvalStatus) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ isset($package->created_at) ? $package->created_at->format('M d, Y') : 'Jan 15, 2024' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.packages.show', $package->id) }}" class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if(($package->approval_status ?? 'pending') === 'pending')
                                                <form method="POST" action="{{ route('admin.packages.approve', $package->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this package?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success" title="Approve Package">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger" title="Reject Package" onclick="showRejectModal({{ $package->id }}, '{{ addslashes($package->name) }}')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @elseif(($package->approval_status ?? 'pending') === 'rejected')
                                                <form method="POST" action="{{ route('admin.packages.approve', $package->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to re-approve this package?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Re-approve Package">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <button type="button" class="btn btn-warning" title="Edit Package (Coming Soon)" disabled>
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

    <!-- Package Rejection Modal -->
    <div class="modal fade" id="rejectPackageModal" tabindex="-1" role="dialog" aria-labelledby="rejectPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectPackageModalLabel">Reject Package</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="rejectPackageForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject the package "<strong id="packageNameToReject"></strong>"?</p>
                        
                        <div class="form-group">
                            <label for="rejectionReason">Reason for rejection (optional):</label>
                            <textarea class="form-control" id="rejectionReason" name="reason" rows="3" placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times mr-1"></i>Reject Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Status filter functionality
    $('.card-tools .btn-group .btn').click(function() {
        $('.card-tools .btn-group .btn').removeClass('btn-primary btn-success btn-warning btn-danger')
                                      .addClass('btn-outline-secondary');
        
        if ($(this).text().includes('All')) {
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
            showAllPackages();
        } else if ($(this).text().includes('Active')) {
            $(this).removeClass('btn-outline-secondary').addClass('btn-success');
            filterPackages('active');
        } else if ($(this).text().includes('Pending')) {
            $(this).removeClass('btn-outline-secondary').addClass('btn-warning');
            filterPackages('pending');
        } else if ($(this).text().includes('Rejected')) {
            $(this).removeClass('btn-outline-secondary').addClass('btn-danger');
            filterPackages('rejected');
        }
    });
    
    function showAllPackages() {
        $('table tbody tr').show();
    }
    
    function filterPackages(status) {
        $('table tbody tr').each(function() {
            const statusBadges = $(this).find('.badge');
            let hasStatus = false;
            
            statusBadges.each(function() {
                const badgeText = $(this).text().toLowerCase();
                if ((status === 'active' && badgeText.includes('active')) ||
                    (status === 'pending' && badgeText.includes('pending')) ||
                    (status === 'rejected' && badgeText.includes('rejected'))) {
                    hasStatus = true;
                }
            });
            
            if (hasStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
});

// Function to show rejection modal
function showRejectModal(packageId, packageName) {
    $('#packageNameToReject').text(packageName);
    $('#rejectPackageForm').attr('action', '/admin/packages/' + packageId + '/reject');
    $('#rejectionReason').val('');
    $('#rejectPackageModal').modal('show');
}
</script>
@endsection
