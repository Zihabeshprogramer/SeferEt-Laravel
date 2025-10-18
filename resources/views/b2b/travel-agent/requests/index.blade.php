@extends('layouts.b2b')

@section('title', 'Flight Requests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Flight Requests</h1>
                    <p class="text-muted">Manage requests from other travel agents for your flights</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="refreshRequests()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="filterRequests('all')">All Requests</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRequests('pending')">Pending</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRequests('approved')">Approved</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRequests('rejected')">Rejected</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Requests
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingCount">0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Approved This Month
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="approvedCount">0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-left-danger">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Expiring Soon
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="expiringSoonCount">0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Response Rate
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="responseRate">0%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="requestsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Travel Agent</th>
                                    <th>Package</th>
                                    <th>Service Type</th>
                                    <th>Flight Details</th>
                                    <th>Dates</th>
                                    <th>Seats</th>
                                    <th>Status</th>
                                    <th>Deadline</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="requestsTableBody">
                                <!-- Table content will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalTitle">Respond to Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="responseForm">
                    <input type="hidden" id="requestId" name="request_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Response</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="response" id="responseApprove" value="approve">
                            <label class="form-check-label text-success" for="responseApprove">
                                <i class="fas fa-check-circle me-1"></i> Approve Request
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="response" id="responseReject" value="reject">
                            <label class="form-check-label text-danger" for="responseReject">
                                <i class="fas fa-times-circle me-1"></i> Reject Request
                            </label>
                        </div>
                    </div>

                    <div id="approvalFields" class="approval-fields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Confirmed Price per Seat</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="confirmedPrice" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Currency</label>
                                <select class="form-select" id="currency">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Terms & Conditions (Optional)</label>
                            <textarea class="form-control" id="termsConditions" rows="3" placeholder="Any specific terms or conditions for this booking..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Response Notes</label>
                        <textarea class="form-control" id="providerNotes" rows="3" placeholder="Provide details about your response..." required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="notifyAgent" checked>
                        <label class="form-check-label" for="notifyAgent">
                            Notify travel agent via email
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitResponse()">Send Response</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentFilter = 'all';
let currentRequestId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadRequests();
    loadStats();
    
    // Set up response type toggle
    document.querySelectorAll('input[name="response"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const approvalFields = document.getElementById('approvalFields');
            if (this.value === 'approve') {
                approvalFields.style.display = 'block';
            } else {
                approvalFields.style.display = 'none';
            }
        });
    });
});

function loadRequests() {
    const tableBody = document.getElementById('requestsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading requests...</div>
            </td>
        </tr>
    `;
    
    // Build query parameters
    const params = new URLSearchParams({
        status: currentFilter,
        per_page: 25
    });
    
    // Make API call to get requests (travel agent route)
    fetch(`{{ route('b2b.travel-agent.requests.data') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRequests(data.data.data);
            } else {
                displayError(data.message || 'Failed to load requests');
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            displayError('Failed to load requests. Please try again.');
        });
}

function displayRequests(requests) {
    const tableBody = document.getElementById('requestsTableBody');
    
    let html = '';
    requests.forEach(request => {
        const statusBadge = getStatusBadge(request.status);
        const urgencyClass = request.days_left <= 1 ? 'border-start border-danger border-3' : request.days_left <= 3 ? 'border-start border-warning border-3' : '';
        
        html += `
            <tr class="${urgencyClass}">
                <td class="font-weight-bold">${request.uuid || request.id}</td>
                <td>${request.travel_agent ? request.travel_agent.name : 'N/A'}</td>
                <td>${request.package ? request.package.title : 'N/A'}</td>
                <td>${request.service_type}</td>
                <td class="service-details">${request.service_details}</td>
                <td>
                    <small>${request.dates.formatted}</small>
                </td>
                <td><strong>${request.quantity || 'N/A'}</strong></td>
                <td>${statusBadge}</td>
                <td>
                    <small>${request.deadline ? new Date(request.deadline).toLocaleDateString() : 'No deadline'}</small>
                    <br>
                    ${request.days_left !== null ? `
                    <span class="text-${request.days_left <= 1 ? 'danger' : request.days_left <= 3 ? 'warning' : 'muted'}">
                        ${request.days_left} days left
                    </span>
                    ` : ''}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="viewRequest(${request.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${request.status === 'pending' ? `
                            <button class="btn btn-outline-success" onclick="respondToRequest(${request.id}, 'approve')" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="respondToRequest(${request.id}, 'reject')" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    
    if (html === '') {
        html = `
            <tr>
                <td colspan="10" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <br>No requests found
                </td>
            </tr>
        `;
    }
    
    tableBody.innerHTML = html;
}

function displayError(message) {
    const tableBody = document.getElementById('requestsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4 text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <br>${message}
                <br><button class="btn btn-sm btn-outline-primary mt-2" onclick="loadRequests()">Try Again</button>
            </td>
        </tr>
    `;
}

function loadStats() {
    fetch('{{ route('b2b.travel-agent.requests.stats') }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('pendingCount').textContent = data.stats.pending_count;
                document.getElementById('approvedCount').textContent = data.stats.approved_this_month;
                document.getElementById('expiringSoonCount').textContent = data.stats.expiring_soon_count;
                document.getElementById('responseRate').textContent = data.stats.response_rate + '%';
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            // Keep default values on error
        });
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge badge-warning">Pending</span>',
        'approved': '<span class="badge badge-success">Approved</span>',
        'rejected': '<span class="badge badge-danger">Rejected</span>',
        'expired': '<span class="badge badge-secondary">Expired</span>',
        'cancelled': '<span class="badge badge-dark">Cancelled</span>'
    };
    return badges[status] || '<span class="badge badge-light">Unknown</span>';
}

function viewRequest(requestId) {
    currentRequestId = requestId;
    
    // Show loading state
    document.getElementById('requestDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading request details...</div>
        </div>
    `;
    
    // Fetch request details (travel agent route)
    fetch(`{{ route('b2b.travel-agent.requests.show', '') }}/${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.data;
                const details = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Request Information</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Request ID:</strong></td><td>${request.uuid}</td></tr>
                                <tr><td><strong>Travel Agent:</strong></td><td>${request.travel_agent.name}</td></tr>
                                <tr><td><strong>Package:</strong></td><td>${request.package ? request.package.title : 'N/A'}</td></tr>
                                <tr><td><strong>Service Type:</strong></td><td>${request.service_type}</td></tr>
                                <tr><td><strong>Seats Requested:</strong></td><td><strong>${request.request_details.quantity}</strong></td></tr>
                                <tr><td><strong>Status:</strong></td><td>${getStatusBadge(request.status)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Flight Details</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Flight:</strong></td><td>${request.service_details}</td></tr>
                                <tr><td><strong>Departure:</strong></td><td>${request.request_details.start_date}</td></tr>
                                <tr><td><strong>Return:</strong></td><td>${request.request_details.end_date || 'One way'}</td></tr>
                                <tr><td><strong>Passengers:</strong></td><td>${request.request_details.guest_count || 'Not specified'}</td></tr>
                                ${request.pricing.offered_price ? `<tr><td><strong>Offered Price:</strong></td><td>${request.pricing.offered_price} ${request.pricing.currency}</td></tr>` : ''}
                            </table>
                        </div>
                    </div>
                    ${request.notes.agent_notes ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Travel Agent Notes</h6>
                            <div class="bg-light p-3 rounded">
                                ${request.notes.agent_notes}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    ${request.request_details.special_requirements ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Special Requirements</h6>
                            <div class="bg-light p-3 rounded">
                                ${request.request_details.special_requirements}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Timeline</h6>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <small class="text-muted">${new Date(request.timeline.created_at).toLocaleString()}</small>
                                    <div>Request submitted</div>
                                </div>
                                ${request.timeline.responded_at ? `
                                <div class="timeline-item">
                                    <small class="text-muted">${new Date(request.timeline.responded_at).toLocaleString()}</small>
                                    <div>Response provided</div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Contact Information</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Agent:</strong></td><td>${request.travel_agent.name}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${request.travel_agent.email}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${request.travel_agent.phone || 'Not provided'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                document.getElementById('requestDetailsContent').innerHTML = details;
            } else {
                document.getElementById('requestDetailsContent').innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <br>Failed to load request details
                        <br><small>${data.message}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading request details:', error);
            document.getElementById('requestDetailsContent').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <br>Error loading request details
                    <br><small>Please try again later</small>
                </div>
            `;
        });
    
    new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
}

function respondToRequest(requestId, defaultResponse = null) {
    currentRequestId = requestId;
    document.getElementById('requestId').value = requestId;
    document.getElementById('responseModalTitle').textContent = `Respond to Request ${requestId}`;
    
    // Reset form
    document.getElementById('responseForm').reset();
    document.getElementById('approvalFields').style.display = 'none';
    
    // Set default response if provided
    if (defaultResponse) {
        document.getElementById('response' + (defaultResponse === 'approve' ? 'Approve' : 'Reject')).checked = true;
        if (defaultResponse === 'approve') {
            document.getElementById('approvalFields').style.display = 'block';
        }
    }
    
    new bootstrap.Modal(document.getElementById('responseModal')).show();
}

function submitResponse() {
    const form = document.getElementById('responseForm');
    const formData = new FormData(form);
    
    // Validate form
    const response = formData.get('response');
    const notes = document.getElementById('providerNotes').value;
    
    if (!response) {
        alert('Please select a response (Approve or Reject)');
        return;
    }
    
    if (!notes.trim()) {
        alert('Please provide response notes');
        return;
    }
    
    // Show loading state
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    submitBtn.disabled = true;
    
    // Prepare request data
    const requestData = {
        provider_notes: notes,
        notify_agent: document.getElementById('notifyAgent').checked
    };
    
    if (response === 'approve') {
        requestData.confirmed_price = document.getElementById('confirmedPrice').value;
        requestData.currency = document.getElementById('currency').value;
        requestData.terms_conditions = document.getElementById('termsConditions').value;
    } else {
        requestData.rejection_reason = notes;
    }
    
    // Make API call (travel agent routes)
    let approveUrl = "{{ route('b2b.travel-agent.requests.approve', ['id' => ':id']) }}";
    let rejectUrl = "{{ route('b2b.travel-agent.requests.reject', ['id' => ':id']) }}";
    const url = response === 'approve' 
        ? approveUrl.replace(':id', currentRequestId)
        : rejectUrl.replace(':id', currentRequestId);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || `Request ${response === 'approve' ? 'approved' : 'rejected'} successfully!`);
            bootstrap.Modal.getInstance(document.getElementById('responseModal')).hide();
            loadRequests(); // Reload the requests
            loadStats(); // Reload stats
        } else {
            alert(data.message || 'Failed to process request');
            if (data.errors) {
                Object.values(data.errors).flat().forEach(error => {
                    alert(error);
                });
            }
        }
    })
    .catch(error => {
        console.error('Error submitting response:', error);
        alert('Network error occurred. Please try again.');
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function filterRequests(status) {
    currentFilter = status;
    loadRequests();
}

function refreshRequests() {
    loadRequests();
    loadStats();

}
</script>
@endpush
