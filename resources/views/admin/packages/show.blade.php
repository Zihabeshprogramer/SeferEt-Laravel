@extends('layouts.admin')

@section('title', 'Package Details - ' . $package->name)

@section('page-title', 'Package Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.packages') }}">Packages</a></li>
    <li class="breadcrumb-item active">{{ $package->name }}</li>
@endsection

@section('content')
    <!-- Package Header -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box mr-2"></i>
                        {{ $package->name }}
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Package Status -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Package Status:</strong>
                            @if($package->status === 'active')
                                <span class="badge badge-success ml-2">Active</span>
                            @elseif($package->status === 'draft')
                                <span class="badge badge-secondary ml-2">Draft</span>
                            @else
                                <span class="badge badge-warning ml-2">{{ ucfirst($package->status) }}</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Approval Status:</strong>
                            @if($package->approval_status === 'approved')
                                <span class="badge badge-success ml-2">✓ Approved</span>
                            @elseif($package->approval_status === 'pending')
                                <span class="badge badge-warning ml-2">⏳ Pending Review</span>
                            @elseif($package->approval_status === 'rejected')
                                <span class="badge badge-danger ml-2">✗ Rejected</span>
                            @endif
                        </div>
                    </div>

                    <!-- Basic Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Type:</strong> {{ $package->type ? ucfirst($package->type) : 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Duration:</strong> {{ $package->duration }} days
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Base Price:</strong> ${{ number_format($package->base_price, 2) }}
                        </div>
                        <div class="col-md-6">
                            <strong>Currency:</strong> {{ $package->currency ?? 'USD' }}
                        </div>
                    </div>

                    <!-- Partner Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Created by:</strong> {{ $package->creator->name ?? 'Unknown' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Partner Email:</strong> {{ $package->creator->email ?? 'N/A' }}
                        </div>
                    </div>

                    <!-- Destinations -->
                    @if($package->destinations)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Destinations:</strong>
                            @foreach($package->destinations as $destination)
                                <span class="badge badge-primary ml-1">{{ $destination }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Description -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p class="mt-2">{{ $package->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>

                    @if($package->detailed_description)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Detailed Description:</strong>
                            <p class="mt-2">{{ $package->detailed_description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Inclusions -->
                    @if($package->inclusions)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Inclusions:</strong>
                            <ul class="mt-2">
                                @foreach($package->inclusions as $inclusion)
                                    <li>{{ $inclusion }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <!-- Exclusions -->
                    @if($package->exclusions)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Exclusions:</strong>
                            <ul class="mt-2">
                                @foreach($package->exclusions as $exclusion)
                                    <li>{{ $exclusion }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <!-- Features -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Package Features:</strong>
                            <div class="mt-2">
                                @if(!empty($package->includes_flights))
                                    <span class="badge badge-info mr-1">Flights Included</span>
                                @endif
                                @if(!empty($package->includes_accommodation))
                                    <span class="badge badge-info mr-1">Accommodation</span>
                                @endif
                                @if(!empty($package->includes_meals))
                                    <span class="badge badge-info mr-1">Meals</span>
                                @endif
                                @if(!empty($package->includes_transport))
                                    <span class="badge badge-info mr-1">Transportation</span>
                                @endif
                                @if(!empty($package->includes_guide))
                                    <span class="badge badge-info mr-1">Guide</span>
                                @endif
                                @if(!empty($package->free_cancellation))
                                    <span class="badge badge-success mr-1">Free Cancellation</span>
                                @endif
                                @if(!empty($package->instant_booking))
                                    <span class="badge badge-success mr-1">Instant Booking</span>
                                @endif
                                @if(empty($package->includes_flights) && empty($package->includes_accommodation) && empty($package->includes_meals) && empty($package->includes_transport) && empty($package->includes_guide) && empty($package->free_cancellation) && empty($package->instant_booking))
                                    <span class="text-muted">No special features configured</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Created:</strong> {{ $package->created_at->format('M d, Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Last Updated:</strong> {{ $package->updated_at->format('M d, Y H:i') }}
                        </div>
                    </div>

                    @if($package->approval_status === 'rejected' && $package->rejection_reason)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Rejection Reason:</strong>
                            <div class="alert alert-danger mt-2">
                                {{ $package->rejection_reason }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    @if($package->approval_status === 'pending')
                        <form method="POST" action="{{ route('admin.packages.approve', $package->id) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Are you sure you want to approve this package?')">
                                <i class="fas fa-check mr-2"></i>Approve Package
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-block" onclick="showRejectModal({{ $package->id }}, '{{ addslashes($package->name) }}')">
                            <i class="fas fa-times mr-2"></i>Reject Package
                        </button>

                        <hr>
                    @elseif($package->approval_status === 'approved')
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">This package has been approved</p>
                            @if($package->approved_at)
                                <small>Approved on {{ $package->approved_at->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @elseif($package->approval_status === 'rejected')
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-times-circle fa-2x mb-2"></i>
                            <p class="mb-0">This package has been rejected</p>
                        </div>
                        
                        <form method="POST" action="{{ route('admin.packages.approve', $package->id) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-block" onclick="return confirm('Are you sure you want to re-approve this package?')">
                                <i class="fas fa-redo mr-2"></i>Re-approve Package
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.packages') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Packages
                    </a>
                    
                    @if($package->approval_status === 'approved')
                        <hr>
                        <a href="{{ route('packages.details', $package->slug ?: $package->id) }}" class="btn btn-info btn-block" target="_blank">
                            <i class="fas fa-external-link-alt mr-2"></i>View on B2C Site
                        </a>
                    @endif
                </div>
            </div>

            <!-- Package Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ $package->views_count ?? 0 }}</h5>
                                <span class="description-text">Views</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header">{{ $package->bookings_count ?? 0 }}</h5>
                                <span class="description-text">Bookings</span>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ number_format($package->average_rating ?? 0, 1) }}</h5>
                                <span class="description-text">Rating</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header">{{ $package->reviews_count ?? 0 }}</h5>
                                <span class="description-text">Reviews</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
// Function to show rejection modal
function showRejectModal(packageId, packageName) {
    $('#packageNameToReject').text(packageName);
    $('#rejectPackageForm').attr('action', '/admin/packages/' + packageId + '/reject');
    $('#rejectionReason').val('');
    $('#rejectPackageModal').modal('show');
}
</script>
@endsection