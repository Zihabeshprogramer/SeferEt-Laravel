@extends('layouts.admin')

@section('title', 'Edit Admin User')
@section('page-title', 'Edit Admin User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.moderation') }}">User Moderation</a></li>
    <li class="breadcrumb-item active">Edit {{ $user->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit mr-2"></i>
                        Edit Admin User: {{ $user->name }}
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="{{ route('admin.users.moderation') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Users
                            </a>
                            @if(auth()->user()->getPermissionsViaRoles()->where('name', 'manage users')->isNotEmpty())
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')" 
                                        @if(auth()->id() === $user->id) disabled title="Cannot delete yourself" @endif>
                                    <i class="fas fa-trash mr-1"></i> Delete User
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('admin.users.update', $user) }}" method="POST" id="editUserForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-user mr-2"></i>Personal Information</h5>
                                
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Account Status *</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>
                                            Suspended
                                        </option>
                                        <option value="pending" {{ old('status', $user->status) === 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Current status: <span class="badge badge-{{ $user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'warning') }}">{{ ucfirst($user->status) }}</span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-key mr-2"></i>Security & Access</h5>
                                
                                <div class="form-group">
                                    <label for="password">New Password (Optional)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" placeholder="Leave blank to keep current password">
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Minimum 8 characters required if changing password</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Confirm new password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="permission_roles">Administrative Permission Roles *</label>
                                    <select class="form-control @error('permission_roles') is-invalid @enderror" 
                                            id="permission_roles" name="permission_roles[]" multiple size="5" required>
                                        @foreach($assignableRoles as $role)
                                            <option value="{{ $role->name }}" 
                                                    {{ in_array($role->name, old('permission_roles', $currentRoles)) ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', ucwords($role->name, '_')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('permission_roles')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Current roles: 
                                        @foreach($currentRoles as $role)
                                            <span class="badge badge-info mr-1">{{ str_replace('_', ' ', ucwords($role, '_')) }}</span>
                                        @endforeach
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Information -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3"><i class="fas fa-info-circle mr-2"></i>Account Information</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <tbody>
                                            <tr>
                                                <td><strong>User ID:</strong></td>
                                                <td>{{ $user->id }}</td>
                                                <td><strong>Account Created:</strong></td>
                                                <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Updated:</strong></td>
                                                <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                                                <td><strong>Email Verified:</strong></td>
                                                <td>
                                                    @if($user->email_verified_at)
                                                        <span class="badge badge-success">Verified</span>
                                                        <small class="text-muted">({{ $user->email_verified_at->format('M d, Y') }})</small>
                                                    @else
                                                        <span class="badge badge-warning">Not Verified</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($user->suspend_reason)
                                            <tr>
                                                <td><strong>Suspension Reason:</strong></td>
                                                <td colspan="3">
                                                    <span class="text-danger">{{ $user->suspend_reason }}</span>
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Update Admin User
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="resetForm()">
                                    <i class="fas fa-undo mr-2"></i>Reset Changes
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.users.moderation') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Back to User List
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Delete Admin User
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-user-times fa-4x text-danger mb-3"></i>
                        <h5>Are you sure you want to delete this admin user?</h5>
                        <p class="text-muted">This action cannot be undone. The user will be permanently removed from the system.</p>
                        <div class="alert alert-warning mt-3">
                            <strong>User to delete:</strong> <span id="deleteUserName"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash mr-1"></i> Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let userToDelete = null;
    
    // Initialize Select2 for roles using global function
    if (typeof window.initializeSelect2 === 'function') {
        $('#permission_roles').attr('data-placeholder', 'Select administrative roles');
        window.initializeSelect2('#permission_roles');
    } else {
        console.error('Global Select2 initialization function not available');
    }
    
    // Form submission
    $('#editUserForm').submit(function(e) {
        e.preventDefault();
        
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.href = response.redirect || '{{ route("admin.users.moderation") }}';
                    }, 1500);
                } else {
                    showAlert('error', response.message);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while updating the user.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    let errors = xhr.responseJSON.errors;
                    $('.form-control').removeClass('is-invalid');
                    $('.invalid-feedback').text('');
                    
                    Object.keys(errors).forEach(function(field) {
                        $('#' + field).addClass('is-invalid');
                        $('#' + field).siblings('.invalid-feedback').text(errors[field][0]);
                    });
                    
                    message = 'Please correct the errors below.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                showAlert('error', message);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Delete functionality
    window.confirmDelete = function(userId, userName) {
        userToDelete = userId;
        $('#deleteUserName').text(userName);
        $('#deleteModal').modal('show');
    };
    
    $('#confirmDeleteBtn').click(function() {
        if (!userToDelete) return;
        
        let btn = $(this);
        let originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
        
        $.ajax({
            url: '{{ route("admin.users.destroy", ":id") }}'.replace(':id', userToDelete),
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.href = '{{ route("admin.users.moderation") }}';
                    }, 1500);
                } else {
                    showAlert('error', response.message);
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                let message = xhr.responseJSON && xhr.responseJSON.message ? 
                    xhr.responseJSON.message : 
                    'An error occurred while deleting the user.';
                showAlert('error', message);
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Reset form
    window.resetForm = function() {
        if (confirm('Are you sure you want to reset all changes?')) {
            $('#editUserForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Reset Select2
            $('#permission_roles').val({!! json_encode($currentRoles) !!}).trigger('change');
            
            showAlert('info', 'Form has been reset to original values.');
        }
    };
    
    // Alert helper
    function showAlert(type, message) {
        let alertClass = type === 'success' ? 'alert-success' : 
                        type === 'error' ? 'alert-danger' : 
                        type === 'warning' ? 'alert-warning' : 'alert-info';
                        
        let iconClass = type === 'success' ? 'fa-check-circle' : 
                       type === 'error' ? 'fa-exclamation-circle' : 
                       type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        
        let alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                       '<i class="fas ' + iconClass + ' mr-2"></i>' + message +
                       '<button type="button" class="close" data-dismiss="alert">' +
                       '<span>&times;</span></button></div>';
        
        $('.card-body').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush

@push('styles')
<!-- Select2 is already included in base layout, only add theme CSS -->
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<style>
.form-group label {
    font-weight: 600;
    color: #495057;
}

select[multiple] {
    height: auto !important;
}

.badge {
    font-size: 0.8em;
}

.table td {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
}

.card-header .btn-group .btn {
    margin-left: 0.25rem;
}

.select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da;
}

.select2-container--bootstrap4 .select2-selection:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.alert {
    border-radius: 0.375rem;
}

.modal-header.bg-danger {
    border-color: #dc3545;
}

.text-danger {
    font-weight: 500;
}
</style>
@endpush
