@extends('layouts.admin')

@section('title', 'Edit Profile')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Edit Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                </div>
                
                <form action="{{ route('admin.profile.update', $user->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="card-body">
                        <!-- Name Field -->
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Email Field -->
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Role Display -->
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ ucfirst($user->role) }}" 
                                   readonly>
                            <small class="form-text text-muted">Role cannot be changed from this interface.</small>
                        </div>
                        
                        <!-- Password Change Section -->
                        <hr>
                        <h5>Change Password</h5>
                        <p class="text-muted">Leave password fields empty if you don't want to change your password.</p>
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   autocomplete="new-password">
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Update Profile
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Account Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>User ID:</strong></td>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td>
                                <span class="badge badge-success">{{ ucfirst($user->role) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge badge-primary">{{ ucfirst($user->status ?? 'active') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Joined:</strong></td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Security Tips -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt mr-2"></i>Security Tips
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success mr-2"></i>Use a strong password (8+ characters)</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Include numbers and symbols</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Don't reuse passwords from other accounts</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Keep your email address up to date</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Show password strength indicator
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        // Number check
        if (/\d/.test(password)) strength++;
        // Lowercase check
        if (/[a-z]/.test(password)) strength++;
        // Uppercase check  
        if (/[A-Z]/.test(password)) strength++;
        // Special character check
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        var strengthText = '';
        var strengthClass = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Very Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
                strengthText = 'Weak';
                strengthClass = 'text-warning';
                break;
            case 3:
                strengthText = 'Fair';
                strengthClass = 'text-info';
                break;
            case 4:
                strengthText = 'Good';
                strengthClass = 'text-primary';
                break;
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }
        
        // Remove existing feedback
        $('#password').next('.password-strength').remove();
        
        if (password.length > 0) {
            $('#password').after('<div class="password-strength small ' + strengthClass + '">Password strength: ' + strengthText + '</div>');
        }
    });
</script>
@endsection
