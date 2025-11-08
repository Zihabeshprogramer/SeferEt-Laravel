@extends('layouts.b2b')

@section('title', 'View Ad')
@section('page-title', $ad->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('b2b.ads.index') }}">My Ads</a></li>
    <li class="breadcrumb-item active">{{ $ad->title }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Left Column - Ad Details -->
        <div class="col-md-8">
            <!-- Status Card -->
            <div class="card">
                <div class="card-header bg-{{ $ad->status_badge }}">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Status: {{ ucfirst($ad->status) }}
                    </h3>
                    <div class="card-tools">
                        @if($ad->isDraft() || $ad->isRejected())
                            <a href="{{ route('b2b.ads.edit', $ad) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        @if($ad->isPending())
                            <button type="button" class="btn btn-secondary btn-sm" id="withdraw-btn">
                                <i class="fas fa-undo"></i> Withdraw
                            </button>
                        @endif
                        @if($ad->isApproved())
                            <button type="button" class="btn btn-{{ $ad->is_active ? 'secondary' : 'success' }} btn-sm" id="toggle-active-btn">
                                <i class="fas fa-toggle-{{ $ad->is_active ? 'off' : 'on' }}"></i> 
                                {{ $ad->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($ad->isRejected() && $ad->rejection_reason)
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Rejection Reason</h5>
                            <p class="mb-0">{{ $ad->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($ad->isPending())
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Your ad is pending admin approval. You will be notified once reviewed.
                        </div>
                    @endif

                    <!-- Ad Preview -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block" style="max-width: 100%;">
                            @if($ad->hasImage())
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="img-fluid border">
                                <div class="position-absolute" style="top: {{ $ad->cta_position_y ?? 50 }}%; left: {{ $ad->cta_position_x ?? 50 }}%; transform: translate(-50%, -50%);">
                                    <button type="button" class="btn btn-{{ $ad->cta_style ?? 'primary' }} btn-sm">
                                        {{ $ad->cta_text ?? 'Book Now' }}
                                    </button>
                                </div>
                            @else
                                <div class="bg-secondary text-white p-5">
                                    <i class="fas fa-image fa-3x"></i>
                                    <p class="mt-2">No image uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ad Information -->
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">Title</th>
                                <td>{{ $ad->title }}</td>
                            </tr>
                            @if($ad->description)
                            <tr>
                                <th>Description</th>
                                <td>{{ $ad->description }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Linked Product</th>
                                <td>
                                    @if($ad->product)
                                        @if($ad->product_type === 'flight')
                                            <strong>{{ $ad->product->airline }} {{ $ad->product->flight_number }}</strong><br>
                                        @else
                                            <strong>{{ $ad->product->name ?? $ad->product->title ?? 'N/A' }}</strong><br>
                                        @endif
                                        <span class="badge badge-info">{{ ucfirst($ad->product_type) }}</span>
                                    @else
                                        <span class="text-muted">No product linked</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>CTA Button</th>
                                <td>
                                    Text: <strong>{{ $ad->cta_text ?? 'Book Now' }}</strong><br>
                                    Style: <span class="badge badge-{{ $ad->cta_style ?? 'primary' }}">{{ ucfirst($ad->cta_style ?? 'primary') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Schedule</th>
                                <td>
                                    @if($ad->start_at || $ad->end_at)
                                        <strong>Start:</strong> {{ $ad->start_at ? $ad->start_at->format('M d, Y') : 'Immediately' }}<br>
                                        <strong>End:</strong> {{ $ad->end_at ? $ad->end_at->format('M d, Y') : 'No end date' }}
                                    @else
                                        Always active (no schedule)
                                    @endif
                                </td>
                            </tr>
                            @if($ad->isApproved())
                            <tr>
                                <th>Performance</th>
                                <td>
                                    <strong>Impressions:</strong> {{ number_format($ad->impressions_count) }}<br>
                                    <strong>Clicks:</strong> {{ number_format($ad->clicks_count) }}<br>
                                    <strong>CTR:</strong> {{ $ad->impressions_count > 0 ? number_format($ad->ctr, 2) : '0.00' }}%
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th>Created</th>
                                <td>{{ $ad->created_at->format('M d, Y \a\t H:i') }}</td>
                            </tr>
                            @if($ad->approved_at && $ad->approver)
                            <tr>
                                <th>{{ $ad->isApproved() ? 'Approved' : 'Reviewed' }}</th>
                                <td>
                                    {{ $ad->approved_at->format('M d, Y \a\t H:i') }}<br>
                                    <small class="text-muted">by {{ $ad->approver->name }}</small>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Audit Log (if available) -->
            @if($ad->auditLogs->count() > 0)
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
                                <th>Date</th>
                                <th>Event</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ad->auditLogs as $log)
                            <tr>
                                <td><small>{{ $log->created_at->format('M d, Y H:i') }}</small></td>
                                <td><span class="badge badge-secondary">{{ $log->event_type }}</span></td>
                                <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Actions & Info -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('b2b.ads.index') }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-arrow-left"></i> Back to My Ads
                        </a>

                        @if($ad->isDraft() || $ad->isRejected())
                            <a href="{{ route('b2b.ads.edit', $ad) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Edit Ad
                            </a>
                            
                            @if($ad->isDraft())
                            <button type="button" class="btn btn-success btn-block" id="submit-btn">
                                <i class="fas fa-paper-plane"></i> Submit for Approval
                            </button>
                            @endif

                            <button type="button" class="btn btn-danger btn-block" id="delete-btn">
                                <i class="fas fa-trash"></i> Delete Ad
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Info -->
            <div class="card card-{{ $ad->status_badge }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Status Information
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Current Status -->
                    <div class="mb-3">
                        <strong class="d-block mb-1"><i class="fas fa-flag"></i> Current Status</strong>
                        <span class="badge badge-{{ $ad->status_badge }} badge-lg" style="font-size: 14px; padding: 8px 12px;">
                            {{ ucfirst($ad->status) }}
                        </span>
                        <p class="text-muted small mb-0 mt-1">
                            @if($ad->isDraft())
                                Your ad is saved as draft. Submit it for admin review when ready.
                            @elseif($ad->isPending())
                                Your ad is awaiting admin approval. You'll be notified once reviewed.
                            @elseif($ad->isApproved())
                                Your ad has been approved and can be activated.
                            @elseif($ad->isRejected())
                                Your ad was rejected. Review the feedback and resubmit after making changes.
                            @endif
                        </p>
                    </div>

                    @if($ad->isApproved())
                    <hr>
                    <!-- Active Status -->
                    <div class="mb-3">
                        <strong class="d-block mb-1">
                            <i class="fas fa-toggle-{{ $ad->is_active ? 'on' : 'off' }}"></i> Active Status
                        </strong>
                        @if($ad->is_active)
                            <span class="badge badge-success badge-lg" style="font-size: 14px; padding: 8px 12px;">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                            <p class="text-muted small mb-0 mt-1">Your ad is currently being displayed to users.</p>
                        @else
                            <span class="badge badge-secondary badge-lg" style="font-size: 14px; padding: 8px 12px;">
                                <i class="fas fa-pause-circle"></i> Inactive
                            </span>
                            <p class="text-muted small mb-0 mt-1">Your ad is paused and not being shown to users.</p>
                        @endif
                    </div>

                    @if($ad->isExpired())
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Expired</strong><br>
                        <small>This ad's end date has passed.</small>
                    </div>
                    @endif

                    <!-- Performance Stats -->
                    @if($ad->impressions_count > 0 || $ad->clicks_count > 0)
                    <hr>
                    <div class="mb-3">
                        <strong class="d-block mb-2"><i class="fas fa-chart-line"></i> Performance</strong>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="info-box-content">
                                    <span class="info-box-number text-info">{{ number_format($ad->impressions_count) }}</span>
                                    <span class="info-box-text small">Impressions</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box-content">
                                    <span class="info-box-number text-success">{{ number_format($ad->clicks_count) }}</span>
                                    <span class="info-box-text small">Clicks</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box-content">
                                    <span class="info-box-number text-warning">{{ $ad->impressions_count > 0 ? number_format($ad->ctr, 2) : '0.00' }}%</span>
                                    <span class="info-box-text small">CTR</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif

                    <hr>
                    <!-- Available Actions -->
                    <div>
                        <strong class="d-block mb-2"><i class="fas fa-tasks"></i> Available Actions</strong>
                        <ul class="list-unstyled mb-0">
                            @if($ad->isDraft())
                                <li><i class="fas fa-check text-success"></i> Edit ad details</li>
                                <li><i class="fas fa-check text-success"></i> Submit for approval</li>
                                <li><i class="fas fa-check text-success"></i> Delete ad</li>
                            @elseif($ad->isPending())
                                <li><i class="fas fa-check text-success"></i> Withdraw from review</li>
                                <li class="text-muted"><i class="fas fa-times"></i> Cannot edit while pending</li>
                            @elseif($ad->isRejected())
                                <li><i class="fas fa-check text-success"></i> Edit and improve</li>
                                <li><i class="fas fa-check text-success"></i> Resubmit for review</li>
                                <li><i class="fas fa-check text-success"></i> Delete ad</li>
                            @elseif($ad->isApproved())
                                <li><i class="fas fa-check text-success"></i> Activate/Deactivate</li>
                                <li class="text-muted"><i class="fas fa-times"></i> Cannot edit when approved</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Submit for approval
            $('#submit-btn').on('click', function() {
                if (confirm('Submit this ad for admin approval?')) {
                    submitAd();
                }
            });

            // Withdraw from approval
            $('#withdraw-btn').on('click', function() {
                if (confirm('Withdraw this ad from approval? It will return to draft status.')) {
                    withdrawAd();
                }
            });

            // Toggle active status
            $('#toggle-active-btn').on('click', function() {
                toggleActive();
            });

            // Delete ad
            $('#delete-btn').on('click', function() {
                if (confirm('Are you sure you want to delete this ad? This action cannot be undone.')) {
                    deleteAd();
                }
            });

            function submitAd() {
                $.ajax({
                    url: '{{ route('b2b.ads.submit-for-approval', $ad) }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Ad submitted for approval successfully');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to submit ad for approval');
                    }
                });
            }

            function withdrawAd() {
                $.ajax({
                    url: '{{ route('b2b.ads.update', $ad) }}',
                    type: 'POST',
                    data: {
                        _method: 'PUT',
                        status: 'draft'
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Ad withdrawn successfully');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to withdraw ad');
                    }
                });
            }

            function toggleActive() {
                $.ajax({
                    url: '{{ route('b2b.ads.toggle-active', $ad) }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Status updated successfully');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update status');
                    }
                });
            }

            function deleteAd() {
                $.ajax({
                    url: '{{ route('b2b.ads.destroy', $ad) }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Ad deleted successfully');
                        setTimeout(function() {
                            window.location.href = '{{ route('b2b.ads.index') }}';
                        }, 1000);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete ad');
                    }
                });
            }
        });
    </script>
@endsection
