@extends('layouts.admin')

@section('title', 'Ad Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ad Management</h1>
        <div>
            <a href="{{ route('admin.ads.pending') }}" class="btn btn-warning">
                <i class="fas fa-clock"></i> Pending Approvals 
                @if($stats['pending'] > 0)
                    <span class="badge badge-light">{{ $stats['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.ads.analytics.index') }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Ads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ad"></i>
                </div>
                <a href="{{ route('admin.ads.index') }}" class="small-box-footer">View all <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['pending']) }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.ads.index', ['status' => 'pending']) }}" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['approved']) }}</h3>
                    <p>Approved</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.ads.index', ['status' => 'approved']) }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['active']) }}</h3>
                    <p>Active</p>
                </div>
                <div class="icon">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <a href="{{ route('admin.ads.index', ['is_active' => '1']) }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['rejected']) }}</h3>
                    <p>Rejected</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="{{ route('admin.ads.index', ['status' => 'rejected']) }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['draft'] ?? 0 }}</h3>
                    <p>Draft</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('admin.ads.index', ['status' => 'draft']) }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-primary card-outline collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
            <div class="card-tools">
                @if(request()->hasAny(['status', 'placement', 'device_type', 'search', 'is_active', 'sort']))
                    <span class="badge badge-primary">{{ count(array_filter(request()->only(['status', 'placement', 'device_type', 'search', 'is_active', 'sort']))) }} active</span>
                @endif
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ads.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Active Status</label>
                            <select name="is_active" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active Only</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Placement</label>
                            <select name="placement" class="form-control form-control-sm">
                                <option value="">All Placements</option>
                                <option value="home_top" {{ request('placement') == 'home_top' ? 'selected' : '' }}>Home Top</option>
                                <option value="home_middle" {{ request('placement') == 'home_middle' ? 'selected' : '' }}>Home Middle</option>
                                <option value="package_details" {{ request('placement') == 'package_details' ? 'selected' : '' }}>Package Details</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Device Type</label>
                            <select name="device_type" class="form-control form-control-sm">
                                <option value="">All Devices</option>
                                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                                <option value="all" {{ request('device_type') == 'all' ? 'selected' : '' }}>All Devices</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Sort By</label>
                            <select name="sort" class="form-control form-control-sm">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>Priority</option>
                                <option value="impressions" {{ request('sort') == 'impressions' ? 'selected' : '' }}>Most Impressions</option>
                                <option value="clicks" {{ request('sort') == 'clicks' ? 'selected' : '' }}>Most Clicks</option>
                                <option value="ctr" {{ request('sort') == 'ctr' ? 'selected' : '' }}>Best CTR</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Title, description, owner" value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.ads.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-undo"></i> Clear
                        </a>
                    </div>
                    <div class="text-muted small">
                        Showing {{ $ads->firstItem() ?? 0 }} to {{ $ads->lastItem() ?? 0 }} of {{ $ads->total() }} results
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ads Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> All Advertisements
                <span class="badge badge-secondary">{{ $ads->total() }}</span>
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="quickSearch" class="form-control float-right" placeholder="Quick search...">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 800px;">
            <table class="table table-hover text-nowrap table-striped table-sm" id="adsTable">
                <thead class="sticky-top bg-light">
                    <tr>
                        <th style="width: 40px;">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="selectAll">
                                <label class="custom-control-label" for="selectAll"></label>
                            </div>
                        </th>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 80px;">Preview</th>
                        <th>Title & Description</th>
                        <th style="width: 150px;">Owner</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 120px;">Placement</th>
                        <th style="width: 120px;">Performance</th>
                        <th style="width: 100px;">Schedule</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ads as $ad)
                    <tr class="ad-row" data-ad-id="{{ $ad->id }}" data-ad-title="{{ strtolower($ad->title) }}" data-ad-owner="{{ strtolower($ad->owner->name ?? '') }}">
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input ad-checkbox" id="ad_{{ $ad->id }}" value="{{ $ad->id }}">
                                <label class="custom-control-label" for="ad_{{ $ad->id }}"></label>
                            </div>
                        </td>
                        <td><small class="text-muted">#{{ $ad->id }}</small></td>
                        <td>
                            @if($ad->image_path)
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" 
                                     class="img-thumbnail" 
                                     style="width: 70px; height: 50px; object-fit: cover; cursor: pointer;" 
                                     onclick="showImagePreview('{{ $ad->image_url }}', '{{ $ad->title }}')">
                            @else
                                <div class="bg-light border text-center" style="width: 70px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $ad->title }}</strong>
                                @if($ad->priority && $ad->priority > 5)
                                    <span class="badge badge-warning badge-sm ml-1" title="High Priority">P{{ $ad->priority }}</span>
                                @endif
                            </div>
                            <small class="text-muted d-block" style="max-width: 300px; white-space: normal;">
                                {{ Str::limit($ad->description, 60) }}
                            </small>
                            <small class="text-muted">
                                <i class="far fa-calendar"></i> {{ $ad->created_at->diffForHumans() }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($ad->owner)
                                    <div>
                                        <div><strong>{{ $ad->owner->name }}</strong></div>
                                        <small class="text-muted">{{ Str::limit($ad->owner->email, 20) }}</small>
                                        @if($ad->is_local_owner ?? false)
                                            <br><span class="badge badge-info badge-sm">Local</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                @if($ad->status == 'draft')
                                    <span class="badge badge-secondary"><i class="fas fa-file-alt"></i> Draft</span>
                                @elseif($ad->status == 'pending')
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                @elseif($ad->status == 'approved')
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>
                                @elseif($ad->status == 'rejected')
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Rejected</span>
                                @endif
                            </div>
                            @if($ad->status == 'approved')
                                <div class="mt-1">
                                    @if($ad->is_active)
                                        <button type="button" class="btn btn-xs btn-success" onclick="toggleActive({{ $ad->id }})" title="Active - Click to Deactivate">
                                            <i class="fas fa-toggle-on"></i> Active
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-xs btn-secondary" onclick="toggleActive({{ $ad->id }})" title="Inactive - Click to Activate">
                                            <i class="fas fa-toggle-off"></i> Inactive
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>
                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $ad->placement ?? 'any')) }}</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-{{ $ad->device_type == 'mobile' ? 'mobile-alt' : ($ad->device_type == 'tablet' ? 'tablet-alt' : ($ad->device_type == 'desktop' ? 'desktop' : 'devices')) }}"></i>
                                {{ ucfirst($ad->device_type ?? 'all') }}
                            </small>
                        </td>
                        <td>
                            <div class="progress-group">
                                <div class="d-flex justify-content-between mb-1">
                                    <small><i class="fas fa-eye text-info"></i> {{ number_format($ad->impressions_count) }}</small>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small><i class="fas fa-mouse-pointer text-success"></i> {{ number_format($ad->clicks_count) }}</small>
                                </div>
                                <div class="progress progress-xs">
                                    <div class="progress-bar {{ $ad->ctr >= 5 ? 'bg-success' : ($ad->ctr >= 2 ? 'bg-warning' : 'bg-danger') }}" 
                                         style="width: {{ min($ad->ctr * 10, 100) }}%" 
                                         title="CTR: {{ number_format($ad->ctr, 2) }}%"></div>
                                </div>
                                <small class="text-muted">CTR: {{ number_format($ad->ctr, 2) }}%</small>
                            </div>
                        </td>
                        <td>
                            @php
                                $now = now();
                                $isScheduled = $ad->start_at || $ad->end_at;
                                $isActive = (!$ad->start_at || $ad->start_at <= $now) && (!$ad->end_at || $ad->end_at >= $now);
                                $isPast = $ad->end_at && $ad->end_at < $now;
                                $isFuture = $ad->start_at && $ad->start_at > $now;
                            @endphp
                            <small>
                                @if($isScheduled)
                                    @if($isPast)
                                        <span class="badge badge-secondary" title="Ended"><i class="fas fa-stop-circle"></i> Ended</span>
                                    @elseif($isFuture)
                                        <span class="badge badge-info" title="Scheduled"><i class="fas fa-clock"></i> Scheduled</span>
                                    @else
                                        <span class="badge badge-success" title="Running"><i class="fas fa-play-circle"></i> Running</span>
                                    @endif
                                    <div class="mt-1">
                                        <div>{{ $ad->start_at ? $ad->start_at->format('M d, Y') : 'Now' }}</div>
                                        <div class="text-muted">to {{ $ad->end_at ? $ad->end_at->format('M d, Y') : '∞' }}</div>
                                    </div>
                                @else
                                    <span class="badge badge-light"><i class="fas fa-infinity"></i> Always</span>
                                @endif
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.ads.show', $ad->id) }}" class="btn btn-info btn-sm" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown" title="More Actions">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" role="menu">
                                    <a class="dropdown-item" href="{{ route('admin.ads.show', $ad->id) }}">
                                        <i class="fas fa-eye text-info"></i> View Details
                                    </a>
                                    @if($ad->status == 'pending')
                                        <a class="dropdown-item" href="#" onclick="approveAd({{ $ad->id }}); return false;">
                                            <i class="fas fa-check text-success"></i> Approve
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="rejectAd({{ $ad->id }}); return false;">
                                            <i class="fas fa-times text-danger"></i> Reject
                                        </a>
                                    @endif
                                    @if($ad->status == 'approved')
                                        <a class="dropdown-item" href="#" onclick="toggleActive({{ $ad->id }}); return false;">
                                            <i class="fas fa-toggle-on text-primary"></i> Toggle Active
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="editPriority({{ $ad->id }}, {{ $ad->priority ?? 5 }}); return false;">
                                            <i class="fas fa-sort-amount-up text-warning"></i> Set Priority
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="editSchedule({{ $ad->id }}); return false;">
                                            <i class="fas fa-calendar text-info"></i> Edit Schedule
                                        </a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('admin.ads.analytics.show', $ad->id) }}">
                                        <i class="fas fa-chart-line text-primary"></i> View Analytics
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No advertisements found</h5>
                            <p class="text-muted">Try adjusting your filters or search criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div id="bulkActions" class="btn-group" style="display: none;">
                        <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                            <i class="fas fa-check"></i> Approve Selected
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkReject()">
                            <i class="fas fa-times"></i> Reject Selected
                        </button>
                    </div>
                    <small class="text-muted" id="selectedCount"></small>
                </div>
                <div>
                    {{ $ads->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewTitle">Image Preview</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="approveForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Advertisement</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this advertisement?</p>
                    <div class="form-group">
                        <label>Priority (1-10)</label>
                        <input type="number" name="priority" class="form-control" min="1" max="10" value="5">
                    </div>
                    <div class="form-group">
                        <label>Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2"></textarea>
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="rejectForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Advertisement</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
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

@section('css')
<style>
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    .progress-xs {
        height: 5px;
    }
    .ad-row:hover {
        background-color: #f8f9fa;
    }
    .collapsed-card .card-body {
        display: none;
    }
</style>
@stop

@section('js')
<script>
// Ad Management Functions
function approveAd(id) {
    $('#approveForm').attr('action', '/admin/ads/' + id + '/approve');
    $('#approveModal').modal('show');
}

function rejectAd(id) {
    $('#rejectForm').attr('action', '/admin/ads/' + id + '/reject');
    $('#rejectModal').modal('show');
}

function toggleActive(id) {
    if(confirm('Are you sure you want to toggle the active status of this ad?')) {
        $.ajax({
            url: '/admin/ads/' + id + '/toggle-active',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error toggling ad status: ' + xhr.responseJSON.message);
            }
        });
    }
}

function editPriority(id, currentPriority) {
    const priority = prompt('Enter new priority (1-10):', currentPriority);
    if(priority !== null && priority >= 1 && priority <= 10) {
        $.ajax({
            url: '/admin/ads/' + id + '/priority',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                priority: priority
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error updating priority: ' + xhr.responseJSON.message);
            }
        });
    }
}

function editSchedule(id) {
    // Could open a modal for more complex scheduling
    window.location.href = '/admin/ads/' + id;
}

function showImagePreview(url, title) {
    $('#previewImage').attr('src', url);
    $('#imagePreviewTitle').text(title);
    $('#imagePreviewModal').modal('show');
}

// Bulk Actions
function getSelectedIds() {
    const ids = [];
    $('.ad-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function bulkApprove() {
    const ids = getSelectedIds();
    if(ids.length === 0) {
        alert('Please select at least one ad to approve.');
        return;
    }
    
    if(confirm('Are you sure you want to approve ' + ids.length + ' ad(s)?')) {
        $.ajax({
            url: '/admin/ads/bulk-approve',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ad_ids: ids
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error approving ads: ' + xhr.responseJSON.message);
            }
        });
    }
}

function bulkReject() {
    const ids = getSelectedIds();
    if(ids.length === 0) {
        alert('Please select at least one ad to reject.');
        return;
    }
    
    const reason = prompt('Enter rejection reason for ' + ids.length + ' ad(s):');
    if(reason) {
        $.ajax({
            url: '/admin/ads/bulk-reject',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ad_ids: ids,
                reason: reason
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error rejecting ads: ' + xhr.responseJSON.message);
            }
        });
    }
}

// Quick Search
$('#quickSearch').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('#adsTable tbody tr.ad-row').filter(function() {
        const title = $(this).data('ad-title');
        const owner = $(this).data('ad-owner');
        $(this).toggle(title.indexOf(value) > -1 || owner.indexOf(value) > -1);
    });
});

// Select All Checkbox
$('#selectAll').on('change', function() {
    $('.ad-checkbox').prop('checked', $(this).prop('checked'));
    updateBulkActions();
});

$('.ad-checkbox').on('change', function() {
    updateBulkActions();
    // Update select all checkbox
    const total = $('.ad-checkbox').length;
    const checked = $('.ad-checkbox:checked').length;
    $('#selectAll').prop('checked', total === checked);
});

function updateBulkActions() {
    const selected = $('.ad-checkbox:checked').length;
    if(selected > 0) {
        $('#bulkActions').show();
        $('#selectedCount').text(selected + ' ad(s) selected');
    } else {
        $('#bulkActions').hide();
        $('#selectedCount').text('');
    }
}

// Auto-submit filters on change (optional)
$('#filterForm select').on('change', function() {
    // Uncomment to auto-submit:
    // $('#filterForm').submit();
});

// Initialize on page load
$(document).ready(function() {
    updateBulkActions();
    
    // Show filter card if filters are active
    @if(request()->hasAny(['status', 'placement', 'device_type', 'search', 'is_active', 'sort']))
        $('[data-card-widget="collapse"]').trigger('click');
    @endif
});
</script>
@stop
