@extends('layouts.admin')

@section('title', 'Partner Management')
@section('page-title', 'Partner Management')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Partner Management</li>
@endsection

@section('content')
    <!-- Stats Overview -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Partners</p>
                </div>
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <a href="#" class="small-box-footer">
                    All Partners <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Active Partners</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="#" onclick="filterByStatus('active')" class="small-box-footer">
                    View Active <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending Approval</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="#" onclick="filterByStatus('pending')" class="small-box-footer">
                    Review Pending <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['suspended'] }}</h3>
                    <p>Suspended</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
                <a href="#" onclick="filterByStatus('suspended')" class="small-box-footer">
                    View Suspended <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Partner Type Distribution -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-plane"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Travel Agents</span>
                    <span class="info-box-number">{{ $stats['travel_agents'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-hotel"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Hotel Providers</span>
                    <span class="info-box-number">{{ $stats['hotel_providers'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-dark"><i class="fas fa-bus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Transport Providers</span>
                    <span class="info-box-number">{{ $stats['transport_providers'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Partner Management Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-handshake mr-2"></i>
                Partner Management
            </h3>
            <div class="card-tools">
                <div class="btn-group">
                    <a href="{{ route('admin.partners.business-overview') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-chart-bar mr-1"></i> Business Overview
                    </a>
                    <a href="{{ route('admin.partners.export') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download mr-1"></i> Export
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Partner Type</label>
                    <select id="partner-type-filter" class="form-control">
                        <option value="">All Types</option>
                        @foreach($partnerTypes as $key => $type)
                            <option value="{{ $key }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select id="status-filter" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Approval Status</label>
                    <select id="approval-status-filter" class="form-control">
                        <option value="">All</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div class="form-group">
                        <button type="button" id="clear-filters" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Partners DataTable -->
            <div class="table-responsive">
                <table id="partners-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Business Info</th>
                            <th>Business Stats</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
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
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<!-- Select2 is already included in base layout -->

<script>
$(document).ready(function() {
    // Initialize Select2 on filter dropdowns using global function
    if (typeof window.initializeSelect2 === 'function') {
        window.initializeSelect2('#partner-type-filter, #status-filter, #approval-status-filter');
    } else {
        console.error('Global Select2 initialization function not available');
    }
    
    var table = $('#partners-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.partners.management') }}",
            data: function(d) {
                d.partner_type = $('#partner-type-filter').val();
                d.status = $('#status-filter').val();
                d.approval_status = $('#approval-status-filter').val();
            }
        },
        columns: [
            {data: 'name', name: 'name'},
            {data: 'partner_type', name: 'partner_type', orderable: false},
            {data: 'business_info', name: 'business_info', orderable: false},
            {data: 'business_stats', name: 'business_stats', orderable: false},
            {data: 'status_badge', name: 'status_badge', orderable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Filter handlers
    $('#partner-type-filter, #status-filter, #approval-status-filter').change(function() {
        table.draw();
    });

    $('#clear-filters').click(function() {
        $('#partner-type-filter, #status-filter, #approval-status-filter').val('');
        table.draw();
    });

    // Global variables for modals
    var currentPartnerId = null;

    // Partner action handlers
    window.approvePartner = function(partnerId) {
        if (confirm('Are you sure you want to approve this partner?')) {
            $.post("{{ route('admin.partners.approve', ':id') }}".replace(':id', partnerId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    table.draw(false);
                    showAlert('success', response.message);
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
        currentPartnerId = partnerId;
        $('#rejection-modal').modal('show');
    };

    window.suspendPartner = function(partnerId) {
        currentPartnerId = partnerId;
        $('#suspension-modal').modal('show');
    };

    window.reactivatePartner = function(partnerId) {
        if (confirm('Are you sure you want to reactivate this partner?')) {
            $.post("{{ route('admin.partners.reactivate', ':id') }}".replace(':id', partnerId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    table.draw(false);
                    showAlert('success', response.message);
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
        $.post("{{ route('admin.partners.reject', ':id') }}".replace(':id', currentPartnerId), $(this).serialize())
        .done(function(response) {
            if (response.success) {
                $('#rejection-modal').modal('hide');
                table.draw(false);
                showAlert('success', response.message);
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
        $.post("{{ route('admin.partners.suspend', ':id') }}".replace(':id', currentPartnerId), $(this).serialize())
        .done(function(response) {
            if (response.success) {
                $('#suspension-modal').modal('hide');
                table.draw(false);
                showAlert('success', response.message);
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

    window.filterByStatus = function(status) {
        if (status === 'pending') {
            $('#approval-status-filter').val('pending');
        } else {
            $('#status-filter').val(status);
        }
        table.draw();
    };
});
</script>
@endpush

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">
<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2-bootstrap4.min.css">

<style>
.info-box {
    cursor: pointer;
}

.info-box:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transform: translateY(-1px);
    transition: all 0.2s;
}

#partners-table th {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.85em;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endpush
