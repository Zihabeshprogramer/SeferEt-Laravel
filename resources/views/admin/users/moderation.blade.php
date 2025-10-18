@extends('layouts.admin')

@section('title', 'User Moderation')
@section('page-title', 'User Moderation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">User Moderation</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Management</h3>
                    <div class="card-tools">
                        @if(auth()->user()->getPermissionsViaRoles()->where('name', 'create admin users')->isNotEmpty())
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createAdminModal">
                                <i class="fas fa-plus"></i> Create Admin User
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="roleFilter" class="form-control">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ str_replace('_', ' ', ucwords($role, '_')) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="permissionRoleFilter" class="form-control">
                                <option value="">All Permission Roles</option>
                                @foreach($permissionRoles as $permissionRole)
                                    <option value="{{ $permissionRole->name }}">{{ str_replace('_', ' ', ucwords($permissionRole->name, '_')) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Permission Roles</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Admin Modal -->
    @if(auth()->user()->getPermissionsViaRoles()->where('name', 'create admin users')->isNotEmpty())
    <div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog" aria-labelledby="createAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAdminModalLabel">Create Admin User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createAdminForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <span class="text-danger" id="name-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <span class="text-danger" id="email-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="text-danger" id="password-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password *</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            <span class="text-danger" id="password_confirmation-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">User Role *</label>
                            <select class="form-control" id="role" name="role" required readonly>
                                <option value="admin" selected>Admin (Only admin users can be created)</option>
                            </select>
                            <small class="form-text text-muted">Only admin users can be created through this interface.</small>
                            <span class="text-danger" id="role-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="permission_roles">Administrative Permission Roles *</label>
                            <select class="form-control" id="permission_roles" name="permission_roles[]" multiple required>
                                @foreach($permissionRoles as $permissionRole)
                                    <option value="{{ $permissionRole->name }}">{{ str_replace('_', ' ', ucwords($permissionRole->name, '_')) }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select one or more administrative roles. Admin can have multiple permission roles.</small>
                            <span class="text-danger" id="permission_roles-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Change User Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="statusForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="statusUserId" name="user_id">
                        <input type="hidden" id="statusAction" name="status">
                        
                        <p id="statusMessage"></p>
                        
                        <div class="form-group" id="reasonGroup" style="display: none;">
                            <label for="reason">Reason (Optional)</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter reason for this action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="statusSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <style>
        .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .badge {
            font-size: 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const usersTable = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.users.moderation") }}',
                    data: function(d) {
                        d.role = $('#roleFilter').val();
                        d.status = $('#statusFilter').val();
                        d.permission_role = $('#permissionRoleFilter').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'role_badge', name: 'role', orderable: false},
                    {data: 'roles', name: 'roles', orderable: false},
                    {data: 'status_badge', name: 'status', orderable: false},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                order: [[0, 'desc']],
                responsive: true,
                pageLength: 25
            });

            // Filter functionality
            $('#roleFilter, #statusFilter, #permissionRoleFilter').on('change', function() {
                usersTable.draw();
            });

            $('#clearFilters').on('click', function() {
                $('#roleFilter, #statusFilter, #permissionRoleFilter').val('');
                usersTable.draw();
            });

            // Create Admin Form Submit
            $('#createAdminForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                $('.text-danger').text('');
                
                $.ajax({
                    url: '{{ route("admin.users.store-admin") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#createAdminModal').modal('hide');
                            $('#createAdminForm')[0].reset();
                            usersTable.draw();
                            
                            // Show success message
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key + '-error').text(value[0]);
                            });
                        } else {
                            toastr.error('An error occurred while creating the admin user.');
                        }
                    }
                });
            });

            // Status Form Submit
            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                
                const userId = $('#statusUserId').val();
                const status = $('#statusAction').val();
                const reason = $('#reason').val();
                
                $.ajax({
                    url: '{{ url("admin/users") }}/' + userId + '/status',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status,
                        reason: reason
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#statusModal').modal('hide');
                            $('#statusForm')[0].reset();
                            usersTable.draw();
                            
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred while updating user status.');
                    }
                });
            });
        });

        // Global function for status change buttons
        function setUserStatus(userId, status) {
            $('#statusUserId').val(userId);
            $('#statusAction').val(status);
            
            let message = '';
            let showReason = false;
            
            switch(status) {
                case 'active':
                    message = 'Are you sure you want to activate this user?';
                    break;
                case 'suspended':
                    message = 'Are you sure you want to suspend this user?';
                    showReason = true;
                    break;
                case 'rejected':
                    message = 'Are you sure you want to reject this user?';
                    showReason = true;
                    break;
            }
            
            $('#statusMessage').text(message);
            $('#reasonGroup').toggle(showReason);
            $('#statusSubmitBtn').text('Confirm ' + status.charAt(0).toUpperCase() + status.slice(1));
            $('#statusModal').modal('show');
        }
        
        // Global function for user deletion
        function confirmDeleteUser(userId, userName) {
            if (confirm('Are you sure you want to permanently delete the admin user "' + userName + '"?\n\nThis action cannot be undone.')) {
                $.ajax({
                    url: '{{ url("admin/users") }}/' + userId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            usersTable.draw();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred while deleting the user.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message);
                    }
                });
            }
        }
    </script>
@endpush
