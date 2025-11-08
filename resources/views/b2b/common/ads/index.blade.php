@extends('layouts.b2b')

@section('title', 'My Ads')
@section('page-title', 'My Ads')

@section('breadcrumb')
    <li class="breadcrumb-item active">My Ads</li>
@endsection

@section('content')
    <!-- Stats Row -->
    <div class="row" id="stats-row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="stat-total">0</h3>
                    <p>Total Ads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ad"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3 id="stat-draft">0</h3>
                    <p>Drafts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="stat-pending">0</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="stat-approved">0</h3>
                    <p>Approved</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="stat-rejected">0</h3>
                    <p>Rejected</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="stat-active">0</h3>
                    <p>Active Now</p>
                </div>
                <div class="icon">
                    <i class="fas fa-toggle-on"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-ad mr-2"></i>
                Ad Management
            </h3>
            <div class="card-tools">
                <a href="{{ route('b2b.ads.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Create New Ad
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Row -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status-filter">Filter by Status:</label>
                        <select id="status-filter" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div class="table-responsive">
                <table id="ads-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Schedule</th>
                            <th>Stats</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load stats
            loadStats();

            // Initialize DataTable
            var table = $('#ads-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('b2b.ads.data') }}',
                    data: function(d) {
                        d.status = $('#status-filter').val();
                    }
                },
                columns: [
                    { data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false },
                    { data: 'title', name: 'title' },
                    { data: 'product_info', name: 'product_info', orderable: false },
                    { data: 'status_badge', name: 'status' },
                    { data: 'schedule', name: 'schedule', orderable: false },
                    { data: 'stats', name: 'stats', orderable: false },
                    { data: 'created_at', name: 'created_at', render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }},
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[6, 'desc']],
                pageLength: 15
            });

            // Status filter change
            $('#status-filter').on('change', function() {
                table.ajax.reload();
            });

            // Delete ad
            $(document).on('click', '.delete-ad', function() {
                var adId = $(this).data('id');
                
                if (confirm('Are you sure you want to delete this ad? This action cannot be undone.')) {
                    $.ajax({
                        url: '/b2b/ads/' + adId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            toastr.success(response.message || 'Ad deleted successfully');
                            table.ajax.reload();
                            loadStats();
                        },
                        error: function(xhr) {
                            var message = xhr.responseJSON && xhr.responseJSON.message 
                                ? xhr.responseJSON.message 
                                : 'Failed to delete ad';
                            toastr.error(message);
                        }
                    });
                }
            });

            // Toggle active status
            $(document).on('click', '.toggle-active', function() {
                var adId = $(this).data('id');
                
                $.ajax({
                    url: '/b2b/ads/' + adId + '/toggle-active',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Ad status updated');
                        table.ajax.reload();
                        loadStats();
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'Failed to update ad status';
                        toastr.error(message);
                    }
                });
            });

            // Load statistics
            function loadStats() {
                $.get('{{ route('b2b.ads.stats') }}', function(response) {
                    if (response.success && response.data) {
                        $('#stat-total').text(response.data.total);
                        $('#stat-draft').text(response.data.draft);
                        $('#stat-pending').text(response.data.pending);
                        $('#stat-approved').text(response.data.approved);
                        $('#stat-rejected').text(response.data.rejected);
                        $('#stat-active').text(response.data.active);
                    }
                });
            }
        });
    </script>
@endsection
