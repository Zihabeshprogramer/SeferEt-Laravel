@extends('layouts.admin')

@section('title', 'Featured Requests Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Featured Requests Management</h1>
        <div>
            <a href="{{ route('admin.featured.featured') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-star"></i> View Featured Products
            </a>
            <a href="{{ route('admin.featured.manual') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Manual Feature
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Featured</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="rejected-count">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Requests</h6>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row g-3">
                <div class="col-md-3">
                    <label for="status-filter" class="form-label">Status</label>
                    <select class="form-control" id="status-filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type-filter" class="form-label">Product Type</label>
                    <select class="form-control" id="type-filter" name="product_type">
                        <option value="">All Types</option>
                        <option value="flight">Flight</option>
                        <option value="hotel">Hotel</option>
                        <option value="package">Package</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search-filter" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search-filter" name="search" placeholder="Search by requester...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Featured Requests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="requests-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Type</th>
                            <th>Product ID</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Date Range</th>
                            <th>Requested Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requests-tbody">
                        <tr>
                            <td colspan="9" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="pagination" class="mt-3"></div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Featured Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="approve-form">
                <div class="modal-body">
                    <input type="hidden" id="approve-request-id">
                    <div class="form-group">
                        <label>Priority Level</label>
                        <input type="number" class="form-control" id="approve-priority" min="1" max="100" value="1">
                        <small class="form-text text-muted">Higher priority = shown first (1-100)</small>
                    </div>
                    <div class="form-group">
                        <label>Start Date (Optional)</label>
                        <input type="date" class="form-control" id="approve-start-date">
                    </div>
                    <div class="form-group">
                        <label>End Date (Optional)</label>
                        <input type="date" class="form-control" id="approve-end-date">
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea class="form-control" id="approve-notes" rows="3"></textarea>
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
            <div class="modal-header">
                <h5 class="modal-title">Reject Featured Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="reject-form">
                <div class="modal-body">
                    <input type="hidden" id="reject-request-id">
                    <div class="form-group">
                        <label>Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject-reason" rows="4" required></textarea>
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

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let filters = {};

    // Load statistics
    loadStatistics();

    // Load requests
    loadRequests();

    // Filter form submission
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        filters = {
            status: $('#status-filter').val(),
            product_type: $('#type-filter').val(),
            search: $('#search-filter').val()
        };
        currentPage = 1;
        loadRequests();
    });

    // Approve modal
    $(document).on('click', '.btn-approve', function() {
        const requestId = $(this).data('id');
        $('#approve-request-id').val(requestId);
        $('#approveModal').modal('show');
    });

    // Reject modal
    $(document).on('click', '.btn-reject', function() {
        const requestId = $(this).data('id');
        $('#reject-request-id').val(requestId);
        $('#rejectModal').modal('show');
    });

    // Approve form submission
    $('#approve-form').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#approve-request-id').val();
        approveRequest(requestId);
    });

    // Reject form submission
    $('#reject-form').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#reject-request-id').val();
        rejectRequest(requestId);
    });

    function loadStatistics() {
        $.ajax({
            url: '/api/admin/feature/statistics',
            headers: {
                'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("admin")->plainTextToken ?? "" }}'
            },
            success: function(response) {
                $('#pending-count').text(response.data.pending_requests);
                $('#active-count').text(response.data.active_featured);
                $('#total-count').text(response.data.total_requests);
                $('#rejected-count').text(response.data.rejected_requests);
            }
        });
    }

    function loadRequests() {
        const params = new URLSearchParams({...filters, page: currentPage});
        
        $.ajax({
            url: '/api/admin/feature/requests?' + params.toString(),
            headers: {
                'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("admin")->plainTextToken ?? "" }}'
            },
            success: function(response) {
                renderRequests(response.data.data);
                renderPagination(response.data);
            }
        });
    }

    function renderRequests(requests) {
        const tbody = $('#requests-tbody');
        tbody.empty();

        if (requests.length === 0) {
            tbody.append('<tr><td colspan="9" class="text-center">No requests found</td></tr>');
            return;
        }

        requests.forEach(request => {
            const statusBadge = getStatusBadge(request.status);
            const typeBadge = getTypeBadge(request.product_type);
            const dateRange = request.start_date && request.end_date 
                ? `${request.start_date} to ${request.end_date}` 
                : 'Indefinite';

            const actions = request.status === 'pending' 
                ? `<button class="btn btn-success btn-sm btn-approve" data-id="${request.id}">Approve</button>
                   <button class="btn btn-danger btn-sm btn-reject" data-id="${request.id}">Reject</button>`
                : `<span class="text-muted">No actions</span>`;

            tbody.append(`
                <tr>
                    <td>${request.id}</td>
                    <td>${typeBadge}</td>
                    <td>${request.product_id}</td>
                    <td>${request.requester.name}</td>
                    <td>${statusBadge}</td>
                    <td>${request.priority_level}</td>
                    <td>${dateRange}</td>
                    <td>${new Date(request.created_at).toLocaleDateString()}</td>
                    <td>${actions}</td>
                </tr>
            `);
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-warning">Pending</span>',
            'approved': '<span class="badge badge-success">Approved</span>',
            'rejected': '<span class="badge badge-danger">Rejected</span>'
        };
        return badges[status] || status;
    }

    function getTypeBadge(type) {
        const badges = {
            'flight': '<span class="badge badge-primary">Flight</span>',
            'hotel': '<span class="badge badge-info">Hotel</span>',
            'package': '<span class="badge badge-secondary">Package</span>'
        };
        return badges[type] || type;
    }

    function renderPagination(data) {
        // Implement pagination rendering logic
    }

    function approveRequest(requestId) {
        const data = {
            priority_level: $('#approve-priority').val(),
            start_date: $('#approve-start-date').val(),
            end_date: $('#approve-end-date').val(),
            notes: $('#approve-notes').val()
        };

        $.ajax({
            url: `/api/admin/feature/approve/${requestId}`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("admin")->plainTextToken ?? "" }}'
            },
            data: data,
            success: function(response) {
                $('#approveModal').modal('hide');
                alert('Request approved successfully!');
                loadRequests();
                loadStatistics();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    }

    function rejectRequest(requestId) {
        const data = {
            rejection_reason: $('#reject-reason').val()
        };

        $.ajax({
            url: `/api/admin/feature/reject/${requestId}`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + '{{ auth()->user()->createToken("admin")->plainTextToken ?? "" }}'
            },
            data: data,
            success: function(response) {
                $('#rejectModal').modal('hide');
                alert('Request rejected successfully!');
                loadRequests();
                loadStatistics();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    }
});
</script>
@endpush
