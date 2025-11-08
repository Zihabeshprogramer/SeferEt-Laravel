@extends('layouts.admin')

@section('title', 'Pending Ads - Approval Queue')

@section('content_header')
    <h1>Pending Ads - Approval Queue</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Total Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['today'] }}</h3>
                    <p>Submitted Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $ads->count() }}</h3>
                    <p>On This Page</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
    </div>

    @if($ads->count() > 0)
    <!-- Bulk Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bulk Actions</h3>
        </div>
        <div class="card-body">
            <form method="POST" id="bulkActionForm">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                        <span id="selectedCount" class="text-muted ml-3">0 selected</span>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()" disabled id="bulkApproveBtn">
                            <i class="fas fa-check"></i> Approve Selected
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkReject()" disabled id="bulkRejectBtn">
                            <i class="fas fa-times"></i> Reject Selected
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Pending Ads -->
    @foreach($ads as $ad)
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input ad-checkbox" type="checkbox" name="ad_ids[]" value="{{ $ad->id }}" id="ad{{ $ad->id }}">
                        <label class="form-check-label" for="ad{{ $ad->id }}">
                            <strong>{{ $ad->title }}</strong>
                            <span class="badge badge-warning ml-2">Pending</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        Submitted {{ $ad->created_at->diffForHumans() }} by {{ $ad->owner->name ?? 'Unknown' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Ad Preview -->
                <div class="col-md-4">
                    @if($ad->image_path)
                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white text-center rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                            <div>
                                <i class="fas fa-image fa-3x"></i>
                                <p class="mt-2">No Image</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Ad Details -->
                <div class="col-md-8">
                    <h5>{{ $ad->title }}</h5>
                    <p class="text-muted">{{ $ad->description ?? 'No description provided' }}</p>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Owner:</strong> {{ $ad->owner->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $ad->owner->email ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>CTA Text:</strong> {{ $ad->cta_text ?? 'None' }}</p>
                            <p class="mb-1"><strong>CTA Action:</strong> 
                                @if($ad->cta_action)
                                    <a href="{{ $ad->cta_action }}" target="_blank" rel="noopener">
                                        {{ Str::limit($ad->cta_action, 40) }} <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                @else
                                    None
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Placement:</strong> <span class="badge badge-info">{{ $ad->placement ?? 'Any' }}</span></p>
                            <p class="mb-1"><strong>Device:</strong> <span class="badge badge-secondary">{{ $ad->device_type ?? 'All' }}</span></p>
                            <p class="mb-1"><strong>Schedule:</strong> 
                                @if($ad->start_at || $ad->end_at)
                                    {{ $ad->start_at ? $ad->start_at->format('M d, Y') : 'Immediate' }} - 
                                    {{ $ad->end_at ? $ad->end_at->format('M d, Y') : 'No end' }}
                                @else
                                    Always active
                                @endif
                            </p>
                            <p class="mb-1"><strong>Submitted:</strong> {{ $ad->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($ad->product)
                    <div class="alert alert-info mt-2">
                        <strong>Linked Product:</strong> {{ $ad->product->name ?? 'Unknown' }} 
                        <span class="badge badge-primary">{{ class_basename($ad->product_type) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.ads.show', $ad->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-success btn-sm" onclick="quickApprove({{ $ad->id }})">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="quickReject({{ $ad->id }})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $ads->links() }}
    </div>

    @else
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4>No Pending Ads</h4>
            <p class="text-muted">All advertisements have been reviewed. Great job!</p>
            <a href="{{ route('admin.ads.index') }}" class="btn btn-primary">
                View All Ads
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Quick Approve Modal -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="quickApproveForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Quick Approve</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Approve this advertisement?</p>
                    <div class="form-group">
                        <label>Priority (1-10) <span class="text-danger">*</span></label>
                        <input type="number" name="priority" class="form-control" min="1" max="10" value="5" required>
                        <small class="form-text text-muted">Higher priority ads are shown more frequently</small>
                    </div>
                    <div class="form-group">
                        <label>Start Date (Optional)</label>
                        <input type="datetime-local" name="start_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>End Date (Optional)</label>
                        <input type="datetime-local" name="end_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Reject Modal -->
<div class="modal fade" id="quickRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="quickRejectForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Advertisement</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Provide a clear reason for rejection..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal notes..."></textarea>
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
$(document).ready(function() {
    // Select All
    $('#selectAll').change(function() {
        $('.ad-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    // Individual checkbox
    $('.ad-checkbox').change(function() {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.ad-checkbox:checked').length;
        $('#selectedCount').text(count + ' selected');
        $('#bulkApproveBtn, #bulkRejectBtn').prop('disabled', count === 0);
    }
});

function quickApprove(id) {
    $('#quickApproveForm').attr('action', '/admin/ads/' + id + '/approve');
    $('#quickApproveModal').modal('show');
}

function quickReject(id) {
    $('#quickRejectForm').attr('action', '/admin/ads/' + id + '/reject');
    $('#quickRejectModal').modal('show');
}

function bulkApprove() {
    const selected = $('.ad-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (selected.length === 0) {
        alert('Please select at least one ad');
        return;
    }

    if (confirm(`Approve ${selected.length} advertisement(s)?`)) {
        $.post('/admin/ads/bulk-approve', {
            _token: '{{ csrf_token() }}',
            ad_ids: selected
        })
        .done(function(response) {
            location.reload();
        })
        .fail(function(xhr) {
            alert('Failed to approve ads: ' + (xhr.responseJSON?.message || 'Unknown error'));
        });
    }
}

function bulkReject() {
    const selected = $('.ad-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (selected.length === 0) {
        alert('Please select at least one ad');
        return;
    }

    const reason = prompt(`Reject ${selected.length} advertisement(s)? Enter rejection reason:`);
    if (reason) {
        $.post('/admin/ads/bulk-reject', {
            _token: '{{ csrf_token() }}',
            ad_ids: selected,
            reason: reason
        })
        .done(function(response) {
            location.reload();
        })
        .fail(function(xhr) {
            alert('Failed to reject ads: ' + (xhr.responseJSON?.message || 'Unknown error'));
        });
    }
}
</script>
@stop
