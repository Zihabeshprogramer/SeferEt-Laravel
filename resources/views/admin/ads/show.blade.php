@extends('layouts.admin')

@section('title', 'Ad Details - ' . $ad->title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>Ad Details</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.ads.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($ad->isPending())
                <a href="{{ route('admin.ads.pending') }}" class="btn btn-warning">
                    <i class="fas fa-clock"></i> Pending Queue
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Status Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Ad Preview Card -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-eye"></i> Ad Preview
                    </h3>
                </div>
                <div class="card-body">
                    @if($ad->image_path)
                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="img-fluid rounded mb-3" style="max-height: 400px; width: 100%; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white text-center rounded mb-3 d-flex align-items-center justify-content-center" style="height: 300px;">
                            <div>
                                <i class="fas fa-image fa-4x"></i>
                                <p class="mt-3">No Image Uploaded</p>
                            </div>
                        </div>
                    @endif

                    <h3>{{ $ad->title }}</h3>
                    <p class="text-muted">{{ $ad->description }}</p>

                    @if($ad->cta_text)
                        <div class="mt-3">
                            <a href="{{ $ad->cta_action }}" target="_blank" class="btn btn-{{ $ad->cta_style ?? 'primary' }} btn-lg">
                                {{ $ad->cta_text }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ad Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Advertisement Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Title</dt>
                                <dd>{{ $ad->title }}</dd>

                                <dt>Description</dt>
                                <dd>{{ $ad->description ?? 'None' }}</dd>

                                <dt>CTA Text</dt>
                                <dd>{{ $ad->cta_text ?? 'None' }}</dd>

                                <dt>CTA Action</dt>
                                <dd>
                                    @if($ad->cta_action)
                                        <a href="{{ $ad->cta_action }}" target="_blank" rel="noopener">
                                            {{ $ad->cta_action }} <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a>
                                    @else
                                        None
                                    @endif
                                </dd>

                                <dt>CTA Style</dt>
                                <dd><span class="badge badge-{{ $ad->cta_style ?? 'primary' }}">{{ ucfirst($ad->cta_style ?? 'primary') }}</span></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Placement</dt>
                                <dd><span class="badge badge-info">{{ $ad->placement ?? 'Any' }}</span></dd>

                                <dt>Device Type</dt>
                                <dd><span class="badge badge-secondary">{{ $ad->device_type ?? 'All' }}</span></dd>

                                <dt>Priority</dt>
                                <dd>
                                    <span class="badge badge-warning">{{ $ad->priority }}</span>
                                    @can('update', $ad)
                                        <button class="btn btn-xs btn-link" onclick="editPriority()">Edit</button>
                                    @endcan
                                </dd>

                                <dt>Schedule</dt>
                                <dd>
                                    @if($ad->start_at || $ad->end_at)
                                        <strong>Start:</strong> {{ $ad->start_at ? $ad->start_at->format('M d, Y H:i') : 'Immediate' }}<br>
                                        <strong>End:</strong> {{ $ad->end_at ? $ad->end_at->format('M d, Y H:i') : 'No end date' }}
                                    @else
                                        Always active
                                    @endif
                                    @can('update', $ad)
                                        <button class="btn btn-xs btn-link" onclick="editSchedule()">Edit</button>
                                    @endcan
                                </dd>

                                @if($ad->product)
                                    <dt>Linked Product</dt>
                                    <dd>
                                        {{ $ad->product->name ?? 'Unknown' }}
                                        <span class="badge badge-primary">{{ class_basename($ad->product_type) }}</span>
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Performance Metrics
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.ads.analytics', $ad->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-chart-bar"></i> Full Analytics
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Impressions</span>
                                    <span class="info-box-number">{{ number_format($ad->impressions_count) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-mouse-pointer"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Clicks</span>
                                    <span class="info-box-number">{{ number_format($ad->clicks_count) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">CTR</span>
                                    <span class="info-box-number">{{ number_format($ad->ctr, 2) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-chart-area"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Performance</span>
                                    <span class="info-box-number">
                                        @if($ad->ctr >= 5)
                                            <span class="text-success">Excellent</span>
                                        @elseif($ad->ctr >= 2)
                                            <span class="text-info">Good</span>
                                        @else
                                            <span class="text-muted">Average</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Log -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Activity Log
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ad->auditLogs()->latest()->limit(10)->get() as $log)
                            <tr>
                                <td><span class="badge badge-info">{{ $log->event_type }}</span></td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($log->changes)
                                        <small class="text-muted">{{ Str::limit(json_encode($log->changes), 50) }}</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No activity recorded</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card">
                <div class="card-header bg-{{ $ad->status_badge }}">
                    <h3 class="card-title text-white">
                        <i class="fas fa-flag"></i> Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2>
                            <span class="badge badge-{{ $ad->status_badge }} badge-lg">
                                {{ ucfirst($ad->status) }}
                            </span>
                        </h2>
                        @if($ad->isApproved())
                            <div class="mt-2">
                                @if($ad->is_active)
                                    <span class="badge badge-success"><i class="fas fa-toggle-on"></i> Active</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-toggle-off"></i> Inactive</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($ad->isPending())
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Awaiting approval
                        </div>
                        <form method="POST" action="{{ route('admin.ads.approve', $ad->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-block mt-2" onclick="showRejectModal()">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    @endif

                    @if($ad->isApproved())
                        <form method="POST" action="{{ route('admin.ads.toggle-active', $ad->id) }}">
                            @csrf
                            @if($ad->is_active)
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fas fa-pause"></i> Deactivate
                                </button>
                            @else
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-play"></i> Activate
                                </button>
                            @endif
                        </form>
                    @endif

                    @if($ad->isRejected() && $ad->rejection_reason)
                        <div class="alert alert-danger mt-3">
                            <strong>Rejection Reason:</strong><br>
                            {{ $ad->rejection_reason }}
                        </div>
                    @endif

                    @if($ad->admin_notes)
                        <div class="alert alert-info mt-3">
                            <strong>Admin Notes:</strong><br>
                            {{ $ad->admin_notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Owner Info Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Owner Information
                    </h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Name</dt>
                        <dd>{{ $ad->owner->name ?? 'N/A' }}</dd>

                        <dt>Email</dt>
                        <dd>{{ $ad->owner->email ?? 'N/A' }}</dd>

                        <dt>Role</dt>
                        <dd><span class="badge badge-primary">{{ ucfirst($ad->owner->role ?? 'unknown') }}</span></dd>

                        @if($ad->owner->company_name)
                            <dt>Company</dt>
                            <dd>{{ $ad->owner->company_name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Timestamps
                    </h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Created</dt>
                        <dd>{{ $ad->created_at->format('M d, Y H:i') }}</dd>

                        <dt>Updated</dt>
                        <dd>{{ $ad->updated_at->format('M d, Y H:i') }}</dd>

                        @if($ad->approved_at)
                            <dt>Approved</dt>
                            <dd>
                                {{ $ad->approved_at->format('M d, Y H:i') }}<br>
                                <small class="text-muted">by {{ $ad->approver->name ?? 'Unknown' }}</small>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i> Actions
                    </h3>
                </div>
                <div class="card-body">
                    @can('delete', $ad)
                        <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Delete Ad
                        </button>
                        <form id="deleteForm" method="POST" action="{{ route('admin.ads.destroy', $ad->id) }}" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Priority Modal -->
<div class="modal fade" id="priorityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.ads.priority.update', $ad->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Priority</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Priority (1-10)</label>
                        <input type="number" name="priority" class="form-control" min="1" max="10" value="{{ $ad->priority }}" required>
                        <small class="form-text text-muted">Higher priority ads are shown more frequently</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.ads.scheduling.update', $ad->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Start Date & Time</label>
                        <input type="datetime-local" name="start_at" class="form-control" 
                               value="{{ $ad->start_at ? $ad->start_at->format('Y-m-d\TH:i') : '' }}">
                        <small class="form-text text-muted">Leave empty for immediate start</small>
                    </div>
                    <div class="form-group">
                        <label>End Date & Time</label>
                        <input type="datetime-local" name="end_at" class="form-control"
                               value="{{ $ad->end_at ? $ad->end_at->format('Y-m-d\TH:i') : '' }}">
                        <small class="form-text text-muted">Leave empty for no end date</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.ads.reject', $ad->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Advertisement</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function editPriority() {
    $('#priorityModal').modal('show');
}

function editSchedule() {
    $('#scheduleModal').modal('show');
}

function showRejectModal() {
    $('#rejectModal').modal('show');
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this advertisement? This action cannot be undone.')) {
        $('#deleteForm').submit();
    }
}
</script>
@stop
