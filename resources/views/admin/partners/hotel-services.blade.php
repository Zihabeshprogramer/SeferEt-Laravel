@extends('layouts.admin')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Hotel Services Review</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.partners.management') }}">Partner Management</a></li>
                    <li class="breadcrumb-item active">Hotel Services</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-3">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_services'] }}</h3>
                        <p>Total Services</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hotel"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_services'] }}</h3>
                        <p>Pending Review</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['approved_services'] }}</h3>
                        <p>Approved Services</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-hotel mr-2"></i>
                    Hotel Services Management
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()" id="bulkApproveBtn" style="display: none;">
                        <i class="fas fa-check mr-1"></i>
                        Bulk Approve
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="cityFilter" class="form-control" placeholder="Filter by city...">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-info" onclick="refreshTable()">
                            <i class="fas fa-sync mr-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="hotelServicesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Hotel Name</th>
                                <th>Provider</th>
                                <th>Location</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hotel Service Details Modal -->
<div class="modal fade" id="hotelServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Hotel Service Details</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="hotelServiceModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Approve Hotel Service</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="approvalForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approvalNotes">Notes (Optional)</label>
                        <textarea class="form-control" id="approvalNotes" name="notes" rows="3" 
                                  placeholder="Add any notes for this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reject Hotel Service</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="rejectionForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectionReason">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Suspension Modal -->
<div class="modal fade" id="suspensionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Suspend Hotel Service</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="suspensionForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="suspensionReason">Suspension Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="suspensionReason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for suspension..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-pause mr-1"></i>
                        Suspend
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
let hotelServicesTable;
let currentServiceId;

$(document).ready(function() {
    // Initialize DataTable
    hotelServicesTable = $('#hotelServicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.partners.hotel-services") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.city = $('#cityFilter').val();
            }
        },
        columns: [
            { 
                data: 'id', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="service-checkbox" value="${data}">`;
                }
            },
            { data: 'name' },
            { data: 'provider_name' },
            { data: 'location' },
            { data: 'rating', orderable: false, searchable: false },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Filters
    $('#statusFilter').change(function() {
        hotelServicesTable.ajax.reload();
    });
    
    $('#cityFilter').on('keyup', function() {
        hotelServicesTable.ajax.reload();
    });

    // Select all checkbox
    $('#selectAll').change(function() {
        $('.service-checkbox').prop('checked', this.checked);
        toggleBulkActions();
    });

    // Individual checkbox change
    $(document).on('change', '.service-checkbox', function() {
        toggleBulkActions();
    });

    setupFormHandlers();
});

function toggleBulkActions() {
    const checkedBoxes = $('.service-checkbox:checked').length;
    if (checkedBoxes > 0) {
        $('#bulkApproveBtn').show();
    } else {
        $('#bulkApproveBtn').hide();
    }
}

function setupFormHandlers() {
    // Approval form
    $('#approvalForm').submit(function(e) {
        e.preventDefault();
        const notes = $('#approvalNotes').val();
        
        $.ajax({
            url: `/admin/partners/hotel-services/${currentServiceId}/approve`,
            method: 'POST',
            data: { notes: notes, _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#approvalModal').modal('hide');
                showAlert('success', response.message);
                hotelServicesTable.ajax.reload();
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Error occurred');
            }
        });
    });

    // Rejection form
    $('#rejectionForm').submit(function(e) {
        e.preventDefault();
        const reason = $('#rejectionReason').val();
        
        $.ajax({
            url: `/admin/partners/hotel-services/${currentServiceId}/reject`,
            method: 'POST',
            data: { reason: reason, _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#rejectionModal').modal('hide');
                showAlert('success', response.message);
                hotelServicesTable.ajax.reload();
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Error occurred');
            }
        });
    });

    // Suspension form
    $('#suspensionForm').submit(function(e) {
        e.preventDefault();
        const reason = $('#suspensionReason').val();
        
        $.ajax({
            url: `/admin/partners/hotel-services/${currentServiceId}/suspend`,
            method: 'POST',
            data: { reason: reason, _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#suspensionModal').modal('hide');
                showAlert('success', response.message);
                hotelServicesTable.ajax.reload();
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Error occurred');
            }
        });
    });
}

// Action functions
function approveHotelService(id) {
    currentServiceId = id;
    $('#approvalModal').modal('show');
}

function rejectHotelService(id) {
    currentServiceId = id;
    $('#rejectionModal').modal('show');
}

function suspendHotelService(id) {
    currentServiceId = id;
    $('#suspensionModal').modal('show');
}

function viewHotelService(id) {
    $.ajax({
        url: `/admin/partners/hotel-services/${id}`,
        method: 'GET',
        success: function(response) {
            const service = response.data;
            $('#hotelServiceModalBody').html(`
                <div class="row">
                    <div class="col-md-6">
                        <h6>Hotel Information</h6>
                        <p><strong>Name:</strong> ${service.name}</p>
                        <p><strong>Address:</strong> ${service.address || 'N/A'}</p>
                        <p><strong>City:</strong> ${service.city}</p>
                        <p><strong>Country:</strong> ${service.country}</p>
                        <p><strong>Star Rating:</strong> ${getStarRating(service.star_rating)}</p>
                        <p><strong>Status:</strong> <span class="badge badge-${service.is_active ? 'success' : 'warning'}">${service.is_active ? 'Active' : 'Pending'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Provider Information</h6>
                        <p><strong>Provider:</strong> ${service.provider ? service.provider.name : 'Unknown'}</p>
                        <p><strong>Company:</strong> ${service.provider ? (service.provider.company_name || 'N/A') : 'N/A'}</p>
                        <p><strong>Email:</strong> ${service.provider ? service.provider.email : 'N/A'}</p>
                        <p><strong>Created:</strong> ${new Date(service.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
            `);
            $('#hotelServiceModal').modal('show');
        },
        error: function(xhr) {
            showAlert('error', 'Failed to load hotel service details');
        }
    });
}

function bulkApprove() {
    const selectedIds = getSelectedServices();
    if (selectedIds.length === 0) {
        showAlert('warning', 'Please select services first');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selectedIds.length} hotel service(s)?`)) {
        let processed = 0;
        let errors = 0;
        
        selectedIds.forEach(id => {
            $.ajax({
                url: `/admin/partners/hotel-services/${id}/approve`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    processed++;
                    checkBulkComplete();
                },
                error: function() {
                    errors++;
                    checkBulkComplete();
                }
            });
        });
        
        function checkBulkComplete() {
            if (processed + errors === selectedIds.length) {
                if (errors === 0) {
                    showAlert('success', `Successfully approved ${processed} service(s)`);
                } else {
                    showAlert('warning', `Approved ${processed} service(s), ${errors} failed`);
                }
                hotelServicesTable.ajax.reload();
                $('#selectAll').prop('checked', false);
                $('#bulkApproveBtn').hide();
            }
        }
    }
}

function getSelectedServices() {
    return $('.service-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
}

function refreshTable() {
    hotelServicesTable.ajax.reload();
}

function getStarRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= rating ? '★' : '☆';
    }
    return stars + ` (${rating}/5)`;
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const alert = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>`;
    
    $('.content').prepend(alert);
    
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush
