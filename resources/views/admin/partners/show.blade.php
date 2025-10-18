@extends('layouts.admin')

@section('title', 'Partner Details')
@section('page-title', 'Partner Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.partners.management') }}">Partner Management</a></li>
    <li class="breadcrumb-item active">{{ $partner->name }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Partner Info -->
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-user-tie fa-5x text-muted mb-3"></i>
                        <h3 class="profile-username text-center">{{ $partner->name }}</h3>
                        
                        <p class="text-muted text-center">
                            @php
                                $typeMap = [
                                    'partner' => 'Travel Agent',
                                    'hotel_provider' => 'Hotel Provider',
                                    'transport_provider' => 'Transport Provider',
                                ];
                                $type = $typeMap[$partner->role] ?? 'Unknown';
                                $badgeClass = match ($partner->role) {
                                    'partner' => 'info',
                                    'hotel_provider' => 'warning',
                                    'transport_provider' => 'dark',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $badgeClass }} badge-lg">{{ $type }}</span>
                        </p>

                        <p class="text-center">
                            @php
                                $statusClass = match ($partner->status) {
                                    'active' => 'success',
                                    'suspended' => 'danger',
                                    'pending' => 'warning',
                                    default => 'secondary'
                                };
                                $statusText = match ($partner->status) {
                                    'active' => 'Approved & Active',
                                    'suspended' => 'Suspended',
                                    'pending' => 'Pending Approval',
                                    default => ucfirst($partner->status)
                                };
                            @endphp
                            <span class="badge badge-{{ $statusClass }} badge-lg">{{ $statusText }}</span>
                        </p>
                    </div>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $partner->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Company</b> <a class="float-right">{{ $partner->company_name ?: 'N/A' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Joined</b> <a class="float-right">{{ $partner->created_at->format('M d, Y') }}</a>
                        </li>
                        @if($partner->approved_at)
                        <li class="list-group-item">
                            <b>Approved</b> <a class="float-right text-success">{{ $partner->approved_at->format('M d, Y') }}</a>
                        </li>
                        @endif
                        @if($partner->rejected_at ?? false)
                        <li class="list-group-item">
                            <b>Rejected</b> <a class="float-right text-danger">{{ $partner->rejected_at->format('M d, Y') }}</a>
                        </li>
                        @endif
                        @if($partner->suspend_reason ?? false)
                        <li class="list-group-item">
                            <b>Reason</b> <a class="float-right text-danger">{{ $partner->suspend_reason }}</a>
                        </li>
                        @endif
                    </ul>

                    <!-- Action Buttons -->
                    <div class="text-center">
                        @if($partner->status === 'pending')
                            <button class="btn btn-success btn-sm mr-2" onclick="approvePartner({{ $partner->id }})">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="rejectPartner({{ $partner->id }})">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        @elseif($partner->status === 'active')
                            <button class="btn btn-warning btn-sm" onclick="suspendPartner({{ $partner->id }})">
                                <i class="fas fa-pause"></i> Suspend
                            </button>
                        @elseif($partner->status === 'suspended')
                            <button class="btn btn-success btn-sm" onclick="reactivatePartner({{ $partner->id }})">
                                <i class="fas fa-play"></i> Reactivate
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Overview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" href="#overview" data-toggle="tab">Business Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#metrics" data-toggle="tab">Performance Metrics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#activity" data-toggle="tab">Recent Activity</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="active tab-pane" id="overview">
                            <h4 class="mb-3">Business Overview</h4>
                            <div class="row">
                                @foreach($businessData['overview'] as $label => $value)
                                <div class="col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary">
                                            <i class="fas fa-{{ 
                                                str_contains(strtolower($label), 'hotel') ? 'hotel' : 
                                                (str_contains(strtolower($label), 'booking') ? 'calendar-check' : 
                                                (str_contains(strtolower($label), 'revenue') ? 'dollar-sign' : 'chart-bar'))
                                            }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $label }}</span>
                                            <span class="info-box-number">{{ $value }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($partner->role === 'hotel_provider' && isset($businessData['overview']['Total Hotels']) && $businessData['overview']['Total Hotels'] > 0)
                            <div class="mt-4">
                                <h5>Quick Actions</h5>
                                <div class="btn-group">
                                    <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="btn btn-info btn-sm" target="_blank">
                                        <i class="fas fa-hotel mr-1"></i> View Hotels
                                    </a>
                                    <a href="{{ route('b2b.hotel-provider.bookings') }}" class="btn btn-warning btn-sm" target="_blank">
                                        <i class="fas fa-calendar-check mr-1"></i> View Bookings
                                    </a>
                                    <a href="{{ route('b2b.hotel-provider.reports') }}" class="btn btn-success btn-sm" target="_blank">
                                        <i class="fas fa-chart-line mr-1"></i> View Reports
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Metrics Tab -->
                        <div class="tab-pane" id="metrics">
                            <h4 class="mb-3">Performance Metrics</h4>
                            <div class="row">
                                @foreach($businessData['business_metrics'] as $label => $value)
                                <div class="col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-{{ 
                                                str_contains(strtolower($label), 'revenue') ? 'dollar-sign' : 
                                                (str_contains(strtolower($label), 'rating') ? 'star' : 
                                                (str_contains(strtolower($label), 'occupancy') ? 'percentage' : 'chart-line'))
                                            }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $label }}</span>
                                            <span class="info-box-number">{{ $value }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane" id="activity">
                            <h4 class="mb-3">Recent Activity</h4>
                            @if(count($recentActivity) > 0)
                            <div class="timeline">
                                @foreach($recentActivity as $activity)
                                <div class="time-label">
                                    <span class="bg-{{ $activity['color'] }}">{{ \Carbon\Carbon::parse($activity['date'])->format('M d') }}</span>
                                </div>
                                <div>
                                    <i class="{{ $activity['icon'] }} bg-{{ $activity['color'] }}"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($activity['date'])->format('H:i') }}
                                        </span>
                                        <h3 class="timeline-header">{{ $activity['title'] }}</h3>
                                        <div class="timeline-body">
                                            {{ $activity['description'] }}
                                            @if($activity['amount'])
                                                <span class="badge badge-success ml-2">{{ $activity['amount'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <div>
                                    <i class="fas fa-clock bg-gray"></i>
                                </div>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <h5>No Recent Activity</h5>
                                <p>This partner hasn't had any recent business activity.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejection-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reject Partner</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejection-form">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Reason for Rejection</label>
                            <textarea name="reason" class="form-control" rows="4" 
                                      placeholder="Please provide a reason for rejecting this partner..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject Partner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suspension Modal -->
    <div class="modal fade" id="suspension-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Suspend Partner</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="suspension-form">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Reason for Suspension</label>
                            <textarea name="reason" class="form-control" rows="4" 
                                      placeholder="Please provide a reason for suspending this partner..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-pause"></i> Suspend Partner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Partner action handlers
    window.approvePartner = function(partnerId) {
        if (confirm('Are you sure you want to approve this partner?')) {
            $.post("{{ route('admin.partners.approve', ':id') }}".replace(':id', partnerId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('error', response.message);
                }
            })
            .fail(function() {
                showAlert('error', 'Failed to approve partner.');
            });
        }
    };

    window.rejectPartner = function(partnerId) {
        $('#rejection-modal').modal('show');
    };

    window.suspendPartner = function(partnerId) {
        $('#suspension-modal').modal('show');
    };

    window.reactivatePartner = function(partnerId) {
        if (confirm('Are you sure you want to reactivate this partner?')) {
            $.post("{{ route('admin.partners.reactivate', ':id') }}".replace(':id', partnerId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('error', response.message);
                }
            })
            .fail(function() {
                showAlert('error', 'Failed to reactivate partner.');
            });
        }
    };

    // Modal form handlers
    $('#rejection-form').submit(function(e) {
        e.preventDefault();
        $.post("{{ route('admin.partners.reject', $partner->id) }}", $(this).serialize())
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        })
        .fail(function() {
            showAlert('error', 'Failed to reject partner.');
        });
    });

    $('#suspension-form').submit(function(e) {
        e.preventDefault();
        $.post("{{ route('admin.partners.suspend', $partner->id) }}", $(this).serialize())
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                showAlert('error', response.message);
            }
        })
        .fail(function() {
            showAlert('error', 'Failed to suspend partner.');
        });
    });

    // Helper functions
    function showAlert(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                       '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' +
                       message +
                       '<button type="button" class="close" data-dismiss="alert">' +
                       '<span>&times;</span></button></div>';
        $('.content').prepend(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush

@push('styles')
<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.info-box {
    margin-bottom: 15px;
}

.timeline > div > .timeline-item {
    margin-right: 0;
}

.timeline > div > .timeline-item > .time {
    color: #999;
}

.tab-content {
    padding-top: 20px;
}

.profile-username {
    margin-bottom: 10px;
}
</style>
@endpush
